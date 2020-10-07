<?php

class VetementManager extends DataBase{
    private $reqBase = "SELECT *, (SELECT COUNT(id) FROM commentaire WHERE idVet=v.id) AS nbCommentaire
    FROM vetement v
    INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet = v.id" ;
    public $Pagination = null;

    
    //    public function nbCommentaire($id){
    //     $req="SELECT COUNT(id) FROM commentaire WHERE idVet=?";
    //     $this->getBdd();
    //     $nbCommentaire= $this->getModele($req, [$id], "Commentaire")[0];

    //     return $nbCommentaire;
    // }

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
            AND vvd.listeTailleDispo IS NOT NULL ORDER BY v.id DESC";
            $this->Pagination->getBdd();
            
            $newReq = $this->Pagination->getReqPagination($req, ["*"]);
            $resultat = $this->Pagination->getModele($newReq, ["*"], "Vetement") ;
        }
       
        return $resultat;
    }

    public function getRechercheVetement($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre, $motCle){
       
        $resultat = null;
        if( $this->Pagination != null){
            $Recherche = new Recherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre, $motCle) ;
            $reqRecherche = $Recherche->getReqFinal() ;
            $this->Pagination->getBdd();
       
            $newReq = $this->Pagination->getReqPagination($reqRecherche, ["*"]);
            $resultat = $this->Pagination->getModele($newReq, ["*"], "Vetement") ;
        }
       
        return $resultat;
        
     
    }

    public function getListeVetByCategGenre($codeGenre, $idCateg){
        $this->getBdd();

        $this->getBdd();
        $req = $this->reqBase." WHERE v.idCateg = ? 
        AND codeGenre = ? 
        AND vvd.listeIdCouleurDispo IS NOT NULL
        AND vvd.listeTailleDispo IS NOT NULL
        ORDER BY v.id DESC";
        
        return $this->getModele($req, [$idCateg, $codeGenre], "Vetement");
    }

    public function getListeVetByGenre($codeGenre){
        $this->getBdd();

        $req = $this->reqBase." WHERE codeGenre = ? 
        AND vvd.listeIdCouleurDispo IS NOT NULL
        AND vvd.listeTailleDispo IS NOT NULL
        ORDER BY v.id DESC";
        $this->getBdd();
        return $this->getModele($req, [$codeGenre], "Vetement");
    }

}

?>