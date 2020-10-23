<?php
 
class ControleurCompte{
    private $vue;
    public function  __construct($url){
      
        if( isset($url) && count($url) > 3 ){
            throw new Exception(null, 404);
        }
        else{

            if($GLOBALS["client_en_ligne"] != null ){

                if( isset($url[1]) && $url[1] == "suivi"){
                    
                    if( isset($url[2]) ){
                        if($this->commande($url[2]) != null){
                            if( $this->commande($url[2])->idClient() == $GLOBALS["client_en_ligne"]->id() ){
                                $this->vue = new Vue('Suivi') ;
                                $this->vue->genererVue(array(
                                    "clientActif"=> $GLOBALS["client_en_ligne"],
                                    "listeEtat" => $this->listeEtat(),
                                    "infoCommande" => $this->commande($url[2])
                                )) ;
                            }else{ throw new Exception('La commande ne vous appartient pas', 423); }
                        } else{ throw new Exception('La commande n\'existe pas', 404); }
                    } else{ throw new Exception(null, 404); }
                   
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
                        if (!empty($_POST['changeRue']) && !empty($_POST['changeRue2'])){
                                if($_POST['changeRue'] == $_POST['changeRue2']){
                                    $this->changeAdresse();
                                }else{ $message = "Les adresses ne sont pas identiques";}
                        }else{ $message = "Il manque au moins une information";}
                    }
    
                    //Actualisation des  nouvelles données du client
                    $GLOBALS["client_en_ligne"] = $this->getNewInfoClientActif() ;
                    
                    $this->vue = new Vue('Compte') ;
                    $this->vue->setListeJsScript(["public\script\DataTable\datatable.js"]);
                    $this->vue->setListeCss(["public/css/compte_dataTables.css", "public/css/compte_responsive"]);
                    $this->vue->genererVue(array(
                        "clientActif"=> $GLOBALS["client_en_ligne"],
                        "message"=>$message,
                        "listeCP"=>$this->getListCp()
                    )) ;
                }
           
                

            }
            else{
                throw new Exception("Vous devez être connecté pour accèder à cette page", 401);
            }
       

        }
    }

        
    private function getNewInfoClientActif(){
        $ClientManager = new ClientManager();
        return  $ClientManager->getClient( $GLOBALS["client_en_ligne"]->id() );
    }

    
    private function getListCp(){
        $CodePostalManager = new CodePostalManager();
        $listCP = $CodePostalManager->getListCp();

        return $listCP;
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
        $idCli = $GLOBALS["client_en_ligne"]->id();
        $ClientManager->changeMail($idCli);
    }

    public function changeMdp(){
        $ClientManager = new ClientManager();
        $idCli = $GLOBALS["client_en_ligne"]->id();
        $ClientManager->changeMdp($idCli);
    }

    public function changeAdresse(){
        $ClientManager = new ClientManager();
        $idCli = $GLOBALS["client_en_ligne"]->id();
        $ClientManager->changeAdresse($idCli);
    }




}

?>