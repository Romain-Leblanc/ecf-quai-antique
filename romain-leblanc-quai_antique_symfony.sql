/* Création de la base de données 'quai_antique_symfony' */
DROP DATABASE IF EXISTS quai_antique_symfony;
CREATE DATABASE quai_antique_symfony DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE quai_antique_symfony;

/* Prise en compte des accents dans les requêtes d'insertions de données */
SET NAMES utf8mb4;

/**
  Table doctrine_migration_versions + insertions
  Ajouté par Symfony
  */
DROP TABLE IF EXISTS doctrine_migration_versions;
CREATE TABLE IF NOT EXISTS doctrine_migration_versions (
  version VARCHAR(191) NOT NULL,
  executed_at DATETIME DEFAULT NULL,
  execution_time INT DEFAULT NULL,
  PRIMARY KEY (version)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO doctrine_migration_versions (version, executed_at, execution_time) VALUES
('DoctrineMigrations\\Version20230127180948', '2023-01-27 18:13:33', 2014),
('DoctrineMigrations\\Version20230128154042', '2023-01-28 15:41:31', 919),
('DoctrineMigrations\\Version20230128173426', '2023-01-28 17:36:31', 3995),
('DoctrineMigrations\\Version20230128180826', '2023-01-28 18:08:48', 675),
('DoctrineMigrations\\Version20230128220102', '2023-01-28 22:05:52', 3903),
('DoctrineMigrations\\Version20230130142015', '2023-01-30 14:20:54', 910),
('DoctrineMigrations\\Version20230202194543', '2023-02-02 19:48:58', 2982),
('DoctrineMigrations\\Version20230206140252', '2023-02-06 14:04:26', 1965),
('DoctrineMigrations\\Version20230208093305', '2023-02-08 09:33:16', 1960);


/**
  Table messenger_messages
  Ajouté par Symfony
  */
DROP TABLE IF EXISTS messenger_messages;
CREATE TABLE IF NOT EXISTS messenger_messages (
  id BIGINT AUTO_INCREMENT NOT NULL,
  body LONGTEXT NOT NULL,
  headers LONGTEXT NOT NULL,
  queue_name VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL,
  available_at DATETIME NOT NULL,
  delivered_at DATETIME DEFAULT NULL,
  INDEX IDX_75EA56E0FB7336F0 (queue_name),
  INDEX IDX_75EA56E0E3BD61CE (available_at),
  INDEX IDX_75EA56E016BA31DB (delivered_at),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;


/* Table categorie + insertions */
DROP TABLE IF EXISTS categorie;
CREATE TABLE IF NOT EXISTS categorie (
  id INT AUTO_INCREMENT NOT NULL,
  libelle VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO categorie (id, libelle) VALUES
(1, 'Entrées'),
(2, 'Plats principaux'),
(3, 'Desserts'),
(4, 'Soupes / potages'),
(5, 'Boissons / cocktails'),
(6, 'Cafés');


/* Table jour + insertions */
DROP TABLE IF EXISTS jour;
CREATE TABLE IF NOT EXISTS jour (
  id INT AUTO_INCREMENT NOT NULL,
  libelle VARCHAR(15) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO jour (id, libelle) VALUES
(1, 'Lundi'),
(2, 'Mardi'),
(3, 'Mercredi'),
(4, 'Jeudi'),
(5, 'Vendredi'),
(6, 'Samedi'),
(7, 'Dimanche');


/* Table formule + insertions */
DROP TABLE IF EXISTS formule;
CREATE TABLE IF NOT EXISTS formule (
  id INT AUTO_INCREMENT NOT NULL,
  titre_formule VARCHAR(50) NOT NULL,
  description_formule LONGTEXT NOT NULL,
  prix_formule NUMERIC(10,2) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO formule (id, titre_formule, description_formule, prix_formule) VALUES
(1, 'Déjeuner', 'Entrée + plat ou plat + dessert', '18.00'),
(2, 'Dîner', 'Entrée + plat + dessert', '23.50'),
(3, 'Excellence', 'Entrée/plat + dessert du chef', '30.00');


/* Table menu + insertions */
DROP TABLE IF EXISTS menu;
CREATE TABLE IF NOT EXISTS menu (
  id INT AUTO_INCREMENT NOT NULL,
  titre_menu VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO menu (id, titre_menu) VALUES
(1, 'Signature'),
(2, 'Classique'),
(3, 'Enfant');


/* Table menu_formule + insertions */
DROP TABLE IF EXISTS menu_formule;
CREATE TABLE IF NOT EXISTS menu_formule (
  menu_id INT NOT NULL,
  formule_id INT NOT NULL,
  INDEX IDX_E8878126CCD7E912 (menu_id),
  INDEX IDX_E88781262A68F4D1 (formule_id),
  PRIMARY KEY (menu_id, formule_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO menu_formule (menu_id, formule_id) VALUES
(1, 3),
(2, 1),
(2, 2),
(3, 1);


/* Table plat + insertions */
DROP TABLE IF EXISTS plat;
CREATE TABLE IF NOT EXISTS plat (
  id INT AUTO_INCREMENT NOT NULL,
  fk_categorie_id INT NOT NULL,
  titre_plat VARCHAR(50) NOT NULL,
  description_plat LONGTEXT NOT NULL,
  prix_plat NUMERIC(10,2) NOT NULL,
  lien_photo VARCHAR(255) DEFAULT NULL,
  afficher_photo TINYINT(1) NOT NULL,
  updated_at DATETIME NOT NULL,
  INDEX IDX_2038A2079D28E534 (fk_categorie_id),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO plat (id, fk_categorie_id, titre_plat, description_plat, prix_plat, lien_photo, afficher_photo, updated_at) VALUES
(1, 3, 'Fondant au chocolat', 'Gâteau au chocolat-noisettes', '7.00', '63e11d9963cbc128974347.jpg', 1, '2023-02-06 15:32:41'),
(2, 1, 'Saumon fumé', 'Roulade de saumon fumé et ses petits légumes', '5.50', '63e11d8cdaafb601850788.jpeg', 1, '2023-02-06 15:32:28'),
(3, 2, 'Boeuf bourguignon', 'Fait maison', '7.00', NULL, 0, '2023-02-09 11:02:56'),
(4, 4, 'Velouté de Topinambour', 'Velouté de topinambour, jambon de Parme et au chou noir de Toscane', '4.50', NULL, 0, '2023-02-09 11:12:39'),
(5, 5, 'Chardonnay', 'Vin blanc', '16.00', NULL, 0, '2023-02-09 11:14:44'),
(6, 5, 'Pinot Noir', 'Vin rouge', '35.00', NULL, 0, '2023-02-09 11:16:55'),
(7, 6, 'Café gourmand', 'Café double accompagné de cookies', '6.50', NULL, 0, '2023-02-09 11:30:21'),
(8, 2, 'Pâtes fraiches', 'Pâtes fraiches au foie gras aux magrets et aux truffes', '10.50', '63e4d977e111e904302888.png', 1, '2023-02-09 11:31:03');


/* Table seuil_convive + insertions */
DROP TABLE IF EXISTS seuil_convive;
CREATE TABLE IF NOT EXISTS seuil_convive (
  id INT AUTO_INCREMENT NOT NULL,
  nombre INT NOT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO seuil_convive (id, nombre) VALUES
(1, 150);


/* Table utilisateur + insertions */
DROP TABLE IF EXISTS utilisateur;
CREATE TABLE IF NOT EXISTS utilisateur (
  id INT AUTO_INCREMENT NOT NULL,
  email VARCHAR(180) NOT NULL,
  roles JSON NOT NULL,
  password VARCHAR(255) NOT NULL,
  nom VARCHAR(50) NOT NULL,
  prenom VARCHAR(50) NOT NULL,
  nombre_convives INT NOT NULL,
  numero_telephone VARCHAR(10) DEFAULT NULL,
  UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO utilisateur (id, email, roles, password, nom, prenom, nombre_convives, numero_telephone) VALUES
(1, 'admin@quai-antique.fr', '[\"ROLE_ADMIN\"]', '$2y$13$0ADkAe4niUAH.La4hs9gzOM3sFWUjNXeymKnc5nsUt.FFyrYGaxOO', 'Michant', 'Arnaud', 1, NULL),
(2, 'admin-nouveau@quai-antique.fr', '[\"ROLE_ADMIN\"]', '$2y$13$fxEXcsM7sSh0NpliJweVSucxxVR73/5rfFCH77I/7NUO1zir22lyy', 'DUPONT', 'Jean-Luc', 1, NULL),
(3, 'romleb2001@gmail.com', '[\"ROLE_USER\"]', '$2y$13$aEtAjZTOqjpgY3w43t.zLuZ0/sCOIJf642IYwwhVAY30vl.sfa4/K', 'DUPONT', 'Thomas', 15, '0908070605');

/* Table visiteur + insertions */
DROP TABLE IF EXISTS visiteur;
CREATE TABLE IF NOT EXISTS visiteur (
  id INT AUTO_INCREMENT NOT NULL,
  nom VARCHAR(50) NOT NULL,
  prenom VARCHAR(50) NOT NULL,
  email VARCHAR(180) NOT NULL,
  nombre_convives INT NOT NULL,
  numero_telephone VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB;

INSERT INTO visiteur (id, nom, prenom, email, nombre_convives, numero_telephone) VALUES
(1, 'Dupont', 'Thomas', 'romleb2001@gmail.com', 5, '0908070605');

/* Table allergie_utilisateur + insertions */
DROP TABLE IF EXISTS allergie_utilisateur;
CREATE TABLE IF NOT EXISTS allergie_utilisateur (
  id INT AUTO_INCREMENT NOT NULL,
  fk_utilisateur_id INT NOT NULL,
  allergie VARCHAR(50) NOT NULL,
  INDEX IDX_3A5002D08E8608A6 (fk_utilisateur_id),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO allergie_utilisateur (id, fk_utilisateur_id, allergie) VALUES
(1, 3, 'gluten'),
(2, 3, 'poissons');


/* Table allergie_visiteur + insertions */
DROP TABLE IF EXISTS allergie_visiteur;
CREATE TABLE IF NOT EXISTS allergie_visiteur (
  id INT AUTO_INCREMENT NOT NULL,
  fk_visiteur_id INT NOT NULL,
  allergie VARCHAR(50) NOT NULL,
  INDEX IDX_B023C34B16849F09 (fk_visiteur_id),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO allergie_visiteur (id, fk_visiteur_id, allergie) VALUES
(1, 1, 'gluten'),
(2, 1, 'champignons');


/* Table horaire + insertions */
DROP TABLE IF EXISTS horaire;
CREATE TABLE IF NOT EXISTS horaire (
  id INT AUTO_INCREMENT NOT NULL,
  fk_jour_id INT NOT NULL,
  heure_ouverture TIME NOT NULL,
  heure_fermeture TIME NOT NULL,
  INDEX IDX_BBC83DB6D22357FC (fk_jour_id),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO horaire (id, fk_jour_id, heure_ouverture, heure_fermeture) VALUES
(1, 2, '12:00:00', '14:00:00'),
(2, 2, '19:00:00', '22:00:00'),
(3, 5, '12:00:00', '14:00:00'),
(4, 5, '19:00:00', '22:00:00'),
(5, 6, '12:00:00', '14:00:00'),
(6, 6, '19:00:00', '23:00:00'),
(7, 7, '12:00:00', '14:00:00'),
(8, 4, '12:00:00', '14:00:00'),
(9, 4, '19:00:00', '22:00:00');


/* Table reservation + insertions */
DROP TABLE IF EXISTS reservation;
CREATE TABLE IF NOT EXISTS reservation (
  id INT AUTO_INCREMENT NOT NULL,
  fk_utilisateur_id INT DEFAULT NULL,
  fk_visiteur_id INT DEFAULT NULL,
  date DATE NOT NULL,
  heure TIME NOT NULL,
  INDEX IDX_42C849558E8608A6 (fk_utilisateur_id),
  INDEX IDX_42C8495516849F09 (fk_visiteur_id),
  PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB;

INSERT INTO reservation (id, fk_utilisateur_id, fk_visiteur_id, date, heure) VALUES
(1, NULL, 1, '2023-01-31', '19:00:00'),
(2, 3, NULL, '2023-02-02', '12:30:00'),
(3, NULL, 1, '2023-02-03', '21:00:00'),
(4, 3, NULL, '2023-02-03', '21:00:00');


/* Contraintes pour les tables */
ALTER TABLE allergie_utilisateur
  ADD CONSTRAINT FK_3A5002D08E8608A6 FOREIGN KEY (fk_utilisateur_id) REFERENCES utilisateur (id);
  
ALTER TABLE allergie_visiteur
  ADD CONSTRAINT FK_B023C34B16849F09 FOREIGN KEY (fk_visiteur_id) REFERENCES visiteur (id);
  
ALTER TABLE horaire
  ADD CONSTRAINT FK_BBC83DB6D22357FC FOREIGN KEY (fk_jour_id) REFERENCES jour (id);
  
ALTER TABLE menu_formule
  ADD CONSTRAINT FK_E8878126CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE,
  ADD CONSTRAINT FK_E88781262A68F4D1 FOREIGN KEY (formule_id) REFERENCES formule (id) ON DELETE CASCADE;
  
ALTER TABLE plat
  ADD CONSTRAINT FK_2038A2079D28E534 FOREIGN KEY (fk_categorie_id) REFERENCES categorie (id);
  
ALTER TABLE reservation
  ADD CONSTRAINT FK_42C849558E8608A6 FOREIGN KEY (fk_utilisateur_id) REFERENCES utilisateur (id),
  ADD CONSTRAINT FK_42C8495516849F09 FOREIGN KEY (fk_visiteur_id) REFERENCES visiteur (id);