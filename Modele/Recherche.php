<?php 

class Recherche{
    private $prix;
    private $taille;
    private $couleur;
    private $categorie;
    private $genreHomme;
    private $genreFemme;
    private $genreMixte;

    public function __construct($prix ){
        
    }


}

/*

SELECT * 
FROM taille
WHERE libelle IN("S")        req pour S

SELECT * 
FROM taille
WHERE libelle IN("XS")        req pour XS

SELECT * 
FROM taille
WHERE libelle IN("M")           req pour M

SELECT * 
FROM taille
WHERE libelle IN("L")        req pour L

SELECT * 
FROM taille
WHERE libelle IN("XL")             req pour XL

SELECT * 
FROM taille
WHERE libelle IN("XL","L")          req pour XL + L

SELECT * 
FROM taille
WHERE libelle IN("XS","S")          REQ POUR XS + S

SELECT * 
FROM taille
WHERE libelle IN("M","S")           req pour M + S


SELECT * 
FROM taille
WHERE libelle IN("M","L")            req pour M + L














*/






?>
