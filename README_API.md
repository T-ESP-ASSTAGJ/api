# RESSOURCE API

## USEFULL COMMANDS

- `sudo docker exec -it jamly-api bin/console c:c` (Vider le cache)
- `sudo docker exec -it jamly-api bin/console m:mig` (Crée un fichier de migration)
- `sudo docker exec -it jamly-api bin/console d:m:m` (Jouer les migrations) "prev" pour rollback
- `sudo docker exec -it jamly-api bin/console debug:router` (Affiche les routes)
- `sudo docker exec -it jamly-api bin/console d:f:l` (Run les factories)

## SWAGGER

Url : http://localhost:80/api/docs (81 pour SERGIO LE BG)

Connection JWT : 
1. /api/auth/request
2. Verification code for user@example.com is: *360466* (Dans les logs jamly-api)
3. /api/auth/verify ---> code = *360466* (Verifier le mail)
4. Récuperer le token dans la réponse ---> token:*eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...*
5. Authorize en haut, rentrer : "Bearer *eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...*"

