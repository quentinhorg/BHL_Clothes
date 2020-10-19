<?php


class ClientManager extends DataBase{


    public function getClient($id){
      
        $req = "SELECT c.*,GROUP_CONCAT(co.num) as 'listeIdCmd'
                FROM client c 
                LEFT JOIN commande co ON c.id = co.idClient 
                WHERE c.id = ?
                GROUP BY c.id";
                
        $this->getBdd();
       
        
        return @$this->getModele($req, [$id], "Client")[0];
    }

    public function ClientEnLigne(){
        if(  isset($_SESSION["id_client_en_ligne"]) ){
            return  $this->getClient($_SESSION["id_client_en_ligne"]);
        }else{ return null ;}
    
    }
    
    public function insertBDD($email, $mdp, $nom, $prenom, $cp,$rue, $tel, $cleActivation){
        $this->getBdd(); //Autoriser l'access a la BDD
        $req = "INSERT INTO client (email, mdp, nom, prenom ,codePostal, rue, tel, cleActivation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"; 

        $this->execBDD($req,[$email, sha1($mdp), $nom, $prenom, $cp,$rue, $tel, $cleActivation]);

    }

    public function getId($mail, $mdp){
  
        $this->getBdd();
        $verif_user= "SELECT id FROM client WHERE email LIKE ? AND mdp LIKE ?";
        return @$this->execBDD($verif_user,[$mail,sha1($mdp)])[0]["id"];
      
    }

    public function tryActiveCompte($mail, $cle){

        $this->getBdd();
        $req = "CALL activeCompte(?, ?)";
        $this->execBDD($req,[$mail,$cle]) ;
        
    }

    public function changeMail($id){
        $this->getBdd();
        $changeMail = "UPDATE client SET email = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeMail,[$_POST['changeMail'],$id]);
    }

    public function changeMdp($id){
        $this->getBdd();
        $changeMdp = "UPDATE client SET mdp = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeMdp,[sha1($_POST['changeMdp']),$id]);
    }
    
    public function deconnexion(){
        $_SESSION["id_client_en_ligne"] = null;
        unset($_SESSION["id_client_en_ligne"]);
        session_destroy();
    }

    public function changeAdresse($id){
        $this->getBdd();
        $changeAdresse = "UPDATE client SET rue = ?, codePostal = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeAdresse,[$_POST['changeRue'], $_POST['changeCP'],$id]);
    }


}

?>