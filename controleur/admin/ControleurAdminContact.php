<?php 



class ControleurAdminContact{
   private $vue;
   public $message;
   private $ContactManager;
   
   // CONSTRUCTEUR 
   public function __construct($url){

      
      if( isset($url) && count($url) > 3 ){
      
         throw new Exception(null, 404); //Erreur 404
      }
      else{

         /*---------MANAGER---------*/
         $this->ContactManager= new ContactManager();
         /*------------------*/

      
         /*---------FORMULAIRE---------*/
         if( isset($_POST["supMessage"]) ){ //Si formulaire supprimer
            $this->ContactManager->supprimer($_POST["supMessage"]);
         }
         /*------------------*/
     
         /*---------VUE---------*/
         if(isset($url[2])) {
            $idContact=$url[2];
            $nomVue= "AdminContactMessage";
            $donnee= array(
               "contactInfo" => $this->ContactManager->getContact($idContact) //Obtenir la liste des contacts
            );
         }
         else{
            $nomVue= "AdminContact";
            $donnee= array(
               "contactList" => $this->ContactManager->getListeContact() //Obtenir la liste des contacts
            );
         }

         $this->vue = new VueAdmin($nomVue) ;
         $this->vue->Popup->setMessage($this->message);
         $this->vue->genererVue($donnee) ;
         /*------------------*/
      }
   }


   

}

?>