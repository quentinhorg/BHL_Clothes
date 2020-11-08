<?php

class VetementManager extends DataBase{
    private $liaison = "FROM vetement v
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
        $reqVet = "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis ".
        $this->liaison.
        " WHERE v.id = ?";
        $this->getBdd();
        $dataAricle = $this->execBDD($reqVet, [$idVet]);
        return $dataAricle ;
    }


    //Obtient les 3 dernières nouveautés
    public function getNouveaute(){
        $req= "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis ".$this->liaison." ORDER BY v.id DESC LIMIT 3";
        $this->getBdd();
        return $this->getModele("Vetement", $req);
    }

    //Obtient les infos d'un des vêtements
    public function getVetement($id){
        $req = "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis ".$this->liaison." WHERE v.id = ?" ;
        $this->getBdd();
        return $this->getModele("Vetement", $req, [$id])[0];
    }

    //Obtient la liste des vêtements dispo ou non
    public function getListeVetement($dispo = false){
        $condition = null;
        if($dispo){ $condition = "WHERE vvd.listeNumCouleurDispo IS NOT NULL AND vvd.listeTailleDispo IS NOT NULL" ;}

        $req = "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis ".$this->liaison." ".$condition." ORDER BY v.id DESC";

        $this->getBdd();
        if( $this->Pagination == null){
            $resultat = $this->getModele("Vetement", $req) ;
        }
        else{
            $req = $this->Pagination->getReqPagination($req);
            $resultat = $this->Pagination->getModele("Vetement", $req) ;
        }
        return $resultat;
    }

    //Obtient toute la liste de vêtements dispo

    public function getRechercheVetement($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre, $motCle){
       
        $resultat = null;
        if( $this->Pagination != null){
            $Recherche = new Recherche($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre, $motCle) ;
            $reqRecherche = "SELECT DISTINCT(v.id), v.* , vvd.*, (SELECT COUNT(id) FROM avis WHERE idVet=v.id) AS nbAvis ".$this->liaison." WHERE vvd.listeNumCouleurDispo IS NOT NULL
            AND vvd.listeTailleDispo IS NOT NULL ".$Recherche->getReqFinal();
            $this->Pagination->getBdd();
            $newReq = $this->Pagination->getReqPagination($reqRecherche);
            $resultat = $this->Pagination->getModele("Vetement", $newReq);
        }
       
        return $resultat;
        
     
    }


    //Retourne vrai si la taille et la couleur du vetement sont dispo
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
        AND vc.dispo=1
        AND vc.num = ?
        AND v.id = ?";
        $this->getBdd();
        $nbRow = intval($this->execBDD($req, [$taille, $numClr, $idVet])[0]["nbRow"]) ;
  
        if($nbRow >= 1){
           $dispo = true;
        }
  
        return $dispo;
        
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