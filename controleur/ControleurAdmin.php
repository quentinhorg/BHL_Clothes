<?php 





class ControleurAdmin{
   private $vue;
   public $message;

   // CONSTRUCTEUR 
   public function __construct($url){
      
      if( !isset($url) ){
         throw new Exception(null, 404);
      }
      else{
 
         if( !isset($url[1])  ) {  $urlAdmin = "Accueil" ; } else {$urlAdmin = $url[1] ;} //Si pas défini mettre à l'accueil de l'admin

     
         $ctrlName = $this->getCtrlAdmin($urlAdmin);  //Obtention du nom du controleur admin
        
         //Inclusion du controleur admin concerné
         include "controleur/admin/$ctrlName.php";
         new $ctrlName($url);

      }

     

   }

   //Obtention du controleur adin du dossier "admin"
   private function getCtrlAdmin($urlAdmin){
      $ctrlName = "ControleurAdmin".ucfirst($urlAdmin);
      if(!file_exists("controleur/admin/".$ctrlName.".php")){ throw new Exception(null, 404); }
      return $ctrlName;
   }




}

?>