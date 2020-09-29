<?php
class ControleurCompte{
    private $vue;
    public function  __construct($url){
        if( isset($url) && count($url) > 1 ){
            throw new Exception('Page introuvable');
        }
        else{
            $message =null;
            if(isset($_POST['submitMail'])){
                if (!empty($_POST['changeMail'])){
                    if(!empty($_POST['changeMail2'])){
                        if($_POST['changeMail'] == $_POST['changeMail2']){
                            $this->changeMail();
                        }else{ $message = "Les informations saisies ne sont pas identiques";}
                    }else { $message = "Une information requise n'à pas été saisie";}
                }else{ $message = "Une information requise n'à pas été saisie";}
            }

            if(isset($_POST['submitMdp'])){
                $mdpBdd = $this->client()->mdp();
                    if (!empty($_POST['changeMdp']) || !empty($_POST['changeMdp2']) ){
                        if($_POST['changeMdp'] == $_POST['changeMdp2']){
                            if($_POST['ancienMdp'] == $mdpBdd  ){
                                $this->changeMdp();
                            }else{$message = "L'ancien mot de passe ne correspond pas";}
                        }else{ $message = "Les nouveaux mots de passes ne sont pas identiques";}
                    }else{ $message = "Veuillez remplir tous les champs";}
            }

            if(isset($_POST['submitAdresse'])){
                if (!empty($_POST['changeAdresse'])){
                    if(!empty($_POST['changeAdresse2'])){
                        if($_POST['changeAdresse'] == $_POST['changeAdresse2']){
                            $this->changeAdresse();
                        }else{ $message = "Les adresses ne sont pas identiques";}
                    }else { $message = "Les mots de passes ne sont pas identiques";}
                }else{ $message = "Les mots de passes ne sont pas identiques";}
            }

            if (isset($_GET['deco'])) {
                   $this->deconnexion();
                   header("Location: ".ACCUEIL);
            }

            
            $this->vue = new Vue('Compte') ;
            $this->vue->genererVue(array(
                "clientActif"=> $this->client(),
                "message"=>$message
            )) ;
            }
        }   
    


    public function client(){
        $ClientManageur = new ClientManager();
        $clientCmd= $ClientManageur->ClientEnLigne();
        return $clientCmd;
    }

    public function changeMail(){
        $ClientManager = new ClientManager();
        $idCli = $this->client()->getId();
        $ClientManager->changeMail($idCli);
    }

    public function changeMdp(){
        $ClientManager = new ClientManager();
        $idCli = $this->client()->getId();
        $ClientManager->changeMdp($idCli);
    }

    public function changeAdresse(){
        $ClientManager = new ClientManager();
        $idCli = $this->client()->getId();
        $ClientManager->changeAdresse($idCli);
    }

    private function deconnexion(){
        $ClientManager = new ClientManager();
        $ClientManager->deconnexion();
    }


}

?>i