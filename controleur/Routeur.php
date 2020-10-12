<?php 
header("Cache-Control: no-cache, must-revalidate");
require_once('vue/Vue.php');

class Routeur{
   
   private $ctrl;
   private $vue;

   public function routerLaPage(){
      try{
         //Permet d'auto générer les modèles necessaires pour données appelées
         spl_autoload_register(function($classe){
            require_once('Modele/'.$classe.'.php');
         });
         
         session_start(); //Démarrage de la session
         $ClientManager = new ClientManager;
         $GLOBALS["client_en_ligne"] = $ClientManager->ClientEnLigne() ;

       

         //Si le panier session n'a pas encore été défni et personne n'est connecté
         if( !isset($_SESSION["ma_commande"]) && $GLOBALS["client_en_ligne"] == null ){
            $CommandeManager = new CommandeManager;
            $CommandeManager->creerCommandeSession();
         }    

       
         

      
         
         
         //Vérifie sur on navigue sur une page
         if( isset($_GET['url']) ){
          
            $url = explode('/', filter_var( $_GET['url'], FILTER_SANITIZE_URL) ); //Récupération de chaque partie de l'url (ex: www.site.fr/admin/commande/ => [admin,commande])
            
            $controleur = ucfirst(strtolower($url[0])); //Tranform la première du ctrl en MAJ (pour convention fichier)
            $controleurClasse = "Controleur".$controleur; // Récupération du nom du  de la classe Controleur
            $controleurFichier = "Controleur/".$controleurClasse.".php"; // Récupération du path nom Controleur
            
           //Si le fichier controleur existe
            if( file_exists($controleurFichier) ){
             
               require_once($controleurFichier); //Intègre le controleur
               $this->ctrl = new $controleurClasse($url); //Affection du la classe controleur
               

               
            }
            else{
               throw new Exception ('Page introuvable');  
            }
         }
         
         else{
            $url = [''];
            require_once('controleur/ControleurAccueil.php');
            $this->ctrl = new ControleurAccueil($url);
         }
     
      
      }
      //GESTION DES ERREURS
      catch(Exception $e){
      
         $erreurMsg = $e->getMessage();
         $this->vue = new Vue('Erreur');
         $this->vue->setHeader("vue/header.php") ;
         $this->vue->genererVue(array('erreurMsg' => $erreurMsg));
      }
      
   }
}

?>