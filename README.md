# Blog Symfony

Blog développé avec Symfony 7.x, permettant aux utilisateurs de créer, modifier, publier des articles, ajouter des images, et commenter. Les administrateurs peuvent aussi gérer les articles et les commentaires. 

![Logo du projet](https://symfony.com/logos/symfony_black_03.svg)

## Table des matières

- [Description](#description)
- [Fonctionnalités](#fonctionnalités)
- [Installation](#installation)
- [Utilisation](#utilisation)

## Description

Ce projet est une plateforme de blog où les utilisateurs peuvent créer un compte, publier des articles, commenter, et gérer les articles et commentaires. Les administrateurs ont des privilèges supplémentaires pour gérer les articles (modification, suppression, publication) et gérer les commentaires. Le blog supporte l'ajout d'images et permet de choisir la publication immédiate ou plus tard pour chaque article.

## Fonctionnalités

### Utilisateur lambda
- Créer un compte utilisateur
- Publier des commentaires sur les articles
- Visualiser les articles avec ou sans image
- Inscription et authentification

### Administrateur
- Ajouter, modifier ou supprimer des articles
- Ajouter, modifier ou supprimer des images associées aux articles
- Publier ou mettre en attente un article
- Gérer les commentaires des utilisateurs

## Installation

### Prérequis

- PHP 8.1 ou supérieur
- Composer (gestionnaire de dépendances PHP)
- Symfony 7.x
- MySQL pour la base de données

### Étapes d'installation

1. **Clonez le projet**
    ```bash
    git clone https://github.com/RaphaelV0/Blog-Symfony.git
    cd Blog-Symfony
    ```

2. **Installez les dépendances**
    ```bash
    composer install
    ```

3. **Configurez la base de données**
    Vous devez configurer les informations de connexion à la base de données dans le fichier `.env` ou `.env.local` en fonction de votre environnement.

    Exemple de configuration pour MySQL :
    ```dotenv
    DATABASE_URL="..."
    ```

    Créez la base de données et appliquez les migrations :
    ```bash
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

4. **Lancez le serveur Symfony**
    ```bash
    symfony serve
    ```

## Utilisation

1. Si vous êtes un utilisateur lambda, vous pouvez créer un compte pour pouvoir commenter les articles.
2. Si vous êtes un administrateur (login : admin@gmail.com password : Test123* ), vous pouvez gérer les articles et les commentaires dans le tableau de bord.

### Fonctionnalités utilisateur
- Créez un compte pour vous inscrire par défaut vous êtes un utilisateur lambda (pour être administrateur écrire ["ROLE_ADMIN"], à role dans la base de données.
- Après avoir crée votre compte, vous êtes sur une page pour confirmer votre inscription, revenez sur la page d'accueil du site pour pouvoir vous connecter.
- Commentez les articles existants.
- Visualisez les articles avec ou sans image, et l'état de publication.

### Fonctionnalités administrateur
- Ajoutez des articles, y compris des images.
- Modifiez les articles existants.
- Supprimez les articles et leurs commentaires associés.
- Publiez ou mettez en attente un article.
