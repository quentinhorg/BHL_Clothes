SHOW CREATE PROCEDURE bhl_clothes.<nom_procedure>
SHOW CREATE FUNCTION bhl_clothes.<nom_procedure>



--Fonction pour vérifier si l'article existe
DELIMITER |
CREATE FUNCTION qte_article(_numCmd int(11), _idVet int(3), _taille varchar(3), _numClr int(11)) 
RETURNS int
BEGIN
  RETURN (SELECT qte
  FROM article_panier ap
  WHERE ap.numCmd = _numCmd 
  AND ap.idVet = _idVet 
  AND ap.taille  = _taille 
  AND ap.numClr = _numClr);
END |





-- Procedure d'insertion d'un article

DELIMITER |
CREATE PROCEDURE insert_article(_numCmd int(11), _idVet int(3), _taille varchar(3), _numClr int(11), _qte int)
BEGIN
DECLARE newOrdreArr tinyint;
DECLARE qteArticle int;

SET qteArticle = (SELECT qte_article(_numCmd , _idVet , _taille , _numClr));

SET newOrdreArr = (
  SELECT
CASE WHEN MAX(ap.ordreArrivee) = (
  SELECT ap2.ordreArrivee FROM article_panier ap2
  WHERE ap2.numCmd = _numCmd 
  AND ap2.idVet = _idVet 
  AND ap2.taille  = _taille 
  AND ap2.numClr = _numClr
) THEN MAX(ap.ordreArrivee) ELSE MAX(ap.ordreArrivee)+1
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

END |


