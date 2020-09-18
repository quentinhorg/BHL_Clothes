<?php

class RechercheManager extends DataBase{
    
    public function getRecherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre){
        
        $Recherche = new Recherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre) ;
        $reqRecherche = $Recherche->getReqFinal() ;
      
        return $reqRecherche ;
     
    }



}

?>