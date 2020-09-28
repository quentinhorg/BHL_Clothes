<?php

class CommandeManager extends DataBase{

    public function getCommande($numCmdBDD){
        $req = "SELECT * FROM commande WHERE num = ?";
        $this->getBdd();
        $commande =  $this->getModele($req, [$numCmdBDD], "Commande");

        return $commande;
    }

    public function insertCommandeSessionToClient($idClient){

        var_dump($_SESSION["ma_commande"]->panier()) ;
        $this->getBdd(); //Autoriser l'access a la BDD
        $newID = $this->getNewIdTable('commande','num');
        $reqClient = "INSERT INTO commande VALUES(?,?, NOW())" ;
        
        $this->getBdd();
        $this->execBdd($reqClient, [$newID, $idClient]);


        // foreach ($_SESSION["ma_commande"]->panier() as $article) {
        //     $reqArticle = "INSERT INTO article_panier VALUES(?,?,?,?)" ;    
        // }
       
        //return $commande;
    }

    // A COMPLETER
    public function getCmdActive(){
        $ClientManager = new ClientManager() ;
 
        if( $ClientManager->ClientEnLigne() != null ){
            // $clientId = $ClientManager->ClientEnLigne()->id();
            // $req = "SELECT derniere COMMANDE UTILISATEUR" ;
            // $CmdId = "ID DE LA CMD ACTIVE";
            //$cmd = $this->getCommande($CmdId);

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
    public function renitialiseSession(){
        $_SESSION["ma_commande"] = null;
    }




}

?>