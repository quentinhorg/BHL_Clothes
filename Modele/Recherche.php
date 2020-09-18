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
            $req = "v.idCateg = ".$this->categorie;
        }else{ $req = "v.idCateg = v.idCateg" ;}

        return $req;
    }
    public function getReqGenre(){
        if($this->categorie != null){
            $req = "v.numGenre = ".$this->genre;
        }else{ $req = "v.numGenre = v.numGenre" ;}

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








?>
