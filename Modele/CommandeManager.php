<?php

class CommandeManager extends DataBase{

    public $reqBase = "SELECT DISTINCT c.*, calcCmdTTC(c.num) AS 'prixTTC',  
    calcCmdHT(c.num) AS 'prixHT',
    (SELECT SUM(ap2.qte) FROM article_panier ap2 WHERE ap2.numCmd = c.num ) as 'totalArticle' 
    FROM commande c
    LEFT JOIN client clt ON clt.id=c.idClient
    LEFT JOIN code_postal cp ON cp.cp=clt.codePostal
    LEFT JOIN article_panier ap ON ap.numCmd=c.num" ;

    public function getListCommande(){
        $req = $this->reqBase;
        $this->getBdd();
        $commande =  $this->getModele($req, ["*"], "Commande");

        return $commande;
    }

    public function getListCommandeForClient($idClient){
        $req = $this->reqBase." WHERE c.idClient= ?";
        $this->getBdd();
        $commande =  $this->getModele($req, [$idClient], "Commande");
        return $commande;
    }

    public function getCommande($numCmdBDD){
        $req = $this->reqBase." WHERE c.num = ? GROUP BY c.num";
        $this->getBdd();
        $commande =  @$this->getModele($req, [$numCmdBDD], "Commande")[0];

        return $commande;
    }

    public function insertCommande($idClient){
       
        $this->getBdd(); 
        $newID = $this->getNewIdTable('commande','num');
        $reqClient = "INSERT INTO commande(num,idClient, dateCreation) VALUES(?,?, NOW())" ;
        $this->getBdd();
        $this->execBdd($reqClient, [$newID, $idClient]);

        return $newID;
    }

    public function modifierEtat($numCmd, $idEtat){
   
        $this->getBdd(); 
        $req = "UPDATE commande SET idEtat = ? WHERE num = ?" ;
        $this->execBdd($req, [$idEtat, $numCmd]);
    }


    // A COMPLETER
    public function getCmdActiveClient(){
    
       
        if( $GLOBALS["client_en_ligne"] != null  ){
        
            $clientId = $GLOBALS["client_en_ligne"]->id();
 
            $req = "SELECT c.num as 'numCmd'
            FROM commande c
            WHERE c.idClient = ?
            AND c.idEtat = 1
            " ;
            
            $this->getBdd();

            $resultat =  $this->execBdd($req, [$clientId]);
         
            if(  $resultat != null  ){
                $CmdId = $resultat[0]["numCmd"] ;
                $cmd = $this->getCommande( $CmdId );
            } else{ $cmd = new Commande(array(null)) ; }
          
        }else{ 
            $cmd = $_SESSION["ma_commande"] ;
        }

        
        
        return $cmd;
    }

    
    public function creerCommandeSession(){
        if( !isset($_SESSION["ma_commande"]) ){
            $_SESSION["ma_commande"] = new CommandeSession;
        }
    }

    //Permet également d'effacer la commande (panier) de la session
    public function effacerCmdSession(){
        $_SESSION["ma_commande"] = null;
        unset($_SESSION["ma_commande"]);
    }

    public function getPrixTotalPanierHT($panier){

        $prixTotal = (float) 0;
        foreach ($panier as $article) {
            $req = "SELECT prixTotalArt(?, ?) AS 'prixTotalArt';" ;
            $this->getBdd();
            
            $prixTotal += floatval( $this->execBdd($req, [$article->id(), $article->qte()])[0]["prixTotalArt"]);
            
        }
       
        return $prixTotal ;
    
        
    }

    public function payerPanierActif($idClient){
        
        $req = "SELECT c.num as 'numCmd'
        FROM commande c
        WHERE c.idClient = ?
        AND c.idEtat = 1
        " ;
        $this->getBdd();

        $resultat =  $this->execBdd($req, [$idClient]);

        $req = "CALL payerCommandeViaSolde(?, ?);";
        $this->getBdd();
        $this->execBdd($req, [$idClient, $resultat[0]["numCmd"] ]);
    }

    public function possedeCommandeNonPayer($idClient){
        $possede = false;

        $req = "SELECT COUNT(c.num) as 'nbCmdNonPayer'
        FROM commande c
        WHERE c.idClient = ?
        AND c.idEtat = 1
        " ;
        $this->getBdd();

        $resultat =  $this->execBdd($req, [$idClient]);

        if($resultat[0]["nbCmdNonPayer"] >= 1){
            $possede = true ;
        }

        return $possede;
    }

    public function viderPanier($numCmd){
        
        //Supprime également la commande car trigger
        $req = "DELETE FROM article_panier WHERE numCmd = ?" ;
        $this->getBdd();

        $this->execBdd($req, [$numCmd]);
    }



}

?>