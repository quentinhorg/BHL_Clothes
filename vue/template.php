<!DOCTYPE html>
<html lang="fr">
<head>
    <base href="/btssio/BTS2/BHL_Clothes/">
    <link href='public/font/Archivo.css' rel='stylesheet'>
    <meta charset="UTF-8">
    <title> <?php echo $titre  ?> </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/bhl_clothes.css">
    <link rel="stylesheet" href="public/css/erreur.css">
    <link rel="stylesheet" href="public/css/navigation.css">
    <link rel="stylesheet" href="public/script/DataTable/datatable.css"> <!-- Provisoir -->
    <script src="public/script/js/jquery-3.4.0.min.js"></script>
    

    <!-- liens notes -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
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
    <div>

    
  
</body>
</html>