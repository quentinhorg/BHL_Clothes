
<?php //var_dump($clientInfo) ?>
<section id="paiement"> 

  
<h2>Paiement</h2>
<p>Vous allez procéder au paiement, merci de bien vouloir vérifier les informations</p>
<div class="row">
  <div class="col-75">
    <div class="container">
      <form action="" method='POST'>
      
        <div class="row">
          <div class="col-50">
            <h3>Adresse de facturation</h3>
            <p> Pour modifier vos informations aller dans les paramètres. </p>
            <label for="fnom"><i class="fa fa-user"></i> Nom </label>
            <input type="text" id="fnom" name="nom" value="<?php echo $clientInfo->getNom() ;?>" disabled>

            <label for="fprenom"><i class="fa fa-user"></i> Prénom </label>
            <input type="text" id="fprenom" value="<?php echo $clientInfo->getPrenom() ;?>" disabled>

            <label for="femail"><i class="fa fa-envelope"></i> Mail </label>
            <input type="text" id="femail" value="<?php echo $clientInfo->email() ;?>" disabled>

            <label for="adr"><i class="fa fa-address-card-o"></i> Addresse</label>
            <input type="text" id="adr"  value="<?php echo $clientInfo->getAdresse() ;?>" disabled>

            <label for="fcp"><i class="fa fa-address-card-o"></i> Code Postal </label>
            <input type="text" id="fcp" value="974" disabled>

            <label for="city"><i class="fa fa-institution"></i> Ville </label>
            <input type="text" id="city"  value="New York" disabled>

       

          </div>

          <div class="col-50">
            <h3>Paiement</h3>
            <label for="fname">Méthode de paiement</label>
            <select name="payeMethode" id="payeMethode">
            <option value="solde"> Solde (<?php echo $clientInfo->solde()."€" ?>) </option>
            <option value="card" disabled> Carte </option>
            </select>

            <p>
              <?php 
                  echo "<p style=' font-weight:bold'> Votre solde actuel :  ".$clientInfo->solde()."€" ;
              if($clientInfo->solde() >= $maCommande->prixTTC() ){
                $mtnApresAchat = $clientInfo->solde()- $maCommande->prixTTC() ;
            
                echo "<p style='color:green; font-weight:bold'> Estimation de votre après l'achat : ".$mtnApresAchat."€" ;
                $disabled = null;
              }
              else{
                echo "<p style='color:red; font-weight:bold'> Vous n'avez pas les fonds nécessaires pour l'achat . </p> " ;
                $disabled = "disabled";
              }
              
              
              ?>
              
            </p>
          </div>
          
        </div>
        
        <input <?php echo $disabled ?> type="submit" name="payerCmd" value="Payer ma commande" class="btn">
      </form>
    </div>
  </div>
  <div class="col-25">
    <div class="container">
      <h4>Article(s) <span class="price" style="color:black"><i class="fa fa-shopping-cart"></i> <b><?php echo $maCommande->getQuantiteArticle() ?></b></span></h4>
      
      <?php foreach ($maCommande->panier() as $article) { ?>
        <div class="articleLigne"> <div class='nomArti' ><?php echo "x".$article->qte()." " ?><a href="#"><?php echo $article->nom() ?></a></div>  <span class="price"><?php echo $article->prixTotalArt()."€" ?> </span> </div>
      <?php } ?>
     
      <hr>
      <p>Total <span class="price" style="color:black"><b> <?php echo $maCommande->prixTTC()."€" ?></b></span></p>
    </div>
  </div>
</div>



</section>

<script>

</script>


