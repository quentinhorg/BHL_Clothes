

<!--------------- Info de la commande ---------------->
<table class="table" id="commandeInfo">
  <thead class="thead-dark">
    <tr>
      <th scope="col">Champs</th>
      <th scope="col">Valeurs</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Date de création</th>
      <td><input class='form-control' disabled value='<?php echo $commande->dateCreation('d/m/Y à H\hi')?>' type="text"></td>
    </tr>

    <tr>
      <th scope="row">Numéro de la commande</th>
      <td><input class='form-control' disabled value='<?php echo $commande->num()?>' type="text"></td>
 
    </tr>
 
    <tr>
      <th scope="row">Id du client</th>
      <td><input class='form-control' value='<?php echo $commande->idClient()?>' type="number"> <?php echo $commande->Client()->nom()." ". $commande->Client()->prenom()?> </td>
    </tr>

    <form action="" id='etatCmd' method="POST"> 
    <tr>
      <th scope="row">Etat de la commande</th>
      <td class='form-inline'>
         
          <select class='form-control col-sm-7' name="etat" id="selectEtat">
      
              <?php foreach ($listEtat as $Etat) {?>
               
                <option value="<?php echo $Etat->id() ?>"> <?php echo $Etat->libelle() ?></option>
                <?php  } ?>
          
          </select>
                <input type="submit" value='Modifier' name='modifierEtatCmd' class='form-control btn-primary col-sm-5'>
        </td>

    </tr>
    </form>

    <tr>
      <th scope="row">Prix HT</th>
      <td> <?php echo $commande->prixHT()."€" ?> </td>
    </tr>

    <?php  if($commande->getFacture() != null){ $facture = $commande->getFacture() ; ?>

    <tr class='paiementInfo'>
      <th scope="row"> Information du paiement </th>
      <td> 
          <div> <label> Date de paiement: </label> <span>  <?php echo $facture->datePaiement('d/m/Y à H\hi') ?> <span> </div> 
          <div> <label> Type de paiement: </label> <span>  <?php echo $facture->typePaiement() ?> <span> </div> 
          <div> <label> Total payé : </label> <span>  <?php echo $commande->prixTTC()."€" ?> <span> </div> 
          <div> <label> Prix livraison : </label> <span>  <?php echo $facture->CodePostal()->prixLiv()."€ (".$facture->CodePostal()->libelle().", ".$facture->CodePostal()->cp().")" ?> <span> </div> 

          <?php if($facture->soldeAvantPaiement() != null) { ?>
          <div> <label> Solde avant le paiement : </label> <span>  <?php echo $facture->soldeAvantPaiement()."€" ?> <span> </div> 
          <div> <label> Solde après le paiement : </label> <span>  <?php echo $facture->soldeApresPaiement()."€" ?> <span> </div> 
          <?php } else{ echo "Le solde avant/après n'est pas disponible"; }?>
        </td>
    </tr>
  
    <?php  } ?>

    <tr>
      <th scope="row">Action</th>
      
        <td class='form-inline'> 
            <a href="facture/<?php echo $commande->num(); ?>"><button type='button' <?php if($commande->getFacture() == null){echo "disabled title='Aucune facture ne correspond à cette commande' " ;} ?>> Voir la facture PDF</button> </a>
            <?php if($commande->getFacture() != null) { ?>
                <form action="" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer cette facture ?")' type='submit' name='supprimerFacture'> Supprimer la facture </form>
            <?php } ?>
            <form action="" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer cette commande ?")' type='submit' name='supprimerCommande'> Supprimer la commande </button> </form>
        </td> 
    </tr>
 
  </tbody>
</table>

<h1> Listes des articles </h1>
<!--------------- Liste des articles ---------------->
<table class="table" id='listeArticle'>
  <thead class="thead-dark">
    <tr>
      <th scope="col">Article</th>
      <th scope="col">Taille</th>
      <th scope="col">Couleur</th>
      <th scope="col">Quantités</th>
      <th scope="col">Prix total</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($commande->panier() as $Article) {?>
    
    <form id="editArticle" method="POST">
    
    <!-- Anciennes valeurs  -->
    <div style="display:none">
      <input type="text" readonly name="ancien[taille]" value='<?php echo $Article->Taille()->libelle() ?>'>
      <input type="text" readonly name="ancien[numClr]" value='<?php echo $Article->Couleur()->num() ?>'>
    </div>


      <tr>
        <th scope="row"><?php echo $Article->nom() ?> </th>
        <td> 
          <select name="tailleArt" >
          <?php foreach ($Article->listeTailleDispo() as $Taille) { ?>
              <option value="<?php echo $Taille->libelle() ?>"> <?php echo $Taille->libelle() ?> </option>
            <?php } ?>
          </select>

           <!-- Selection automatique de la taille de l'article concerné  -->
           <script> $("select[name='tailleArt']").last().val('<?php echo $Article->Taille()->libelle() ?>') ;</script>
      
        
        </td>
        <td>
          <select name="numClrArt">
            <?php foreach ($Article->listeCouleurDispo() as $couleur) { ?>
              <option value="<?php echo $couleur->num() ?>"> <?php echo $couleur->nom() ?> </option>
            <?php } ?>
          </select>

             <!-- Selection automatique de la couleur de l'article concerné  -->
             <script> $("select[name='numClrArt']").last().val('<?php echo $Article->Couleur()->num() ?>') ;</script>
      
              

        </td>
        <td> <input type="number" value="<?php echo $Article->qte() ?>" max="10" name="qteArt"></td>
        <td> <?php echo $Article->prixTotalArt()."€" ?> </td>
        <td> 
          <button onclick='return confirm("Voulez-vous vraiment supprimer cette article ?")' type='submit' name='supprimerArticle' value="<?php echo $Article->id() ?>" > Supprimer </button> 
              
            
   
         

          <button type='submit' name='modifierArticle' value="<?php echo $Article->id() ?>" > Modifier </button> 
        </td>
      </tr>
    </form>
  <?php } ?>
   
  </tbody>
</table>


<script>
    //Attribuer automatiquement l'etat de la commande au menu de selection
    $("#selectEtat").val(<?php echo $commande->Etat()->id() ; ?>) ;
</script>

