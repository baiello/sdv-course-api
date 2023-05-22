## 1. Création d'une application Symfony avec Docker

Dans un premier temps, nous souhaitons creer la structure de notre projet.
Nous allons donc utiliser un container temporaire permettant de créer une application symfony.

Notre but : **récupérer tous les fichier du projet**.

Pour ce faire, nous allons partager un dossier du container sous forme de volume pour récupérer le contenu de ce dossier : les fichiers de notre application Symfony.

### 1. On lance un container temporaire basé sur PHP 8.2

**Sous Unix**
```text
docker run --rm -ti -v "$PWD":/app php:8.2 /bin/bash
```

**Sous Windows**
```text
wsl --update
docker run --rm -ti -v "C:\\Users\\toto\\...:/app" php:8.2 /bin/bash
```

### 2. Installation de composer

Dans le container lancé, on installe composer et ses pré-requis

```text
apt update
apt install git zip unzip

git config --global user.name "Toto TITI"
git config --global user.email "toto.titi@sdvqqch.com"

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/bin --filename=composer
```

>Pour tester si composer est bien installé
> 
> `composer`

### 3. Installation de Symfony CLI

```text
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
apt install symfony-cli
```

### 4. Création du projet

On peut vérifier que tout est ok pour faire tourner Symfony dans notre container temporaire :

```text
symfony check:requirements
```

Puis on crée notre projet :

```text
mkdir /app && cd /app
symfony new apisdv --version="6.2.*"
```

## 2. Création de l'infrastructure Docker pour faire tourner notre projet

## 3. Création d'un CRUD basique

## 4. Installation du serveur d'authorisation et protection des routes API

Nous allons intéger un serveur d'authorisation OAuth2 à notre API.
Sous Symfony, le bundle [oauth2-server-bundle](https://github.com/thephpleague/oauth2-server-bundle) va nous faciliter cette tâche !

### 1. Installation de oauth2-server-bundle

```bash
composer require league/oauth2-server-bundle
mkdir config/jwt
openssl genrsa -aes128 -passout pass:_passphrase_ -out config/jwt/private.pem 2048
openssl rsa -in config/jwt/private.pem -passin pass:_passphrase_ -pubout -out config/jwt/public.pem
```
