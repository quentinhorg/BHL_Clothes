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

CREATE FUNCTION `prixTotalArt`(`_idArt` int, `_qte` int) RETURNS float
BEGIN
  RETURN (SELECT ROUND(_qte*v.prix,2) AS 'prixTotalArt' 
FROM vetement v
WHERE v.id = _idArt);
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

CREATE PROCEDURE `activeCompte`(IN `_email` varchar(255), IN `_cle` varchar(255))
BEGIN
   DECLARE dejaActive int; DECLARE emailCli varchar(255); DECLARE cleCli varchar(255);

   SELECT email, active, cleActivation
   INTO  emailCli , dejaActive, cleCli
   FROM client WHERE email LIKE _email;
   
   IF(emailCli IS NOT NULL) THEN
      IF(dejaActive = 0) THEN
          IF(cleCli = _cle) THEN
             UPDATE client SET active = 1 WHERE email = _email ;
          ELSE 
              SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La clé d\'activation est incorrecte';
          END IF;
      ELSE 
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le compte est déjà activé';
      END IF ;
   ELSE 
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'L\'email n\'existe pas';
   END IF;



END;;

CREATE PROCEDURE `desactiveCompte`(IN `_email` varchar(255), IN `_cle` varchar(255))
BEGIN
   DECLARE compteActive int; DECLARE emailCli varchar(255); DECLARE cleCli varchar(255);

   SELECT email, active, cleActivation
   INTO  emailCli , compteActive, cleCli
   FROM client WHERE email LIKE _email;
   
   IF(emailCli IS NOT NULL) THEN
      IF(compteActive = 0) THEN
          IF(cleCli = _cle) THEN
             DELETE FROM client WHERE email LIKE _email ;
          ELSE 
              SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La clé est incorrecte';
          END IF;
      ELSE 
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le compte est déjà activé';
      END IF ;
   ELSE 
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'L\'email n\'existe pas';
   END IF;

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
               

                #Récupération des infos Clients
                 SELECT c.nom, c.prenom, c.rue, c.codePostal
                 INTO  nomCli, prenomCli, rueCli, cpCli
                 FROM client c
                 where c.id = _idClient;

                UPDATE client SET solde = (soldeClient-montantCmdTTC) WHERE id = _idClient ; 
                INSERT INTO facture VALUES(_numCmd, nomCli, prenomCli, rueCli, cpCli, "Solde", NOW());
                UPDATE commande SET idEtat = 2 WHERE num = _numCmd ; 
              ELSE
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La paiement n\'a pas été effectué';
              END IF;
            ELSE
              SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La commande a déjà été payé'; 
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
(2,	1,	'M',	18,	1,	1),
(3,	3,	'L',	5,	1,	2),
(4,	3,	'L',	5,	1,	1),
(3,	6,	'L',	7,	1,	1),
(3,	6,	'L',	16,	1,	4),
(1,	7,	'L',	10,	1,	3),
(1,	7,	'L',	17,	1,	2),
(3,	8,	'M',	11,	1,	6),
(3,	10,	'36',	13,	1,	5),
(1,	11,	'42',	9,	1,	1)
ON DUPLICATE KEY UPDATE `numCmd` = VALUES(`numCmd`), `idVet` = VALUES(`idVet`), `taille` = VALUES(`taille`), `numClr` = VALUES(`numClr`), `qte` = VALUES(`qte`), `ordreArrivee` = VALUES(`ordreArrivee`);

DELIMITER ;;

CREATE TRIGGER `before_insert_taille` BEFORE INSERT ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE tailleDispo int;
SET tailleDispo= (SELECT COUNT(taille) FROM vet_taille  WHERE idVet=NEW.idVet AND taille LIKE NEW.taille );
IF (tailleDispo = 0) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Ce vêtement n'est pas disponible dans cette taille.";
end if;



END;;

CREATE TRIGGER `before_insert_couleur` BEFORE INSERT ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE couleurDispo int;
SET couleurDispo= (SELECT dispo
                   FROM vet_couleur 
                   WHERE idVet=NEW.idVet AND num = NEW.numClr);

IF (couleurDispo IS NULL) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Ce vêtement n'est pas disponible dans cette couleur.";
end if;



END;;

CREATE TRIGGER `before_update_ap` BEFORE UPDATE ON `article_panier` FOR EACH ROW
BEGIN 
DECLARE tailleDispo int;
SET tailleDispo= (SELECT COUNT(taille) FROM vet_taille  WHERE idVet=NEW.idVet AND taille LIKE NEW.taille );
IF (tailleDispo = 0) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Ce vêtement n'est plus disponible disponible dans cette taille.";
end if;



END;;

CREATE TRIGGER `before_update_article` BEFORE UPDATE ON `article_panier` FOR EACH ROW
BEGIN

DECLARE  int;
SET  =(SELECT c.idEtat FROM commande c
            WHERE c.num=OLD.numCmd);
IF( >= 2) THEN

     SIGNAL SQLSTATE '45000'SET MESSAGE_TEXT = "Impossible de modifier un article déjà payé.";
END IF;
END;;

CREATE TRIGGER `article_panier_before_delete` BEFORE DELETE ON `article_panier` FOR EACH ROW
BEGIN 

DECLARE idEtatCmd int;

SELECT c.idEtat
INTO idEtatCmd
FROM commande c
WHERE c.num =OLD.numCmd ;

#Si la commande est déjà payé empêcher la supression de l'article, mais si livré autorisé
#Pour pourvoir supprimer un article, il faut soit être livré ou soit la commande n'est pas encore payé
IF ( idEtatCmd BETWEEN 2 AND 4) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de supprimer un article déjà payé et non livré";
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
(8,	4,	4,	'Matière souple et confortable. Bon pull',	4,	'2020-10-13 20:51:44'),
(9,	14,	1,	'Matière très agréable à porter. De bonne qualité.\r\nChoisir une taille au dessus si vous êtes grande. ',	4,	'2020-10-18 18:53:09')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `idClient` = VALUES(`idClient`), `idVet` = VALUES(`idVet`), `commentaire` = VALUES(`commentaire`), `note` = VALUES(`note`), `date` = VALUES(`date`);

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
(12,	'Pantalons'),
(4,	'Pulls'),
(1,	'Robes'),
(10,	'Shorts & Bermudas'),
(5,	'Shorts de bain'),
(2,	'T-shirts & Débardeurs'),
(8,	'Vestes & Manteaux')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `nom` = VALUES(`nom`);

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
  `cleActivation` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `dateInscription` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  KEY `codePostal` (`codePostal`),
  CONSTRAINT `client_ibfk_1` FOREIGN KEY (`codePostal`) REFERENCES `code_postal` (`cp`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

INSERT INTO `client` (`id`, `email`, `mdp`, `nom`, `prenom`, `codePostal`, `rue`, `tel`, `solde`, `cleActivation`, `active`, `dateInscription`) VALUES
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2849.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09'),
(3,	'azaz@zaz.fre',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1871.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 23:38:03'),
(15,	'hoareauquentin97480@gmail.com',	'26f293bee30380fdeeece466b90493ebfaa0d234',	'aza',	'zazazaz',	'97419',	'azazaz',	'87684684',	100,	'39c160cc462c6d690e3433feaf038a23966c241b',	1,	'2020-10-21 10:16:43')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `mdp` = VALUES(`mdp`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `codePostal` = VALUES(`codePostal`), `rue` = VALUES(`rue`), `tel` = VALUES(`tel`), `solde` = VALUES(`solde`), `cleActivation` = VALUES(`cleActivation`), `active` = VALUES(`active`), `dateInscription` = VALUES(`dateInscription`);

DELIMITER ;;

CREATE TRIGGER `after_update_client` AFTER UPDATE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.email,OLD.mdp,  
OLD.nom, OLD.prenom, OLD.codePostal, OLD.rue, OLD.tel, OLD.solde, OLD.cleActivation, OLD.active, OLD.dateInscription, NOW(),  "UPDATE");
END;;

CREATE TRIGGER `after_delete_client` AFTER DELETE ON `client` FOR EACH ROW
BEGIN 
INSERT INTO client_histo VALUES(OLD.id, OLD.email,OLD.mdp,  
OLD.nom, OLD.prenom, OLD.codePostal, OLD.rue, OLD.tel, OLD.solde, OLD.cleActivation, OLD.active, OLD.dateInscription, NOW(),  "DELETE");
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
  `cleActivation` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `dateInscription` datetime NOT NULL,
  `date_histo` datetime NOT NULL,
  `evenement_histo` varchar(30) NOT NULL,
  PRIMARY KEY (`id`,`date_histo`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8;

INSERT INTO `client_histo` (`id`, `email`, `mdp`, `nom`, `prenom`, `codePostal`, `rue`, `tel`, `solde`, `cleActivation`, `active`, `dateInscription`, `date_histo`, `evenement_histo`) VALUES
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'22 rue des frangipaniers St Joseph',	'0692466990',	613.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-11 21:19:45',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'22 rue des frangipaniers St Joseph',	'0692466990',	800,	'',	0,	'0000-00-00 00:00:00',	'2020-10-11 21:32:13',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'',	'',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:33:36',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'974',	'',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:33:43',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97410',	'4 rue papangue',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:08:01',	'UPDATE'),
(1,	'andrea@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:46:02',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	780,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:55:05',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	700.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:56:19',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	644.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:57:01',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	602.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:57:24',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	3000,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:57:45',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2955,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 15:00:20',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:02',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:08',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:09',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:10',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:18',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97420',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:46',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2905,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2905,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 16:17:04',	'UPDATE'),
(2,	'quentin@live.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'',	'',	'0694458553',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(2,	'quentin@live.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97400',	'7 impasse jesus',	'0694458553',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:45:52',	'UPDATE'),
(2,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97400',	'7 impasse jesus',	'0694458553',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(2,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97400',	'7 impasse jesus',	'0694458553',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(2,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97400',	'7 impasse jesus',	'0694458553',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:20:48',	'DELETE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'',	'',	'0693122478',	85.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	85.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-14 22:11:41',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	1200,	'',	0,	'0000-00-00 00:00:00',	'2020-10-14 22:11:52',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	10200,	'',	0,	'0000-00-00 00:00:00',	'2020-10-14 22:14:01',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9954.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-14 22:16:39',	'UPDATE'),
(3,	'jeremy@mail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:46:38',	'UPDATE'),
(3,	'',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:47:51',	'UPDATE'),
(3,	'azaz@zaz',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 19:33:35',	'UPDATE'),
(3,	'azaz@zaz.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 19:33:43',	'UPDATE'),
(3,	'azaz@zaz.fre',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(3,	'azaz@zaz.fre',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(3,	'azaz@zaz.fre',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'',	'',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'',	'',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97469',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:35',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'',	'',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97469',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'',	'',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97466',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'',	'',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97442',	'8 chemin coquelicots',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:00:16',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97442',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:17',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:20',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:34',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:43',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:44',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:08:11',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97420',	'rue test',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:09:05',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:09:08',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:15:22',	'UPDATE'),
(8,	'goldow9744@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:17:55',	'UPDATE'),
(8,	'goldow9744@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-14 17:13:43',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	656,	'',	0,	'0000-00-00 00:00:00',	'2020-10-16 23:05:10',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	530,	'',	0,	'0000-00-00 00:00:00',	'2020-10-16 23:37:04',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	418,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 07:51:41',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	172.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 09:30:03',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	50.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 13:57:39',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	5000,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:03:21',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4711.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:10:41',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4609.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:24:09',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4507.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:25:52',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4443,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:32:00',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4399.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:32:46',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4355.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:33:05',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4310.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:34:03',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4254.9,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:35:39',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4211.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:38:06',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4167.3,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:40:16',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4123.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 14:42:58',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	4000.3,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 15:03:51',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3870.3,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:21:33',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3825.3,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 18:40:00',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3760.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 09:28:21',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3605.8,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 09:31:31',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3452,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 11:17:42',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3452,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 13:54:48',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	3402,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 13:57:17',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2801.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 22:28:53',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2751.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 22:34:30',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2583.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2583.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2583.4,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 13:02:48',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2538.4,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 10:17:53',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2459.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 11:06:23',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2384.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 11:34:01',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'<script> alert() ;</script>',	'97400',	'aaaaa',	'0628468787',	2384.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 11:41:31',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2384.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 16:38:00',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2309.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 16:38:06',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2234.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 16:38:08',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2159.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 16:38:16',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2084.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 17:04:21',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	2016.3,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-21 17:10:57',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1943.7,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-22 10:13:43',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'test rue',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 17:25:03',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'rue du test',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 17:25:12',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'rue du test',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 17:57:50',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'ruelolo',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 17:59:50',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97413',	'lala',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:11',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97419',	'lolo',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:24',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97430',	'lolo',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:07:42',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97441',	'lolo',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:08:16',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97400',	'lele',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:09:05',	'UPDATE'),
(9,	'test@test',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'aaa',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:09:51',	'UPDATE'),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'aaa',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:14:59',	'UPDATE'),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'test',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 18:15:20',	'UPDATE'),
(9,	'test@test2',	'df5fe22a5f8fb50cc3bd59f34a438bc6dddb52a3',	'testnom',	'testpnom',	'97410',	'test',	'6969',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:18:56',	'DELETE'),
(10,	'roro13@gmail.com',	'3eddfbf3c48b779222cd8eebb3e137614d5ffee2',	'Robin',	'Jean',	'97413',	'36 rue des merisier ',	'roro',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:46',	'UPDATE'),
(10,	'roro13@gmail.com',	'3eddfbf3c48b779222cd8eebb3e137614d5ffee2',	'Robin',	'Jean',	'97419',	'36 rue des merisier ',	'roro',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(10,	'roro13@gmail.com',	'3eddfbf3c48b779222cd8eebb3e137614d5ffee2',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 11:17:42',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97419',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:46',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97419',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:24',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'a2b7caddbc353bd7d7ace2067b8c4e34db2097a3',	'zerzerazeaze',	'zerzr',	'97400',	'zerzerzer',	'984684',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:18:51',	'DELETE'),
(12,	'zzzzz@gmail.z',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 18:23:00',	'UPDATE'),
(12,	'zzzzz@gmail.z',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 19:33:30',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:24',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(13,	'eeeee@gmail.com',	'b2c4ee5de82866db38f79c6d4a91a626486b70e9',	'gggg',	'gggg',	'97419',	'gggg',	'4577357',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:18:51',	'DELETE'),
(13,	'leajuliehoareau@orange.fr',	'93aff2be9522378c7f1b2ae24a5bfc95ae69acef',	'Hoareau',	'Léa',	'97480',	'10 rue Thérésien Cadet, BUTOR',	'0692345678',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:33:02',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'93aff2be9522378c7f1b2ae24a5bfc95ae69acef',	'Hoareau',	'Léa',	'97480',	'10 rue Thérésien Cadet, BUTOR',	'0692345678',	1000,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:33:53',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'93aff2be9522378c7f1b2ae24a5bfc95ae69acef',	'Hoareau',	'Léa',	'97480',	'10 rue Thérésien Cadet, BUTOR',	'0692345678',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:30',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'lolo',	'Hoareau',	'Léa',	'97480',	'10 rue Thérésien Cadet, BUTOR',	'0692345678',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:35',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue Thérésien Cadet, BUTOR',	'0692345678',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:51',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, BUTOR',	'0692345678',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:56',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692345678',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:41:04',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:10',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-21 10:28:24',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 18:44:30',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:21',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 20:35:57',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 20:35:57',	'2020-10-20 23:37:15',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 23:37:15',	'2020-10-20 23:38:03',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 23:38:03',	'2020-10-21 10:28:24',	'UPDATE'),
(15,	'vvvvv@gmail.com',	'54a3ed0aa931b8a2c6666be8f3460ce0c9cde050',	'vvvv',	'vvvv',	'97419',	'vvvv',	'zerzer',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(15,	'vvvvv@gmail.com',	'54a3ed0aa931b8a2c6666be8f3460ce0c9cde050',	'vvvv',	'vvvv',	'97419',	'vvvv',	'zerzer',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:06:02',	'DELETE'),
(15,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 18:08:50',	'UPDATE'),
(15,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 18:08:52',	'DELETE'),
(17,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'b1c16753f8776ab41f2156723ca3ad12c8d3fd61',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:45:05',	'DELETE'),
(21,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:20:17',	'DELETE'),
(23,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:21:25',	'DELETE'),
(25,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:24:11',	'DELETE'),
(27,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'83787f060a59493aefdcd4b2369990e7303e186e',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:45:55',	'DELETE'),
(29,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'f890d752d330caf426a52643f6510d6efd597f3e',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:46:22',	'DELETE'),
(30,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'ed573491383d5d7052276dd09beebea1637ac2a3',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:47:21',	'UPDATE'),
(30,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'ed573491383d5d7052276dd09beebea1637ac2a3',	0,	'2020-10-20 23:47:21',	'2020-10-20 23:48:04',	'UPDATE'),
(30,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'ed573491383d5d7052276dd09beebea1637ac2a3',	0,	'2020-10-20 23:47:21',	'2020-10-20 23:48:10',	'UPDATE'),
(30,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'ed573491383d5d7052276dd09beebea1637ac2a3',	1,	'2020-10-20 23:48:10',	'2020-10-21 08:20:13',	'DELETE'),
(31,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'xxx',	'xxx',	'97400',	'xxxxx',	'8787787',	100,	'f7b41d20b69937da146fc75bff4c97615532586b',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:22:52',	'DELETE'),
(32,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'xxx',	'xxx',	'97400',	'xxxxx',	'8787787',	100,	'eb189f950d515341e1515abcd894d9a37047c5c8',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:23:09',	'DELETE'),
(33,	'hoareauquentin97480@gmail.com',	'aaa',	'aaa',	'aaa',	'97480',	'rue du machon',	'0698989898',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:31:31',	'DELETE'),
(33,	'hoareauquentin97480@gmail.com',	'6f139768968a335839eae419e014a930f0758b77',	'zazaz',	'zaza',	'97413',	'azaza',	'zazazaz',	100,	'24309eca598922fc5db29c35679966ea8b14a4fd',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:24:22',	'DELETE'),
(34,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:31:48',	'DELETE'),
(34,	'hoareauquentin97480@gmail.com',	'111f84b4a009f4c93e8a915c61d88bb90c3b2841',	'azazaz',	'zaza',	'97413',	'azaza',	'zazazaz',	100,	'7c269d45e17c15a6defc8e36c2f9a95852bfa188',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:25:07',	'DELETE'),
(35,	'hoareauquentin97480@gmail.com',	'111f84b4a009f4c93e8a915c61d88bb90c3b2841',	'azazaz',	'zaza',	'97413',	'azaza',	'zazazaz',	100,	'f37062d9a65543a46f2ba13299ba77a370a1c4eb',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:25:27',	'DELETE'),
(36,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:34:47',	'DELETE'),
(36,	'hoareauquentin97480@gmail.com',	'111f84b4a009f4c93e8a915c61d88bb90c3b2841',	'azazaz',	'zaza',	'97413',	'azaza',	'zazazaz',	100,	'7eba27381c7a688f80d1f97c8ccfaa7ded17ee57',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:26:14',	'DELETE'),
(37,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'ff4fcd352b70c29f1b65c7d1702239a5c4a5f323',	0,	'0000-00-00 00:00:00',	'2020-10-21 08:44:00',	'DELETE'),
(38,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:36:22',	'DELETE'),
(38,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'0c774d8e1e30b273143a93836f845a4d3f44a60f',	0,	'2020-10-21 08:44:45',	'2020-10-21 08:45:18',	'DELETE'),
(39,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:36:29',	'DELETE'),
(39,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'bcd6b053f39a7428e6157dc0574980132111a7a5',	0,	'2020-10-21 08:45:20',	'2020-10-21 08:49:59',	'DELETE'),
(40,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:37:07',	'UPDATE'),
(40,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:38:12',	'DELETE'),
(40,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'f010bc8c02bed4710d06bca5d4d05a483810c609',	0,	'2020-10-21 08:50:01',	'2020-10-21 08:53:57',	'DELETE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:38:30',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 08:30:49',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 08:30:55',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 08:31:37',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 08:31:41',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 10:49:52',	'UPDATE'),
(41,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'4cc9d214074f0e8a09509bf88bd95b1c069b0565',	0,	'2020-10-21 08:53:58',	'2020-10-21 08:57:01',	'DELETE'),
(42,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'da73f2a1704c83909a2cea4ad496fbc746e4de1a',	0,	'2020-10-21 08:57:02',	'2020-10-21 09:00:12',	'DELETE'),
(43,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'c5646c24aae34705a73634c70f2616d6428d2a77',	0,	'2020-10-21 09:00:14',	'2020-10-21 09:01:00',	'DELETE'),
(44,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'5748e8895723a0da63d2bc75b935735a9c0d9699',	0,	'2020-10-21 09:03:50',	'2020-10-21 09:04:35',	'UPDATE'),
(44,	'hoareauquentin97480@gmail.com',	'c11db41b7fed034b25f1593da58f383cd60af7e2',	'zazaz',	'zaza',	'97400',	'azaza',	'azazaz',	100,	'5748e8895723a0da63d2bc75b935735a9c0d9699',	1,	'2020-10-21 09:03:50',	'2020-10-21 09:11:13',	'DELETE'),
(45,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzzz',	'zz',	'97410',	'zzz',	'zzzz',	100,	'01592d51db5afd0165cb73baca5c0b340c4889f1',	0,	'2020-10-21 09:11:56',	'2020-10-21 09:12:25',	'DELETE'),
(47,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzzz',	'zz',	'97410',	'zzz',	'zzzz',	100,	'81ea8be1af26fa1f9dfcd078e6471d549f88a70d',	0,	'2020-10-21 09:12:46',	'2020-10-21 09:13:06',	'UPDATE'),
(47,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzzz',	'zz',	'97410',	'zzz',	'zzzz',	100,	'81ea8be1af26fa1f9dfcd078e6471d549f88a70d',	1,	'2020-10-21 09:12:46',	'2020-10-21 09:14:19',	'DELETE'),
(48,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzzz',	'zz',	'97410',	'zzz',	'zzzz',	100,	'83c1b24c6399b8284b114fb23fa4a965446d27fc',	0,	'2020-10-21 09:14:21',	'2020-10-21 09:14:36',	'DELETE'),
(49,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'zaza',	'97400',	'aza',	'azazaz',	100,	'9529ae880d8ed449abf95e7d43935cc9622b7fa9',	0,	'2020-10-21 09:23:18',	'2020-10-21 09:25:28',	'DELETE'),
(50,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'zaza',	'97400',	'aza',	'azazaz',	100,	'bc4d45844d467b9fbd27dcd0b41fe52d229884c3',	0,	'2020-10-21 09:25:31',	'2020-10-21 10:11:35',	'UPDATE'),
(50,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'zaza',	'97400',	'aza',	'azazaz',	100,	'bc4d45844d467b9fbd27dcd0b41fe52d229884c3',	1,	'2020-10-21 09:25:31',	'2020-10-21 10:16:20',	'DELETE'),
(51,	'hoareauquentin97480@gmail.com',	'26f293bee30380fdeeece466b90493ebfaa0d234',	'aza',	'zazazaz',	'97419',	'azazaz',	'87684684',	100,	'39c160cc462c6d690e3433feaf038a23966c241b',	0,	'2020-10-21 10:16:43',	'2020-10-21 10:26:46',	'UPDATE'),
(51,	'hoareauquentin97480@gmail.com',	'26f293bee30380fdeeece466b90493ebfaa0d234',	'aza',	'zazazaz',	'97419',	'azazaz',	'87684684',	100,	'39c160cc462c6d690e3433feaf038a23966c241b',	1,	'2020-10-21 10:16:43',	'2020-10-21 10:27:46',	'UPDATE')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `email` = VALUES(`email`), `mdp` = VALUES(`mdp`), `nom` = VALUES(`nom`), `prenom` = VALUES(`prenom`), `codePostal` = VALUES(`codePostal`), `rue` = VALUES(`rue`), `tel` = VALUES(`tel`), `solde` = VALUES(`solde`), `cleActivation` = VALUES(`cleActivation`), `active` = VALUES(`active`), `dateInscription` = VALUES(`dateInscription`), `date_histo` = VALUES(`date_histo`), `evenement_histo` = VALUES(`evenement_histo`);

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
(1,	8,	'2020-10-21 16:47:38',	5),
(2,	1,	'2020-10-21 22:22:18',	1),
(3,	8,	'2020-10-22 10:42:31',	5),
(4,	8,	'2020-10-22 11:05:52',	1)
ON DUPLICATE KEY UPDATE `num` = VALUES(`num`), `idClient` = VALUES(`idClient`), `dateCreation` = VALUES(`dateCreation`), `idEtat` = VALUES(`idEtat`);

DELIMITER ;;

CREATE TRIGGER `commande_before_update` BEFORE UPDATE ON `commande` FOR EACH ROW
BEGIN 

DECLARE nbFactureActif int;
DECLARE nbCommandeNonPaye int;


SET nbFactureActif = (SELECT COUNT(f.numCmd) FROM facture f WHERE f.numCmd = OLD.num);

#Empêcher de considérer une commande non payé à une commande payé, car elle n'a aucune une facture
IF (nbFactureActif = 0 AND NEW.idEtat >= 2) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de considérée cette commande comme payée, car aucune facture ne lui correspond.";
end if;

#Empêcher de considérer une commande payé à une commande non payé, car elle possède une facture
IF (nbFactureActif >= 1 AND NEW.idEtat = 1) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de considérer cette commande comme non payé, car elle correspond à une facture";
end if;


#Empecher d'avoir plusieurs fois l'Etat non payé / validé (id=1)
SET nbCommandeNonPaye= (SELECT COUNT(c.idEtat) 
          FROM commande c 
          WHERE c.idClient=OLD.idClient 
          AND c.idEtat=1
          AND c.num != OLD.num); 
IF ( nbCommandeNonPaye >= 1 ) THEN
     SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible d'avoir plusieurs commandes non payé.";
end if;


END;;

DELIMITER ;

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
(1,	'Pas confirmé',	'Votre commande n\'a pas encore été validée, ni payé.'),
(2,	'En instruction ',	'Vous avez payé, votre commande est en cours d\'instruction par nos experts.'),
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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;

INSERT INTO `facture` (`numCmd`, `nomProp`, `prenomProp`, `rueLiv`, `cpLiv`, `typePaiement`, `datePaiement`) VALUES
(3,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-22 10:13:43')
ON DUPLICATE KEY UPDATE `numCmd` = VALUES(`numCmd`), `nomProp` = VALUES(`nomProp`), `prenomProp` = VALUES(`prenomProp`), `rueLiv` = VALUES(`rueLiv`), `cpLiv` = VALUES(`cpLiv`), `typePaiement` = VALUES(`typePaiement`), `datePaiement` = VALUES(`datePaiement`);

DELIMITER ;;

CREATE TRIGGER `facture_after_insert` AFTER INSERT ON `facture` FOR EACH ROW
BEGIN
#Mettre à jours l'etat de la commande à : 'payé'
UPDATE commande SET idEtat = 2 WHERE num = NEW.numCmd ;
END;;

CREATE TRIGGER `facture_before_delete` BEFORE DELETE ON `facture` FOR EACH ROW
BEGIN 
DECLARE  int ;

SELECT c.idEtat 
INTO  
FROM commande c
WHERE c.num = OLD.numCmd ;

#Empêcher de supprimer une facture dont la commande n'a pas encore été livré
IF ( != 5) THEN
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Impossible de supprimer une facture reliée à une commande non livrée';
END IF;

END;;

CREATE TRIGGER `facture_after_delete` AFTER DELETE ON `facture` FOR EACH ROW
BEGIN
#Mettre l'etat de la commande à 'non payé'
UPDATE commande SET idEtat = 1 WHERE num= OLD.numCmd ;

END;;

DELIMITER ;

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
  PRIMARY KEY (`libelle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `taille` (`libelle`) VALUES
('32'),
('34'),
('36'),
('38'),
('40'),
('42'),
('L'),
('M'),
('S'),
('XL'),
('XS')
ON DUPLICATE KEY UPDATE `libelle` = VALUES(`libelle`);

DROP TABLE IF EXISTS `vetement`;
CREATE TABLE `vetement` (
  `id` int(11) NOT NULL,
  `nom` varchar(60) NOT NULL,
  `prix` float NOT NULL,
  `motifPosition` varchar(150) DEFAULT '',
  `codeGenre` varchar(1) NOT NULL,
  `description` text NOT NULL,
  `idCateg` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `numGenre` (`codeGenre`),
  KEY `idCateg` (`idCateg`),
  CONSTRAINT `vetement_ibfk_2` FOREIGN KEY (`idCateg`) REFERENCES `categorie` (`id`),
  CONSTRAINT `vetement_ibfk_3` FOREIGN KEY (`codeGenre`) REFERENCES `genre` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `vetement` (`id`, `nom`, `prix`, `motifPosition`, `codeGenre`, `description`, `idCateg`) VALUES
(1,	'Robe D\'Eté Superposée Fleurie Imprimée',	25.5,	NULL,	'F',	'Petite robe imprimée en coton avec des bretelles fines. Matières: rayonne.',	1),
(2,	'Short de Survêtement à Cordon',	10,	NULL,	'F',	'Short',	5),
(3,	'T-shirt Manche longue unicolore',	15,	NULL,	'F',	'Tshirt manche longue en coton.',	2),
(4,	'Pull Court Simple Surdimensionné',	37,	NULL,	'F',	'Pull court manches longues. Matières: coton, polyester',	4),
(5,	'Pull Court Rayé à Col Rond',	38.2,	NULL,	'F',	'Pull rayé manches longues au col rond. Matières: polyester, coton',	4),
(6,	'Short Décontracté En Couleur Jointive à Taille Elastique',	13.8,	NULL,	'H',	'Matières: Polyamide',	5),
(7,	'T-shirt Motif De Lettre Dessin Animé',	15,	NULL,	'H',	'T-shirt pour homme en coton, col rond.',	2),
(8,	'Pull Tordu à Epaule Dénudée',	20,	NULL,	'F',	'Pull qui décore avec un design torsadé à l\'avant. Matières: coton, polyacrylique.',	4),
(9,	'Veste Déchirée En Couleur Unie En Denim',	34.9,	NULL,	'M',	'Veste déchirée avec un col rabattu à manches longues. Matières: coton, polyester.',	8),
(10,	'Pantalon Slim Taille Haute Déchiré',	12,	NULL,	'F',	'222',	12),
(11,	'Bermuda chino uni',	15,	NULL,	'H',	'222',	10),
(12,	'T-shirt Graphique Grue Barboteuse Chinoise Fleurie Imprimé',	17.99,	NULL,	'H',	'T-shirt manches courtes imprimé en coton.',	2),
(13,	'T-shirt Court Sanglé à Col V',	10,	NULL,	'F',	'T-shirt Court Sanglé à Col V.\r\nMatières: Polyuréthane,Rayonne',	2),
(14,	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée',	11,	NULL,	'F',	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée. \r\nMatières: Coton,Polyester',	2),
(15,	'Haut Court Côtelé Sans Dos à Bretelle ',	12,	NULL,	'F',	'Haut Court Côtelé Sans Dos à Bretelle qui met en valeur la taille marquée. \r\nMatières: Polyuréthane,Rayonne',	2),
(16,	' Haut Court Côtelé à Bretelle Trodu',	15,	NULL,	'F',	'Haut Court Côtelé à Bretelle Trodu.\r\nHaut qui flatte la silhouette avec des fines bretelles mettant en avant le décolleté et le dos. \r\nMatières: Polyuréthane,Rayonne',	2),
(17,	'T-Shirt à Imprimé Rayures En Blocs De Couleurs',	10,	NULL,	'H',	'Un t-shirt avec un motif à rayures panachées, un col rond, des manches courtes et une coupe classique.\r\nMatières: Polyester',	2),
(18,	'T-shirt Rose Brodée à Manches Courtes',	13.5,	NULL,	'H',	'T-shirt basique surmonté d\'un col rond et manches courtes.\r\nMatières: Coton,Polyester,Spandex',	2),
(19,	'Veste Déchirée Avec Poche à Rabat En Denim',	37.6,	NULL,	'H',	'Veste déchirée manches longues.\r\nMatières: Coton,Polyester,Spandex',	8),
(20,	'Pantalon de Survêtement Lettre Applique à Cordon en Laine',	23.5,	NULL,	'H',	'Pantalon de Survêtement avec élastique à la taille en coton.',	12),
(21,	'Pantalon Panneau En Blocs De Couleurs à Taille Elastique',	19.99,	NULL,	'H',	'Pantalon à Taille Elastique en polyesther. ',	12),
(22,	'T-shirt Rayé Chiffre Brodé à Manches Longues',	14.9,	NULL,	'H',	'T-shirt Rayé Chiffre Brodé à Manches Longues\r\nMatières: Coton,Polyacrylique,Polyester',	4),
(23,	'Robe à Bretelle Fleurie Plissée à Volants',	20,	NULL,	'F',	'Robe à Bretelle Fleurie Plissée à Volants.\r\nLes plis sont réunis avec la taille élastique et le dos smocké aide à façonner les courbes.\r\nMatières: Polyester',	1),
(24,	'Mini Robe à Carreaux Ligne A',	11.2,	NULL,	'F',	'Détendu en forme, féminin dans le style, cette robe cami dispose d\'une impression tout au long de ceindre, fines bretelles et une coupe mini longueur séduisante, dans une silhouette évasée. portez-le avec des talons pour un style charmant.\r\nMatières: Polyester',	1),
(25,	'Jupe Ligne A Teintée à Cordon',	13,	NULL,	'F',	'Jupe colorée en polyester. ',	6),
(26,	'Mini Jupe Ligne A Nouée',	14,	NULL,	'F',	'Jupe courte avec une fermeture zippée. \r\nMatières: Polyester,Polyuréthane',	6),
(27,	'Short Déchiré Zippé Design En Denim',	19.65,	NULL,	'H',	'Short déchiré zippé en denim.\r\nMatières: Coton,Polyester,Spandex',	10)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`), `nom` = VALUES(`nom`), `prix` = VALUES(`prix`), `motifPosition` = VALUES(`motifPosition`), `codeGenre` = VALUES(`codeGenre`), `description` = VALUES(`description`), `idCateg` = VALUES(`idCateg`);

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
(10,	'36'),
(11,	'42'),
(27,	'42'),
(3,	'L'),
(6,	'L'),
(7,	'L'),
(12,	'L'),
(27,	'L'),
(1,	'M'),
(2,	'M'),
(3,	'M'),
(4,	'M'),
(7,	'M'),
(8,	'M'),
(9,	'M'),
(14,	'M'),
(22,	'M'),
(23,	'M'),
(3,	'S'),
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

-- 2020-10-22 07:07:53
