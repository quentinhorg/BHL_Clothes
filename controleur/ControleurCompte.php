<?php
 
class ControleurCompte{
    private $vue;
    public function  __construct($url){
      
        if( isset($url) && count($url) > 3 ){
            throw new Exception('Page introuvable');
        }
        else{

         
            if( isset($url[1]) && $url[1] == "suivi"){
                
                $this->vue = new Vue('Suivi') ;
                $this->vue->genererVue(array(
                    "clientActif"=> $GLOBALS["client_en_ligne"],
                    "listeEtat" => $this->listeEtat(),
                    "infoCommande" => $this->commande($url[2])
                    
                )) ;
            }
            else{

                $message = null;
                if(isset($_POST['submitMail'])){
                    if (!empty($_POST['changeMail']) && !empty($_POST['changeMail2']) ){
                        if($_POST['changeMail'] == $_POST['changeMail2']){
                            $this->changeMail();
                        }else { $message = "Les adresses Email ne sont pas identiques";}
                    }else{ $message = "Il manque au moins une information";}
                }
    
                if(isset($_POST['submitMdp'])){
                    $mdpBdd = $GLOBALS["client_en_ligne"]->mdp();
                        if (!empty($_POST['changeMdp']) && !empty($_POST['changeMdp2']) && !empty($_POST['ancienMdp']) ){
                            if($_POST['changeMdp'] == $_POST['changeMdp2']){
                                if($_POST['ancienMdp'] == $mdpBdd  ){
                                    $this->changeMdp();
                                }else{$message = "L'ancien mot de passe ne correspond pas";}
                            }else{ $message = "Les nouveaux mots de passes ne sont pas identiques";}
                        }else{ $message = "Il manque au moins une information";}
                }
    
                if(isset($_POST['submitAdresse'])){
                    if (!empty($_POST['changeAdresse']) && !empty($_POST['changeAdresse2'])){
                            if($_POST['changeAdresse'] == $_POST['changeAdresse2']){
                                $this->changeAdresse();
                            }else{ $message = "Les adresses ne sont pas identiques";}
                    }else{ $message = "Il manque au moins une information";}
                }
    
                if (isset($_GET['deco'])) {
                       $this->deconnexion();
                       header("Location: ".URL_SITE);
                }
    
                
                $this->vue = new Vue('Compte') ;
                $this->vue->setListeJsScript(["public\script\DataTable\datatable.js"]);
                $this->vue->setListeCss(["public/css/compte_dataTables.css", "public/css/compte_responsive"]);
                $this->vue->genererVue(array(
                    "clientActif"=> $GLOBALS["client_en_ligne"],
                    "message"=>$message
                )) ;
            }
           

        }
    }
        
        
    private function listeEtat(){
        $EtatManager = new EtatManager();
        $listeEtat = $EtatManager->getListeEtatSuivi();
       
        return $listeEtat ;
     }

    private function commande($num){
        $CommandeManager = new CommandeManager();
        $commande = $CommandeManager->getCommande($num);
       
        return $commande ;
     }


    public function changeMail(){
        $ClientManager = new ClientManager();
        $idCli = $GLOBALS["client_en_ligne"]->getId();
        $ClientManager->changeMail($idCli);
    }

    public function changeMdp(){
        $ClientManager = new ClientManager();
        $idCli = $GLOBALS["client_en_ligne"]->getId();
        $ClientManager->changeMdp($idCli);
    }

    public function changeAdresse(){
        $ClientManager = new ClientManager();
        $idCli = $GLOBALS["client_en_ligne"]->getId();
        $ClientManager->changeAdresse($idCli);
    }

    private function deconnexion(){
        $ClientManager = new ClientManager();
        $ClientManager->deconnexion();
    }


}

?>