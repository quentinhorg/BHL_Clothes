<?php 

class Recherche{
    private $intervalePrix = array();
    private $listeTaille = array();
    private $listeCouleur = array();
    private $categorie;
    private $genre;
    private $listeMotCle;

    public function __construct($prixIntervale, $listeTaille, $listeCouleur, $categorie, $genre, $listeMotCle){
        $this->intervalePrix = $prixIntervale ;
        $this->listeTaille = $listeTaille ;
        $this->listeCouleur = $listeCouleur ;
        $this->categorie = $categorie ;
        $this->genre = $genre;
        $this->listeMotCle = explode(" ", $listeMotCle);
      

       
    }

    public function reqTaille(){
        if($this->listeTaille != null){
            $req = " AND t.libelle IN('".implode("','", $this->listeTaille )."')";
        }else{ $req = null ;}

        return $req;
    }

    public function reqCouleur(){
        if($this->listeCouleur != null){
            $req = "AND (vc.nom LIKE '%". implode("%' OR vc.nom LIKE '%", $this->listeCouleur )."%')";
        }else{ $req = null ;}

        return $req;
    }
    public function reqCateg(){
        if($this->categorie != null){
            $req = "AND v.idCateg = ".$this->categorie;
        }else{ $req = null ;}

        return $req;
    }
    
    public function reqGenre(){
        if($this->genre != null){
            $req = "AND g.code LIKE  '".$this->genre."'";
        }else{ $req = null ;}

        return $req;
    }

    public function reqPrix(){
        if($this->intervalePrix != null){
            $req = "AND v.prix BETWEEN ".$this->intervalePrix[0]." AND ".$this->intervalePrix[1]."";
        }else{ $req = null;}
        
        return $req;
    }

    public function reqMotCle(){
 
        $concatChamps = "
            CONCAT(' ', v.Nom,' ', v.description, ' ', c.nom, ' ', g.libelle, ' ',
                ( 
                    SELECT GROUP_CONCAT(vc2.nom) 
                    FROM vetement v2 
                    INNER JOIN vet_couleur vc2 ON vc2.idVet= v2.id 
                    WHERE v2.id = v.id
                ) 
            )" ;

        $sqlMotCle = implode("%' OR $concatChamps LIKE '%", $this->listeMotCle ) ;
 
        if($this->listeMotCle != null){
            $req = "
                AND ($concatChamps LIKE '%$sqlMotCle%')
                GROUP BY v.id
            ";
        }else{ $req = null; }
        
        return $req;
    }

    public function getReqFinal(){

        $reqFinal = "SELECT DISTINCT(v.id), v.*
            FROM vetement v 
            INNER JOIN vet_taille vt ON vt.idVet = v.id 
            INNER JOIN vet_couleur vc ON vc.idVet= v.id 
            INNER JOIN categorie c ON c.id= v.idCateg
            INNER JOIN genre g ON g.code = v.codeGenre
            INNER JOIN vue_vet_disponibilite vvd ON vvd.idVet= v.id 
            LEFT JOIN taille t ON t.libelle = vt.taille
            WHERE vvd.listeIdCouleurDispo IS NOT NULL
            AND vvd.listeTailleDispo IS NOT NULL".
            " ".$this->reqCouleur().
            " " .$this->reqTaille().
            " ".$this->reqPrix().
            " ".$this->reqCateg().
            " ".$this->reqGenre().
            " ".$this->reqMotCle();

       
        return $reqFinal;
    }
    
    

    

}








?>
