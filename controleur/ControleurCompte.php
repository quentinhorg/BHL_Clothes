<?php
class ControleurCompte{
    private $vue;

    public function  __construct($url){
        if( isset($url) && count($url) > 1 ){
            throw new Exception('Page introuvable');
        }
        else{

            $this->vue = new Vue('Compte') ;
            $this->vue->genererVue(array(
                "clientActif"=> $this->client()
            )) ;
        }
    }

    public function client(){
        $ClientManageur = new ClientManager();
        $clientCmd= $ClientManageur->ClientEnLigne();

        return $clientCmd;
    }

}

?>