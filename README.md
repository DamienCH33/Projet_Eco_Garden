# EcoGarden API

API REST développée avec Symfony permettant de fournir des informations liées au jardinage écologique, notamment des conseils de culture et des informations météorologiques utiles aux jardiniers.

Cette API est destinée à être utilisée par les sites partenaires et les associations afin d’encourager la culture de plantes, légumes et herbes aromatiques dans une démarche durable.

Projet réalisé dans le cadre d'une mission freelance pour EcoGarden & Co.

# Contexte du projet

EcoGarden & Co est une entreprise spécialisée dans le domaine du jardinage et de l’agriculture écologique.

Afin de permettre à ses partenaires de diffuser ces informations, l’entreprise souhaite mettre à disposition une API publique permettant :

d’accéder à des conseils de jardinage

d’obtenir des informations météorologiques

de gérer les utilisateurs et l’authentification

Cette première version est un prototype technique destiné à évoluer selon les retours des partenaires.

# Stack technique


PHP 8
Symfony 6(Skeleton)
Doctrine ORM
MySQL
JWT Authentication
PHPUnit
PHPStan
API REST

API météo utilisée :
OpenWeatherMap (API gratuite)

# Installation

### 1. Cloner le projet

```bash
git clone https://github.com/DamienCH33/Projet_Eco_Garden.git
cd Projet_Eco_Garden
composer install

# Configuration environnement
.env.local

# Base de donnée

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

#Structure du projet
src/
 ├── Controller
 ├── Datafixture
 ├── Entity
 ├── EventSubscriber
 ├── Factory
 ├── Form
 ├── Repository
 ├── Response
 ├── Transformer
config/
migrations/
templates/
tests/

#Authentification

L’API utilise une authentification par token JWT.

Le token doit être envoyé dans le header :

Authorization: Bearer TOKEN

#Importer la collection

Ouvrir Postman

Cliquer sur Import

Importer les fichiers :

EcoGarden_API.postman_collection.json
EcoGarden_Environment.postman_environment.json

#Synthèse des routes – EcoGarden API
Routes publiques (sans authentification)

Méthode	Route	Description
POST	/user	Créer un compte utilisateur
POST	/auth	Authentification et récupération d’un token JWT

Routes utilisateur authentifié

Méthode	Route	Description
GET	/user/me	Récupérer les informations du compte connecté
GET	/conseil/{mois}	Obtenir les conseils d’un mois spécifique
GET	/conseil	Obtenir les conseils du mois courant
GET	/meteo/{ville}	Obtenir la météo d’une ville
GET	/meteo	Obtenir la météo de la ville de l’utilisateur

Routes administrateur
Méthode	Route	Description
POST	/conseil	Ajouter un conseil
PUT	/conseil/{id}	Modifier un conseil
DELETE	/conseil/{id}	Supprimer un conseil
PUT	/user/{id}	Modifier un utilisateur
DELETE	/user/{id}	Supprimer un utilisateur

# Qualité du code

Analyse statique :

vendor/bin/phpstan analyse

Tests :

php bin/phpunit

CI :

![CI](https://github.com/DamienCH33/Projet_Eco_Garden/actions/workflows/ci.yml/badge.svg)

Auteur
Damien Chauveau
Développeur Backend PHP / Symfony
