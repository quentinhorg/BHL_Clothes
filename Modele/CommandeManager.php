<?php

class CommandeManager extends DataBase{

    public function getCommande($numCmdBDD){

        if(  $numCmdBDD != null ){
            $req = "SELECT * FROM commande WHERE num = ?";
            $this->getBdd();
            $commande =  $this->getModele($req, [$numCmdBDD], "Commande");
        }
        else{
            $commande = $_SESSION["ma_commande"] ;
        }

        return $commande;
    }

    // A COMPLETER
    public function getCmdActive(){
        $ClientManager = new ClientManager() ;
 
        if( $ClientManager->ClientEnLigne() != null ){
            // $clientId = $ClientManager->ClientEnLigne()->id();
            // $req = "SELECT derniere COMMANDE UTILISATEUR" ;
            // $CmdId = "ID DE LA CMD ACTIVE";
            $CmdId = null;
         }else{ $CmdId = null; }

        
        
        return $this->getCommande($CmdId);
    }

    


  
    public function creerCommandeSession(){
        if( !isset($_SESSION["ma_commande"]) ){
            $_SESSION["ma_commande"] = new Commande([null]);
        }
    }

    //Permet également d'effacer la commande (panier) de la session
    public function renitialiseSession(){
        $_SESSION["ma_commande"] = null;
    }




}

?>