<?php

class VetementManager extends DataBase{
    private $reqBase = "SELECT *
    FROM vetement v
    INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet = v.id" ;
    public $Pagination = null;

    public function setPagination($nbArtPage){
        if( !isset($_GET["page"]) ){ $_GET["page"] = null ; }
        $this->Pagination = new Pagination($_GET["page"], $nbArtPage) ;
    }

    //Obtient les 3 dernières nouveautés
    public function getNouveaute(){
        $req = "SELECT * FROM vetement LIMIT 3";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Vetement");
    }

    //Obtient les infos d'un de vêtements
    public function getVetement($id){
        $req = $this->reqBase." WHERE v.id = ?" ;
        $this->getBdd();
        return $this->getModele($req, [$id], "Vetement")[0];
    }


    //Obtient toute la liste de vêtements
    public function getListeVetement(){
        $resultat = null;
        if( $this->Pagination != null){
            $req = $this->reqBase." WHERE vvd.listeIdCouleurDispo IS NOT NULL
            AND vvd.listeIdTailleDispo IS NOT NULL";
            $this->Pagination->getBdd();
    
            $newReq = $this->Pagination->getReqPagination($req, ["*"]);
            $resultat = $this->Pagination->getModele($newReq, ["*"], "Vetement") ;
        }
       
        return $resultat;
    }

    public function getRechercheVetement($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre){
       
        $resultat = null;
        if( $this->Pagination != null){
            $Recherche = new Recherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre) ;
            $reqRecherche = $Recherche->getReqFinal() ;
            $this->Pagination->getBdd();
    
            $newReq = $this->Pagination->getReqPagination($reqRecherche, ["*"]);
            $resultat = $this->Pagination->getModele($newReq, ["*"], "Vetement") ;
        }
       
        return $resultat;
        
     
    }

    public function getListeVetByCategGenre($libelleGenre, $idCateg){
        $this->getBdd();
        //Obtention de l'id du genre
        $reqGnere= "SELECT num FROM genre WHERE libelle LIKE ?" ;
        $numGenre = $this->execBDD($reqGnere, [$libelleGenre])[0]["num"];

        $this->getBdd();
        $req = $this->reqBase." WHERE v.idCateg = ? AND numGenre = ?";
        
        return $this->getModele($req, [$idCateg, $numGenre], "Vetement");
    }

    public function getListeVetByGenre($libelleGenre){
        $this->getBdd();
        //Obtention de l'id du genre
        $reqGnere = "SELECT num FROM genre WHERE libelle LIKE ?" ;
        $numGenre = $this->execBDD($reqGnere, [$libelleGenre])[0]["num"];


        $req = $this->reqBase." WHERE numGenre = ?";
        $this->getBdd();
        return $this->getModele($req, [$numGenre], "Vetement");
    }

}

?>