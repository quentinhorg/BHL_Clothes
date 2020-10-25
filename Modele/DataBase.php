<?php 






include "Pagination.php" ;


abstract class DataBase{
   private static $bdd;
 


   private static function setBdd(){
      self::$bdd = new PDO('mysql:host=localhost;dbname=bhl_clothes;charset=utf8', 'btssio', 'btssio');
      self::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
   }

   protected function getBdd(){
      if(self::$bdd == null){
         $this->setBdd();
         return self::$bdd;
      }
   }


   protected function getModele($req, $tabValeur, $obj){  
      
      $var = [];
      $req = self::$bdd->prepare($req);
      $req->execute($tabValeur);
      
      
      

      if($req != null || empty($req) ){

         while($donnee = $req->fetch(PDO::FETCH_ASSOC)){
     
            $var[] = new $obj($donnee);
            
         }

         
     
      }

      else{
         $var = null;
      }

      
     
      return $var;
      $req->closeCursor();
      
}





   protected function execBDD($req, $tabValeur){
      $resultat = null;
      

      $req = self::$bdd->prepare($req);
      if(@!$req->execute($tabValeur) && isset($req->errorInfo()[0]) && !empty($req->errorInfo()[0]) ){

         $ErrorCode = (int) $req->errorInfo()[0];

         if( !is_int($ErrorCode) ){
            $ErrorCode = null;
         }
        
         throw new Exception( $req->errorInfo()[2], $ErrorCode )  ;
      }
      
 
      
      @$resultat = $req->fetchAll(PDO::FETCH_ASSOC) ;
      
      
     
      return $resultat ;
      
      $req->closeCursor();
      

   }



   

   protected function getNewIdTable($table, $pk){

      $var = [];
     
         $req = self::$bdd->prepare("SELECT max($pk)+1 as 'newId' FROM $table");
      
         $req->execute();
         $resultat = $req->fetchAll(PDO::FETCH_ASSOC)[0]["newId"];
     
      


      if( empty($resultat) ){ $resultat = 1 ; }
      return $resultat;
      $req->closeCursor();



   }



}


?>