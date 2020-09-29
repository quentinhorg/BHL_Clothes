-- Adminer 4.7.7 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `bhl_clothes`;
CREATE DATABASE `bhl_clothes` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `bhl_clothes`;

DROP TABLE IF EXISTS `article_panier`;
CREATE TABLE `article_panier` (
  `numCmd` int(11) NOT NULL,
  `idVet` int(3) NOT NULL,
  `taille` varchar(3) NOT NULL,
  `numClr` int(11) NOT NULL,
  `qte` int(11) NOT NULL,
  PRIMARY KEY (`idVet`,`numCmd`,`numClr`,`taille`) USING BTREE,
  KEY `CONTIENT_commande0_FK` (`numCmd`),
  KEY `idTaille` (`taille`),
  KEY `numClr` (`numClr`),
  CONSTRAINT `CONTIENT_commande0_FK` FOREIGN KEY (`numCmd`) REFERENCES `commande` (`num`),
  CONSTRAINT `CONTIENT_vetement_FK` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`),
  CONSTRAINT `article_panier_ibfk_2` FOREIGN KEY (`numClr`) REFERENCES `vet_couleur` (`num`),
  CONSTRAINT `article_panier_ibfk_3` FOREIGN KEY (`taille`) REFERENCES `taille` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article_panier` (`numCmd`, `idVet`, `taille`, `numClr`, `qte`) VALUES
(1,	1,	'L',	1,	1),
(1,	1,	'XL',	6,	2),
(1,	2,	'S',	4,	1),
(1,	3,	'L',	5,	1),
(1,	4,	'M',	1,	1)
ON DUPLICATE KEY UPDATE `numCmd` = VALUES(`numCmd`), `idVet` = VALUES(`idVet`), `taille` = VALUES(`taille`), `numClr` = VALUES(`numClr`), `qte` = VALUES(`qte`);

DELIMITER ;;

CREATE TRIGGER `before_insert_taille` BEFORE INSERT ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE tailleDispo int;
SET tailleDispo= (SELECT COUNT(taille) FROM vet_taille  WHERE idVet=NEW.idVet AND taille LIKE NEW.taille );
IF (tailleDispo = 0) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Erreur: Ce vêtement n'est pas disponible dans cette taille.";
end if;



END;;

CREATE TRIGGER `before_insert_couleur` BEFORE INSERT ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE couleurDispo int;
SET couleurDispo= (SELECT dispo
                   FROM vet_couleur 
                   WHERE idVet=NEW.idVet AND num = NEW.numClr );

IF (couleurDispo = 0) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Erreur: Ce vêtement n'est pas disponible dans cette couleur.";
end if;



END;;

DELIMITER ;

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `typeTaille` varchar(20) NOT NULL DEFAULT 'chiffre',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `categorie` (`id`, `nom`, `typeTaille`) VALUES
(1,	'Robes',	'lettre'),
(2,	'T-shirts & Débardeurs',	'lettre'),
(3,	'Jeans',	'chiffre'),
(4,	'Pulls',	'lettre'),
(5,	'Shorts de bain',	'chiffre'),
(6,	'Jupes',	'lettre'),
(7,	'Gilets',	'chiffre'),
(8,	'Vestes & Manteaux',	'chiffre'),
(9,	'Chemisiers & Tuniques',	'lettre'),
(10,	'Shorts & Bermudas',	'chiffre'),
(11,	'Pantacourts',	'chiffre'),
(12,	'Pantalon ',	'chiffre')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `nom` = VALUES(`nom`), `typeTaille` = VALUES(`typeTaille`);

DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `mdp` varchar(150) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

INSERT INTO `client` (`id`, `email`, `mdp`, `nom`, `prenom`, `adresse`, `tel`) VALUES
(1,	'andrea@gmail.com',	'',	'BIGOT',	'Andréa',	'22 rue des frangipaniers St Joseph',	'0692466990'),
(2,	'quentin@live.fr',	'123456',	'HOAREAU',	'Quentin',	'17 chemin des hirondelles St pierre',	'0694458553'),
(3,	'jeremy@mail.com',	'klMgfD',	'LEBON',	'Jérémy',	'6 rue du pingouin salé',	'0693122478'),
(4,	'grondin.sam@gmail.com',	'',	'GRONDIN',	'Samuel',	'88 rue des lilas Saint-Joseph ',	'0693238645'),
(5,	'ryan.lauret974@gmail.com',	'',	'LAURET',	'Ryan',	'50 chemin Général de Gaulle Saint Pierre',	'0692851347'),
(6,	'mathilde20@gmail.com',	'',	'PAYET',	'Mathilde',	'10 rue des marsouins Saint Joseph ',	'0692753212'),
(7,	'test@test.com',	'test',	'azeaze',	'zerzer',	'efefefefefeffe',	'65454')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `mdp` = VALUES(`mdp`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `adresse` = VALUES(`adresse`), `tel` = VALUES(`tel`);

DELIMITER ;;

CREATE TRIGGER `after_update_client` AFTER UPDATE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id,NOW(), OLD.nom, OLD.prenom, OLD.adresse, OLD.tel, "UPDATE");
END;;

CREATE TRIGGER `after_delete_client` AFTER DELETE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id,NOW(), OLD.nom, OLD.prenom, OLD.adresse, OLD.tel, "UPDATE");
END;;

DELIMITER ;

DROP TABLE IF EXISTS `client_histo`;
CREATE TABLE `client_histo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_histo` datetime NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  `evenement_histo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`,`date_histo`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

INSERT INTO `client_histo` (`id`, `date_histo`, `nom`, `prenom`, `adresse`, `tel`, `evenement_histo`) VALUES
(1,	'2020-09-07 00:00:00',	'BIGOT',	'Andréazzzz',	'St jo',	'0692466990',	'UPDATE'),
(1,	'2020-09-13 18:25:40',	'BIGOT',	'test',	'St jo',	'0692466990',	'UPDATE'),
(1,	'2020-09-13 18:25:46',	'BIGOT',	'test',	'St Joseph',	'0692466990',	'UPDATE'),
(1,	'2020-09-13 18:32:19',	'BIGOT',	'Andréa',	'St Joseph',	'0692466990',	'UPDATE'),
(1,	'2020-09-13 18:36:43',	'BIGOT',	'Andréa',	'St Joseph',	'0692466990',	'UPDATE'),
(1,	'2020-09-14 15:40:14',	'BIGOT',	'Andréa',	'22 rue des frangipaniers St Joseph',	'0692466990',	'UPDATE'),
(2,	'2020-09-13 18:31:38',	'HOAREAU',	'Quentin',	'St pierre',	'426525',	'UPDATE'),
(2,	'2020-09-13 18:32:37',	'HOAREAU',	'Quentin',	'St pierre',	'0694458553',	'UPDATE'),
(2,	'2020-09-13 18:37:01',	'HOAREAU',	'Quentin',	'St pierre',	'0694458553',	'UPDATE'),
(2,	'2020-09-14 15:40:14',	'HOAREAU',	'Quentin',	'17 chemin des hirondelles St pierre',	'0694458553',	'UPDATE'),
(2,	'2020-09-28 13:30:00',	'HOAREAU',	'Quentin',	'17 chemin des hirondelles St pierre',	'0694458553',	'UPDATE'),
(3,	'2020-09-13 18:31:56',	'LEBON',	'Jérémy',	'St denis',	'8285252',	'UPDATE'),
(3,	'2020-09-13 18:32:27',	'LEBON',	'Jérémy',	'St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-13 18:37:31',	'LEBON',	'Jérémy',	'St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-14 15:40:14',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-21 15:43:07',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 14:43:57',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 14:44:24',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 14:57:42',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 15:26:32',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 15:37:15',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-28 15:38:49',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-29 21:58:17',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-29 22:04:03',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478',	'UPDATE'),
(3,	'2020-09-29 22:10:32',	'LEBON',	'Jérémy',	'3 rue coco',	'0693122478',	'UPDATE'),
(3,	'2020-09-29 22:10:53',	'LEBON',	'Jérémy',	'lala',	'0693122478',	'UPDATE'),
(4,	'2020-09-07 00:00:00',	'test',	'test',	'22 st jo',	'2485',	'DELETE'),
(4,	'2020-09-14 15:40:14',	'GRONDIN',	'Samuel',	'88 rue des lilas Saint-Joseph ',	'0693238645',	'UPDATE'),
(5,	'2020-09-07 00:00:00',	'',	'',	'',	'',	'DELETE'),
(5,	'2020-09-14 15:40:14',	'LAURET',	'Ryan',	'50 chemin Général de Gaulle Saint Pierre',	'0692851347',	'UPDATE'),
(6,	'2020-09-07 00:00:00',	'aa',	'aa',	'ashia',	'798',	'DELETE'),
(6,	'2020-09-14 15:40:14',	'PAYET',	'Mathilde',	'10 rue des marsouins Saint Joseph ',	'0692753212',	'UPDATE'),
(7,	'2020-09-07 00:00:00',	'azeaze',	'zerzer',	'10 rue ouaiso uais',	'azeaze',	'DELETE')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `date_histo` = VALUES(`date_histo`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `adresse` = VALUES(`adresse`), `tel` = VALUES(`tel`), `evenement_histo` = VALUES(`evenement_histo`);

DROP TABLE IF EXISTS `commande`;
CREATE TABLE `commande` (
  `num` int(11) NOT NULL,
  `idClient` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`num`),
  KEY `commande_client_FK` (`idClient`),
  CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `commande` (`num`, `idClient`, `date`) VALUES
(1,	1,	'2019-12-02 12:30:00'),
(2,	2,	'2019-12-17 18:48:11'),
(3,	3,	'2020-12-23 08:02:08'),
(5,	5,	'2020-09-17 11:00:00'),
(6,	4,	'2020-09-13 14:18:23')
ON DUPLICATE KEY UPDATE `num` = VALUES(`num`), `idClient` = VALUES(`idClient`), `date` = VALUES(`date`);

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE `commentaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idClient` int(11) NOT NULL,
  `idVet` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `note` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idClient` (`idClient`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`),
  CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

INSERT INTO `commentaire` (`id`, `idClient`, `idVet`, `commentaire`, `note`, `date`) VALUES
(1,	1,	1,	'Commentaire vêtement 1.',	5,	'2020-09-12 12:50:52'),
(2,	1,	1,	'aaa',	2,	'2020-09-17 08:50:52'),
(3,	1,	1,	'aapppa',	2,	'2020-09-02 13:50:00'),
(4,	2,	3,	'zzzzz',	1,	'2020-09-20 14:14:14'),
(5,	2,	3,	'test',	5,	'2020-09-05 06:30:52'),
(7,	2,	3,	'aaaacxvbb',	3,	'2020-09-27 22:14:17'),
(8,	2,	3,	'azedfvcxsd',	5,	'2020-09-22 09:10:11'),
(13,	4,	5,	'test',	5,	'2020-09-28 00:00:00'),
(14,	3,	3,	'date',	2,	'2020-09-29 20:14:39')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `idClient` = VALUES(`idClient`), `idVet` = VALUES(`idVet`), `commentaire` = VALUES(`commentaire`), `note` = VALUES(`note`), `date` = VALUES(`date`);

DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `idContact` int(3) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `numero` int(10) NOT NULL,
  `sujet` varchar(40) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`idContact`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `contact` (`idContact`, `nom`, `email`, `numero`, `sujet`, `message`) VALUES
(1,	'Andréa',	'andrea@bigot974',	692466990,	'compte',	'J\'ai oublié mon mot de passe'),
(2,	'Andréa',	'andrea@bigot974',	692458565,	'subject',	'Problème'),
(3,	'Jérémy',	'andrea@bigot974',	69232231,	'subject',	'Problème avec ma commande')
ON DUPLICATE KEY UPDATE `idContact` = VALUES(`idContact`), `nom` = VALUES(`nom`), `email` = VALUES(`email`), `numero` = VALUES(`numero`), `sujet` = VALUES(`sujet`), `message` = VALUES(`message`);

DROP TABLE IF EXISTS `genre`;
CREATE TABLE `genre` (
  `code` varchar(1) NOT NULL,
  `libelle` varchar(20) NOT NULL,
  PRIMARY KEY (`code`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `genre` (`code`, `libelle`) VALUES
('F',	'Femme'),
('H',	'Homme'),
('M',	'Mixte')
ON DUPLICATE KEY UPDATE `code` = VALUES(`code`), `libelle` = VALUES(`libelle`);

DROP TABLE IF EXISTS `taille`;
CREATE TABLE `taille` (
  `libelle` varchar(3) NOT NULL,
  `type` varchar(15) NOT NULL DEFAULT 'chiffre',
  PRIMARY KEY (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `taille` (`libelle`, `type`) VALUES
('32',	'chiffre'),
('33',	'chiffre'),
('34',	'chiffre'),
('35',	'chiffre'),
('36',	'chiffre'),
('42',	'chiffre'),
('L',	'lettre'),
('M',	'lettre'),
('S',	'lettre'),
('XL',	'lettre'),
('XS',	'lettre')
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`), `type` = VALUES(`type`);

DROP TABLE IF EXISTS `vetement`;
CREATE TABLE `vetement` (
  `id` int(11) NOT NULL,
  `nom` varchar(60) NOT NULL,
  `prix` float NOT NULL,
  `codeRgbOriginal` varchar(10) NOT NULL,
  `motifPosition` varchar(150) NOT NULL,
  `codeGenre` varchar(1) NOT NULL,
  `description` text NOT NULL,
  `idCateg` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `numGenre` (`codeGenre`),
  KEY `idCateg` (`idCateg`),
  CONSTRAINT `vetement_ibfk_2` FOREIGN KEY (`idCateg`) REFERENCES `categorie` (`id`),
  CONSTRAINT `vetement_ibfk_3` FOREIGN KEY (`codeGenre`) REFERENCES `genre` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vetement` (`id`, `nom`, `prix`, `codeRgbOriginal`, `motifPosition`, `codeGenre`, `description`, `idCateg`) VALUES
(1,	'Robe D\'Eté Superposée Fleurie Imprimée',	25.5,	'#fff',	'test',	'F',	'Petite robe imprimée en coton avec des bretelles fines. Matières: rayonne.',	1),
(2,	'Short de Survêtement à Cordon',	10,	'#f3b2c2',	'',	'F',	'Short',	5),
(3,	'T-shirt Manche longue unicolore',	15,	'#fff',	'',	'F',	'Tshirt manche longue en coton.',	2),
(4,	'Pull Court Simple Surdimensionné',	37,	'#8ba3ad',	'testr',	'F',	'Pull court manches longues. Matières: coton, polyester',	4),
(5,	'Pull Court Rayé à Col Rond',	38.2,	'#fff',	'',	'F',	'Pull rayé manches longues au col rond. Matières: polyester, coton',	4),
(6,	'Short Décontracté En Couleur Jointive à Taille Elastique',	13.8,	'#fff',	'',	'H',	'Matières: Polyamide',	5),
(7,	'T-shirt Motif De Lettre Dessin Animé',	15,	'',	'',	'H',	'T-shirt pour homme en coton, col rond.',	2),
(8,	'Pull Tordu à Epaule Dénudée',	20,	'',	'',	'F',	'Pull qui décore avec un design torsadé à l\'avant. Matières: coton, polyacrylique.',	4),
(9,	'Veste Déchirée En Couleur Unie En Denim',	34.9,	'',	'',	'M',	'Veste déchirée avec un col rabattu à manches longues. Matières: coton, polyester.',	8),
(10,	'Pantalon Slim Taille Haute Déchiré',	12,	'',	'',	'F',	'222',	12),
(11,	'Bermuda chino uni',	15,	'',	'',	'H',	'222',	10),
(12,	'T-shirt Graphique Grue Barboteuse Chinoise Fleurie Imprimé',	17.99,	'',	'',	'H',	'T-shirt manches courtes imprimé en coton.',	2),
(13,	'T-shirt Court Sanglé à Col V',	10,	'',	'',	'F',	'T-shirt Court Sanglé à Col V.\r\nMatières: Polyuréthane,Rayonne',	2),
(14,	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée',	11,	'',	'',	'F',	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée. \r\nMatières: Coton,Polyester',	2),
(15,	'Haut Court Côtelé Sans Dos à Bretelle ',	12,	'',	'',	'F',	'Haut Court Côtelé Sans Dos à Bretelle qui met en valeur la taille marquée. \r\nMatières: Polyuréthane,Rayonne',	2),
(16,	' Haut Court Côtelé à Bretelle Trodu',	15,	'',	'',	'F',	'Haut Court Côtelé à Bretelle Trodu.\r\nHaut qui flatte la silhouette avec des fines bretelles mettant en avant le décolleté et le dos. \r\nMatières: Polyuréthane,Rayonne',	2),
(17,	'T-Shirt à Imprimé Rayures En Blocs De Couleurs',	10,	'',	'',	'H',	'Un t-shirt avec un motif à rayures panachées, un col rond, des manches courtes et une coupe classique.\r\nMatières: Polyester',	2),
(18,	'T-shirt Rose Brodée à Manches Courtes',	13.5,	'',	'',	'H',	'T-shirt basique surmonté d\'un col rond et manches courtes.\r\nMatières: Coton,Polyester,Spandex',	2),
(19,	'Veste Déchirée Avec Poche à Rabat En Denim',	37.6,	'',	'',	'H',	'Veste déchirée manches longues.\r\nMatières: Coton,Polyester,Spandex',	8),
(20,	'Pantalon de Survêtement Lettre Applique à Cordon en Laine',	23.5,	'',	'',	'H',	'Pantalon de Survêtement avec élastique à la taille en coton.',	12),
(21,	'Pantalon Panneau En Blocs De Couleurs à Taille Elastique',	19.99,	'',	'',	'H',	'Pantalon à Taille Elastique en polyesther. ',	12),
(22,	'T-shirt Rayé Chiffre Brodé à Manches Longues',	14.9,	'',	'',	'H',	'T-shirt Rayé Chiffre Brodé à Manches Longues\r\nMatières: Coton,Polyacrylique,Polyester',	4),
(23,	'Robe à Bretelle Fleurie Plissée à Volants',	20,	'',	'',	'F',	'Robe à Bretelle Fleurie Plissée à Volants.\r\nLes plis sont réunis avec la taille élastique et le dos smocké aide à façonner les courbes.\r\nMatières: Polyester',	1),
(24,	'Mini Robe à Carreaux Ligne A',	11.2,	'',	'',	'F',	'Détendu en forme, féminin dans le style, cette robe cami dispose d\'une impression tout au long de ceindre, fines bretelles et une coupe mini longueur séduisante, dans une silhouette évasée. portez-le avec des talons pour un style charmant.\r\nMatières: Polyester',	1),
(25,	'Jupe Ligne A Teintée à Cordon',	13,	'',	'',	'F',	'Jupe colorée en polyester. ',	6),
(26,	'Mini Jupe Ligne A Nouée',	14,	'',	'',	'F',	'Jupe courte avec une fermeture zippée. \r\nMatières: Polyester,Polyuréthane',	6),
(27,	'Short Déchiré Zippé Design En Denim',	19.65,	'',	'',	'H',	'Short déchiré zippé en denim.\r\nMatières: Coton,Polyester,Spandex',	10)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `nom` = VALUES(`nom`), `prix` = VALUES(`prix`), `codeRgbOriginal` = VALUES(`codeRgbOriginal`), `motifPosition` = VALUES(`motifPosition`), `codeGenre` = VALUES(`codeGenre`), `description` = VALUES(`description`), `idCateg` = VALUES(`idCateg`);

DROP TABLE IF EXISTS `vet_couleur`;
CREATE TABLE `vet_couleur` (
  `num` int(3) NOT NULL,
  `idVet` int(3) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `filterCssCode` varchar(200) DEFAULT NULL,
  `dispo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`num`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `vet_couleur_ibfk_1` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vet_couleur` (`num`, `idVet`, `nom`, `filterCssCode`, `dispo`) VALUES
(1,	4,	'Rose bonbon',	'hue-rotate(95deg)',	1),
(2,	4,	'Bleu clair',	NULL,	1),
(3,	4,	'Vert Forêt',	'hue-rotate(969deg) brightness(0.9)',	1),
(4,	2,	'Rose bonbon',	NULL,	1),
(5,	3,	'Blanc cassé',	NULL,	1),
(6,	1,	'Rouge',	NULL,	1),
(7,	6,	'Jaune',	NULL,	1),
(8,	5,	'Beige',	NULL,	1),
(9,	11,	'Noir',	NULL,	1),
(10,	7,	'Rose bonbon',	NULL,	1),
(11,	8,	'Marron',	NULL,	1),
(12,	9,	'Noir et blanc',	NULL,	1),
(13,	10,	'Noir terne',	NULL,	1)
ON DUPLICATE KEY UPDATE `num` = VALUES(`num`), `idVet` = VALUES(`idVet`), `nom` = VALUES(`nom`), `filterCssCode` = VALUES(`filterCssCode`), `dispo` = VALUES(`dispo`);

DROP TABLE IF EXISTS `vet_taille`;
CREATE TABLE `vet_taille` (
  `idVet` int(3) NOT NULL,
  `taille` varchar(3) NOT NULL,
  PRIMARY KEY (`taille`,`idVet`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `vet_taille_ibfk_3` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`),
  CONSTRAINT `vet_taille_ibfk_4` FOREIGN KEY (`taille`) REFERENCES `taille` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vet_taille` (`idVet`, `taille`) VALUES
(20,	'33'),
(21,	'35'),
(10,	'36'),
(11,	'42'),
(27,	'42'),
(1,	'L'),
(2,	'L'),
(3,	'L'),
(6,	'L'),
(7,	'L'),
(8,	'L'),
(12,	'L'),
(1,	'M'),
(3,	'M'),
(4,	'M'),
(5,	'M'),
(6,	'M'),
(7,	'M'),
(9,	'M'),
(14,	'M'),
(22,	'M'),
(23,	'M'),
(4,	'S'),
(5,	'S'),
(6,	'S'),
(8,	'S'),
(15,	'S'),
(17,	'S'),
(25,	'S'),
(26,	'S'),
(1,	'XL'),
(2,	'XL'),
(3,	'XL'),
(5,	'XL'),
(6,	'XL'),
(7,	'XL'),
(16,	'XL'),
(18,	'XL'),
(19,	'XL'),
(1,	'XS'),
(6,	'XS'),
(8,	'XS'),
(13,	'XS'),
(24,	'XS')
ON DUPLICATE KEY UPDATE `idVet` = VALUES(`idVet`), `taille` = VALUES(`taille`);

DROP VIEW IF EXISTS `vue_categpargenre`;
CREATE TABLE `vue_categpargenre` (`codeGenre` varchar(1), `ListeIdCategorie` mediumtext);


DROP VIEW IF EXISTS `vue_vet_disponibilite`;
CREATE TABLE `vue_vet_disponibilite` (`idVet` int(11), `listeIdCouleurDispo` mediumtext, `listeTailleDispo` mediumtext);


DROP TABLE IF EXISTS `vue_categpargenre`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vue_categpargenre` AS select `g`.`code` AS `codeGenre`,group_concat(distinct `v`.`idCateg` separator ',') AS `ListeIdCategorie` from (`genre` `g` join `vetement` `v` on(`v`.`codeGenre` = `g`.`code`)) group by `v`.`codeGenre` order by `g`.`code`;

DROP TABLE IF EXISTS `vue_vet_disponibilite`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vue_vet_disponibilite` AS select `v`.`id` AS `idVet`,group_concat(distinct `vcl`.`num` separator ',') AS `listeIdCouleurDispo`,group_concat(distinct `vt`.`taille` separator ',') AS `listeTailleDispo` from ((`vetement` `v` left join `vet_couleur` `vcl` on(`vcl`.`idVet` = `v`.`id`)) left join `vet_taille` `vt` on(`vt`.`idVet` = `v`.`id`)) group by `v`.`id`;

-- 2020-09-29 18:28:36
