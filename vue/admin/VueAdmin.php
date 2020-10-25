<?php 


class VueAdmin extends Vue{
      
      //Construction de la vue
     public function __construct($page){
        //Initialisation par défaut
        $this->fichier= 'vue/admin/vue'.ucfirst($page).'.php';
        $this->template= "vue/admin/adminTemplate.php" ;
        $this->titre= $page;
        $this->listeCss= ["public/css/admin/".strtolower($page).".css"] ;
        $this->nav= "vue/admin/adminNavigation.php";
        $this->footer = "vue/admin/adminFooter.php";
        $this->listeJsScript= array() ;
        $this->Popup = new Popup;
      }

        private function getNav(){
          return $this->genererFichier($this->nav, array()) ;
        }
  
  
       public function genererVue($donnee){
       
        //PARTIE DE LA VUE
        $contenu = $this->genererFichier($this->fichier, $donnee);
  
         $nav = $this->getNav();

        if($this->header != null){
           $header =  $this->genererFichier($this->header, $donnee);
        }else{ $header  = null;}
        
        $footer= $this->genererFichier($this->footer, $donnee);
       
        //Génération final
        $vue = $this->genererFichier($this->template, array(
           'titre' => $this->titre, 
           'contenu' => $contenu,
           'nav' => $nav,
           'header' => $header,
           'footer' => $footer,
           'listeCss' => $this->listeCss,
           "listeJsScript"=> $this->listeJsScript,
           "Popup" => $this->Popup
        ));
  
        //Intégration de la vue
        echo $vue;
       
         
       }
  
  
  

  
  
  




}

?>