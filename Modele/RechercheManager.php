<?php

class RechercheManager extends DataBase{


    public function setPagination($nbArtPage){
        if( !isset($_GET["page"]) ){ $_GET["page"] = null ; }
        $this->Pagination = new Pagination($_GET["page"], $nbArtPage) ;
    }

    
    public function getRecherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre){
       
        $Recherche = new Recherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre) ;
        $reqRecherche = $Recherche->getReqFinal() ;
        
        $this->getBdd();
        $resultat = $this->getModele($reqRecherche, ["*"],"Vetement") ;
        return $resultat;
        
     
    }



}

?>