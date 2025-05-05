# 🎯 Eventopia - Plateforme de Gestion d'Événements
Eventopia est une plateforme web collaborative développée dans le cadre du projet PIDEV 3A à l’Esprit School of Engineering (Tunis, 2024-2025).  
Elle permet aux organisateurs, prestataires et administrateurs de gérer efficacement leurs événements grâce à des outils modernes, dynamiques et intuitifs.

## 🚀 Hébergement
Le code source du projet est disponible sur [GitHub](https://github.com/nawresbensethom/Event_planner-Eventopia/new/main)

## 🔍 3. Mots-clés 
 - Symfony 6
- PHP
- Plateforme de réservation
- Gestion des utilisateurs
- Création d’événements
- Back-office
- Dashboard administrateur
- Services personnalisés
- Catégories de services
- Statistiques des posts
- QR Code
- Gestion des rôles
- CRUD Symfony
- Base de données MySQL
- Authentification et sécurité
- Réactions (likes/dislikes)
- Upload d’image
- Interface moderne

# #🏷Topics
- Symfony
- Gestion des événements
- PHP
- Système de réservation
- Application Web
- Gestion des utilisateurs
- Dashboard administrateur
- Services personnalisés
- Statistiques et analyse

---
## ⚙️ Technologies utilisées

| Technologie     | Version     | Description                        |
|----------------|-------------|------------------------------------|
| Symfony         | 6.4         | Framework PHP MVC principal        |
| PHP             | 8.2         | Langage backend                    |
| MySQL/MariaDB   | 10.4.32     | Base de données relationnelle      |
| Doctrine ORM    | -           | Mapping objet-relationnel          |
| Twig            | -           | Moteur de templates frontend       |
| Bootstrap 5     | -           | Design responsive                  |
| JavaScript      | -           | Interactions client (likes, filtres...) |

---
**🔐 Fonctionnalités principales**

### 👥 Utilisateurs
### 📅 Événements
### 🧰 Services 
### 🗂️ Blog
### 📊 Planification & Statistique
### 💼 Offres d’emploi

---
**⚙️ Démarrage**

**1)--Cloner le dépôt :**
git clone https://github.com/nawresbensethom/Event_planner-Eventopia.git
**2)Installer les dépendances**
composer install
**3)Configurer la base de données dans le fichier .env:**
DATABASE_URL="mysql://root:@127.0.0.1:3306/event_planner?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
**4)Crée la base de données:**
php bin/console doctrine:database:create
**5)Exécuter les migrations:**
php bin/console doctrine:migrations:migrate
**6)Lancer le serveur :**
 symfony server:start

**🧭Utilisation:**

### 1. 👤 Création d’un Compte Utilisateur

#### 🔐 Inscription
- Cliquez sur **“S'inscrire”**.
- Remplissez : `Nom`, `Email`, `Mot de passe`.
- Cliquez sur **“S'inscrire”**.

#### 👤 Création du Profil
- Une fois inscrit, remplissez :
  - `Date de naissance`
  - `Adresse`
  - `Image de profil` (optionnel)
- Vous pouvez modifier votre profil via **“Mon profil”**.

### 2. 🔓 Connexion et Gestion de Compte
#### 🔑 Connexion
- Accédez à la page **“Connexion”**.
- Entrez votre `email` et `mot de passe`.
- Accès redirigé vers le **Tableau de bord**.

#### 📝 Modifier le Profil
- Allez sur **“Mon profil”**.
- Mettez à jour vos infos, changez l’image si souhaité.
- Cliquez sur **“Enregistrer”**.

#### 🔚 Déconnexion
- Cliquez sur le bouton **“Déconnexion”** en haut à droite.
  
### 3. 🛡️ Gestion des Utilisateurs (Administrateur)

#### ⛔ Blocage/Déblocage
- Via le backoffice, l’admin peut :
  - Bloquer/Débloquer un utilisateur.
  - Un utilisateur bloqué verra le message :  
    **“Votre compte est bloqué par l’administrateur”** à la connexion.

#### 👥 Gestion des Profils
- Depuis le menu **"Utilisateurs"**, l’admin peut :
  - Modifier, supprimer ou consulter les profils.
---
### 4. 📅 Création et Gestion des Événements

#### ➕ Créer un Événement
- Accédez à **“Créer un événement”**.
- Remplissez :
  - `Titre`, `Description`, `Date et heure`, `Services liés`

#### 🎟 Réserver un Événement
- Les utilisateurs sélectionnent les services liés à l’événement.
- Cliquez sur **“Réserver”** pour finaliser (paiement inclus).
- Un **QR code** est généré automatiquement pour la réservation.

---

### 5. 🧰 Gestion des Services & Catégories

#### 📌 Services (Prestataires)
- Ajouter / Modifier / Supprimer un service
- Associer chaque service à :
  - une **catégorie** (traiteur, DJ, photographe, etc.)
  - une **promotion** (si disponible)

#### 📂 Catégories (Admin)
- L’administrateur peut créer/modifier/supprimer des catégories pour organiser les services.
---

### 6. 📰 Fonctionnalités Avancées

#### 👍 Réactions aux Articles
- Les utilisateurs peuvent :
  - **Liker**, **Disliker**, **Signaler** un post

#### 📊 Statistiques Blog
- L’admin accède aux statistiques :
  - Nombre de likes/dislikes par post
  - Nombre de signalements
  - Top publications les plus engageantes
---

### 7. 📈 Statistiques & Suivi des Réservations

#### 🧾 Réservations
- L’administrateur consulte toutes les réservations.
- Accès aux détails : nom, événement, services réservés, date.
- Génération automatique de **QR codes** pour l’entrée ou le contrôle.

#### 📍 Tâches & Suivi (Module Planification)
- Création et Suivi d’événements via des **plans et tâches**.
- Visualisation par états :
  - À faire, En cours, Terminé
- Diagrammes et indicateurs statistiques disponibles.

**Pile Technologique**
**Frontend**
Twig : Moteur de templates pour des interfaces dynamiques.
Bootstrap 5 : Framework CSS pour un design responsive.
JavaScript : Interactions client (ex. filtres, likes/dislikes).

**Backend**
Symfony 6.4 : Framework PHP pour la logique serveur.
PHP 8.2 : Langage principal.
Doctrine ORM : Gestion des entités et requêtes DQL.
MySQL : Base de données relationnelle.

**Structure des Répertoires**
Event_planner-Eventopia/
├── src/
│   ├── Controller/
│   ├── Entity/
│   ├── Form/
│   ├── Repository/
├── templates/
├── public/
├── config/
├── .env / .env.local


🙏 **Remerciements**
Ce projet a été réalisé par :
👩‍💻 Chaabani Hibet Allah
👨‍💻 Bensethom Nawres
👩‍💻 Houimel Oumaima
👨‍💻 Mahjoub Zied
👩‍💻 Turki Kenza
👨‍💻 Hamza Fida
Encadré par l’équipe pédagogique de l’Esprit School of Engineering ,Un grand merci pour leur accompagnement durant le projet PIDEV 3A.


