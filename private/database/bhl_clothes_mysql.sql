-- Adminer 4.7.7 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP DATABASE IF EXISTS `bhl_clothes`;
CREATE DATABASE `bhl_clothes` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `bhl_clothes`;

DELIMITER ;;

CREATE FUNCTION `calcCmdHT`(`_numCmd` int) RETURNS float
BEGIN
          
        RETURN (SELECT ROUND(sum(ap.qte*v.prix),2) AS 'prixTTC' FROM article_panier ap 
INNER JOIN vetement v ON v.id = ap.idVet
WHERE ap.numCmd = _numCmd );
    END;;

CREATE FUNCTION `calcCmdTTC`(`_numCmd` int) RETURNS float
BEGIN
RETURN (

SELECT ROUND(calcCmdHT(cmd.num)+cp.prixLiv,2) AS 'prixTTC' 
FROM commande cmd
INNER JOIN client c ON c.id= cmd.idClient
INNER JOIN code_postal cp ON cp.cp=c.codePostal
WHERE cmd.num = _numCmd

);

END;;

CREATE FUNCTION `qte_article`(_numCmd int(11), _idVet int(3), _taille varchar(3), _numClr int(11)) RETURNS int(11)
BEGIN
  RETURN (SELECT qte
  FROM article_panier ap
  WHERE ap.numCmd = _numCmd 
  AND ap.idVet = _idVet 
  AND ap.taille  = _taille 
  AND ap.numClr = _numClr);
END;;

CREATE PROCEDURE `insert_article`(_numCmd int(11), _idVet int(3), _taille varchar(3), _numClr int(11), _qte int)
BEGIN
DECLARE newOrdreArr tinyint;
DECLARE qteArticle int;

SET qteArticle = (SELECT qte_article(_numCmd , _idVet , _taille , _numClr));

SET newOrdreArr = (
SELECT
CASE 
WHEN MAX(ap.ordreArrivee) IS NULL THEN 1
WHEN MAX(ap.ordreArrivee) = (
  SELECT ap2.ordreArrivee FROM article_panier ap2
  WHERE ap2.numCmd = _numCmd 
  AND ap2.idVet = _idVet 
  AND ap2.taille  = _taille 
  AND ap2.numClr = _numClr
) THEN MAX(ap.ordreArrivee) 
ELSE MAX(ap.ordreArrivee)+1
END AS 'newOrdreArr'
  FROM article_panier ap
  WHERE ap.numCmd = _numCmd
  ORDER BY ap.ordreArrivee DESC
 
);


IF( qteArticle >= 1) THEN 
  UPDATE article_panier SET qte = qteArticle + _qte, ordreArrivee = newOrdreArr
  WHERE numCmd = _numCmd 
  AND idVet = _idVet 
  AND taille  = _taille 
  AND numClr = _numClr ;
ELSE
INSERT INTO article_panier VALUES(_numCmd , _idVet , _taille , _numClr, _qte, newOrdreArr) ;
END IF;

END;;

CREATE PROCEDURE `payerCommandeViaSolde`(IN `_idClient` int, IN `_numCmd` int)
BEGIN
            DECLARE soldeClient float; DECLARE montantCmdTTC float; DECLARE etatCmd float;
            DECLARE nomCli varchar(200); DECLARE prenomCli varchar(200); DECLARE rueCli varchar(200); DECLARE cpCli varchar(5);

            SET soldeClient = ( SELECT c.solde FROM client c WHERE c.id = _idClient ) ;
            SET montantCmdTTC = ( SELECT calcCmdTTC(cmd.num) FROM commande cmd WHERE cmd.num = _numCmd AND cmd.idClient = _idClient ) ;
            SET etatCmd = ( SELECT cmd2.idEtat FROM commande cmd2 WHERE cmd2.num = _numCmd) ;

            IF (etatCmd = 1) THEN
              IF (soldeClient > montantCmdTTC AND montantCmdTTC IS NOT NULL AND soldeClient IS NOT NULL ) THEN 
                UPDATE commande SET idEtat = 2 WHERE num = _numCmd ; 

                #Récupération des infos Clients
                 SELECT c.nom, c.prenom, c.rue, c.codePostal
                 INTO  nomCli, prenomCli, rueCli, cpCli
                 FROM client c
                 where c.id = _idClient;


                INSERT INTO facture VALUES(_numCmd, nomCli, prenomCli, rueCli, cpCli, "Solde", NOW());
                UPDATE client SET solde = (soldeClient-montantCmdTTC) WHERE id = _idClient ; 
              ELSE
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur: La paiement n\'a pas été effectué.';
              END IF;
            ELSE
              SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Erreur: La commande a déjà été payé.'; 
            END IF; 
           
      
    END;;

DELIMITER ;

DROP TABLE IF EXISTS `article_panier`;
CREATE TABLE `article_panier` (
  `numCmd` int(11) NOT NULL,
  `idVet` int(3) NOT NULL,
  `taille` varchar(3) NOT NULL,
  `numClr` int(11) NOT NULL,
  `qte` int(11) NOT NULL,
  `ordreArrivee` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idVet`,`numCmd`,`numClr`,`taille`) USING BTREE,
  UNIQUE KEY `ordreArrivee_numCmd` (`ordreArrivee`,`numCmd`),
  KEY `CONTIENT_commande0_FK` (`numCmd`),
  KEY `idTaille` (`taille`),
  KEY `numClr` (`numClr`),
  CONSTRAINT `CONTIENT_commande0_FK` FOREIGN KEY (`numCmd`) REFERENCES `commande` (`num`),
  CONSTRAINT `CONTIENT_vetement_FK` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`),
  CONSTRAINT `article_panier_ibfk_2` FOREIGN KEY (`numClr`) REFERENCES `vet_couleur` (`num`),
  CONSTRAINT `article_panier_ibfk_3` FOREIGN KEY (`taille`) REFERENCES `taille` (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article_panier` (`numCmd`, `idVet`, `taille`, `numClr`, `qte`, `ordreArrivee`) VALUES
(1,	1,	'XL',	6,	3,	2),
(12,	1,	'L',	18,	6,	6),
(1,	2,	'S',	4,	1,	3),
(3,	2,	'XL',	4,	1,	10),
(3,	3,	'L',	5,	1,	20),
(8,	3,	'M',	5,	1,	27),
(3,	4,	'M',	1,	1,	2),
(3,	4,	'M',	2,	1,	9),
(12,	4,	'M',	2,	1,	4),
(14,	4,	'M',	1,	1,	1),
(16,	4,	'M',	3,	1,	2),
(3,	6,	'L',	15,	1,	19),
(3,	6,	'L',	16,	1,	14),
(3,	6,	'M',	16,	1,	15),
(3,	6,	'XL',	16,	2,	16),
(3,	6,	'XS',	16,	1,	17),
(8,	6,	'L',	7,	4,	25),
(8,	6,	'L',	16,	1,	28),
(17,	6,	'L',	7,	1,	8),
(17,	6,	'M',	7,	1,	9),
(17,	6,	'S',	7,	1,	10),
(17,	6,	'XL',	7,	1,	11),
(17,	6,	'XS',	7,	1,	12),
(17,	6,	'L',	15,	1,	15),
(17,	6,	'XS',	15,	1,	14),
(17,	6,	'XS',	16,	1,	13),
(3,	7,	'L',	10,	2,	21),
(3,	7,	'M',	10,	1,	22),
(3,	7,	'XL',	10,	1,	23),
(3,	7,	'L',	17,	1,	25),
(3,	7,	'M',	17,	1,	26),
(3,	7,	'XL',	17,	2,	27),
(13,	7,	'M',	17,	1,	3),
(16,	7,	'L',	17,	3,	1),
(17,	7,	'L',	10,	1,	2),
(17,	7,	'M',	10,	1,	3),
(17,	7,	'XL',	10,	1,	4),
(17,	7,	'L',	17,	1,	7),
(17,	7,	'M',	17,	1,	6),
(17,	7,	'XL',	17,	1,	5),
(7,	8,	'L',	11,	1,	1),
(8,	8,	'S',	11,	3,	29),
(9,	8,	'S',	11,	1,	1),
(10,	9,	'M',	12,	1,	2),
(3,	10,	'36',	13,	4,	13),
(13,	10,	'36',	13,	1,	2),
(15,	10,	'36',	13,	8,	1),
(3,	11,	'42',	9,	1,	4),
(10,	11,	'42',	9,	1,	3),
(12,	11,	'42',	9,	1,	5),
(13,	11,	'42',	9,	1,	1),
(17,	11,	'42',	9,	1,	1)
ON DUPLICATE KEY UPDATE `numCmd` = VALUES(`numCmd`), `idVet` = VALUES(`idVet`), `taille` = VALUES(`taille`), `numClr` = VALUES(`numClr`), `qte` = VALUES(`qte`), `ordreArrivee` = VALUES(`ordreArrivee`);

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
                   WHERE idVet=NEW.idVet AND num = NEW.numClr);

IF (couleurDispo IS NULL) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Erreur: Ce vêtement n'est pas disponible dans cette couleur.";
end if;



END;;

CREATE TRIGGER `before_update_ap` BEFORE UPDATE ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE tailleDispo int;
SET tailleDispo= (SELECT COUNT(taille) FROM vet_taille  WHERE idVet=NEW.idVet AND taille LIKE NEW.taille );
IF (tailleDispo = 0) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Erreur: Ce vêtement n'est plus de stock disponible dans cette taille.";
end if;



END;;

CREATE TRIGGER `article_panier_ad` AFTER DELETE ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE nbArticle int;
SET nbArticle= ( SELECT COUNT(*) AS 'nbArticle' FROM article_panier ap WHERE ap.numCmd = OLD.numCmd);
IF (nbArticle= 0 OR nbArticle IS NULL ) THEN
   DELETE FROM commande WHERE num = OLD.numCmd ;
end if;
END;;

DELIMITER ;

DROP TABLE IF EXISTS `avis`;
CREATE TABLE `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idClient` int(11) NOT NULL,
  `idVet` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `note` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idClient` (`idClient`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`),
  CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

INSERT INTO `avis` (`id`, `idClient`, `idVet`, `commentaire`, `note`, `date`) VALUES
(1,	8,	1,	'woooooaaaww',	4,	'2020-10-05 21:48:01'),
(2,	1,	7,	'Tshirt de bonne qualité qui taille un peu large. Parfait pour faire un style oversize ! ',	5,	'2020-10-09 17:30:14'),
(3,	5,	6,	'Short de bonne qualité, conforme à la photo',	4,	'2020-10-01 21:55:01'),
(4,	1,	1,	'Je trouve que la robe est un peu transparente à la lumière mais ce problème est vite réglé avec un petit short en dessous',	4,	'2020-10-06 21:57:09'),
(5,	6,	1,	'Elle correspond à mes attentes et la livraison était plutôt rapide! \r\nBon produit',	5,	'2020-10-10 21:58:28'),
(6,	8,	11,	'Je suis déçu, la texture blanchit facilement. ',	1,	'2020-10-11 00:03:20'),
(7,	11,	10,	'Ce pantalon est sympa mais un peu grand pour un 36',	3,	'2020-10-13 20:40:17'),
(8,	4,	4,	'Matière souple et confortable. Bon pull',	4,	'2020-10-13 20:51:44')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `idClient` = VALUES(`idClient`), `idVet` = VALUES(`idVet`), `commentaire` = VALUES(`commentaire`), `note` = VALUES(`note`), `date` = VALUES(`date`);

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
  `codePostal` varchar(5) NOT NULL,
  `rue` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  `solde` float NOT NULL DEFAULT 100,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

INSERT INTO `client` (`id`, `email`, `mdp`, `nom`, `prenom`, `codePostal`, `rue`, `tel`, `solde`) VALUES
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	780),
(2,	'quentin@live.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97400',	'7 impasse jesus',	'0694458553',	45.15),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97469',	'6 impasse du cocon',	'0692851347',	84.6),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97469',	'9 chemin des zoizeau',	'0692753212',	984.2),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97466',	'3 rue de lameme',	'65454',	351),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	418),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'test',	'6969',	100),
(10,	'roro13@gmail.com',	'3eddfbf3c48b779222cd8eebb3e137614d5ffee2',	'Robin',	'Jean',	'97413',	'36 rue des merisier ',	'roro',	100),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97419',	'34 rue des fleurs',	'0693455667',	100)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `mdp` = VALUES(`mdp`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `codePostal` = VALUES(`codePostal`), `rue` = VALUES(`rue`), `tel` = VALUES(`tel`), `solde` = VALUES(`solde`);

DELIMITER ;;

CREATE TRIGGER `after_update_client` AFTER UPDATE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.email,OLD.mdp,  
OLD.nom, OLD.prenom, OLD.codePostal, OLD.rue, OLD.tel, OLD.solde, NOW(),  "UPDATE");
END;;

CREATE TRIGGER `after_delete_client` AFTER DELETE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.email,OLD.mdp,  
OLD.nom, OLD.prenom, OLD.codePostal, OLD.rue, OLD.tel, OLD.solde, NOW(),  "DELETE");
END;;

DELIMITER ;

DROP TABLE IF EXISTS `client_histo`;
CREATE TABLE `client_histo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `mdp` varchar(150) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `codePostal` varchar(5) NOT NULL,
  `rue` varchar(100) NOT NULL,
  `tel` varchar(10) NOT NULL,
  `solde` float NOT NULL,
  `date_histo` datetime NOT NULL,
  `evenement_histo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`,`date_histo`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

INSERT INTO `client_histo` (`id`, `email`, `mdp`, `nom`, `prenom`, `codePostal`, `rue`, `tel`, `solde`, `date_histo`, `evenement_histo`) VALUES
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'22 rue des frangipaniers St Joseph',	'0692466990',	613.6,	'2020-10-11 21:19:45',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'22 rue des frangipaniers St Joseph',	'0692466990',	800,	'2020-10-11 21:32:13',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'',	'0692466990',	780,	'2020-10-13 16:33:36',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'974',	'',	'0692466990',	780,	'2020-10-13 16:33:43',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'',	'0692466990',	780,	'2020-10-13 16:34:07',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97410',	'4 rue papangue',	'0692466990',	780,	'2020-10-13 18:08:01',	'UPDATE'),
(2,	'quentin@live.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'',	'',	'0694458553',	45.15,	'2020-10-13 16:34:07',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'',	'',	'0693122478',	85.6,	'2020-10-13 16:34:07',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	85.6,	'2020-10-14 22:11:41',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	1200,	'2020-10-14 22:11:52',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	10200,	'2020-10-14 22:14:01',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9954.4,	'2020-10-14 22:16:39',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'',	'',	'0693238645',	45.15,	'2020-10-13 16:34:07',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'',	'',	'0692851347',	84.6,	'2020-10-13 16:34:07',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'',	'',	'0692753212',	984.2,	'2020-10-13 16:34:07',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'',	'',	'65454',	351,	'2020-10-13 16:34:07',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'',	'',	'0628468787',	656,	'2020-10-13 16:34:07',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97442',	'8 chemin coquelicots',	'0628468787',	656,	'2020-10-13 18:00:16',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97442',	'rue test',	'0628468787',	656,	'2020-10-13 18:07:17',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:07:20',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:07:34',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:07:43',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:07:44',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:08:11',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'2020-10-13 18:09:05',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'2020-10-13 18:09:08',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'2020-10-13 18:15:22',	'UPDATE'),
(8,	'goldow9744@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'2020-10-13 18:17:55',	'UPDATE'),
(8,	'goldow9744@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'2020-10-14 17:13:43',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'2020-10-16 23:05:10',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	530,	'2020-10-16 23:37:04',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'test rue',	'6969',	100,	'2020-10-13 17:25:03',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'rue du test',	'6969',	100,	'2020-10-13 17:25:12',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'rue du test',	'6969',	100,	'2020-10-13 17:57:50',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'ruelolo',	'6969',	100,	'2020-10-13 17:59:50',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'lala',	'6969',	100,	'2020-10-13 18:07:11',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97419',	'lolo',	'6969',	100,	'2020-10-13 18:07:24',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97430',	'lolo',	'6969',	100,	'2020-10-13 18:07:42',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97441',	'lolo',	'6969',	100,	'2020-10-13 18:08:16',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97400',	'lele',	'6969',	100,	'2020-10-13 18:09:05',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'aaa',	'6969',	100,	'2020-10-13 18:09:51',	'UPDATE'),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'aaa',	'6969',	100,	'2020-10-13 18:14:59',	'UPDATE'),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'test',	'6969',	100,	'2020-10-13 18:15:20',	'UPDATE')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `mdp` = VALUES(`mdp`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `codePostal` = VALUES(`codePostal`), `rue` = VALUES(`rue`), `tel` = VALUES(`tel`), `solde` = VALUES(`solde`), `date_histo` = VALUES(`date_histo`), `evenement_histo` = VALUES(`evenement_histo`);

DROP TABLE IF EXISTS `code_postal`;
CREATE TABLE `code_postal` (
  `cp` varchar(5) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `prixLiv` float NOT NULL DEFAULT 30,
  PRIMARY KEY (`cp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `code_postal` (`cp`, `libelle`, `prixLiv`) VALUES
('97400',	'Saint-Denis',	30),
('97410',	'Saint-Pierre',	30),
('97412',	'Bras-Panon',	30),
('97413',	'Cilaos',	0),
('97414',	'Entre-Deux',	30),
('97419',	'La Possession',	30),
('97420',	'Le port',	30),
('97425',	'Les Avirons',	30),
('97426',	'Trois-Bassins',	30),
('97427',	'L\'Etang-salé',	30),
('97429',	'Petit-Ile',	30),
('97430',	'Tampon',	30),
('97431',	'La Plaine des Palmistes',	30),
('97433',	'Salazie',	30),
('97436',	'Saint-Leu',	30),
('97438',	'Sainte-Marie',	30),
('97439',	'Sainte-Rose',	30),
('97440',	'Saint-André',	30),
('97441',	'Sainte-Suzanne',	30),
('97442',	'Saint-Philippe',	30),
('97450',	'Saint-Louis',	30),
('97460',	'Saint-Paul',	30),
('97470',	'Saint-Benoit',	30),
('97480',	'Saint-Joseph',	30)
ON DUPLICATE KEY UPDATE `cp` = VALUES(`cp`), `libelle` = VALUES(`libelle`), `prixLiv` = VALUES(`prixLiv`);

DROP TABLE IF EXISTS `commande`;
CREATE TABLE `commande` (
  `num` int(11) NOT NULL,
  `idClient` int(11) NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `idEtat` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`num`),
  KEY `commande_client_FK` (`idClient`),
  KEY `idEtat` (`idEtat`),
  CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`),
  CONSTRAINT `commande_ibfk_3` FOREIGN KEY (`idEtat`) REFERENCES `etat` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `commande` (`num`, `idClient`, `dateCreation`, `idEtat`) VALUES
(1,	1,	'2019-12-02 12:30:00',	3),
(2,	2,	'2019-12-02 12:30:00',	1),
(3,	3,	'2020-10-14 22:16:39',	1),
(5,	5,	'2019-12-02 12:30:00',	1),
(7,	8,	'2020-10-01 21:05:30',	2),
(8,	8,	'2020-10-11 14:21:20',	2),
(9,	1,	'2020-10-11 21:32:13',	2),
(10,	1,	'2019-12-02 12:30:00',	1),
(12,	10,	'2019-12-02 12:30:00',	1),
(13,	11,	'2019-12-02 12:30:00',	1),
(14,	4,	'2019-12-02 12:30:00',	1),
(15,	8,	'2020-10-16 23:05:10',	2),
(16,	8,	'2020-10-16 23:37:04',	2),
(17,	8,	'0000-00-00 00:00:00',	1)
ON DUPLICATE KEY UPDATE `num` = VALUES(`num`), `idClient` = VALUES(`idClient`), `dateCreation` = VALUES(`dateCreation`), `idEtat` = VALUES(`idEtat`);

DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `idContact` int(3) NOT NULL AUTO_INCREMENT,
  `nom` varchar(20) NOT NULL,
  `email` varchar(20) NOT NULL,
  `numero` int(10) NOT NULL,
  `sujet` varchar(40) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`idContact`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

INSERT INTO `contact` (`idContact`, `nom`, `email`, `numero`, `sujet`, `message`) VALUES
(1,	'Andréa',	'andrea@bigot974',	692466990,	'compte',	'J\'ai oublié mon mot de passe'),
(2,	'Andréa',	'andrea@bigot974',	692458565,	'subject',	'Problème'),
(3,	'Jérémy',	'andrea@bigot974',	69232231,	'subject',	'Problème avec ma commande'),
(4,	'test',	'test@gmail.com',	2,	'compteVole',	'TEEEEEEEST'),
(5,	'test',	'test@gmail.com',	2,	'compteVole',	'TEEEEEEEST'),
(6,	'TEST',	'andrea@bigot974',	555,	'compteVole',	'AZDFGHYGTFD')
ON DUPLICATE KEY UPDATE `idContact` = VALUES(`idContact`), `nom` = VALUES(`nom`), `email` = VALUES(`email`), `numero` = VALUES(`numero`), `sujet` = VALUES(`sujet`), `message` = VALUES(`message`);

DROP TABLE IF EXISTS `etat`;
CREATE TABLE `etat` (
  `id` tinyint(4) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `etat` (`id`, `libelle`, `description`) VALUES
(1,	'Pas confirmé',	'Votre commande n\'a pas encore été validée.'),
(2,	'En instruction ',	'Votre commande est en cours d\'instruction par nos experts.'),
(3,	'Préparation en cours',	'Votre commande est en préparation.'),
(4,	'Livraison en cours',	'Votre commande est actuellement en chemin.'),
(5,	'Livré',	'Votre commande à été livré.')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `libelle` = VALUES(`libelle`), `description` = VALUES(`description`);

DROP TABLE IF EXISTS `facture`;
CREATE TABLE `facture` (
  `numCmd` int(11) NOT NULL AUTO_INCREMENT,
  `nomProp` varchar(200) NOT NULL,
  `prenomProp` varchar(200) NOT NULL,
  `rueLiv` varchar(200) NOT NULL,
  `cpLiv` varchar(5) NOT NULL,
  `typePaiement` varchar(100) NOT NULL DEFAULT 'Solde',
  `datePaiement` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`numCmd`),
  KEY `cpLiv` (`cpLiv`),
  CONSTRAINT `facture_ibfk_1` FOREIGN KEY (`numCmd`) REFERENCES `commande` (`num`),
  CONSTRAINT `facture_ibfk_2` FOREIGN KEY (`cpLiv`) REFERENCES `code_postal` (`cp`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

INSERT INTO `facture` (`numCmd`, `nomProp`, `prenomProp`, `rueLiv`, `cpLiv`, `typePaiement`, `datePaiement`) VALUES
(15,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-16 23:05:10'),
(16,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-16 23:37:04')
ON DUPLICATE KEY UPDATE `numCmd` = VALUES(`numCmd`), `nomProp` = VALUES(`nomProp`), `prenomProp` = VALUES(`prenomProp`), `rueLiv` = VALUES(`rueLiv`), `cpLiv` = VALUES(`cpLiv`), `typePaiement` = VALUES(`typePaiement`), `datePaiement` = VALUES(`datePaiement`);

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
(1,	4,	'Rose bonbon',	'hue-rotate(459deg)',	1),
(2,	4,	'Bleu clair',	NULL,	1),
(3,	4,	'Vert Forêt',	'hue-rotate(969deg) brightness(0.9)',	1),
(4,	2,	'Rose bonbon',	NULL,	1),
(5,	3,	'Blanc cassé',	NULL,	1),
(6,	1,	'Rouge clair rosé',	'saturate(2.0) hue-rotate(130deg)',	1),
(7,	6,	'Bleu rayé blanc et noir',	NULL,	1),
(8,	5,	'Beige',	NULL,	1),
(9,	11,	'Noir',	NULL,	1),
(10,	7,	'Rose bonbon',	NULL,	1),
(11,	8,	'Marron',	NULL,	1),
(12,	9,	'Noir et blanc',	NULL,	1),
(13,	10,	'Noir terne',	NULL,	1),
(14,	2,	'Orange',	'hue-rotate(45deg)',	1),
(15,	6,	'Mauve rayé blanc et noir',	'hue-rotate(45deg)',	1),
(16,	6,	'Rouge rayé blanc et noir',	'hue-rotate(110deg);',	1),
(17,	7,	'Vert fluo',	'hue-rotate(120deg)',	1),
(18,	1,	'Bleu',	'',	1)
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
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vue_categpargenre` AS select `g`.`code` AS `codeGenre`,group_concat(distinct `v`.`idCateg` separator ',') AS `ListeIdCategorie` from (`genre` `g` join `vetement` `v` on(`v`.`codeGenre` = `g`.`code`)) group by `v`.`codeGenre` order by `g`.`code`;

DROP TABLE IF EXISTS `vue_vet_disponibilite`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vue_vet_disponibilite` AS select `v`.`id` AS `idVet`,group_concat(distinct `vcl`.`num` order by `vcl`.`filterCssCode` ASC separator ',') AS `listeIdCouleurDispo`,group_concat(distinct `vt`.`taille` separator ',') AS `listeTailleDispo` from ((`vetement` `v` left join `vet_couleur` `vcl` on(`vcl`.`idVet` = `v`.`id`)) left join `vet_taille` `vt` on(`vt`.`idVet` = `v`.`id`)) group by `v`.`id`;

-- 2020-10-16 19:46:03
