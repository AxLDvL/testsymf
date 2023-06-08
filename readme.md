pour faire tourner symfony dans docker voila ce que j'ai fait. Pour info j'avais deja le necessaire sur mon pc pour lancer un nouveau projet symfony mais je pense que ce n'est pas obligé (mon problème était au niveau de la db).
j'ai donc executé la commande pour initialiser mon projet (si tu n'as pas le necessaire sur ta machine il faudra te mettre dans un conteneur docker pour l'exeuter):
symfony new symfonytest --version="6.3.*" --webapp

ensuite dans mon projet, à la racine j'ai placé les fichiers suivants:
Dockerfile:
```
# Use an official PHP runtime as a parent image
FROM php:8.2-alpine

# Install system dependencies
RUN apk --update add \
    curl \
    bash \
    libzip-dev \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add Symfony install script and execute it
ADD install_symfony.sh /install_symfony.sh
RUN chmod +x /install_symfony.sh
RUN /install_symfony.sh

# Set working directory in the container
WORKDIR /var/www/html

# Copy the current directory contents into the container
COPY . /var/www/html

# Install dependencies with Composer
RUN composer install

# Change owner of project directory to www-data
RUN chown -R www-data:www-data /var/www/html/

# Change current user to www-data
USER www-data

# Define environment variable for Symfony server
ENV SYMFONY_SERVER_PORT=0.0.0.0:8000

# Start the server
CMD ["symfony", "serve", "--no-tls"]

# Expose port 8000 for Symfony
EXPOSE 8000

```
un cript qui installe symfony cli, install_symfony.sh:
```
#!/bin/sh
apk add --no-cache bash
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash
apk add symfony-cli

```
et j'ai modifier le docker-compose.yml:
```
#version: '3'
#
#services:
####> doctrine/doctrine-bundle ###
#  database:
#    image: postgres:${POSTGRES_VERSION:-15}-alpine
#    environment:
#      POSTGRES_DB: ${POSTGRES_DB:-app}
#      # You should definitely change the password in production
#      POSTGRES_PASSWORD: ${POSTGRES_PASSWORD:-!ChangeMe!}
#      POSTGRES_USER: ${POSTGRES_USER:-app}
#    volumes:
#      - database_data:/var/lib/postgresql/data:rw
#      # You may use a bind-mounted host directory instead, so that it is harder to accidentally remove the volume and lose all your data!
#      # - ./docker/db/data:/var/lib/postgresql/data:rw
####< doctrine/doctrine-bundle ###
#
#volumes:
####> doctrine/doctrine-bundle ###
#  database_data:
####< doctrine/doctrine-bundle ###
version: '3'

services:
  database:
    image: mariadb:10.11.2
    environment:
      MYSQL_DATABASE: dbuser
      MYSQL_USER: dbuserapp
      MYSQL_PASSWORD: dbuserpass
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - database_data:/var/lib/mysql
    ports:
      - "3306:3306"

  webserver:
    build:
      context: .
      dockerfile: Dockerfile
    volumes:
      - .:/var/www/html:cached
    ports:
      - "8000:8000"
    depends_on:
      - database

volumes:
  database_data:


```
Dans le .env tu ajoutes la base de donnée:
DATABASE_URL="mysql://dbuserapp:dbuserpass@database:3306/dbuser?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

et le tour est joué!
tu lances tes conteneurs avec la commande:
docker-compose up --build

une fois que c'est lancé tu te places dans ton conteneur webserver avec la commande:
docker exec -it <ID DE TON CONTENEUR> /bin/bash

pour trouver l'id de ton conteneur tu fais la commande:
docker ps


pour info la bd etant deja créée ajoute --if-not-exists   à la commande suivante quand tu en seras à cette etape:
php bin/console doctrine:database:create --if-not-exists


les commandes pour créer ce projet sont:
```
composer require symfony/maker-bundle --dev

php bin/console doctrine:database:create --if-not-exists
```
Nous allons maintenant créer une entité User qui aura un username, un password et un email. 
```
php bin/console make:user
```
Utilisez la commande make:registration-form pour créer un formulaire d'enregistrement.
```
php bin/console make:registration-form
```
Ensuite, utilisez la commande make:auth pour créer un système d'authentification.
```
php bin/console make:auth
```
Création de la page Home
Pour cela, créez un nouveau contrôleur (par exemple HomeController) avec la commande make:controller
```
php bin/console make:controller HomeController
```
Dans le fichier HomeController.php qui a été créé, ajoutez la méthode suivante :
```
/**
 * @Route("/home", name="home")
 */
public function index(): Response
{
    $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    $user = $this->getUser();

    return $this->render('home/index.html.twig', [
        'username' => $user->getUsername(),
    ]);
}
```
Dans le template correspondant (templates/home/index.html.twig), ajoutez le message de bienvenue :
```
{% extends 'base.html.twig' %}

{% block title %}Home{% endblock %}

{% block body %}
    <h1>Welcome, {{ username }}</h1>
{% endblock %}
```
mettre à jour votre base de données
```
php bin/console make:migration
php bin/console doctrine:m
```

pour initialiser la base de donnée:
```
symfony console doctrine:database:create
symfony console doctrine:migrations:migrate
```
la meme chose pour l'environnement de test:
```
symfony console doctrine:database:create --env=test
symfony console doctrine:migrations:migrate --env=test
```



