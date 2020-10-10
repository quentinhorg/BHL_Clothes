<?php

$test = str_replace("index.php", "", (isset($_SERVER['HTTPS']) ? "https" : "http" . "://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']) ) ;
define('URL_SITE',  $test) ;

require_once('controleur/Routeur.php');


$routeur = new Routeur();
$routeur->RouterLaPage();




?>
