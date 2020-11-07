<?php


class ClientManager extends DataBase{

    public function getClient($id){
        $req = "SELECT c.*
                FROM client c 
                LEFT JOIN commande co ON c.id = co.idClient 
                WHERE c.id = ?
                GROUP BY c.id";
                
        $this->getBdd();
       
        
        return @$this->getModele( "Client", $req,[$id])[0];
    }



    public function getCleClient($email){

        $this->getBdd(); //Autoriser l'access a la BDD
        $req = "SELECT cleActivation AS 'cle' FROM client WHERE email LIKE ?"; 
        return @$this->execBDD($req,[$email])[0]['cle'];
    
    }

    public function emailExiste($email){
        $existe = false ;

        $this->getBdd(); //Autoriser l'access a la BDD
        $req = "SELECT COUNT(*) AS 'nbEmail' FROM client WHERE email LIKE ?"; 

        if( $this->execBDD($req,[$email])[0]['nbEmail'] == 1 ){
            $existe = true ;
        }
        
        return $existe ;
    }

    public function desactiveCompte($email, $cle){
     
        $this->getBdd(); //Autoriser l'access a la BDD
        $req = "CALL desactiveCompte(?,?)"; 
        $this->execBDD($req,[$email, $cle]);
    }
    
    public function insertBDD($email, $mdp, $nom, $prenom, $cp,$rue, $tel, $cleActivation){
        $this->getBdd(); //Autoriser l'access a la BDD
        $req = "INSERT INTO client (email, mdp, nom, prenom ,codePostal, rue, tel, cleActivation, dateInscription) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"; 

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
    
    public function changeAdresse($id){
        $this->getBdd();
        $changeAdresse = "UPDATE client SET rue = ?, codePostal = ? WHERE id = ? ;" ;
        $resultat = $this->execBDD($changeAdresse,[$_POST['changeRue'], $_POST['changeCP'],$id]);
    }


}

?>