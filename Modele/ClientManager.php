<?php

class ClientManager extends DataBase{


    public function getClient($id){
        $req = "SELECT c.*,GROUP_CONCAT(co.num) as 'listeIdCmd'
                FROM client c 
                INNER JOIN commande co ON c.id = co.idClient 
                WHERE c.id = ?
                GROUP BY c.id";
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

        return $newID ;
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
            echo "Vous êtes actuellement connecté";
            $_SESSION['id_client_en_ligne'] = $resultat[0]['id'];
            // var_dump($_SESSION['id_client_en_ligne']);
        }else{
            echo "Vous n'êtes actuellement pas connecté";
        }

    }

    public function changeMail($id){
        $this->getBdd();
        $changeMail = "UPDATE client SET email = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeMail,[$_POST['changeMail'],$id]);
    }

    public function changeMdp($id){
        $this->getBdd();
        $changeMdp = "UPDATE client SET mdp = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeMdp,[$_POST['changeMdp'],$id]);
    }
    
    public function deconnexion(){
        $_SESSION=null;
        session_destroy();
    }


}

?>