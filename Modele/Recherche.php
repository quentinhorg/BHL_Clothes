<?php 

class Recherche{
    private $intervalePrix = array();
    private $listeTaille = array();
    private $couleurText = array();
    private $categorie;
    private $genre;

    public function __construct($prixIntervale, $listeTaille, $couleurText, $categorie, $genre){
        $this->intervalePrix = $prixIntervale ;
        $this->listeTaille = $listeTaille ;
        $this->couleurText = $couleurText ;
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
        if($this->couleurText != null){

            $tabCouleur = explode(" ", $this->couleurText);

            $req = "(vc.nom LIKE '%".implode("%' OR vc.nom LIKE '%", $tabCouleur )."%')";
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
        if($this->genre != null){
            $req = "g.libelle LIKE  '".$this->genre."'";
        }else{ $req = "g.libelle = g.libelle" ;}

        return $req;
    }

    public function getReqPrix(){
        if($this->intervalePrix != null){
            $req = "v.prix BETWEEN ".$this->intervalePrix[0]." AND ".$this->intervalePrix[1]."";
        }else{ $req = "v.prix = v.prix" ;}
        
        return $req;
    }

    public function getReqFinal(){
        $reqFinal = "SELECT DISTINCT(v.id), v.*
            FROM vetement v 
            INNER JOIN vet_taille vt ON vt.idVet = v.id 
            INNER JOIN vet_couleur vc ON vc.idVet= v.id 
            INNER JOIN genre g ON g.num = v.numGenre
            INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet= v.id 
            INNER JOIN taille t ON t.id = vt.idVet
            WHERE ".$this->getReqCouleur().
            " AND " .$this->getReqTaille().
            " AND ".$this->getReqPrix().
            " AND ".$this->getReqCateg().
            " AND ".$this->getReqGenre();
        return $reqFinal;
    }
    

    

}








?>
