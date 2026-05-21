# Référence API Jamly

## Vue d'ensemble

Jamly est une plateforme sociale de partage musical. Ce document complète l'interface Swagger interactive disponible sur `http://localhost:80/api/docs`.

|                      |                               |
|----------------------|-------------------------------|
| **URL de base**      | `https://localhost/api` (dev) |
| **Format**           | JSON (`application/json`)     |
| **Authentification** | JWT Bearer token              |
| **Temps réel**       | Mercure (Server-Sent Events)  |
| **Version**          | 1.0.0                         |

---

## Authentification

Jamly utilise un flux **sans mot de passe par code OTP email** — aucun mot de passe n'est jamais stocké.

### Flux

```
1. POST /api/auth/request  →  code OTP envoyé par email
2. POST /api/auth/verify   →  code vérifié, JWT retourné
3. Toutes les requêtes suivantes  →  Authorization: Bearer <token>
```

### Étape 1 — Demander un code

```
POST /api/auth/request
```
```json
{ "email": "utilisateur@example.com" }
```

**Réponse `201`**
```json
{ "message": "If a matching email was found, a code has been sent." }
```

### Étape 2 — Vérifier le code

```
POST /api/auth/verify
```
```json
{ "email": "utilisateur@example.com", "code": "123456" }
```

**Réponse `201`**
```json
{
  "isVerified": true,
  "message": "Verification successful.",
  "needsProfile": true,
  "token": "<jwt>"
}
```

> Quand `needsProfile` est `true`, le compte vient d'être créé : le client doit rediriger vers l'écran de configuration du profil (`PATCH /api/users/me`).

### Utiliser le token

Inclure le JWT dans chaque requête authentifiée :

```
Authorization: Bearer <token>
```

L'expiration est configurée côté serveur. Pour renouveler, relancer le même flux en deux étapes.

---

## Conventions générales

### Pagination

Les collections paginées acceptent ces paramètres :

| Paramètre      | Défaut | Description             |
|----------------|--------|-------------------------|
| `page`         | `1`    | Numéro de page (base 1) |
| `itemsPerPage` | `20`   | Éléments par page       |

### Format des réponses

L'API renvoie du JSON brut — pas de JSON-LD, pas d'enveloppe Hydra.

- **Collections** → tableau JSON : `[ { ... }, { ... } ]`
- **Ressource unique** → objet direct : `{ ... }`
- **Réponses vides** (204) → corps vide

### Dates

Tous les horodatages sont en ISO 8601 : `2024-03-15T14:32:00+00:00`.

### Erreurs

API Platform 4 utilise le format **RFC 9457 Problem Details** pour toutes les erreurs :

```json
{
  "type": "https://tools.ietf.org/html/rfc2616#section-10",
  "title": "An error occurred",
  "detail": "email: This value is not a valid email address.",
  "status": 422
}
```

Les erreurs de validation (`422`) incluent en plus un tableau `violations` :

```json
{
  "type": "...",
  "title": "An error occurred",
  "status": 422,
  "violations": [
    { "propertyPath": "email", "message": "This value is not a valid email address.", "code": "..." }
  ]
}
```

Codes HTTP courants :

| Code  | Signification                                  |
|-------|------------------------------------------------|
| `200` | OK                                             |
| `201` | Créé                                           |
| `204` | Pas de contenu (ex. like/unlike)               |
| `400` | Requête malformée                              |
| `401` | Non authentifié (token manquant/invalide)      |
| `403` | Accès interdit (authentifié mais non autorisé) |
| `404` | Ressource introuvable                          |
| `422` | Entité non traitable (échec de validation)     |

---

## Endpoints

Voir `https://localhost/api/docs` pour la liste complète et interactive des endpoints.

---

## Temps réel (Mercure)

Jamly utilise le [protocole Mercure](https://mercure.rocks/) pour la messagerie en temps réel via Server-Sent Events.  
Le hub Mercure est intégré dans FrankenPHP/Caddy — il n'y a pas de service séparé.

### S'abonner aux événements

1. Obtenir un JWT Mercure (valide **1 heure**) via `GET /api/mercure/token`.  
   La réponse contient aussi la liste exacte des topics auxquels l'utilisateur est abonné.
2. Ouvrir une connexion SSE vers le hub (dev : `http://localhost:80/.well-known/mercure`) :

```
GET http://localhost:80/.well-known/mercure?topic=/conversations/1
Authorization: Bearer <mercure-jwt>
```

### `GET /api/mercure/token` — réponse

```json
{
  "token": "<mercure-jwt>",
  "topics": ["/conversations/1", "/conversations/4"]
}
```

Les topics sont automatiquement scopés à toutes les conversations dont l'utilisateur fait partie.

### Structure des événements

Les événements sont des lignes SSE `data:` contenant un objet JSON avec un champ `type` et un objet imbriqué :

```json
{
  "type": "message",
  "message": {
    "id": 101,
    "conversationId": 1,
    "author": {
      "id": 7,
      "username": "alice",
      "profilePicture": "https://cdn.example.com/avatars/alice.jpg"
    },
    "type": "text",
    "content": "Hey!",
    "track": null,
    "createdAt": "2024-03-15T14:32:00+00:00"
  }
}
```

Pour les messages `type: "music"`, `track` est renseigné et `content` est `null`.  
Pour les messages `type: "image"`, `content` est une URL Azure CDN.

### Topics publiés

| Pattern                     | Publié quand                                        |
|-----------------------------|-----------------------------------------------------|
| `/conversations/{id}`       | Un nouveau message est créé dans cette conversation |
| `/users/{id}/notifications` | Une notification est envoyée à cet utilisateur      |
