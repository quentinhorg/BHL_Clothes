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
            $req = "t.id IN(".implode(",", $this->listeTaille ).")";
        }else{ $req = "t.id = t.id" ;}

        return $req;
    }

    public function getReqCouleur(){
        if($this->listeCouleur != null){
            $req = "(vc.nom LIKE '%".implode("%' OR vc.nom LIKE '%", $this->listeCouleur )."%')";
        }else{ $req = "vc.nom = vc.nom" ;}

        return $req;
    }
    public function getReqCateg(){
        if($this->categorie != null){
            $req = "(v.nom LIKE )";
        }else{ $req = "vc.nom = vc.nom" ;}

        return $req;
    }

    public function getReqPrix(){
        if($this->listeCouleur != null){
            $req = "v.prix BETWEEN ".$this->intervalePrix[0]." AND ".$this->intervalePrix[1]."";
        }else{ $req = "v.prix = v.prix" ;}

        return $req;
    }

    public function getReqFinal(){
        $reqFinal = "SELECT DISTINCT(v.id), v.*
            FROM vetement v 
            INNER JOIN vet_taille vt ON vt.idVet = v.id 
            INNER JOIN vet_couleur vc ON vc.idVet= v.id 
            INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet= v.id 
            INNER JOIN taille t ON t.id = vt.idVet
            WHERE ".$this->getReqCouleur().
            " AND " .$this->getReqTaille().
            " AND ".$this->getReqPrix();
        return $reqFinal;
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
