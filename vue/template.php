<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <base href="/btssio/BTS2/BHL_Clothes/">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    
  
    <link rel="stylesheet" href="public\bootstrap-4.3.1\bootstrap.min.css">
    <link rel="icon" type="image/png" href="public\media\bhl_clothes\logo_icon.png" />
    

    <script src="public/script/js/jquery-3.4.0.min.js"></script>     <!-- JQuery 3.4.0 -->
    <link href='public/font/Archivo.css' rel='stylesheet'>    <!-- Font style -->
    <title> <?php echo $titre  ?> </title>  <!-- Titre de l'onglet -->
    <link rel="stylesheet" href="public/fontawesome/font-awesome.min.css"> <!-- Css pour les icones avec la librerie Fontawesome -->

    <!-- Liste du css besoin qui reviens sur toutes les pages -->
    <link rel="stylesheet" href="public/css/bhl_clothes.css"> <!-- Css Principal du site -->
    <link rel="stylesheet" href="public/css/navigation.css"> <!-- Css de la Navigation -->
    <link rel="stylesheet" href="public/css/footer.css"> <!-- Css du Footer -->
    <link rel="stylesheet" href="public/script/DataTable/datatable.css"> <!-- Css pour la librerie DataTable -->
  
     <!-- Bootstrap Intégration  -->
    
    <script src="public\bootstrap-4.3.1\bootstrap.min.js"></script>

    

    <!-- liens notes -->
   
    
    <script src=""></script>
    

    <?php 
        //Insertion de tous les liens css
        foreach ($listeCss as $fichierCss) {
            echo "<link rel='stylesheet' href='$fichierCss'>" ;
        }

        //Insertion de tous scripts JS
        foreach ($listeJsScript as $fichierJs) {
            echo "<script src='$fichierJs'></script>" ;
        }

        
    ?>
 
    

    
   

</head>
<body>
   
    <?php echo $nav  //Insertion de la bar de navigation ?>
    <?php echo $header  //Insertion de la bar de navigation ?>
    <div id="contenu">
        <?php echo $contenu  //Insertion des contenue de la page active ?>
    </div>

    <?php echo $footer  //Insertion du footer ?>
  
</body>

        
</html>