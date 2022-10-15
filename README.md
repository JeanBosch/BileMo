﻿# BileMo

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/fa8be4ca9b144b538cfe06b63c67a462)](https://app.codacy.com/gh/JeanBosch/BileMo?utm_source=github.com&utm_medium=referral&utm_content=JeanBosch/BileMo&utm_campaign=Badge_Grade_Settings)


BileMo is a REST API that allows you to manage your customers and their orders.
BileMo est une API REST qui permet de transmettre à vos clients les informations sur les téléphones portables disponibles dans notre catalogue

## Installation

### Requirements

- PHP 7.4 ou plus
- Composer
- Symfony 5
- MySQL 8 ou plus

### Installation

1. Cloner le repository : https://github.com/JeanBosch/BileMo.git
2. Installer les dépendances avec la commande `composer install`
3. Créez votre base de données avec la commande `php bin/console doctrine:database:create`
4. Configurez le fichier `.env` avec vos identifiants de base de données
5. Générez des fixtures avec la commande `php bin/console doctrine:fixtures:load`
7. Créez les clefs SSH avec les commandes suivantes : `mkdir -p config/jwt` puis `openssl genrsa -out config/jwt/private.pem -aes256 4096` et enfin `openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem`
8. Créez votre passphrase JWT dans le fichier `.env` avec la variable `JWT_PASSPHRASE`
9. Vous pouvez démarrer le serveur avec la commande `symfony server:start` (pensez à activer Wamp ou Mamp pour MySQL)
10. Vous pouvez accéder à la documentation de l'API via l'URL `http://localhost:8000/api/doc`, qui vous permettra de tester des Endpoints (vous pouvez aussi utiliser Postman)


## Usage

### Authentication via api/doc

Pour vous authentifier, vous devez utiliser la requete dans la catégorie "Token" : `/api/login_check` avec les paramètres suivants:

- `_username` (string): Le nom d'utilisateur (le nom de l'entreprise)
- `_password` (string): Le mot de passe

La réponse contiendra un token JWT que vous devrez utiliser pour accéder aux autres Endpoints. Cliquez sur le bouton "Authorize" et entrez le token dans le champ "Value": bearer `token`


### Authentication via Postman

Pour vous authentifier, vous devez utiliser la requete POST  `/api/login_check` avec les paramètres suivants:

- `_username` (string): Le nom d'utilisateur (le nom de l'entreprise)
- `_password` (string): Le mot de passe

La réponse contiendra un token JWT que vous devrez utiliser pour accéder aux autres Endpoints. Au moment de faire de nouvelles requêtes, entrez dans l'onglet Header le champ "Authorization" avec la valeur "Bearer `token`"

### Test des endpoints

Une fois que vous avez un token, vous pouvez tester les autres Endpoints. Vous pouvez utiliser Postman ou la documentation de l'API.


## Qualité du code 


