<?php 
//Gestionnaire d'erreur personalisé 
set_error_handler('exceptions_error_handler');
function exceptions_error_handler($severity, $message, $filename, $lineno) {

   if (error_reporting() == 0) {
      return;
   }
   if (error_reporting() & $severity) {
      
      throw new ErrorException($message, 500, $severity, $filename, $lineno);
      
   }
 
}

header("Cache-Control: no-cache, must-revalidate");

require_once('vue/Vue.php');
require_once('vue/admin/VueAdmin.php');

class Routeur{
   
   private $url;
   private $ctrl;
   private $vue;

   public function __construct( $url = array("Accueil") ){
      $this->url = $url ;
   }

   private function setControleur($base = null){

         //Vérifie sur on navigue sur une page
         if( !empty($this->url) ){
          

            $controleur = ucfirst(strtolower($this->url[0])); //Tranform la première du ctrl en MAJ (pour convention fichier)
            if($base != null ){
               
               if( !isset($this->url[1]) ) {$this->url[1] = "Accueil" ;}
               $controleurClasse = "Controleur".$controleur.ucfirst(strtolower($this->url[1])); // Récupération du nom du  de la classe Controleur
            }
            else{
               $controleurClasse = "Controleur".$controleur; // Récupération du nom du  de la classe Controleur
            }

           
            
            $controleurFichier = "Controleur/$base/".$controleurClasse.".php"; // Récupération du path nom Controleur
            



           //Si le fichier controleur existe
            if( file_exists($controleurFichier) ){
             
               
               require_once($controleurFichier); //Intègre le controleur
               $this->ctrl = new $controleurClasse($this->url); //Affection du la classe controleur
               
             

               
            }
            else{
               throw new Exception (null,404);  
           
            }
         }
         
         else{
            $this->url = [''];
            require_once('controleur/ControleurAccueil.php');
            $this->ctrl = new ControleurAccueil($this->url);
         }

      
   }
   
   public function routerLaPage(){

      try{
         ob_start();
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
         
         
         if($this->url[0] == "admin" ){
            $this->setControleur("admin");
         }
         else{
            $this->setControleur();
         }
         
      
     
      
      }
      //GESTION DES ERREURS
      catch(Throwable $e){

       
            switch ( $e->getCode() ) {
               //SQL STATE 45000 (Exception custom)
               case 45000:
                  $erreurMsg = $e->xdebug_message;
                  $titreErreur = "<b> SQL STATE Exception : </b> <br>" ;
                  break;
   
               //Accès refusé (global)
               case 403:
                  $titreErreur = "Accès refusé" ;
                  $erreurMsg = $e->getMessage();
                  break;
   
               //Accès refusé, nécessite une connexion de la part du client
               case 401:
                  $titreErreur = "Vous devez être connecté" ;
                  $erreurMsg = $e->getMessage();
                  break; 
               
               //La syntaxe de la requête est erronée (Manque des infos)
               case 400:
                  $titreErreur = "Requête erronée";
                  if( $e->getMessage() == null ){
                     $erreurMsg = $e->getMessage();
                  }else{  $erreurMsg = "La page demandé n'a pas les ressources nécessaire pour répondre à la demande." ;  }
                  break;  
   
               //Page non trouvée
               case 404:
                  $titreErreur = "Page non trouvée";
                  if($e->getMessage() == null){
                     $erreurMsg = "La page demandée est introuvable sur le serveur." ;
                  }  else{ $erreurMsg = $e->getMessage(); }
                  break;  
   
               // Ressource bloqué
               case 423:
                  $titreErreur = "Ressource bloquée";
                  $erreurMsg = $e->getMessage();
                  break; 
                  
               // Authentification acceptée mais les droits refusé
               case 403:
                  header(URL_SITE);
                  break;  
                  
               //Erreur serveur (Par défaut / code = 500)
               default:
                  $cache = ob_get_clean();
                  echo "<h1> Page d'erreur en BETA : </h1> <br>";
                  echo "<h1> Erreur : </h1> <br> " ;
                  
                  echo $e->xdebug_message ;
      
                  echo "<hr> <br> <br> <br> <br>  <h1> Affichage avant l'erreur: </h1> <br> <br> ". $cache ;
                  exit();
            }

            $this->vue = new Vue('Erreur');
            $this->vue->setListeCss(["public/css/erreur.css"]) ;
            $this->vue->setHeader("vue/header.php") ;
            $this->vue->genererVue( array("titreErreur" => $titreErreur,'erreurMsg' => $erreurMsg) );

         }
        


     

      
   }
}

?>