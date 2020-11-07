<?php 
 class Pagination extends DataBase{
   public $numPageActive;
   public $ligneParPage ;
   public $totalPage ;


   public function __construct($numPageActive, $ligneParPage){
      $this->numPageActive = $numPageActive;
      $this->ligneParPage = $ligneParPage;
   }
   
   


   public function getReqPagination($OriginalReq, $tabValeur = null){
      
      $ligneParPage = $this->ligneParPage ;
      $totalResultat = COUNT(  $this->execBDD($OriginalReq, $tabValeur) );
      $numPageActive =  $this->numPageActive ;
      $totalPage = ceil($totalResultat/$ligneParPage); //Calcule du nombre de page totale

     
       if( 
         isset($numPageActive) 
         && !empty($numPageActive) 
         && $numPageActive > 0 
         && $numPageActive <= $totalPage
       ){
         $numPageActive = intval($numPageActive);
         $numPageActive = $numPageActive;
         
        } 
      else {
        $numPageActive = 1;
      }
      $commence = ($numPageActive-1)*$ligneParPage;
      
      $newReq = $OriginalReq." LIMIT ". $commence .", " .$ligneParPage;
      

      //Initialisation des attributs
      $this->totalPage = $totalPage ;
      $this->numPageActive = $numPageActive;


      return $newReq ;

   }


   public function getVuePagination($href){

         //Procédure pour éviter dupliquer la variable "page=" dans l'url
         if(strpos($href,"page")){
            $href =  substr($href, 0, strpos($href,"page")-1); // retourne "de"
            
         }
         $symbole = "?";
         if(strpos($href, "?")){$symbole = "&" ;}
         $href.= $symbole."page=";

      
         
         $totalPage = $this->totalPage ; //Calcule du nombre de page totale
         
         $listePage = "<div class='pagination'>";


         if ($totalPage > 1){
            for($i=1; $i<=$totalPage ;$i++) {
               if($i == $this->numPageActive) {
                  $listePage .= '<span>'.$i.'</span>';
               } 
               else {
        
                  $listePage .= "<a href='$href$i'> $i</a>";
               }
            }
         }
         $listePage .= "</div>";

         return $listePage ;    
   }



  

}
?>