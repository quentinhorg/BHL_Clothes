-- Adminer 4.7.7 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `bhl_clothes` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `bhl_clothes`;

DROP TABLE IF EXISTS `article_panier`;
CREATE TABLE `article_panier` (
  `numCmd` int(11) NOT NULL,
  `idVet` int(3) NOT NULL,
  `idTaille` int(3) NOT NULL,
  `qte` int(11) NOT NULL,
  `idCouleur` int(11) NOT NULL,
  PRIMARY KEY (`idVet`,`numCmd`,`idCouleur`,`idTaille`) USING BTREE,
  KEY `CONTIENT_commande0_FK` (`numCmd`),
  KEY `idTaille` (`idTaille`),
  CONSTRAINT `CONTIENT_commande0_FK` FOREIGN KEY (`numCmd`) REFERENCES `commande` (`num`),
  CONSTRAINT `CONTIENT_vetement_FK` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`),
  CONSTRAINT `article_panier_ibfk_1` FOREIGN KEY (`idTaille`) REFERENCES `taille` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article_panier` (`numCmd`, `idVet`, `idTaille`, `qte`, `idCouleur`) VALUES
(1,	1,	1,	2,	0),
(1,	2,	2,	1,	0),
(1,	3,	2,	1,	0);

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `categorie` (`id`, `nom`) VALUES
(9,	'Chemisiers & Tuniques'),
(7,	'Gilets'),
(3,	'Jeans'),
(6,	'Jupes'),
(11,	'Pantacourts'),
(12,	'Pantalon '),
(4,	'Pulls'),
(1,	'Robes'),
(10,	'Shorts & Bermudas'),
(5,	'Shorts de bain'),
(2,	'T-shirts & Débardeurs'),
(8,	'Vestes & Manteaux');

DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

INSERT INTO `client` (`id`, `email`, `nom`, `prenom`, `adresse`, `tel`) VALUES
(1,	'andrea@gmail.com',	'BIGOT',	'Andréa',	'22 rue des frangipaniers St Joseph',	'0692466990'),
(2,	'quentin@live.fr',	'HOAREAU',	'Quentin',	'17 chemin des hirondelles St pierre',	'0694458553'),
(3,	'lebon@outlook.fr',	'LEBON',	'Jérémy',	'26 rue des corbeilles d\'or St denis',	'0693122478'),
(4,	'grondin.sam@gmail.com',	'GRONDIN',	'Samuel',	'88 rue des lilas Saint-Joseph ',	'0693238645'),
(5,	'ryan.lauret974@gmail.com',	'LAURET',	'Ryan',	'50 chemin Général de Gaulle Saint Pierre',	'0692851347'),
(6,	'mathilde20@gmail.com',	'PAYET',	'Mathilde',	'10 rue des marsouins Saint Joseph ',	'0692753212');

DELIMITER ;;

CREATE TRIGGER `after_update_client` AFTER UPDATE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.nom, OLD.prenom, OLD.adresse, OLD.tel, NOW(), "UPDATE");
END;;

CREATE TRIGGER `after_delete_client` AFTER DELETE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.nom, OLD.prenom, OLD.adresse, OLD.tel, NOW(), "UPDATE");
END;;

DELIMITER ;

DROP TABLE IF EXISTS `client_histo`;
CREATE TABLE `client_histo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `adresse` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  `date_histo` datetime NOT NULL,
  `evenement_histo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`,`date_histo`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

INSERT INTO `client_histo` (`id`, `nom`, `prenom`, `adresse`, `tel`, `date_histo`, `evenement_histo`) VALUES
(1,	'BIGOT',	'Andréazzzz',	'St jo',	'0692466990',	'2020-09-07 00:00:00',	'UPDATE'),
(1,	'BIGOT',	'test',	'St jo',	'0692466990',	'2020-09-13 18:25:40',	'UPDATE'),
(1,	'BIGOT',	'test',	'St Joseph',	'0692466990',	'2020-09-13 18:25:46',	'UPDATE'),
(1,	'BIGOT',	'Andréa',	'St Joseph',	'0692466990',	'2020-09-13 18:32:19',	'UPDATE'),
(1,	'BIGOT',	'Andréa',	'St Joseph',	'0692466990',	'2020-09-13 18:36:43',	'UPDATE'),
(2,	'HOAREAU',	'Quentin',	'St pierre',	'426525',	'2020-09-13 18:31:38',	'UPDATE'),
(2,	'HOAREAU',	'Quentin',	'St pierre',	'0694458553',	'2020-09-13 18:32:37',	'UPDATE'),
(2,	'HOAREAU',	'Quentin',	'St pierre',	'0694458553',	'2020-09-13 18:37:01',	'UPDATE'),
(3,	'LEBON',	'Jérémy',	'St denis',	'8285252',	'2020-09-13 18:31:56',	'UPDATE'),
(3,	'LEBON',	'Jérémy',	'St denis',	'0693122478',	'2020-09-13 18:32:27',	'UPDATE'),
(3,	'LEBON',	'Jérémy',	'St denis',	'0693122478',	'2020-09-13 18:37:31',	'UPDATE'),
(4,	'test',	'test',	'22 st jo',	'2485',	'2020-09-07 00:00:00',	'DELETE'),
(5,	'',	'',	'',	'',	'2020-09-07 00:00:00',	'DELETE'),
(6,	'aa',	'aa',	'ashia',	'798',	'2020-09-07 00:00:00',	'DELETE'),
(7,	'azeaze',	'zerzer',	'10 rue ouaiso uais',	'azeaze',	'2020-09-07 00:00:00',	'DELETE');

DROP TABLE IF EXISTS `commande`;
CREATE TABLE `commande` (
  `num` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `idClient` int(11) NOT NULL,
  PRIMARY KEY (`num`),
  KEY `commande_client_FK` (`idClient`),
  CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `commande` (`num`, `date`, `idClient`) VALUES
(1,	'2019-12-02 12:30:00',	1),
(2,	'2019-12-17 18:48:11',	2),
(3,	'2020-12-23 08:02:08',	3),
(4,	'2020-09-01 21:49:35',	6),
(5,	'2020-09-17 11:00:00',	5),
(6,	'2020-09-13 14:18:23',	4);

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
(3,	'Jérémy',	'andrea@bigot974',	69232231,	'subject',	'Problème avec ma commande');

DROP TABLE IF EXISTS `genre`;
CREATE TABLE `genre` (
  `num` int(11) NOT NULL,
  `libelle` varchar(20) NOT NULL,
  `genre` varchar(1) NOT NULL,
  PRIMARY KEY (`num`),
  UNIQUE KEY `libelle` (`libelle`),
  UNIQUE KEY `genre` (`genre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `genre` (`num`, `libelle`, `genre`) VALUES
(1,	'Femme',	'F'),
(2,	'Homme',	'H'),
(3,	'Mixte',	'M');

DROP TABLE IF EXISTS `taille`;
CREATE TABLE `taille` (
  `id` int(3) NOT NULL,
  `libelle` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `taille` (`id`, `libelle`) VALUES
(1,	'XS'),
(2,	'S'),
(3,	'M'),
(4,	'L'),
(5,	'XL');

DROP TABLE IF EXISTS `vetement`;
CREATE TABLE `vetement` (
  `id` int(11) NOT NULL,
  `nom` varchar(60) NOT NULL,
  `prix` float NOT NULL,
  `codeRgbOriginal` varchar(10) NOT NULL,
  `motifPosition` varchar(150) NOT NULL,
  `numGenre` int(11) NOT NULL,
  `description` text NOT NULL,
  `idCateg` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `numGenre` (`numGenre`),
  KEY `idCateg` (`idCateg`),
  CONSTRAINT `vetement_ibfk_1` FOREIGN KEY (`numGenre`) REFERENCES `genre` (`num`),
  CONSTRAINT `vetement_ibfk_2` FOREIGN KEY (`idCateg`) REFERENCES `categorie` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vetement` (`id`, `nom`, `prix`, `codeRgbOriginal`, `motifPosition`, `numGenre`, `description`, `idCateg`) VALUES
(1,	'Robe D\'Eté Superposée Fleurie Imprimée',	25.5,	'#fff',	'test',	1,	'Petite robe imprimée en coton avec des bretelles fines. Matières: rayonne.',	1),
(2,	'Short de Survêtement à Cordon',	10,	'#f3b2c2',	'',	1,	'Short',	5),
(3,	'T-shirt Manche longue unicolore',	15,	'#fff',	'',	1,	'Tshirt manche longue en coton.',	2),
(4,	'Pull Court Simple Surdimensionné',	37,	'#8ba3ad',	'testr',	1,	'Pull court manches longues. Matières: coton, polyester',	4),
(5,	'Pull Court Rayé à Col Rond',	38.2,	'#fff',	'',	1,	'Pull rayé manches longues au col rond. Matières: polyester, coton',	4),
(6,	'Short Décontracté En Couleur Jointive à Taille Elastique',	13.8,	'#fff',	'',	2,	'Matières: Polyamide',	5),
(7,	'T-shirt Motif De Lettre Dessin Animé',	15,	'',	'',	2,	'T-shirt pour homme en coton, col rond.',	2),
(8,	'Pull Tordu à Epaule Dénudée',	20,	'',	'',	3,	'Pull qui décore avec un design torsadé à l\'avant. Matières: coton, polyacrylique.',	4),
(9,	'Veste Déchirée En Couleur Unie En Denim',	34.9,	'',	'',	3,	'Veste déchirée avec un col rabattu à manches longues. Matières: coton, polyester.',	8),
(10,	'Pantalon slim',	12,	'',	'',	1,	'222',	12),
(11,	'Bermuda chino uni',	15,	'',	'',	2,	'222',	10),
(12,	'T-shirt Graphique Grue Barboteuse Chinoise Fleurie Imprimé',	17.99,	'',	'',	2,	'T-shirt manches courtes imprimé en coton.',	2),
(13,	'T-shirt Court Sanglé à Col V',	10,	'',	'',	1,	'T-shirt Court Sanglé à Col V.\r\nMatières: Polyuréthane,Rayonne',	2),
(14,	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée',	11,	'',	'',	1,	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée. \r\nMatières: Coton,Polyester',	2),
(15,	'Haut Court Côtelé Sans Dos à Bretelle ',	12,	'',	'',	1,	'Haut Court Côtelé Sans Dos à Bretelle qui met en valeur la taille marquée. \r\nMatières: Polyuréthane,Rayonne',	2),
(16,	' Haut Court Côtelé à Bretelle Trodu',	15,	'',	'',	1,	'Haut Court Côtelé à Bretelle Trodu.\r\nHaut qui flatte la silhouette avec des fines bretelles mettant en avant le décolleté et le dos. \r\nMatières: Polyuréthane,Rayonne',	2),
(17,	'T-Shirt à Imprimé Rayures En Blocs De Couleurs',	10,	'',	'',	2,	'Un t-shirt avec un motif à rayures panachées, un col rond, des manches courtes et une coupe classique.\r\nMatières: Polyester',	2),
(18,	'T-shirt Rose Brodée à Manches Courtes',	13.5,	'',	'',	2,	'T-shirt basique surmonté d\'un col rond et manches courtes.\r\nMatières: Coton,Polyester,Spandex',	2),
(19,	'Veste Déchirée Avec Poche à Rabat En Denim',	37.6,	'',	'',	2,	'Veste déchirée manches longues.\r\nMatières: Coton,Polyester,Spandex',	8),
(20,	'Pantalon de Survêtement Lettre Applique à Cordon en Laine',	23.5,	'',	'',	2,	'Pantalon de Survêtement avec élastique à la taille en coton.',	12),
(21,	'Pantalon Panneau En Blocs De Couleurs à Taille Elastique',	19.99,	'',	'',	2,	'Pantalon à Taille Elastique en polyesther. ',	12),
(22,	'T-shirt Rayé Chiffre Brodé à Manches Longues',	14.9,	'',	'',	2,	'T-shirt Rayé Chiffre Brodé à Manches Longues\r\nMatières: Coton,Polyacrylique,Polyester',	4),
(23,	'Robe à Bretelle Fleurie Plissée à Volants',	20,	'',	'',	1,	'Robe à Bretelle Fleurie Plissée à Volants.\r\nLes plis sont réunis avec la taille élastique et le dos smocké aide à façonner les courbes.\r\nMatières: Polyester',	1),
(24,	'Mini Robe à Carreaux Ligne A',	11.2,	'',	'',	1,	'Détendu en forme, féminin dans le style, cette robe cami dispose d\'une impression tout au long de ceindre, fines bretelles et une coupe mini longueur séduisante, dans une silhouette évasée. portez-le avec des talons pour un style charmant.\r\nMatières: Polyester',	1),
(25,	'Jupe Ligne A Teintée à Cordon',	13,	'',	'',	1,	'Jupe colorée en polyester. ',	6),
(26,	'Mini Jupe Ligne A Nouée',	14,	'',	'',	1,	'Jupe courte avec une fermeture zippée. \r\nMatières: Polyester,Polyuréthane',	6),
(27,	'Short Déchiré Zippé Design En Denim',	19.65,	'',	'',	2,	'Short déchiré zippé en denim.\r\nMatières: Coton,Polyester,Spandex',	10);

DROP TABLE IF EXISTS `vet_couleur`;
CREATE TABLE `vet_couleur` (
  `num` int(3) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `idVet` int(3) NOT NULL,
  `filterCssCode` varchar(200) DEFAULT NULL,
  `dispo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`num`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `vet_couleur_ibfk_1` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vet_couleur` (`num`, `nom`, `idVet`, `filterCssCode`, `dispo`) VALUES
(1,	'Rose bonbon',	4,	'hue-rotate(95deg)',	1),
(2,	'Bleu clair',	4,	NULL,	1),
(3,	'Vert Forêt',	4,	'hue-rotate(969deg) brightness(0.9)',	1),
(4,	'Rose bonbon',	2,	NULL,	1),
(5,	'Blanc cassé',	3,	NULL,	1),
(6,	'Rouge',	1,	NULL,	1),
(7,	'Jaune',	6,	NULL,	1),
(8,	'Beige',	5,	NULL,	1);

DROP TABLE IF EXISTS `vet_taille`;
CREATE TABLE `vet_taille` (
  `idVet` int(3) NOT NULL,
  `idTaille` int(3) NOT NULL,
  PRIMARY KEY (`idTaille`,`idVet`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `vet_taille_ibfk_1` FOREIGN KEY (`idTaille`) REFERENCES `taille` (`id`),
  CONSTRAINT `vet_taille_ibfk_3` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vet_taille` (`idVet`, `idTaille`) VALUES
(4,	1),
(5,	1),
(6,	1),
(8,	1),
(1,	2),
(3,	2),
(4,	2),
(5,	2),
(6,	2),
(7,	2),
(1,	3),
(2,	3),
(3,	3),
(5,	3),
(6,	3),
(7,	3),
(1,	4),
(2,	4),
(3,	4),
(6,	4),
(7,	4),
(8,	4),
(1,	5),
(6,	5),
(8,	5);

DROP VIEW IF EXISTS `vue_categpargenre`;
CREATE TABLE `vue_categpargenre` (`num` int(11), `libelle` varchar(20), `genre` varchar(1), `ListeIdCategorie` mediumtext);


DROP VIEW IF EXISTS `vue_vet_disponibilite`;
CREATE TABLE `vue_vet_disponibilite` (`idVet` int(11), `listeIdCouleurDispo` mediumtext, `listeIdTailleDispo` mediumtext);


DROP TABLE IF EXISTS `vue_categpargenre`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vue_categpargenre` AS select `g`.`num` AS `num`,`g`.`libelle` AS `libelle`,`g`.`genre` AS `genre`,group_concat(distinct `v`.`idCateg` separator ',') AS `ListeIdCategorie` from (`genre` `g` join `vetement` `v` on(`v`.`numGenre` = `g`.`num`)) group by `v`.`numGenre` order by `g`.`num`;

DROP TABLE IF EXISTS `vue_vet_disponibilite`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vue_vet_disponibilite` AS select `v`.`id` AS `idVet`,group_concat(distinct `vcl`.`num` separator ',') AS `listeIdCouleurDispo`,group_concat(distinct `vt`.`idTaille` separator ',') AS `listeIdTailleDispo` from ((`vetement` `v` left join `vet_couleur` `vcl` on(`vcl`.`idVet` = `v`.`id`)) left join `vet_taille` `vt` on(`vt`.`idVet` = `v`.`id`)) group by `v`.`id`;

-- 2020-09-13 19:32:36
