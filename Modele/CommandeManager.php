<?php

class CommandeManager extends DataBase{

    public function getCommande($numCmdBDD){
        $req = "SELECT *, calcCmdTTC(commande.num) AS 'prixTTC' FROM commande WHERE num = ?";
        $this->getBdd();
        $commande =  @$this->getModele($req, [$numCmdBDD], "Commande")[0];

        return $commande;
    }

    public function insertCommande($idClient){
       
        $this->getBdd(); 
        $newID = $this->getNewIdTable('commande','num');
        $reqClient = "INSERT INTO commande VALUES(?,?, NOW())" ;
        $this->getBdd();
        $this->execBdd($reqClient, [$newID, $idClient]);

        return $newID;
    }


    // A COMPLETER
    public function getCmdActiveClient(){
  
        if( $GLOBALS["client_en_ligne"] != null  ){
            $clientId = $GLOBALS["client_en_ligne"]->getId();
 
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
            $_SESSION["ma_commande"] = new Commande([null]);
        }
    }

    //Permet également d'effacer la commande (panier) de la session
    public function effacerCmdSession(){
        $_SESSION["ma_commande"] = null;
        unset($_SESSION["ma_commande"]);
    }

    public function payerCommande($iClient, $numCmd){
        $req = "CALL payerCommande(?, ?);";
        $this->getBdd();
        $this->execBdd($req, [$iClient, $numCmd]);
    }




}

?>