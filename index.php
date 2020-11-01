<?php


$test = str_replace("index.php", "", (isset($_SERVER['HTTPS']) ? "https" : "http" . "://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']) ) ;
define('URL_SITE',  $test) ;

$lienActive = str_replace("/btssio/BTS2/BHL_Clothes/","",$_SERVER['REQUEST_URI']) ;
define('LIEN_ACTIVE',  $lienActive) ;

require_once('controleur/Routeur.php');

if( isset($_GET['url']) ){
   $url = explode('/', filter_var( $_GET['url'], FILTER_SANITIZE_URL) ); //Récupération de chaque partie de l'url (ex: www.site.fr/admin/commande/ => [admin,commande])
}
else{
   $url = array("Accueil") ;
}


$routeur = new Routeur($url);
$routeur->RouterLaPage();




?>
