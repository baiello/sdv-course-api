## 1. Création d'une application Symfony avec Docker

Dans un premier temps, nous souhaitons creer la structure de notre projet.
Nous allons donc utiliser un container temporaire permettant de créer une application symfony.

Notre but : **récupérer tous les fichier du projet**.

Pour ce faire, nous allons partager un dossier du container sous forme de volume pour récupérer le contenu de ce dossier : les fichiers de notre application Symfony.

### 1.1. On lance un container temporaire basé sur PHP 8.2

**Sous Unix**
```text
docker run --rm -ti -v "$PWD":/app php:8.2 /bin/bash
```

**Sous Windows**
```text
wsl --update
docker run --rm -ti -v "C:\\Users\\toto\\...:/app" php:8.2 /bin/bash
```

### 1.2. Installation de composer

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

### 1.3. Installation de Symfony CLI

```text
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
apt install symfony-cli
```

### 1.4. Création du projet

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

Création d'un controller

```text
php bin/console make:controller ClassroomController
```

## 4. Serveur d'authorisation OAuth2 et protection des routes API

Nous allons intéger un serveur d'authorisation OAuth2 à notre API.
Sous Symfony, le bundle [oauth2-server-bundle](https://github.com/thephpleague/oauth2-server-bundle) va nous faciliter cette tâche !

### 4.1. Installation de oauth2-server-bundle

```bash
composer require league/oauth2-server-bundle
mkdir config/jwt
openssl genrsa -aes128 -passout pass:_passphrase_ -out config/jwt/private.pem 2048
openssl rsa -in config/jwt/private.pem -passin pass:_passphrase_ -pubout -out config/jwt/public.pem
```

### 4.2 Configuration du serveur d'authorization

Des applications clientes (applications mobile, APPs ReactJS/Vue/Angular, autres serveurs, ...) vont vouloir se connecter à notre API.
Nous allons chercher à autoriser leurs utilisateurs à avoir accès aux resources servies par notre API.

Types de "Authorization Grant" :
* **Implicit :** C'est comme donner une clé spéciale à une application pour accéder à certaines informations de ton compte, sans avoir à entrer ton mot de passe à chaque fois.
* **Authorization code :** C'est comme donner un code spécial à une application, qui échange ensuite ce code avec le service en ligne pour obtenir un jeton d'accès, permettant ainsi à l'application d'accéder à certaines informations de ton compte.
* **Password :** C'est comme donner ton nom d'utilisateur et ton mot de passe directement à une application pour qu'elle puisse obtenir un jeton d'accès et accéder à tes informations de compte. Cependant, ce type de grant doit être utilisé avec précaution car il peut présenter des risques de sécurité.
* **Client grant :** C'est comme accorder à une application de confiance un accès permanent à certaines informations de ton compte, en utilisant ses propres informations d'identification. Cela évite d'avoir à te reconnecter à chaque utilisation de l'application.

### 4.3 Configuration du flux d'authorisation OAuth2

**Objectif :** Mettre en place un flux d'authorisation OAuth2 pour autoriser un utilisateur à accéder à une resource via un client API enregistré (Postman).

![OAuth2 flow](https://i.ibb.co/X52MZzw/oauth2-flow.png)

**Ajout d'un client API**

```text
php bin/console league:oauth2-server:create-client postman --scope=email --grant-type=authorization_code --redirect-uri=https://oauth.pstmn.io/v1/callback
```

**Protection de la route `/authorize`**

Dans le flux OAuth2, la route `/authorize` doit être accessible uniquement aux utilisateurs connectés (authentifiés) à notre serveur d'authorisation.
Nous allons donc lui ajouter des capacités de gestion et de connexion d'utilisateur, en utilisant [les fonctionnalités de Symfony](https://symfony.com/doc/current/security.html).

```text
composer require symfony/security-bundle
php bin/console make:user
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

Si on le souhaite (c'est facultatif), on peut créer un formulaire d'enregistrement d'utilisateur.
Ce formulaire servira uniquement à créer des utilisateurs en bdd, il n'impactera pas notre flux OAuth2.
Les commandes suivantes génèreront donc ce formulaire, qui sera accessible sur la route `/register`.

```text
composer require symfony/validator
composer require symfony/form
composer require symfonycasts/verify-email-bundle
php bin/console make:registration-form
```

Maintenant que nous pouvons créer et gérer des utilisateurs dans notre serveur d'authorisation, nous allons créer un process de login par formulaire.
C'est du pur Symfony, donc nous aurons besoin d'une route `/login` (via un LoginController), d'un formulaire de login à afficher, et d'adapter la configuration du firewall.

Création du LoginController ([Doc ici](https://symfony.com/doc/current/security.html#form-login)) :

```text
php bin/console make:controller Login
```

```php
// LoginController
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
```

```twig
{# templates/login/index.html.twig #}
{% extends 'base.html.twig' %}

{# ... #}

{% block body %}
    {% if error %}
        <div>{{ error.messageKey|trans(error.messageData, 'security') }}</div>
    {% endif %}

    <form action="{{ path('app_login') }}" method="post">
        <label for="username">Email:</label>
        <input type="text" id="username" name="_username" value="{{ last_username }}">

        <label for="password">Password:</label>
        <input type="password" id="password" name="_password">

        {# If you want to control the URL the user is redirected to on success
        <input type="hidden" name="_target_path" value="/account"> #}

        <button type="submit">login</button>
    </form>
{% endblock %}
```

```yaml
# config/packages/security.yaml
security:
    # ...
    firewalls:
        # ...
        main: # En dernière position dans la liste des firewalls
            # ...
            form_login:
                login_path: app_login
                check_path: app_login
```

Avec cela, toutes les routes (non traitées par les autres firewalls) nécessitant une authentification, seront redirigées automatiquement vers le formulaire de login.
Précisons donc que notre route `/authorize`, nécessite d'être appelée par un utilisateur connecté.
Cela se jouera dans les contrôles d'accès :

```yaml
# config/packages/security.yaml
security:
    # ...
    access_control:
        - { path: ^/authorize, roles: IS_AUTHENTICATED_REMEMBERED }
```
