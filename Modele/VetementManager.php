<?php

class VetementManager extends DataBase{
    private $reqBase = "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis
    FROM vetement v
    LEFT JOIN vue_vet_disponibilite vvd ON vvd.idVet = v.id
    LEFT JOIN vet_taille vt ON vt.idVet = v.id 
    LEFT JOIN vet_couleur vc ON vc.idVet= v.id 
    LEFT JOIN categorie c ON c.id= v.idCateg
    LEFT JOIN genre g ON g.code = v.codeGenre
    LEFT JOIN taille t ON t.libelle = vt.taille";

    public $Pagination = null;

    

    public function setPagination($nbArtPage){
        if( !isset($_GET["page"]) ){ $_GET["page"] = null ; }
        $this->Pagination = new Pagination($_GET["page"], $nbArtPage) ;
    }

    
   public function tabAssocVet($idVet){
        $reqVet = $this->reqBase." WHERE v.id = ?";
        $this->getBdd();
        $dataAricle = $this->execBDD($reqVet, [$idVet]);
        return $dataAricle ;
    }


    //Obtient les 3 dernières nouveautés
    public function getNouveaute(){
        $req= $this->reqBase." ORDER BY v.id DESC LIMIT 3";
        $this->getBdd();
        return $this->getModele($req, ["*"], "Vetement");
    }

    //Obtient les infos d'un de vêtements
    public function getVetement($id){
        $req = $this->reqBase." WHERE v.id = ?" ;
        $this->getBdd();
        return $this->getModele($req, [$id], "Vetement")[0];
    }

    public function getListeVetement(){
     
        $req = $this->reqBase." ORDER BY v.id DESC";
        $this->getBdd();
        $resultat = $this->getModele($req, ["*"], "Vetement") ;

        return $resultat;
    }

    //Obtient toute la liste de vêtements
    public function getListeVetementDispo(){
        $resultat = null;
        if( $this->Pagination != null){
            $req = $this->reqBase." WHERE vvd.listeNumCouleurDispo IS NOT NULL
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
            $reqRecherche = $this->reqBase." WHERE vvd.listeNumCouleurDispo IS NOT NULL
            AND vvd.listeTailleDispo IS NOT NULL ".$Recherche->getReqFinal() ;

         
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
        AND vvd.listeNumCouleurDispo IS NOT NULL
        AND vvd.listeTailleDispo IS NOT NULL
        ORDER BY v.id DESC";
        
        return $this->getModele($req, [$idCateg, $codeGenre], "Vetement");
    }

    public function getListeVetByGenre($codeGenre){
        $this->getBdd();

        $req = $this->reqBase." WHERE codeGenre = ? 
        AND vvd.listeNumCouleurDispo IS NOT NULL
        AND vvd.listeTailleDispo IS NOT NULL
        ORDER BY v.id DESC";
        $this->getBdd();
        return $this->getModele($req, [$codeGenre], "Vetement");
    }

    public function verifDisponibiliteTailleCouleur($idVet, $numClr, $taille){
        $dispo = false;
  
        $req = "SELECT COUNT(*) AS 'nbRow'
        FROM vetement v
        INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet = v.id
        INNER JOIN vet_taille vt ON vt.idVet = v.id 
        INNER JOIN vet_couleur vc ON vc.idVet= v.id 
        INNER JOIN categorie c ON c.id= v.idCateg
        INNER JOIN genre g ON g.code = v.codeGenre
        INNER JOIN taille t ON t.libelle = vt.taille
        WHERE vt.taille = ?
        AND vc.num = ?
        AND v.id = ? ";
        $this->getBdd();
        $nbRow = intval($this->execBDD($req, [$taille, $numClr, $idVet])[0]["nbRow"]) ;
  
        if($nbRow >= 1){
           $dispo = true;
        }
  
        return $dispo;
        
    }

    public function verifDisponibilite($id){

        //verif avec une req si le vet a au moins une taille et une couleur par rapport au paramettre passé
        $req="SELECT COUNT(*) AS 'nbRow'
        FROM vue_vet_disponibilite
        WHERE listeNumCouleurDispo is not null 
        AND listeTailleDispo is not null
        AND idVet=?";
        $this->getBdd();
        
        return $this->execBDD($req, [$id])[0]["nbRow"];

    }

    //Modifier un vêtement
    public function updateBDD($id, $nom, $prix ,$motifPosition ,$codeGenre ,$description ,$idCateg){
        if( empty($motifPosition) ){$motifPosition = null;}
        $req="UPDATE vetement SET nom=?, prix=?, motifPosition=?, codeGenre=?, description=?, idCateg = ? WHERE id= ?";
        $this->getBdd();
        $this->execBDD($req, [$nom, $prix ,$motifPosition ,$codeGenre ,$description ,$idCateg, $id]);
      

    }

}

?>