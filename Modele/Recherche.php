<?php 

class Recherche{
    private $intervalePrix = array();
    private $listeTaille = array();
    private $listeCouleur = array();
    private $categorie;
    private $genre;


    public function __construct($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre){
        $this->intervalePrix = $prixIntervale ;
        $this->listeTaille = $listeTaille ;
        $this->listeCouleur = $listeCouleur ;
        $this->categorie = $categorie ;
        $this->genre = $genre;
    }

    public function getReqTaille(){
        if($this->listeTaille != null){
            $req = "t.taille IN(".implode(",", $this->listeTaille ).")";
        }else{ $req = null ;}

        return $req;
    }

    public function get(){
        if($this->listeTaille != null){
            $req = "t.taille IN(".implode(",", $this->listeTaille ).")";
        }else{ $req = null ;}

        return $req;
    }

    

}

/*


SELECT * 
FROM vetement v
INNER JOIN vet_taille t ON t.idVet=v.id
INNER JOIN vet_couleur c ON c.idVet=v.id
INNER JOIN vue_vet_disponibilite d ON d.idVet=v.id          
INNER JOIN taille ON taille.id=t.idTaille
WHERE libelle IN("XS", "S", "L", "M", "XL")


SELECT * 
FROM vetement
INNER JOIN vet_taille t ON t.idVet=vetement.id
INNER JOIN vet_couleur c ON c.idVet=vetement.id
INNER JOIN vue_vet_disponibilite d ON d.idVet=vetement.id
INNER JOIN taille ON taille.id=t.idTaille
WHERE c.nom IN("Jaune","Beige","Rouge")


taille 
couleur

taille + couleur 

vet possède au moins 1 taille, 1 couleur


fonction estDispo= si possède 1 couleur ET 1 taille= dispo sinon pas dispo



*/






?>
