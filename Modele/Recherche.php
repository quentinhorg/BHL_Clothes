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
            $req = " AND t.libelle IN(".implode(",", $this->listeTaille ).")";
        }else{ $req = null ;}

        return $req;
    }

    public function getReqCouleur(){
        if($this->listeCouleur != null){
            $req = "AND (vc.nom LIKE '%". implode("%' OR vc.nom LIKE '%", $this->listeCouleur )."%')";
        }else{ $req = null ;}
        
        return $req;
    }
    public function getReqCateg(){
        if($this->categorie != null){
            $req = "AND v.idCateg = ".$this->categorie;
        }else{ $req = null ;}

        return $req;
    }
    
    public function getReqGenre(){
        if($this->genre != null){
            $req = "AND g.libelle LIKE  '".$this->genre."'";
        }else{ $req = null ;}

        return $req;
    }

    public function getReqPrix(){
        if($this->intervalePrix != null){
            $req = "AND v.prix BETWEEN ".$this->intervalePrix[0]." AND ".$this->intervalePrix[1]."";
        }else{ $req = null;}
        
        return $req;
    }

    public function getReqFinal(){
        $reqFinal = "SELECT DISTINCT(v.id), v.*
            FROM vetement v 
            INNER JOIN vet_taille vt ON vt.idVet = v.id 
            INNER JOIN vet_couleur vc ON vc.idVet= v.id 
            INNER JOIN genre g ON g.code = v.codeGenre
            INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet= v.id 
            LEFT JOIN taille t ON t.libelle = vt.taille
            WHERE vvd.listeIdCouleurDispo IS NOT NULL
            AND vvd.listeTailleDispo IS NOT NULL".
            " ".$this->getReqCouleur().
            " " .$this->getReqTaille().
            " ".$this->getReqPrix().
            " ".$this->getReqCateg().
            " ".$this->getReqGenre();
        return $reqFinal;
    }
    

    

}








?>
