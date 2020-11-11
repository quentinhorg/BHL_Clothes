<?php 

include "Pagination.php" ;


abstract class DataBase{
   private static $bdd;
 

   //Mise en place de la BDD
   private static function setBdd(){
      self::$bdd = new PDO('mysql:host=localhost;dbname=bhl_clothes;charset=utf8mb4', 'btssio', 'btssio');
      self::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
   }

   //Obtenir la BDD
   protected function getBdd(){
      if(self::$bdd == null){
         $this->setBdd();
         return self::$bdd;
      }
   }

   //Obtention d'un Model
   protected function getModele($obj, $req, $tabValeur = null ){  
      
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
      return $var; //Retourne une liste d'objet(s)
      $req->closeCursor();
}




   //Exécute une requête dans la BDD 
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
      
      return $resultat ; //Retourne un résultat si un SELECT
      $req->closeCursor();
      

   }



   
   //Obtention d'un nouvel ID pour insérer dans la table rnesigner 
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