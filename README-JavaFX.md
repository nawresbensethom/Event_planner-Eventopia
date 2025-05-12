# 🎯 Eventopia - Plateforme de Gestion d'Événements
Eventopia est une plateforme développée dans le cadre du projet PIDEV 3A à l’Esprit School of Engineering (Tunis, 2024-2025).  
Elle permet aux organisateurs, prestataires et administrateurs de gérer efficacement leurs événements grâce à des outils modernes, dynamiques et intuitifs.

## 🚀 Hébergement
Le code source du projet est disponible sur [GitHub](https://github.com/nawresbensethom/Event_planner-Eventopia/new/main)

## 🔍 3. Mots-clés 
 JavaFX
Gestion d’événements
Application desktop
CRUD
MVC Java
Gestion des utilisateurs
Connexion / Inscription
Base de données MySQL
QR Code
Statistiques
Réservations
Back-office
Java SE
FXML

# #🏷Topics
JavaFX
Gestion des utilisateurs
Dashboard organisateur
Gestion des prestataires
Statistiques
Interaction avec base de données
QR Code
Interface responsive
Filtrage dynamique
Authentification sécurisée

---
## ⚙️ Technologies utilisées

| Technologie  | Version | Description                               |
| ------------ | ------- | ----------------------------------------- |
| Java SE      | 17+     | Langage principal                         |
| JavaFX       | 20      | Framework UI desktop                      |
| SceneBuilder | -       | Conception d’interface graphique          |
| MySQL        | 10.4+   | Base de données                           |
| JDBC         | -       | Connexion à la base de données            |
| ZXing        | -       | Génération de QR codes                    |
| CSS          | -       | Personnalisation de l’interface           |

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

---

````markdown
## ⚙️ Démarrage de l'application
1. **Cloner le dépôt Git :**
   ```bash
   git clone https://github.com/ton_profil/Eventopia-JavaFX.git
````

2. **Ouvrir le projet :**
   Ouvrez le projet dans un IDE tel que **IntelliJ IDEA** ou **NetBeans**.

3. **Configurer la base de données :**
4. 
   * URL de la base de données
   * Nom d'utilisateur
   * Mot de passe

5. **Importer la base de données :**

   * Fichier : `eventopia.sql` (disponible dans le dossier `database/`)

6. **Exécuter l’application :**
   Lancez la classe `Main.java` pour démarrer l’interface graphique JavaFX.

```

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

#### 📍Plan  & Tâches (Module Planification)
- Création et Suivi d’événements via des **plans et tâches**.
- Visualisation par états :
  - À faire, En cours, Terminé
- Diagrammes et indicateurs statistiques disponibles.

## 🧱 Pile Technologique (JavaFX)

### 🖥 Frontend (Interface utilisateur)
- **JavaFX 20** 
- **FXML** 
- **CSS** 
- **SceneBuilder** 

### ⚙ Backend (Traitement et logique métier)
- **Java SE 17+** 
- **JDBC** 
- **MySQL 10.4+**
- **ZXing**


**Structure des Répertoires**
Eventopia-JavaFX/
├── src/
│   ├── controllers/      
│   ├── entities/         
│   ├── utils/           
│   ├── services/        
│   └── Main.java        
├── resources/
│   ├── fxml/             
│   ├── css/              
│   └── images/           
├── database/
│   └── eventopia.sql     

🙏 **Remerciements**
Ce projet a été réalisé par :
👩‍💻 Chaabani Hibet Allah
👨‍💻 Bensethom Nawres
👩‍💻 Houimel Oumaima
👨‍💻 Mahjoub Zied
👩‍💻 Turki Kenza
👨‍💻 Hamza Fida
Encadré par l’équipe pédagogique de l’Esprit School of Engineering ,Un grand merci pour leur accompagnement durant le projet PIDEV 3A.


