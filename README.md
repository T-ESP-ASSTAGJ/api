# Jamly API

Jamly est une plateforme sociale de partage musical. Ce dépôt contient le back-end REST, construit avec Symfony 7 et API Platform 4.

---

## Table des matières

1. [Architecture](#architecture)
2. [Stack technique](#stack-technique)
3. [Groupes de sérialisation](#groupes-de-sérialisation)
4. [Démarrage rapide](#démarrage-rapide)
5. [Commandes utiles](#commandes-utiles)
6. [Couverture de code](#couverture-de-code)
7. [Plugins PHPStorm recommandés](#plugins-phpstorm-recommandés)
8. [Workflow Git](#workflow-git)

---

## Architecture

### Vue d'ensemble

```
      ┌──────────────────────────────────────────────┐
      │               FrankenPHP / Caddy             │
      │    (serveur HTTP + Mercure hub + TLS auto)   │
      └────────────────────┬─────────────────────────┘
                           │ HTTP
      ┌────────────────────▼─────────────────────────┐
      │            Symfony 7 / API Platform 4        │
      │                                              │
      │         State Processors / Providers         │
      │   (point d'entrée unique — pas de Controller │
      │    sauf DevEmailPreviewController en dev)    │
      │                    ↓                         │
      │               Services                       │
      │             ↓         ↓                      │
      │       Repositories   (Azure, Firebase,       │
      │            ↓          Spotify, Mercure…)     │
      │       Doctrine ORM                           │
      │            ↓                                 │
      │        PostgreSQL                            │
      └──────┬────────────────┬──────────────────────┘
             │ bus messages   │ transport + cache + vues
┌────────────▼────┐   ┌───────▼────────────────────────┐
│Symfony Messenger│   │           Redis                │
│(async workers)  │   │ • transport Messenger (async)  │
└──────────────┬──┘   │ • cache applicatif             │
               │      │ • compteur de vues + debounce  │
               │      └────────────────────────────────┘
               │
┌──────────────▼──────────────────────────────────────────┐
│           Message Handlers (async)                      │
│  Notifications push · Compteur de vues · Emails         │
└─────────────────────────────────────────────────────────┘
```

### Choix techniques et justifications

#### FrankenPHP + Caddy — plutôt que PHP-FPM + Nginx

FrankenPHP est un runtime PHP écrit en Go qui intègre Caddy comme serveur web.  
Avantages retenus :

- **Worker mode** : le processus PHP reste en mémoire entre les requêtes, ce qui élimine le coût de bootstrap de Symfony à chaque appel (gain de latence significatif en production).
- **Mercure intégré** : le hub Mercure (SSE temps réel) tourne dans le même processus Caddy, pas besoin d'un service séparé.
- **HTTPS automatique** : Caddy gère les certificats TLS (Let's Encrypt en prod, auto-signé en dev) sans configuration manuelle.
- **HTTP/2 et HTTP/3** activés nativement.
- **Un seul conteneur** pour le PHP runtime, le serveur web et le hub Mercure, ce qui simplifie le `compose.yaml`.

#### API Platform 4 — plutôt qu'un CRUD Symfony classique

API Platform génère automatiquement les routes CRUD, la documentation OpenAPI/Swagger et la gestion de la pagination à partir des attributs PHP.  
Le pattern **State Processor / State Provider** permet de brancher une logique métier fine sans casser la structure déclarative des ressources.  
Avantages :

- Moins de code répétitif (pas de contrôleur par entité).
- Documentation Swagger toujours à jour (`/api/docs`).
- Sérialisation et validation (Symfony Validator) intégrées.

#### PostgreSQL — plutôt que MySQL

PostgreSQL a été choisi pour ses types avancés (JSONB, tableaux, full-text search natif) et pour sa robustesse sur des schémas relationnels complexes (graphe social, conversations, etc.).

#### Redis — trois usages distincts

Redis joue trois rôles dans le projet :

**1. Transport Symfony Messenger**  
Le DSN `redis://jamly-redis:6379/messages` est configuré comme transport `async`. Les messages (like, follow, etc.) sont mis en file dans Redis et dépilés par le conteneur `messenger-worker`. L'alternative commentée dans `.env` était RabbitMQ (AMQP) — Redis a été retenu pour sa légèreté, sachant qu'il était déjà présent pour les deux autres rôles.

**2. Cache applicatif**  
L'adaptateur `cache.adapter.redis` est configuré dans `cache.yaml` comme cache par défaut de l'application.

**3. Compteur de vues des posts avec anti-rebond**  
Les vues sont incrémentées directement dans Redis via `PostViewCounter` (clé `post:views:{id}`) pour éviter une écriture en base à chaque scroll. Un mécanisme de debounce (clé `post:view:debounce:{postId}:{userId}` avec TTL) empêche de compter plusieurs vues du même utilisateur sur une courte fenêtre. Le `PersistPostViewsMessage` (traité en async) flush périodiquement le compteur Redis en base via `PostViewsPersistenceService`.

#### Symfony Messenger (workers asynchrones) — plutôt qu'un traitement synchrone

Les opérations coûteuses ou non-bloquantes (envoi de notifications push Firebase, emails, persistance des vues) sont **dépilées du cycle requête/réponse** via le bus de messages.  
Cela garantit un temps de réponse court pour l'utilisateur final, même si l'envoi de la notification met quelques secondes.

Messages actuels :

| Message                   | Handler                                 | Déclencheur                     |
|---------------------------|-----------------------------------------|---------------------------------|
| `CommentCreatedMessage`   | Notification push à l'auteur du post    | Nouveau commentaire             |
| `LikeCreatedMessage`      | Notification push                       | Nouveau like                    |
| `FollowCreatedMessage`    | Notification push                       | Nouveau follow                  |
| `MessageCreatedMessage`   | Notification push + event Mercure       | Nouveau message de conversation |
| `PersistPostViewsMessage` | Persistance en base du compteur de vues | Scroll du feed                  |

#### JWT sans mot de passe (Passwordless) — plutôt qu'un login classique

L'authentification repose sur un **code OTP envoyé par email** (via Google SMTP / Symfony Mailer).  
Aucun mot de passe n'est jamais stocké, ce qui réduit la surface d'attaque (pas de hash à protéger, pas de reset password).  
Le JWT retourné après vérification est signé avec une paire de clés RSA (lexik/jwt-authentication-bundle).

#### Azure Blob Storage + Flysystem — pour les médias

Les fichiers uploadés (photos de profil, images de posts) sont stockés sur **Azure Blob Storage**.  
La couche d'abstraction **Flysystem** permet de changer de provider (S3, GCS, local…) sans toucher au code applicatif : seule la configuration change.

#### Firebase Cloud Messaging — pour les push notifications

L'intégration Firebase (kreait/firebase-bundle) gère les push notifications mobiles (iOS & Android).  
Les tokens FCM des appareils sont stockés en base et associés aux utilisateurs.

### Structure des dossiers `src/`

```
src/
├── ApiResource/       # Déclarations API Platform (attributs #[ApiResource])
│   ├── Auth/          # Endpoints d'authentification (OTP, JWT)
│   ├── User/          # Profil, paramètres
│   ├── Post/          # Posts musicaux
│   ├── Feed/          # Fil d'actualité
│   ├── Conversation/  # Messagerie privée
│   ├── Message/       # Messages dans une conversation
│   ├── Follow/        # Abonnements
│   ├── Like/          # Likes
│   ├── Comment/       # Commentaires
│   ├── Track/         # Données musicales (titres)
│   ├── Artist/        # Artistes
│   ├── Spotify/       # Intégration Spotify (OAuth + recherche)
│   ├── Mercure/       # Génération du token Mercure
│   ├── Search/        # Recherche globale
│   └── Report/        # Signalement de contenu
├── Entity/            # Entités Doctrine (modèle de données)
├── Repository/        # Requêtes SQL via Doctrine
├── State/             # State Processors & Providers (logique métier API Platform)
├── Service/           # Services applicatifs (Azure, Spotify, push notifs…)
├── Message/           # Objets messages pour le bus async
├── MessageHandler/    # Handlers correspondants
├── EventListener/     # Listeners Doctrine (slugs, timestamps…)
├── EventSubscriber/   # Subscribers Symfony
├── Validator/         # Contraintes de validation custom
├── Util/              # Helpers utilitaires
└── Factory/           # Factories Foundry (fixtures de test)
```

---

## Stack technique

| Composant          | Technologie                     | Version |
|--------------------|---------------------------------|---------|
| Runtime PHP        | FrankenPHP                      | latest  |
| Framework          | Symfony                         | 7.3     |
| API REST           | API Platform                    | 4.1     |
| ORM                | Doctrine ORM                    | 3.x     |
| Base de données    | PostgreSQL                      | 17      |
| Cache / Queue      | Redis                           | 7       |
| Auth               | lexik/jwt-authentication-bundle | 3.x     |
| Temps réel         | Mercure (intégré Caddy)         | —       |
| Fichiers           | Azure Blob Storage + Flysystem  | —       |
| Push notifications | Firebase Cloud Messaging        | —       |
| Emails             | Symfony Mailer + Google SMTP    | —       |
| Musique            | Spotify Web API                 | —       |
| Conteneurisation   | Docker Compose                  | v2.10+  |

---

## Groupes de sérialisation

La visibilité des champs dans les réponses JSON est contrôlée exclusivement via les **groupes de sérialisation Symfony** (`#[Groups([...])]`). Il n'y a pas de `toArray()` manuel : API Platform applique automatiquement le `normalizationContext` de l'opération appelée.

### Convention de nommage

Chaque entité déclare ses groupes comme constantes de classe, selon le schéma `<entité>:<niveau>` :

```php
public const SERIALIZATION_GROUP_READ   = 'post:read';    // liste / résumé
public const SERIALIZATION_GROUP_DETAIL = 'post:detail';  // item complet
public const SERIALIZATION_GROUP_WRITE  = 'post:write';   // désérialisation (input)
```

Certaines entités ajoutent des groupes spécialisés :

| Suffixe    | Entité    | Rôle                                                             |
|------------|-----------|------------------------------------------------------------------|
| `:mercure` | `Message` | Payload des événements SSE Mercure — sous-ensemble compact       |
| `:update`  | `Message` | Champs autorisés lors d'un PATCH (édition du contenu uniquement) |

---

### Niveaux `read` vs `detail`

| Groupe            | Utilisé sur                        | Champs exposés                                                                                    |
|-------------------|------------------------------------|---------------------------------------------------------------------------------------------------|
| `user:read`       | `GET /users` (collection)          | `id`, `username`, `profilePicture`                                                                |
| `user:detail`     | `GET /users/me`, `GET /users/{id}` | Tout `user:read` + `email`, `bio`, `isVerified`, `parameters`…                                    |
| `post:read`       | Feed, collections                  | `id`, `user`, `caption`, `track`, `frontImage`, `backImage`, `likesCount`, `viewsCount`…          |
| `post:detail`     | `GET /posts/{id}`                  | Identique à `post:read` (Post n'a pas de champs cachés en liste)                                  |
| `message:read`    | Liste des messages                 | `id`, `author`, `type`, `content`, `track`, `createdAt`                                           |
| `message:detail`  | Item message                       | Même chose + `conversationId`                                                                     |
| `message:mercure` | Événement SSE                      | Sous-ensemble compact : `id`, `conversationId`, `author`, `type`, `content`, `track`, `createdAt` |

> **Règle** : un champ qui n'a aucun `#[Groups]` n'est jamais exposé dans aucune réponse.

---

### Groupes croisés

Un champ peut appartenir à un groupe d'une **autre** entité. C'est utilisé pour éviter de créer des sous-resources inutiles quand un objet imbriqué doit exposer certains champs dans un contexte précis.

Exemple — le champ `profilePicture` de `User` porte les groupes suivants :

```php
#[Groups([
    User::SERIALIZATION_GROUP_READ,
    User::SERIALIZATION_GROUP_DETAIL,
    Message::SERIALIZATION_GROUP_READ,      // affiché dans la liste des messages
    Message::SERIALIZATION_GROUP_MERCURE,   // affiché dans l'événement SSE
    Post::SERIALIZATION_GROUP_READ,         // affiché dans le feed
    Conversation::SERIALIZATION_GROUP_READ, // affiché dans la liste des conversations
])]
private ?string $profilePicture = null;
```

Cela signifie que quand l'API sérialise un `Message` avec le groupe `message:read`, l'auteur imbriqué (`User`) exposera automatiquement `id`, `username` et `profilePicture` — et rien d'autre (pas l'email, pas le token FCM).

---

### Désérialisation (écriture)

Les groupes `*:write` contrôlent quels champs sont acceptés en entrée. API Platform ignore silencieusement tout champ envoyé par le client qui n'appartient pas au groupe de désérialisation de l'opération.

Exemple sur `Message` :

```php
// message:write → champs acceptés à la création
#[Groups([self::SERIALIZATION_GROUP_WRITE])]
private Conversation $conversation;

#[Groups([self::SERIALIZATION_GROUP_WRITE, self::SERIALIZATION_GROUP_UPDATE])]
private ?string $content = null;

// message:update → seul ce champ est modifiable via PATCH
// (conversation n'appartient pas à :update → non modifiable)
```

---

### Ajouter un champ à une réponse

1. Localiser la propriété dans l'entité.
2. Ajouter le groupe correspondant à l'opération souhaitée dans `#[Groups([...])]`.
3. Si le champ doit apparaître dans le payload Mercure, ajouter aussi `Message::SERIALIZATION_GROUP_MERCURE`.
4. Aucune modification de contrôleur ou de processor n'est nécessaire.

---

## Démarrage rapide

### Prérequis

- [Docker Compose](https://docs.docker.com/compose/install/) v2.10+
- Fichier `.env.local` configuré (copier `.env` et remplir les variables)

### Installation

```bash
make install   # Build des images + composer install + génération des clés JWT
make start     # Démarre tous les conteneurs
```

L'API est disponible sur `https://localhost` (ou le port défini dans `.env.local`).  
La documentation Swagger est sur `https://localhost/api/docs`.

### Arrêt

```bash
make stop
```

---

## Commandes utiles

```bash
# Vider le cache Symfony
docker exec -it jamly-api bin/console cache:clear

# Créer un fichier de migration
docker exec -it jamly-api bin/console make:migration

# Jouer les migrations
docker exec -it jamly-api bin/console doctrine:migrations:migrate

# Rollback de la dernière migration
docker exec -it jamly-api bin/console doctrine:migrations:migrate prev

# Afficher toutes les routes
docker exec -it jamly-api bin/console debug:router

# Charger les fixtures (données de test)
docker exec -it jamly-api bin/console doctrine:fixtures:load

# Se connecter à Redis
make redis-connect
```

### Connexion JWT via Swagger

1. `POST /api/auth/request` avec `{ "email": "..." }`
2. Récupérer le code dans les logs : `docker logs jamly-api | grep "Verification code"`
3. `POST /api/auth/verify` avec le code reçu
4. Copier le `token` de la réponse
5. Cliquer sur **Authorize** dans Swagger, saisir `Bearer <token>`

---

## Couverture de code

### Lancer les tests avec couverture

```bash
make unit-test
```

La commande active XDebug en mode `coverage` et génère trois formats de rapport :

| Format         | Emplacement                | Usage                                                          |
|----------------|----------------------------|----------------------------------------------------------------|
| **HTML**       | `coverage/html/index.html` | Navigation visuelle par fichier et par méthode                 |
| **Clover XML** | `coverage/clover.xml`      | Intégration CI / SonarQube                                     |
| **Console**    | stdout                     | Résumé immédiat dans le terminal, fichiers non couverts inclus |

> Le dossier `coverage/` est ignoré par Git (`.gitignore`). Ouvrir `coverage/html/index.html` dans un navigateur après avoir lancé les tests.

### Périmètre de couverture

PHPUnit mesure la couverture sur `src/` en **excluant** les répertoires sans logique à tester :

| Exclu               | Raison                                                  |
|---------------------|---------------------------------------------------------|
| `src/Constants/`    | Constantes pures, pas de branche à couvrir              |
| `src/DataFixtures/` | Code d'initialisation de données, pas de logique métier |
| `src/Kernel.php`    | Fichier d'amorçage Symfony, non testé en unitaire       |

### Deux familles de tests

#### Tests unitaires — `PHPUnit\Framework\TestCase`

Utilisés pour les **State Processors/Providers** et les **Services** : toutes les dépendances sont mockées, aucune base de données n'est sollicitée.

```
tests/State/         # Processors & providers (logique métier API Platform)
tests/Service/       # Services applicatifs (Azure, Spotify, ImageService…)
tests/MessageHandler/ # Handlers async (notifications, vues…)
tests/Entity/        # Getters/setters et invariants des entités
tests/ApiResource/   # DTOs d'entrée/sortie
tests/Validator/     # Contraintes de validation custom
tests/Util/
```

Exemple de structure :

```php
class PostCreateProcessorTest extends TestCase
{
    protected function setUp(): void
    {
        // Toutes les dépendances sont mockées
        $this->em        = $this->createMock(EntityManagerInterface::class);
        $this->security  = $this->createMock(Security::class);
        $this->processor = new PostCreateProcessor(...);
    }

    public function testCreatesPost(): void { ... }
    public function testThrowsWhenUnauthenticated(): void { ... }
    public function testThrowsOnValidationFailure(): void { ... }
}
```

#### Tests d'intégration — `KernelTestCase` + Foundry + `ResetDatabase`

Utilisés pour les **Repositories** et quelques scenarios d'intégration : le kernel Symfony est booté, une vraie base de données de test est utilisée, et remise à zéro avant chaque test via `ResetDatabase`.

```
tests/Repository/    # Requêtes Doctrine sur une vraie base PostgreSQL
```

Les factories **Zenstruck Foundry** (`src/Factory/`) créent les fixtures à la volée :

```php
class UserRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase; // rollback automatique entre les tests

    public function testFindByUsername(): void
    {
        UserFactory::createOne(['username' => 'alice']);
        // ...
    }
}
```

#### Tests fonctionnels API — `ApiTestCase`

La classe `tests/ApiTestCase.php` étend `ApiPlatform\Symfony\Bundle\Test\ApiTestCase` et expose une méthode `createAuthenticatedClient(User $user)` qui génère un JWT via `JWTTokenManagerInterface` pour authentifier les requêtes de test sans passer par le flux OTP.

```php
$client = $this->createAuthenticatedClient($user);
$client->request('POST', '/api/posts', ['json' => [...]]);
$this->assertResponseStatusCodeSame(201);
```

### Qualité du code

```bash
make phpcs       # Formatage automatique (php-cs-fixer)
make phpstan     # Analyse statique (PHPStan niveau max)
make unit-test   # Tests PHPUnit avec couverture de code
make quality     # Les trois en une seule commande
```

---

## Plugins PHPStorm recommandés

- Symfony Plugin
- PHP Annotations
- PHP Inspections (EA Extended)
- PHP Toolbox
- PHPUnit Enhancements
- Git Toolbox

---

## Workflow Git

```bash
git add .
git commit -m ":gitmoji: TEM-XXX description"
git push

# Mise à jour depuis staging
git pull staging
git checkout feat/ma-branche
git rebase staging
git push --force
```

Les messages de commit suivent la convention [Gitmoji](https://gitmoji.dev/) + référence ticket Jira (`TEM-XXX`).
