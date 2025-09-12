# Guide de test de l'intégration Spotify

## Endpoints disponibles

### 1. **Génération de l'URL d'autorisation**
```http
GET https://localhost/api/auth/spotify/authorize
```

**Réponse :**
```json
{
    "authorization_url": "https://accounts.spotify.com/authorize?...",
    "message": "Visit this URL to authorize the application with Spotify"
}
```

### 2. **Callback d'autorisation (après redirection Spotify)**
```http
GET https://localhost/api/auth/spotify/callback?code=AQC...&state=...
```

**Headers requis :**
```
Authorization: Bearer {votre_jwt_token_utilisateur}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Spotify account successfully linked",
    "spotify_user": {
        "id": "spotify_user_id",
        "display_name": "Nom d'affichage",
        "email": "email@example.com",
        "country": "FR",
        "followers": 42
    },
    "token_expires_at": "2025-08-26 16:05:10"
}
```

### 3. **Test de connexion Spotify**
```http
GET https://localhost/api/auth/spotify/test
```

**Headers requis :**
```
Authorization: Bearer {votre_jwt_token_utilisateur}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Spotify connection is working correctly",
    "spotify_profile": {
        "id": "spotify_user_id",
        "display_name": "Nom d'affichage",
        "email": "email@example.com",
        "followers": 42
    },
    "sample_playlists": [
        {
            "id": "playlist_id",
            "name": "Ma playlist",
            "tracks": {"total": 25}
        }
    ],
    "token_expires_at": "2025-08-26 16:05:10"
}
```

### 4. **Déconnexion Spotify**
```http
DELETE https://localhost/api/auth/spotify/disconnect
```

**Headers requis :**
```
Authorization: Bearer {votre_jwt_token_utilisateur}
```

## Flux de test complet

### Étape 1 : Créer un utilisateur (si pas déjà fait)
```http
POST https://localhost/register
Content-Type: application/json

{
    "username": "testuser",
    "email": "test@example.com", 
    "password": "password123"
}
```

### Étape 2 : Se connecter pour obtenir un JWT
```http
POST https://localhost/api/login
Content-Type: application/json

{
    "username": "testuser",
    "password": "password123"
}
```

### Étape 3 : Obtenir l'URL d'autorisation Spotify
```http
GET https://localhost/api/auth/spotify/authorize
Authorization: Bearer {jwt_token}
```

### Étape 4 : Visiter l'URL et autoriser l'application
- Copier l'URL retournée et l'ouvrir dans le navigateur
- Se connecter à Spotify et autoriser l'application
- Vous serez redirigé vers le callback avec un code

### Étape 5 : Tester la connexion
```http
GET https://localhost/api/auth/spotify/test
Authorization: Bearer {jwt_token}
```

## Commandes de développement

### Vérifier les logs en cas d'erreur :
```bash
docker exec -it jamly-api tail -f var/log/dev.log
```

### Vider le cache si nécessaire :
```bash
docker exec -it jamly-api bin/console cache:clear
```

### Voir la structure de la table créée :
```bash
docker exec -it jamly-database psql -U postgres -d jamly -c "\d spotify_token"
```

## Points de validation

 **Token stocké** : Vérifier que le token est bien sauvegardé en base  
 **API Spotify** : Confirmer que les appels vers Spotify fonctionnent  
 **Profil utilisateur** : Récupération des infos du compte Spotify  
 **Playlists** : Accès aux données musicales de l'utilisateur  
 **Expiration** : Gestion correcte de l'expiration du token  

##  Troubleshooting

- **"Token expired"** : Le token Spotify expire au bout d'1h, implémenter le refresh si nécessaire
- **"Invalid client"** : Vérifier les credentials Spotify dans .env.local  
- **"Redirect URI mismatch"** : Vérifier l'URI dans les settings Spotify
- **"User not authenticated"** : S'assurer d'être connecté avec un JWT valide
