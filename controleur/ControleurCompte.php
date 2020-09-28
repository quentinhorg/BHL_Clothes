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
                        }else{ $message = "Les mots de passes ne sont pas identiques";}
                    }else { $message = "Les mots de passes ne sont pas identiques";}
                }else{ $message = "Les mots de passes ne sont pas identiques";}
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

    private function deconnexion(){
        $ClientManager = new ClientManager();
        $ClientManager->deconnexion();
     }


}

?>i