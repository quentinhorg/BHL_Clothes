<?php

class ClientManager extends DataBase{


    public function getClient($id){
        $req = "SELECT * FROM client WHERE id = ?";
        $this->getBdd();
        return @$this->getModele($req, [$id], "Client")[0];
    }

    public function ClientEnLigne(){
        if( isset($_SESSION["id_client_en_ligne"]) ){
            $Client = $this->getClient($_SESSION["id_client_en_ligne"]) ;
        }else{ $Client = null ;}
       

        return $Client;
    }
    
    public function insertBDD(){
        $this->getBdd(); //Autoriser l'access a la BDD
        $newID = $this->getNewIdTable('client','id');

        $req = "INSERT INTO client VALUES (?, ?, ?, ?, ?, ?, ?)";

        $this->getBdd();
        $this->execBDD($req,[$newID,$_POST['email'], $_POST['mdp'], $_POST['nom'], $_POST['prenom'], $_POST['adresse'],$_POST['tel']]);
    }

    public function connexion(){
        #$this->getBdd();

        $mail= $_POST['email'];
        $mdp = $_POST['mdp'];

        $this->getBdd();
        $verif_user= "Select id from client WHERE email like ? AND mdp like ?";
        $resultat = $this->execBDD($verif_user,[$mail,$mdp] );
        // var_dump($resultat);

        if (count($resultat)==1){
            echo " connectééé";
            $_SESSION['id_client_en_ligne'] = $resultat[0]['id'];
            // var_dump($_SESSION['id_client_en_ligne']);
        }else{
            echo "pas co";
        }

    }





}

?>