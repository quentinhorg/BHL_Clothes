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

CREATE PROCEDURE `insert_article`(IN `_numCmd` int(11), IN `_idVet` int(3), IN `_taille` varchar(3), IN `_numClr` int(11), IN `_qte` int)
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

   #Article déjà existant
   IF( qteArticle >= 1) THEN 
     IF ( qteArticle+_qte > 10) THEN SET _qte = 10-qteArticle; END IF; #Réquilibrage de la quantié au maximum

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

                UPDATE client SET solde = ROUND(soldeClient-montantCmdTTC,2) WHERE id = _idClient ; 
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
  CONSTRAINT `article_panier_ibfk_3` FOREIGN KEY (`taille`) REFERENCES `taille` (`libelle`),
  CONSTRAINT `article_panier_ibfk_5` FOREIGN KEY (`numClr`) REFERENCES `vet_couleur` (`num`),
  CONSTRAINT `qteMax` CHECK (`qte` <= 10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `article_panier` (`numCmd`, `idVet`, `taille`, `numClr`, `qte`, `ordreArrivee`) VALUES
(15,	1,	'S',	107,	2,	1),
(6,	2,	'M',	4,	1,	5),
(14,	2,	'M',	4,	2,	3),
(3,	3,	'L',	5,	1,	2),
(4,	3,	'L',	5,	1,	1),
(6,	3,	'M',	5,	1,	3),
(16,	4,	'M',	3,	1,	1),
(3,	6,	'L',	7,	1,	1),
(3,	6,	'L',	16,	1,	4),
(6,	6,	'L',	7,	2,	2),
(6,	6,	'L',	16,	1,	4),
(17,	6,	'L',	7,	1,	2),
(3,	8,	'M',	11,	1,	6),
(12,	9,	'M',	12,	1,	3),
(3,	10,	'36',	13,	1,	5),
(6,	10,	'36',	13,	1,	6),
(22,	12,	'L',	29,	1,	1),
(11,	15,	'L',	32,	1,	2),
(14,	15,	'L',	42,	1,	2),
(16,	15,	'L',	41,	1,	2),
(18,	15,	'L',	44,	1,	1),
(18,	17,	'M',	36,	1,	2),
(20,	17,	'M',	40,	1,	1),
(21,	17,	'M',	40,	1,	1),
(12,	18,	'L',	34,	1,	2),
(17,	18,	'XL',	34,	2,	3),
(20,	18,	'L',	34,	1,	2),
(11,	25,	'M',	25,	1,	1),
(11,	28,	'M',	46,	1,	5),
(11,	28,	'M',	48,	1,	4),
(14,	28,	'L',	48,	1,	4),
(11,	29,	'M',	53,	1,	3),
(16,	30,	'34',	104,	2,	3),
(11,	33,	'36',	56,	1,	6),
(17,	37,	'38',	92,	1,	4),
(20,	45,	'L',	71,	1,	3),
(12,	47,	'M',	80,	1,	4),
(12,	49,	'38',	58,	1,	1),
(19,	50,	'36',	59,	6,	1);

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

DECLARE idEtat_  int;
SET idEtat_ =(SELECT c.idEtat FROM commande c
            WHERE c.num=OLD.numCmd);
IF(idEtat_ >= 2) THEN

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
   SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de supprimer un article d'une commande déjà payé et non livré";
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

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `avis`;
CREATE TABLE `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idClient` int(11) NOT NULL,
  `idVet` int(11) NOT NULL,
  `commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `note` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idClient` (`idClient`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`),
  CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

INSERT INTO `avis` (`id`, `idClient`, `idVet`, `commentaire`, `note`, `date`) VALUES
(1,	8,	1,	'woooooaaaww 😊',	4,	'2020-10-05 21:48:01'),
(2,	1,	7,	'Tshirt de bonne qualité qui taille un peu large. Parfait pour faire un style oversize ! ',	5,	'2020-10-09 17:30:14'),
(3,	5,	6,	'Short de bonne qualité, conforme à la photo',	4,	'2020-10-01 21:55:01'),
(4,	1,	1,	'Je trouve que la robe est un peu transparente à la lumière mais ce problème est vite réglé avec un petit short en dessous',	4,	'2020-10-06 21:57:09'),
(5,	6,	1,	'Elle correspond à mes attentes et la livraison était plutôt rapide! \r\nBon produit',	5,	'2020-10-10 21:58:28'),
(6,	8,	11,	'Je suis déçu, la texture blanchit facilement. ',	1,	'2020-10-11 00:03:20'),
(7,	11,	10,	'Ce pantalon est sympa mais un peu grand pour un 36',	3,	'2020-10-13 20:40:17'),
(8,	4,	4,	'Matière souple et confortable. Bon pull',	4,	'2020-10-13 20:51:44'),
(9,	22,	15,	'Bon produit! ',	5,	'2020-10-25 19:19:13'),
(10,	22,	28,	'Pull très confortable de bonne qualité!',	5,	'2020-10-25 19:20:25'),
(11,	23,	50,	'Que dire de plus le site des marques et vous est excellent pour les délais de livraison commande passée le 25/10/20 au soir colis reçu le 26/10/20 en relais colis mondial !Les articles toujours satisfaisants pas de retour a effectuer tout est parfait .Ce site est un de mes préférés! Ne changeait rien des marques et vous! Bravo pour votre réactivité et merci pour les petits carambars dans le colis!!!!',	5,	'2020-10-25 20:03:08'),
(22,	11,	17,	'Tshirt souple idéal pour l\'été. Bon achat',	4,	'2020-11-01 13:50:00'),
(23,	5,	46,	'Veste très confortable, bien taillé complètement mon style! ',	5,	'2020-11-01 06:30:52'),
(24,	10,	47,	'Déçu de cette veste, la couleur s\'en va dès le troisième lavage...',	2,	'2020-07-12 12:50:52'),
(25,	7,	47,	'Bonne veste, aucun problème au lavage pour ma part',	4,	'2020-09-17 22:14:17'),
(27,	22,	34,	'Short conforme à la photo, les couleurs sont magnifiques et tiennent bien au lavage. 	',	4,	'2020-07-25 09:45:52'),
(28,	13,	25,	'Jupe originale de bonne qualité.',	4,	'2020-04-04 18:00:00'),
(29,	4,	48,	'Pull doux et confortable mais un peu serré au niveau des bras',	3,	'2020-03-14 12:50:52'),
(30,	3,	43,	'Bonne qualité mais taille un peu petit. ',	3,	'2020-11-01 14:14:14'),
(31,	6,	39,	'Bon produit',	5,	'2020-07-31 08:50:52'),
(32,	13,	36,	'Pantalon agréable à porter, aucun problème de taille ',	5,	'2020-06-11 15:10:59'),
(33,	4,	32,	'Short un peu court mais de bonne qualité. Je recommande! ',	4,	'2020-04-18 20:00:00'),
(34,	22,	23,	'Robe sympa pour l\'été. Matière de bonne qualité',	5,	'2020-10-29 03:45:52'),
(35,	13,	16,	'Ce tshirt est sympa, de bonne qualité mais j\'ai pris la mauvaise taille. Pensez à prendre plus petit que votre taille habituelle ',	4,	'2020-05-15 16:20:00'),
(36,	6,	10,	'Bonne qualité de tissu',	4,	'2020-07-07 05:50:52'),
(37,	8,	41,	'Superbe short d\'été !',	4,	'2020-11-01 19:53:08'),
(38,	26,	25,	'Super jupe.',	5,	'2020-11-11 09:01:00'),
(39,	8,	1,	'oooooaaaww 😊',	4,	'2020-11-11 09:43:06');

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE `categorie` (
  `id` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `id` (`id`)
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
(5,	'Shorts'),
(2,	'T-shirts & Débardeurs'),
(8,	'Vestes & Manteaux');

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
  `solde` float NOT NULL DEFAULT 200,
  `cleActivation` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `dateInscription` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`),
  KEY `codePostal` (`codePostal`),
  CONSTRAINT `client_ibfk_1` FOREIGN KEY (`codePostal`) REFERENCES `code_postal` (`cp`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

INSERT INTO `client` (`id`, `email`, `mdp`, `nom`, `prenom`, `codePostal`, `rue`, `tel`, `solde`, `cleActivation`, `active`, `dateInscription`) VALUES
(1,	'andrea974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Andréa',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	9779,	'4az486zvfez68g4e86g45sq48er4err469859',	1,	'2020-02-01 17:05:09'),
(3,	'jerem_lebon@fauxemail.fr',	'4d13fcc6eda389d4d679602171e11593eadae9b9',	'LEBON',	'Jérémy',	'97410',	'7 rue du pinguin',	'0693122478',	9582.9,	'4f68ez4gve4r684eg865e4z54ze85r4zr546z6r4ze',	1,	'2020-10-02 17:05:09'),
(4,	'grondin.chalotte@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Charlotte',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'79z4ezfz4e5z4fz68f4z6848eaz4e86a4ez6475293c',	0,	'2020-06-19 17:05:09'),
(5,	'lauret.vincent@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Vincent',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'ed4z86e48g4z8e4774d35d91a475293c',	0,	'2020-10-19 17:05:09'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-31 17:05:09'),
(7,	'seb_morel@outlook25.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	268.6,	'a3fc12f37f48r68g4r84e6bd945eee45682f',	1,	'2020-10-19 17:05:09'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1699.41,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'0692458595',	200,	'4f68ze4r68z4fgz86er4zr86g48z4erez58r4z68raze4',	0,	'2019-08-28 17:05:09'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	200,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-05-08 13:05:09'),
(13,	'leahoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692818484',	846,	'7z84v6re4g68468az4eg854g8gz2e87z48713',	1,	'2020-10-19 17:05:09'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	4841.68,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	1,	'2020-10-25 19:14:02'),
(23,	'ophelien.abufera.bts@gmail.com',	'ae835c4e4a9d5a8876f773313d82f0499ca3dbc6',	'ABUFERA',	'Ophelien',	'97480',	'119 rue leconte de lisle',	'0692991200',	61.61,	'cf72565d2a62067e4e33e16d9e81e366ad08dd54',	1,	'2020-10-25 19:32:53'),
(26,	'nelsiemorel20@gmail.com',	'159b0c4aee9a825be489507183f1cec03951da63',	'Morel',	'Nelsie',	'97480',	'Ididn ',	'0692334445',	200,	'ad5d322a2a88170ddfe69081ec309e7483aabb7d',	1,	'2020-11-11 08:58:12'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	623.01,	'ec262f6b6a06990a6526c471a38734ffa2574114',	1,	'2020-11-11 09:07:54');

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
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2849.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-22 21:38:25',	'UPDATE'),
(1,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2779,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-25 18:51:23',	'UPDATE'),
(1,	'andrea974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Andréa',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	2779,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-01 18:55:54',	'UPDATE'),
(1,	'andrea974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Andréa',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	9779,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-08 15:38:45',	'UPDATE'),
(1,	'andrea974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Andréa',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	9779,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-01 17:05:09',	'2020-11-08 15:38:53',	'UPDATE'),
(1,	'andrea974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Andréa',	'Andréa',	'97480',	'4 rue papangue',	'0692466990',	9779,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-02-01 17:05:09',	'2020-11-08 15:42:26',	'UPDATE'),
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
(3,	'azaz@zaz.fre',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:01:21',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-02 13:31:48',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-02 14:34:32',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-02 14:50:15',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'4d13fcc6eda389d4d679602171e11593eadae9b9',	'LEBON',	'Jérémy',	'97400',	'7 rue ninja',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-02 15:34:14',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'4d13fcc6eda389d4d679602171e11593eadae9b9',	'LEBON',	'Jérémy',	'97410',	'lolo 5 rue',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-02 15:34:28',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'4d13fcc6eda389d4d679602171e11593eadae9b9',	'LEBON',	'Jérémy',	'97410',	'7 rue du pinguin',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-08 15:39:00',	'UPDATE'),
(3,	'jerem_lebon@fauxemail.fr',	'4d13fcc6eda389d4d679602171e11593eadae9b9',	'LEBON',	'Jérémy',	'97410',	'7 rue du pinguin',	'0693122478',	9582.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-02 17:05:09',	'2020-11-08 15:42:20',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'',	'',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(4,	'grondin.sam@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:01:29',	'UPDATE'),
(4,	'grondin.chalotte@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Samuel',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:01:35',	'UPDATE'),
(4,	'grondin.chalotte@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Charlotte',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:39:07',	'UPDATE'),
(4,	'grondin.chalotte@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'GRONDIN',	'Charlotte',	'97410',	'3 chemin des fleurs',	'0693238645',	45.15,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-06-19 17:05:09',	'2020-11-08 15:42:14',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'',	'',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97469',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 17:40:35',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(5,	'ryan.lauret974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:01:53',	'UPDATE'),
(5,	'lauret.vincent@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Ryan',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:01:56',	'UPDATE'),
(5,	'lauret.vincent@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'LAURET',	'Vincent',	'97410',	'6 impasse du cocon',	'0692851347',	84.6,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:42:05',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'',	'',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97469',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:29',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(6,	'mathilde20@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'PAYET',	'Mathilde',	'97410',	'9 chemin des zoizeau',	'0692753212',	984.2,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:39:21',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'',	'',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-13 16:34:07',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97466',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'65454',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:24:29',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'zerzer',	'97480',	'3 rue de lameme',	'0692987874',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:24:56',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'azeaze',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:25:38',	'UPDATE'),
(7,	'test@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:02:05',	'UPDATE'),
(7,	'seb_morel@test.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:02:11',	'UPDATE'),
(7,	'seb_morel@outlook.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:41:58',	'UPDATE'),
(7,	'seb_morel@outlook.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'a3fc12f37f48r68g4r84e6bd945eee45682f',	0,	'2020-10-19 17:05:09',	'2020-11-08 16:17:18',	'UPDATE'),
(7,	'seb_morel@outlook.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	351,	'a3fc12f37f48r68g4r84e6bd945eee45682f',	1,	'2020-10-19 17:05:09',	'2020-11-08 16:18:49',	'UPDATE'),
(7,	'seb_morel@outlook.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	292.6,	'a3fc12f37f48r68g4r84e6bd945eee45682f',	1,	'2020-10-19 17:05:09',	'2020-11-08 16:21:28',	'UPDATE'),
(7,	'seb_morel@outlook25.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'MOREL',	'Seb',	'97480',	'3 rue de lameme',	'0692987874',	292.6,	'a3fc12f37f48r68g4r84e6bd945eee45682f',	1,	'2020-10-19 17:05:09',	'2020-11-08 16:22:10',	'UPDATE'),
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
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1871.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-22 11:09:07',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1826.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-22 11:13:40',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1762.3,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-22 16:54:16',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'aaaaa',	'0628468787',	1653.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-22 20:19:53',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	1653.9,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-23 01:00:54',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	1467.4,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-23 01:05:57',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	1218.7,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-24 21:32:56',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	909.061,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-25 00:42:31',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Gamer',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	2000,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-25 00:57:41',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Goldow',	'97400',	'20 rue de la république',	'0628468787',	2000,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-25 00:57:47',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	2000,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-25 17:30:21',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1873.4,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-10-31 08:33:34',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1734.92,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-01 16:32:30',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1587.94,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-06 21:07:14',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1317.97,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-06 22:44:12',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1262.97,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 12:48:07',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1991,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 12:48:14',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1992,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 12:49:46',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1991.3,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 12:49:53',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1991.4,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 14:08:50',	'UPDATE'),
(8,	'goldow974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Quentin',	'97400',	'20 rue de la république',	'0628468787',	1919.41,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 13:02:48',	'2020-11-07 14:42:06',	'UPDATE'),
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
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'roro',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:20:11',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'0692458595',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:39:34',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'0692458595',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2019-08-28 17:05:09',	'2020-11-08 15:41:17',	'UPDATE'),
(10,	'roro13@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Robin',	'Jean',	'97480',	'36 rue des merisier ',	'0692458595',	200,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2019-08-28 17:05:09',	'2020-11-08 15:42:32',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97419',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:46',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97419',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 21:38:54',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:24',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-11-08 15:39:54',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	100,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-05-08 13:05:09',	'2020-11-08 15:41:14',	'UPDATE'),
(11,	'antho@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'RIVIÈRE ',	'Anthony',	'97480',	'34 rue des fleurs',	'0693455667',	200,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-05-08 13:05:09',	'2020-11-08 16:17:47',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'a2b7caddbc353bd7d7ace2067b8c4e34db2097a3',	'zerzerazeaze',	'zerzr',	'97400',	'zerzerzer',	'984684',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-17 16:18:51',	'DELETE'),
(12,	'zzzzz@gmail.z',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 18:23:00',	'UPDATE'),
(12,	'zzzzz@gmail.z',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 19:33:30',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 16:38:52',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:24',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-21 10:28:43',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'azeaze',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:20:29',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'azeaze',	'0693421697',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:20:53',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'zzzzz',	'97412',	'26 impasse des cerisiers',	'0693421697',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:21:06',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzz',	'Bryan',	'97412',	'26 impasse des cerisiers',	'0693421697',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-22 20:21:51',	'UPDATE'),
(12,	'zzzzz@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'JEAN',	'Bryan',	'97412',	'26 impasse des cerisiers',	'0693421697',	20.1,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-19 17:05:09',	'2020-10-25 18:13:42',	'DELETE'),
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
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-25 18:03:10',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-25 18:03:18',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692848484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-10-25 18:13:37',	'UPDATE'),
(13,	'leajuliehoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692818484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-01 18:55:36',	'UPDATE'),
(13,	'leahoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692818484',	899.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-19 17:05:09',	'2020-11-08 15:42:39',	'UPDATE'),
(13,	'leahoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692818484',	899.5,	'7z84v6re4g68468az4eg854g8gz2e87z48713',	1,	'2020-10-19 17:05:09',	'2020-11-08 16:06:10',	'UPDATE'),
(13,	'leahoareau@orange.fr',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'Hoareau',	'Léa',	'97480',	'10 rue par ici, ter la',	'0692818484',	846,	'7z84v6re4g68468az4eg854g8gz2e87z48713',	1,	'2020-10-19 17:05:09',	'2020-11-08 16:07:05',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-18 18:44:30',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:21',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 20:35:57',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 20:35:57',	'2020-10-20 23:37:15',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	1,	'2020-10-20 23:37:15',	'2020-10-20 23:38:03',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 23:38:03',	'2020-10-21 10:28:24',	'UPDATE'),
(14,	'patihoareau@gmail.com',	'8cb2237d0679ca88db6464eac60da96345513964',	'Hoareau',	'Pati',	'97480',	'15, rue Des Pamplemousses ',	'0693114750',	2.5,	'544107c473636dc8ee1a114774d35d91a475293c',	0,	'2020-10-20 23:38:03',	'2020-10-25 18:02:54',	'DELETE'),
(15,	'vvvvv@gmail.com',	'54a3ed0aa931b8a2c6666be8f3460ce0c9cde050',	'vvvv',	'vvvv',	'97419',	'vvvv',	'zerzer',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:05:09',	'UPDATE'),
(15,	'vvvvv@gmail.com',	'54a3ed0aa931b8a2c6666be8f3460ce0c9cde050',	'vvvv',	'vvvv',	'97419',	'vvvv',	'zerzer',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 17:06:02',	'DELETE'),
(15,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 18:08:50',	'UPDATE'),
(15,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-20 18:08:52',	'DELETE'),
(15,	'hoareauquentin97480@gmail.com',	'26f293bee30380fdeeece466b90493ebfaa0d234',	'aza',	'zazazaz',	'97419',	'azazaz',	'87684684',	100,	'39c160cc462c6d690e3433feaf038a23966c241b',	1,	'2020-10-21 10:16:43',	'2020-10-22 20:21:57',	'DELETE'),
(17,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'b1c16753f8776ab41f2156723ca3ad12c8d3fd61',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:45:05',	'DELETE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zz',	'97470',	'zzzzz',	'zzz',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	0,	'2020-10-22 20:22:08',	'2020-10-22 20:22:31',	'UPDATE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'Quentin',	'97470',	'zzzzz',	'zzz',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	0,	'2020-10-22 20:22:08',	'2020-10-22 20:22:42',	'UPDATE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97470',	'zzzzz',	'zzz',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	0,	'2020-10-22 20:22:08',	'2020-10-22 20:23:07',	'UPDATE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97470',	'123 chemin des lilas',	'zzz',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	0,	'2020-10-22 20:22:08',	'2020-10-22 20:23:22',	'UPDATE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97470',	'123 chemin des lilas',	'zzz',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	1,	'2020-10-22 20:22:08',	'2020-10-22 20:24:06',	'UPDATE'),
(17,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'HOAREAU',	'Quentin',	'97470',	'123 chemin des lilas',	'0692332211',	100,	'2a8a15f1fccbf07279ef24c839182d5f102cdb20',	1,	'2020-10-22 20:22:08',	'2020-10-22 20:25:44',	'DELETE'),
(18,	'hoareauquentin97480@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzzz',	'zzzzz',	'97410',	'zzzzz',	'zzzzz',	100,	'b66cd90e3946dd63b5a914d5eb2c7eddb46177ec',	0,	'2020-10-22 20:26:02',	'2020-10-22 20:26:15',	'UPDATE'),
(18,	'hoareauquentin97480@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'zzzzz',	'zzzzz',	'97410',	'zzzzz',	'zzzzz',	100,	'b66cd90e3946dd63b5a914d5eb2c7eddb46177ec',	1,	'2020-10-22 20:26:02',	'2020-10-22 20:58:29',	'DELETE'),
(19,	'hoareauquentin97480@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'azaz',	'azazaz',	'97400',	'azaz',	'azaz',	100,	'b99dfad9dfce6db8291c587455dec8f5ab378920',	0,	'2020-10-22 20:59:03',	'2020-10-22 20:59:18',	'UPDATE'),
(19,	'hoareauquentin97480@gmail.com',	'cb990257247b592eaaed54b84b32d96b7904fd95',	'azaz',	'azazaz',	'97400',	'azaz',	'azaz',	100,	'b99dfad9dfce6db8291c587455dec8f5ab378920',	1,	'2020-10-22 20:59:03',	'2020-10-25 18:02:32',	'DELETE'),
(20,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97412',	'zzzz',	'zzzz',	100,	'3578d2bd390fc59d28f7909524a01fec45caa0e0',	0,	'2020-10-25 19:01:12',	'2020-10-25 19:01:37',	'UPDATE'),
(20,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97412',	'zzzz',	'zzzz',	100,	'3578d2bd390fc59d28f7909524a01fec45caa0e0',	1,	'2020-10-25 19:01:12',	'2020-10-25 19:02:24',	'UPDATE'),
(20,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97412',	'zzzz',	'zzzz',	100,	'3578d2bd390fc59d28f7909524a01fec45caa0e0',	0,	'2020-10-25 19:01:12',	'2020-10-25 19:02:40',	'DELETE'),
(21,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:20:17',	'DELETE'),
(21,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	100,	'433a4c6712a3e5dab1f803df1aa87edb3f640d7a',	0,	'2020-10-25 19:05:47',	'2020-10-25 19:13:26',	'DELETE'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	100,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	0,	'2020-10-25 19:14:02',	'2020-10-25 19:14:50',	'UPDATE'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	100,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	1,	'2020-10-25 19:14:02',	'2020-11-01 16:16:51',	'UPDATE'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	5000,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	1,	'2020-10-25 19:14:02',	'2020-11-01 18:53:47',	'UPDATE'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	4910,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	1,	'2020-10-25 19:14:02',	'2020-11-08 16:12:11',	'UPDATE'),
(22,	'andrea.bigot974@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'BIGOT',	'Andréa',	'97480',	'22 rue des macarons',	'0692466990',	4841.68,	'a3fc12f37f04ba3fa82daefe36bd945eee45682f',	1,	'2020-10-25 19:14:02',	'2020-11-08 16:12:35',	'UPDATE'),
(23,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:21:25',	'DELETE'),
(23,	'ophelien.abufera.bts@gmail.com',	'ae835c4e4a9d5a8876f773313d82f0499ca3dbc6',	'ABUFERA',	'Ophelien',	'97480',	'119 rue leconte de lisle',	'0692991200',	200,	'cf72565d2a62067e4e33e16d9e81e366ad08dd54',	0,	'2020-10-25 19:32:53',	'2020-10-25 19:33:27',	'UPDATE'),
(23,	'ophelien.abufera.bts@gmail.com',	'ae835c4e4a9d5a8876f773313d82f0499ca3dbc6',	'ABUFERA',	'Ophelien',	'97480',	'119 rue leconte de lisle',	'0692991200',	200,	'cf72565d2a62067e4e33e16d9e81e366ad08dd54',	1,	'2020-10-25 19:32:53',	'2020-10-25 19:34:32',	'UPDATE'),
(24,	'quentinhoareau97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzzz',	'zzzz',	'97413',	'36 rue des merisier ',	'989889',	200,	'beab76da6766b1876a3c54e25e8df53142485962',	0,	'2020-11-07 19:29:36',	'2020-11-07 19:32:04',	'DELETE'),
(25,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zzzz',	'zzzz',	'97400',	'zzzzz',	'zzzzz',	100,	'',	0,	'0000-00-00 00:00:00',	'2020-10-19 23:24:11',	'DELETE'),
(25,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zz',	'zzz',	'97413',	'36 azazazzazr ',	'888888',	200,	'44b2c939d5bc4eeb7035959385a8d1afe5c22e6e',	0,	'2020-11-07 19:32:08',	'2020-11-07 19:32:47',	'UPDATE'),
(25,	'hoareauquentin97480@gmail.com',	'8aa40001b9b39cb257fe646a561a80840c806c55',	'zz',	'zzz',	'97413',	'36 azazazzazr ',	'888888',	200,	'44b2c939d5bc4eeb7035959385a8d1afe5c22e6e',	1,	'2020-11-07 19:32:08',	'2020-11-07 19:41:31',	'DELETE'),
(26,	'nelsiemorel20@gmail.com',	'159b0c4aee9a825be489507183f1cec03951da63',	'Morel',	'Nelsie',	'97480',	'Ididn ',	'0692334445',	200,	'ad5d322a2a88170ddfe69081ec309e7483aabb7d',	0,	'2020-11-11 08:58:12',	'2020-11-11 08:58:45',	'UPDATE'),
(27,	'hoareauquentin97480@gmail.com',	'4ad583af22c2e7d40c1c916b2920299155a46464',	'xxxx',	'xxx',	'97412',	'xxx',	'xxxxx',	100,	'83787f060a59493aefdcd4b2369990e7303e186e',	0,	'0000-00-00 00:00:00',	'2020-10-20 23:45:55',	'DELETE'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	200,	'ec262f6b6a06990a6526c471a38734ffa2574114',	0,	'2020-11-11 09:07:54',	'2020-11-11 09:08:19',	'UPDATE'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	200,	'ec262f6b6a06990a6526c471a38734ffa2574114',	1,	'2020-11-11 09:07:54',	'2020-11-11 09:13:42',	'UPDATE'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	155,	'ec262f6b6a06990a6526c471a38734ffa2574114',	1,	'2020-11-11 09:07:54',	'2020-11-11 09:14:51',	'UPDATE'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	143,	'ec262f6b6a06990a6526c471a38734ffa2574114',	1,	'2020-11-11 09:07:54',	'2020-11-11 09:23:54',	'UPDATE'),
(27,	'laurent.grondin.bts@gmail.com',	'eeb3075f677d345c31958b4691b99fcd55aca2df',	'Grondin ',	'Laurent ',	'97480',	'30 chemin des anémones',	'0692217331',	643,	'ec262f6b6a06990a6526c471a38734ffa2574114',	1,	'2020-11-11 09:07:54',	'2020-11-11 09:27:39',	'UPDATE'),
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
(51,	'hoareauquentin97480@gmail.com',	'26f293bee30380fdeeece466b90493ebfaa0d234',	'aza',	'zazazaz',	'97419',	'azazaz',	'87684684',	100,	'39c160cc462c6d690e3433feaf038a23966c241b',	1,	'2020-10-21 10:16:43',	'2020-10-21 10:27:46',	'UPDATE');

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
('97429',	'Petit-Ile',	1.5),
('97430',	'Tampon',	2.2),
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
('97480',	'Saint-Joseph',	2);

DROP TABLE IF EXISTS `commande`;
CREATE TABLE `commande` (
  `num` int(11) NOT NULL,
  `idClient` int(11) NOT NULL,
  `dateCreation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `idEtat` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`num`),
  KEY `commande_client_FK` (`idClient`),
  KEY `idEtat` (`idEtat`),
  CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`),
  CONSTRAINT `commande_ibfk_3` FOREIGN KEY (`idEtat`) REFERENCES `etat` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `commande` (`num`, `idClient`, `dateCreation`, `idEtat`) VALUES
(3,	8,	'2020-10-22 10:42:31',	4),
(4,	8,	'2020-10-24 20:45:40',	2),
(6,	8,	'2020-10-24 20:45:38',	5),
(11,	22,	'2020-10-25 19:18:12',	3),
(12,	23,	'2020-10-25 19:32:58',	4),
(14,	13,	'2020-11-08 16:05:26',	2),
(15,	13,	'2020-11-08 16:07:46',	1),
(16,	22,	'2020-11-08 16:11:39',	2),
(17,	7,	'2020-11-08 16:18:17',	2),
(18,	7,	'2020-11-08 16:21:56',	4),
(19,	8,	'2020-11-11 07:57:34',	1),
(20,	27,	'2020-11-11 09:11:35',	2),
(21,	27,	'2020-11-11 09:14:40',	2),
(22,	27,	'2020-11-11 09:27:00',	4);

DELIMITER ;;

CREATE TRIGGER `commande_before_update` BEFORE UPDATE ON `commande` FOR EACH ROW
BEGIN 

DECLARE nbFactureActif int;
DECLARE nbCommandeNonPaye int;


SET nbFactureActif = (SELECT COUNT(f.numCmd) FROM facture f WHERE f.numCmd = OLD.num);

#Empêcher de considérer une commande non payé à une commande payé, car elle n'a aucune une facture
IF (nbFactureActif = 0 AND NEW.idEtat >= 2) THEN
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de considérer cette commande comme payée, car aucune facture ne lui correspond.";
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
IF ( nbCommandeNonPaye >= 1 && NEW.idEtat = 1  ) THEN
     SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible d'avoir plusieurs commandes non payé pour un client.";
end if;


END;;

DELIMITER ;

DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `idContact` int(3) NOT NULL AUTO_INCREMENT,
  `idClient` int(11) DEFAULT NULL,
  `nom` varchar(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `numero` int(10) NOT NULL,
  `sujet` varchar(40) NOT NULL,
  `message` text NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`idContact`),
  KEY `idClient` (`idClient`),
  CONSTRAINT `contact_ibfk_1` FOREIGN KEY (`idClient`) REFERENCES `client` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

INSERT INTO `contact` (`idContact`, `idClient`, `nom`, `email`, `numero`, `sujet`, `message`, `date`) VALUES
(1,	1,	'Hoareau Quentin',	'goldow974@gmail.com',	692466980,	'idOublie',	'J\'ai oublié mon mot de passe.',	'2020-02-17 00:00:00'),
(2,	NULL,	'Grondin Charlotte',	'grondin.charlotte@gmail.com',	693238645,	'idOublie',	'Problème avec mes identifiants, comment les récupérer?',	'2020-09-17 12:45:52'),
(4,	NULL,	'ABUFERA ',	'ophelien.abufera.bts@gmail.com',	692991200,	'suiviCommande',	'J\'aimerais savoir si vous effectuez des livraisons le samedi.',	'2020-10-25 08:30:30'),
(7,	NULL,	'Hoareau Léa',	'leajuliehoareau@orange.fr',	692818484,	'retourVet',	'Bonjour, est-ce possible de faire un retour d\'article svp? Si oui, comment?',	'2020-04-11 23:21:02'),
(8,	NULL,	'Robin Jean',	'roro13@gmail.com',	692458595,	'suiviCommande',	'Bonjour, est-ce normal que certains articles arrivent un par un? ',	'2020-07-30 15:48:00'),
(9,	NULL,	'Rivière Anthony',	'antho@gmail.com',	693455667,	'questionVet',	'Bonjour, il y a quelques jours j\'ai repéré un article intéressant. Aujourd\'hui je vais sur le site mais il n\'est plus là. Sera-t-il disponible à nouveau?\r\n\r\nBonne journée. ',	'2020-03-03 19:50:00'),
(10,	NULL,	'BIGOT Andréa',	'andrea.bigot974@gmail.com',	692466990,	'compteVole',	'Bonjour, j\'ai l\'impression qu\'il y a un problème avec mon compte. Serait-il volé? Que faire?',	'2020-06-29 21:52:00'),
(11,	NULL,	'Morel Seb',	'seb_morel@outlook.com',	692987874,	'remboursement',	'Le remboursement ne s\'est pas effectué correctement, il me manque une partie. Pourquoi?',	'2020-01-05 23:30:52'),
(12,	22,	'BIGOT Andréa',	'andrea.bigot974@gmail.com',	692466990,	'Problème lié au compte',	'zerfghtrd',	'2020-11-11 09:00:28');

DROP TABLE IF EXISTS `contact_reponse`;
CREATE TABLE `contact_reponse` (
  `idContact` int(3) NOT NULL,
  `reponse` text NOT NULL,
  `date` datetime NOT NULL,
  KEY `idContact` (`idContact`),
  CONSTRAINT `contact_reponse_ibfk_1` FOREIGN KEY (`idContact`) REFERENCES `contact` (`idContact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `contact_reponse` (`idContact`, `reponse`, `date`) VALUES
(1,	'démerde a ou haha',	'2020-01-05 23:30:52'),
(10,	'Testtestestestest',	'2020-09-02 13:50:00'),
(1,	'aaaa',	'2020-09-12 12:50:52'),
(1,	'Hey',	'2020-11-08 19:57:23'),
(1,	'azaza',	'2020-11-08 19:58:09'),
(1,	'wtffff',	'2020-11-08 19:58:17'),
(1,	'zaaz',	'2020-11-08 19:58:42'),
(1,	'zaaz',	'2020-11-08 19:58:45'),
(1,	'zaaz',	'2020-11-08 19:58:47'),
(1,	'zaaz',	'2020-11-08 19:58:49'),
(1,	'Heu wtf',	'2020-11-08 19:58:56'),
(1,	'azazaz',	'2020-11-08 19:59:50'),
(1,	'Bonjour Hoareau Quentin...',	'2020-11-08 20:02:04'),
(1,	'Bonjour Hoareau Quentin...',	'2020-11-08 20:03:19'),
(1,	'Bonjour Hoareau Quentin...azaz',	'2020-11-08 20:04:07'),
(1,	'Bonjour Hoareau Quentin... heiiin',	'2020-11-08 20:04:52'),
(9,	'Bonjour Rivière Anthony, oui tous nos articles se recharge en stock toutes les semaines. Bonne journée à toi.',	'2020-11-08 20:06:56'),
(1,	'Bonjour Hoareau Quentin...\r\ntest',	'2020-11-11 08:48:17'),
(10,	'Bonjour BIGOT Andréa... \r\ntest aaaa',	'2020-11-11 08:49:15');

DROP TABLE IF EXISTS `etat`;
CREATE TABLE `etat` (
  `id` tinyint(4) NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `etat` (`id`, `libelle`, `description`) VALUES
(1,	'Pas confirmée',	'Votre commande n\'a pas encore été validée, ni payéé.'),
(2,	'En instruction ',	'Vous avez payé, votre commande est en cours d\'instruction par nos experts.'),
(3,	'Préparation en cours',	'Votre commande est en préparation.'),
(4,	'Livraison en cours',	'Votre commande est actuellement en chemin.'),
(5,	'Livré',	'Votre commande à été livrée.');

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
(3,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-22 10:13:43'),
(4,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-22 11:09:07'),
(6,	'Gamer',	'Goldow',	'aaaaa',	'97400',	'Solde',	'2020-10-22 16:54:16'),
(11,	'BIGOT',	'Andréa',	'22 rue des macarons',	'97480',	'Solde',	'2020-11-01 18:53:47'),
(12,	'ABUFERA',	'Ophelien',	'119 rue leconte de lisle',	'97480',	'Solde',	'2020-10-25 19:34:32'),
(14,	'Hoareau',	'Léa',	'10 rue par ici, ter la',	'97480',	'Solde',	'2020-11-08 16:06:10'),
(16,	'BIGOT',	'Andréa',	'22 rue des macarons',	'97480',	'Solde',	'2020-11-08 16:12:11'),
(17,	'MOREL',	'Seb',	'3 rue de lameme',	'97480',	'Solde',	'2020-11-08 16:18:49'),
(18,	'MOREL',	'Seb',	'3 rue de lameme',	'97480',	'Solde',	'2020-11-08 16:22:10'),
(20,	'Grondin ',	'Laurent ',	'30 chemin des anémones',	'97480',	'Solde',	'2020-11-11 09:13:42'),
(21,	'Grondin ',	'Laurent ',	'30 chemin des anémones',	'97480',	'Solde',	'2020-11-11 09:14:51'),
(22,	'Grondin ',	'Laurent ',	'30 chemin des anémones',	'97480',	'Solde',	'2020-11-11 09:27:39');

DELIMITER ;;

CREATE TRIGGER `facture_after_insert` AFTER INSERT ON `facture` FOR EACH ROW
BEGIN
#Mettre à jours l'etat de la commande à : 'payé'
UPDATE commande SET idEtat = 2 WHERE num = NEW.numCmd ;
END;;

CREATE TRIGGER `facture_before_delete` BEFORE DELETE ON `facture` FOR EACH ROW
BEGIN 
DECLARE idEtat_ int ;

SELECT c.idEtat 
INTO idEtat_ 
FROM commande c
WHERE c.num = OLD.numCmd ;

#Empêcher de supprimer une facture dont la commande n'a pas encore été livré
IF ( idEtat_  != 5) THEN
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
  UNIQUE KEY `libelle` (`libelle`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `genre` (`code`, `libelle`) VALUES
('F',	'Femme'),
('H',	'Homme'),
('M',	'Mixte');

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
('XS');

DROP TABLE IF EXISTS `vetement`;
CREATE TABLE `vetement` (
  `id` int(11) NOT NULL,
  `nom` varchar(80) NOT NULL,
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
(1,	'Robe D\'Eté Superposée Fleurie Imprimée',	35.5,	NULL,	'F',	'Petite robe imprimée en coton avec des bretelles fines. Matières: rayonne.        ',	1),
(2,	'Short de Survêtement à Cordon',	10,	NULL,	'F',	'Short court à cordon. Matière: coton.',	5),
(3,	'T-shirt Manche longue unicolore',	15,	NULL,	'F',	'Tshirt manche longue en coton.',	2),
(4,	'Pull Court Simple Surdimensionné',	37,	NULL,	'F',	'Pull court manches longues. Matières: coton, polyester',	4),
(5,	'Pull Court Rayé à Col Rond',	38.2,	NULL,	'F',	'Pull rayé manches longues au col rond. Matières: polyester, coton',	4),
(6,	'Short Décontracté En Couleur Jointive à Taille Elastique',	13.8,	NULL,	'H',	'Matières: Polyamide',	5),
(7,	'T-shirt Motif De Lettre Dessin Animé',	15,	NULL,	'H',	'T-shirt pour homme en coton, col rond.',	2),
(8,	'Pull Tordu à Epaule Dénudée',	20,	NULL,	'F',	'Pull qui décore avec un design torsadé à l\'avant. Matières: coton, polyacrylique.',	4),
(9,	'Veste Déchirée En Couleur Unie En Denim',	34.9,	NULL,	'M',	'Veste déchirée avec un col rabattu à manches longues. Matières: coton, polyester.',	8),
(10,	'Pantalon Slim Taille Haute Déchiré',	12,	'background-position: 250px;',	'F',	'Pantalon taille haute, coupe slim avec la taille élastique. Matière: coton.\r\n',	12),
(11,	'Bermuda chino uni',	15,	NULL,	'H',	'Bermuda chino uni parfait pour l\'été.',	5),
(12,	'T-shirt Graphique Grue Barboteuse Chinoise Fleurie Imprimé',	17.99,	'background-position: 305px;',	'H',	'T-shirt manches courtes imprimé en coton.',	2),
(13,	'T-shirt Court Sanglé à Col V',	10,	NULL,	'F',	'T-shirt Court Sanglé à Col V.\r\nMatières: Polyuréthane,Rayonne',	2),
(14,	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée',	11,	NULL,	'F',	'Débardeur d\'Entraînement Côtelé à Bretelle Croisée. \r\nMatières: Coton,Polyester',	2),
(15,	'Haut Court Côtelé Sans Dos à Bretelle ',	12,	'background-position: 90px;',	'F',	'Haut Court Côtelé Sans Dos à Bretelle qui met en valeur la taille marquée. \r\nMatières: Polyuréthane,Rayonne',	2),
(16,	' Haut Court Côtelé à Bretelle Tordu',	15,	'background-position: 100px;',	'F',	'Haut Court Côtelé à Bretelle Trodu.\r\nHaut qui flatte la silhouette avec des fines bretelles mettant en avant le décolleté et le dos. \r\nMatières: Polyuréthane,Rayonne',	2),
(17,	'T-Shirt à Imprimé Rayures En Blocs De Couleurs',	10,	'    background-position: 180px;',	'H',	'Un t-shirt avec un motif à rayures panachées, un col rond, des manches courtes et une coupe classique.\r\nMatières: Polyester',	2),
(18,	'T-shirt Rose Brodée à Manches Courtes',	13.5,	NULL,	'H',	'T-shirt basique surmonté d\'un col rond et manches courtes.\r\nMatières: Coton,Polyester,Spandex',	2),
(19,	'Veste Déchirée Avec Poche à Rabat En Denim',	37.6,	'background-position: 85px;',	'H',	'Veste déchirée manches longues.\r\nMatières: Coton,Polyester,Spandex',	8),
(20,	'Pantalon de Survêtement Lettre Applique à Cordon en Laine',	23.5,	NULL,	'H',	'Pantalon de Survêtement avec élastique à la taille en coton.',	12),
(21,	'Pantalon Panneau En Blocs De Couleurs à Taille Elastique',	19.99,	NULL,	'H',	'Pantalon à Taille Elastique en polyesther. ',	12),
(22,	'T-shirt Rayé Chiffre Brodé à Manches Longues',	14.9,	NULL,	'H',	'T-shirt Rayé Chiffre Brodé à Manches Longues\r\nMatières: Coton,Polyacrylique,Polyester',	4),
(23,	'Robe à Bretelle Fleurie Plissée à Volants',	20,	NULL,	'F',	'Robe à Bretelle Fleurie Plissée à Volants.\r\nLes plis sont réunis avec la taille élastique et le dos smocké aide à façonner les courbes.\r\nMatières: Polyester',	1),
(24,	'Mini Robe à Carreaux Ligne A',	11.2,	NULL,	'F',	'Détendu en forme, féminin dans le style, cette robe cami dispose d\'une impression tout au long de ceindre, fines bretelles et une coupe mini longueur séduisante, dans une silhouette évasée. portez-le avec des talons pour un style charmant.\r\nMatières: Polyester',	1),
(25,	'Jupe Ligne A Teintée à Cordon',	13,	NULL,	'F',	'Jupe colorée en polyester.',	6),
(26,	'Mini Jupe Ligne A Nouée',	14,	NULL,	'F',	'Jupe courte avec une fermeture zippée. \r\nMatières: Polyester,Polyuréthane',	6),
(27,	'Short Déchiré Zippé Design En Denim',	19.65,	NULL,	'H',	'Short déchiré zippé en denim.\r\nMatières: Coton,Polyester,Spandex',	5),
(28,	'Pull Court Simple Surdimensionné - Brique Réfractaire M',	19.5,	'',	'F',	'Pull oversize, manches longues et épaule tombante.\r\nMatières: Coton,Polyester;',	4),
(29,	'Pull Court Rayé à Col Rond - Noir',	15.5,	'',	'F',	'Pull décontracté court à col rond. \r\nMatières: Coton,Polyester',	4),
(30,	'Short Paperbag Ceinturé Fleuri Imprimé à Volants - Multi Xl',	8.66,	'',	'F',	'Short souple taille haute avec une ceinture à nouer. \r\nMatières: Rayonne',	5),
(31,	'Mini Short Plissé Noué ',	10,	'',	'F',	'Short style décontracté, fermeture braguette zippée. \r\nMatières: Polyester',	5),
(32,	'Short en denim avec poche déchirée et ourlet effiloché',	17.5,	'',	'F',	'Short en denim déchiré.\r\nMatières: Coton, Polyester.',	5),
(33,	'Short Paperbag Rayé Ceinturé',	8.5,	'',	'F',	'Doté d\'un motif à rayures tout au long, ce short a une ceinture haute. La ceinture de  nouée autour de la taille ajoute du charme et de la mode. \r\nMatières: Polyester',	5),
(34,	'Short noué à volants et bordure en crochet',	9,	'',	'F',	'Short court à volants resserré à la taille avec un élastiques.\r\nMatières: Rayonne.',	5),
(35,	'Short Teinté Ceinturé à Jambe Large',	10,	'',	'F',	'Short court noué à la taille.\r\nMatières: Polyester.\r\n',	5),
(36,	'Pantalon Droit Boutonné En Velours Côtelé',	13.5,	'    background-position: 425px;',	'F',	'Pantalon droit en velours côtelé.\r\nMatières: Coton, Polyester',	12),
(37,	'Pantalon Visage Souriant Bicolore à Cordon - Multi-b L',	15.6,	'    background-position: 376px;',	'M',	'Pantalon à cordon décontracté. Tissu légèrement extensible.\r\nMatières: Polyester.',	12),
(38,	'Chemise en velours côtelé à manches longues et empiècement color-block',	20,	'',	'H',	'Veste stylée très colorée.\r\nMatières: Coton, Polyester',	8),
(39,	'Mini Robe Moulante Découpée à Col Montant ',	20,	'',	'F',	'Robe moulante manches longues.\r\nMatières: Polyester,Rayonne',	1),
(40,	'Short de bain imprimé avec cordon de serrage',	25.5,	'    background-position: 440px;',	'H',	'Short de bain en polyester avec cordon.',	5),
(41,	'Short De Plage Palmier Imprimé',	15,	'',	'H',	'Short de plage imprimé à cordon.\r\nMatières: Polyester',	5),
(42,	'Short Déchiré Jointif En Denim',	24,	'',	'H',	'Short déchiré en jean style décontracté.\r\nMatières: Coton,Polyester',	5),
(43,	'Short De Plage Rayé Fleur Imprimé à Cordon',	16,	'    background-position: 373px;',	'H',	'Short de plage court imprimé. \r\nMatières: Polyester',	5),
(44,	'Pantalon Cargo Panneau En Blocs De Couleurs à Pieds Etroits',	25.9,	'background-position: 204px;',	'H',	'Pantalon cargo type regular avec cordon de serrage.\r\nMatières: Coton',	12),
(45,	'Veste Poche à Rabat Motif De Rose',	19.5,	'',	'H',	'Veste à motif, col montant.\r\nMatières: Coton,Polyester',	8),
(46,	'Veste Décontractée Contrastée Rayée En Blocs De Couleurs à Goutte Epaule',	39.99,	'background-position: 206px;',	'H',	'Veste rayée en polyester. ',	8),
(47,	'Veste Motif De Lettre Décorée De Poche',	29.99,	'background-position: 204px;',	'H',	'Veste style décontracté en polyester.',	8),
(48,	'Sweat à Capuche Fourré Teinté Lettre Brodée',	27.99,	'',	'M',	'Sweat à capuche très doux.\r\nMatières: Coton, Polyester',	4),
(49,	'Pantalon Déchiré Zippé En Denim - Bleu 2xl',	30,	'background-position: 204px;',	'H',	'Pantalon déchiré type regular. \r\nMatières: Coton, Polyester, Polyuréthane.',	12),
(50,	'Jean Droit Déchiré Long - Noir Xl',	25,	'    background-position: 222px;',	'H',	'Jean déchiré type regular.\r\nMatières: Coton, Polyester',	3),
(51,	'Pantalon Crayon Zippé Ange en Denim - Blanc 32',	35,	'background-position: 204px;',	'H',	'Pantalon crayon type regular.\r\nMatières: Coton, Spandex.',	12);

DELIMITER ;;

CREATE TRIGGER `after_insert_vetement` AFTER INSERT ON `vetement` FOR EACH ROW
BEGIN

DECLARE numVetCouleur int;
SET numVetCouleur= (SELECT max(num )+1
                    FROM vet_couleur);

INSERT INTO vet_couleur VALUES (numVetCouleur, NEW.id, "Couleur orginale", null, 1);

END;;

DELIMITER ;

DROP TABLE IF EXISTS `vet_couleur`;
CREATE TABLE `vet_couleur` (
  `num` int(3) NOT NULL,
  `idVet` int(3) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `filterCssCode` varchar(200) DEFAULT '',
  `dispo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`num`),
  UNIQUE KEY `idVet_filterCssCode` (`idVet`,`filterCssCode`),
  KEY `idVet` (`idVet`),
  CONSTRAINT `vet_couleur_ibfk_2` FOREIGN KEY (`idVet`) REFERENCES `vetement` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
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
(19,	20,	'Gris',	NULL,	1),
(20,	22,	'Rayé noir',	NULL,	1),
(21,	21,	'Couleur jaune rose bleu vert',	NULL,	1),
(22,	13,	'Noir',	NULL,	1),
(23,	19,	'Bleu marine jean',	NULL,	1),
(24,	14,	'Noir',	NULL,	1),
(25,	25,	'Arc-en-ciel',	NULL,	1),
(26,	23,	'Noir rose',	NULL,	1),
(27,	26,	'Marron',	NULL,	1),
(28,	24,	'Noir',	NULL,	1),
(29,	12,	'Noir à motif rond rouge',	NULL,	1),
(30,	27,	'Jean',	NULL,	1),
(31,	17,	'Blanc à rayure jaune',	NULL,	1),
(32,	15,	'Rose ',	NULL,	1),
(33,	16,	'Violet ',	NULL,	1),
(34,	18,	'Noir',	NULL,	1),
(35,	19,	'Gris',	'grayscale(1);',	1),
(36,	17,	'Blanc à rayure vert',	'hue-rotate(45deg)',	1),
(38,	26,	'Vert',	'hue-rotate(100deg)',	1),
(39,	26,	'Rose',	'hue-rotate(300deg)',	1),
(40,	17,	'Blanc à rayure bleu',	'hue-rotate(190deg)',	1),
(41,	15,	'Vert',	'hue-rotate(100deg)',	1),
(42,	15,	'Bleu',	'hue-rotate(200deg)',	1),
(43,	16,	'Jaune',	'hue-rotate(120deg)',	1),
(44,	15,	'Violet',	'hue-rotate(300deg)',	1),
(45,	16,	'Vert',	'hue-rotate(200deg)',	1),
(46,	28,	'Rouge brique',	NULL,	1),
(47,	25,	'Multicolore océan',	'hue-rotate(120deg);',	1),
(48,	28,	'Vert forêt',	'hue-rotate(180deg);',	1),
(49,	12,	'Noir motif rond orange',	'hue-rotate(45deg);',	1),
(50,	12,	'Noir motif rond rose',	'hue-rotate(320deg);',	1),
(51,	28,	'Violet clairci',	'saturate(1.2) brightness(1.2) hue-rotate(600000000deg)',	1),
(52,	30,	'Rouge à mini pois blanc ',	NULL,	1),
(53,	29,	'Noir rayé blanc',	'',	1),
(54,	31,	'Kaki foncé',	NULL,	1),
(55,	32,	'Jean clair ',	NULL,	1),
(56,	33,	'Vert forêt rayé orange',	NULL,	1),
(57,	37,	'Semi rouge et noir',	NULL,	1),
(58,	49,	'Jean basic',	NULL,	1),
(59,	50,	'Noir jean',	NULL,	1),
(60,	51,	'Blanc à motif coloré',	NULL,	1),
(61,	34,	'Jaune',	NULL,	1),
(62,	35,	'Motif bleu et blanc',	NULL,	1),
(63,	36,	'Vert',	NULL,	1),
(64,	38,	'Coloré jaune vert rouge noir',	NULL,	1),
(65,	39,	'Marron clair',	NULL,	1),
(66,	40,	'Multicolore et noir ',	NULL,	1),
(67,	41,	'Bleu et blanc',	NULL,	1),
(68,	42,	'Jean',	NULL,	1),
(69,	43,	'Noir et motif fleuri',	NULL,	0),
(70,	44,	'Noir et rouge',	NULL,	1),
(71,	45,	'Noir',	NULL,	1),
(72,	46,	'Bleu jaune et blanc',	NULL,	1),
(73,	47,	'Jaune et noir',	NULL,	1),
(74,	48,	'Multicolore blanc rose et bleu',	NULL,	1),
(76,	48,	'Multicolore blanc rose et violet',	'hue-rotate(45deg)',	1),
(77,	48,	'Multicolore blanc rose et vert',	'hue-rotate(500deg)',	1),
(78,	46,	'Violet vert et blanc',	'hue-rotate(45deg)',	1),
(79,	46,	'Vert rose et blanc',	'hue-rotate(300deg)',	1),
(80,	47,	'Vert et noir',	'hue-rotate(45deg)',	1),
(81,	47,	'Bleu et noir',	'hue-rotate(900deg)',	1),
(82,	47,	'Orange et noir',	'hue-rotate(700deg)',	1),
(83,	44,	'Noir et vert',	'hue-rotate(100deg)',	1),
(84,	44,	'Noir et bleu',	'hue-rotate(200deg)',	1),
(85,	43,	'Noir et motif fleuri rose vert',	'hue-rotate(300deg)',	1),
(86,	41,	'Orange et blanc',	'hue-rotate(200deg)',	1),
(87,	41,	'Vert et blanc',	'hue-rotate(300deg)',	1),
(88,	39,	'Gris',	'grayscale(1)',	1),
(89,	39,	'Rose',	'hue-rotate(300deg)',	1),
(90,	39,	'Vert',	'hue-rotate(400deg)',	1),
(91,	39,	'Bleu',	'hue-rotate(900deg)',	1),
(92,	37,	'Semi bleu et noir',	'hue-rotate(200deg)',	1),
(93,	36,	'Bleu',	'hue-rotate(45deg)',	1),
(94,	36,	'Rose',	'hue-rotate(200deg)',	1),
(95,	36,	'Violet',	'hue-rotate(500deg)',	1),
(96,	35,	'Motif violet et blanc',	'hue-rotate(45deg)',	1),
(97,	35,	'Motif rose et blanc',	'hue-rotate(100deg)',	1),
(98,	35,	'Motif vert et blanc',	'hue-rotate(300deg)',	1),
(99,	34,	'Vert',	'hue-rotate(45deg)',	1),
(100,	34,	'Bleu',	'hue-rotate(500deg)',	1),
(101,	34,	'Orange',	'hue-rotate(700deg)',	1),
(102,	32,	'Violet',	'hue-rotate(45deg)',	1),
(103,	32,	'Rose',	'hue-rotate(100deg)',	1),
(104,	30,	'Vert à mini pois blanc',	'hue-rotate(100deg)',	1),
(105,	30,	'Bleu à mini pois blanc',	'hue-rotate(200deg)',	0),
(106,	30,	'Fuchsia à mini pois blanc ',	'hue-rotate(700deg)',	1),
(107,	1,	'Bleu',	NULL,	1);

DELIMITER ;;

CREATE TRIGGER `before_insert_vetCouleur` BEFORE INSERT ON `vet_couleur` FOR EACH ROW
BEGIN

DECLARE nbCouleurOrg int;
SET nbCouleurOrg= (SELECT COUNT(*) 
                   FROM vet_couleur vc
                   WHERE vc.idVet=NEW.idVet AND vc.filterCssCode is null);

-- empêcher d'avoir plus d'une couleur d'origine -- 
IF (nbCouleurOrg >=1) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Un vêtement ne peut posséder qu'une seule couleur d'origine à la fois";
END IF;
END;;

CREATE TRIGGER `before_update_vetCouleur` BEFORE UPDATE ON `vet_couleur` FOR EACH ROW
BEGIN

DECLARE nbCouleurOrg int;
SET nbCouleurOrg= (SELECT COUNT(*) 
                   FROM vet_couleur vc
                   WHERE vc.idVet=OLD.idVet AND vc.filterCssCode is null);

IF (OLD.filterCssCode IS NULL AND NEW.filterCssCode IS NOT NULL  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de modifier, il faut minimum une couleur d'origine";
END IF;




-- empêcher d'avoir plus d'une couleur d'origine -- 
IF (nbCouleurOrg >=1 AND OLD.filterCssCode IS NOT NULL AND NEW.filterCssCode IS NULL) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Un vêtement ne peut posséder qu'une seule couleur d'origine à la fois";
END IF;

END;;

CREATE TRIGGER `before_delete_vetCouleur` BEFORE DELETE ON `vet_couleur` FOR EACH ROW
BEGIN
IF (OLD.filterCssCode IS NULL  ) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT= "Impossible de supprimer, il faut minimum une couleur d'origine";
END IF;
END;;

DELIMITER ;

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
(10,	'32'),
(20,	'32'),
(34,	'32'),
(37,	'32'),
(49,	'32'),
(30,	'34'),
(34,	'34'),
(40,	'34'),
(49,	'34'),
(10,	'36'),
(30,	'36'),
(31,	'36'),
(32,	'36'),
(33,	'36'),
(34,	'36'),
(37,	'36'),
(40,	'36'),
(49,	'36'),
(50,	'36'),
(51,	'36'),
(10,	'38'),
(11,	'38'),
(30,	'38'),
(31,	'38'),
(32,	'38'),
(33,	'38'),
(36,	'38'),
(37,	'38'),
(40,	'38'),
(43,	'38'),
(49,	'38'),
(50,	'38'),
(51,	'38'),
(10,	'40'),
(11,	'40'),
(30,	'40'),
(31,	'40'),
(32,	'40'),
(36,	'40'),
(42,	'40'),
(43,	'40'),
(50,	'40'),
(51,	'40'),
(10,	'42'),
(11,	'42'),
(27,	'42'),
(36,	'42'),
(42,	'42'),
(43,	'42'),
(3,	'L'),
(6,	'L'),
(7,	'L'),
(9,	'L'),
(12,	'L'),
(13,	'L'),
(14,	'L'),
(15,	'L'),
(16,	'L'),
(18,	'L'),
(19,	'L'),
(22,	'L'),
(23,	'L'),
(24,	'L'),
(25,	'L'),
(28,	'L'),
(29,	'L'),
(35,	'L'),
(38,	'L'),
(40,	'L'),
(41,	'L'),
(42,	'L'),
(43,	'L'),
(44,	'L'),
(45,	'L'),
(46,	'L'),
(48,	'L'),
(1,	'M'),
(2,	'M'),
(3,	'M'),
(4,	'M'),
(7,	'M'),
(8,	'M'),
(9,	'M'),
(13,	'M'),
(14,	'M'),
(16,	'M'),
(17,	'M'),
(19,	'M'),
(22,	'M'),
(23,	'M'),
(24,	'M'),
(25,	'M'),
(26,	'M'),
(28,	'M'),
(29,	'M'),
(35,	'M'),
(38,	'M'),
(39,	'M'),
(40,	'M'),
(42,	'M'),
(43,	'M'),
(44,	'M'),
(45,	'M'),
(46,	'M'),
(47,	'M'),
(48,	'M'),
(1,	'S'),
(3,	'S'),
(4,	'S'),
(5,	'S'),
(6,	'S'),
(8,	'S'),
(12,	'S'),
(13,	'S'),
(14,	'S'),
(15,	'S'),
(17,	'S'),
(25,	'S'),
(26,	'S'),
(28,	'S'),
(39,	'S'),
(42,	'S'),
(45,	'S'),
(46,	'S'),
(47,	'S'),
(2,	'XL'),
(3,	'XL'),
(5,	'XL'),
(6,	'XL'),
(7,	'XL'),
(16,	'XL'),
(18,	'XL'),
(19,	'XL'),
(23,	'XL'),
(24,	'XL'),
(25,	'XL'),
(29,	'XL'),
(38,	'XL'),
(40,	'XL'),
(41,	'XL'),
(42,	'XL'),
(43,	'XL'),
(48,	'XL'),
(6,	'XS'),
(8,	'XS'),
(13,	'XS'),
(15,	'XS'),
(17,	'XS'),
(24,	'XS'),
(26,	'XS'),
(41,	'XS'),
(45,	'XS'),
(47,	'XS');

DROP VIEW IF EXISTS `vue_vet_disponibilite`;
CREATE TABLE `vue_vet_disponibilite` (`idVet` int(11), `listeNumCouleurDispo` mediumtext, `listeTailleDispo` mediumtext);


DROP TABLE IF EXISTS `vue_vet_disponibilite`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vue_vet_disponibilite` AS select `v`.`id` AS `idVet`,(select group_concat(`vcl2`.`num` separator ',') from `vet_couleur` `vcl2` where `vcl2`.`idVet` = `v`.`id` and `vcl2`.`dispo` = 1 order by `vcl2`.`filterCssCode`) AS `listeNumCouleurDispo`,group_concat(distinct `vt`.`taille` separator ',') AS `listeTailleDispo` from ((`vetement` `v` left join `vet_couleur` `vcl` on(`vcl`.`idVet` = `v`.`id`)) left join `vet_taille` `vt` on(`vt`.`idVet` = `v`.`id`)) group by `v`.`id`;

-- 2020-11-11 06:13:02
