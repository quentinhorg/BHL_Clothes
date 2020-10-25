
<!--------------- Info du vetement ---------------->
<table class="table" id="vetementInfo">
  <thead class="thead-dark">
    <tr >
      <th scope="col" colspan="2" >Infos Global </th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Id</th>
      <td><input class='form-control' disabled value='<?php echo $vetement->id()?>' type="text"></td>
 
    </tr>

    <tr>
      <th scope="row">Nom</th>
      <td><input class='form-control' disabled value='<?php echo $vetement->nom()?>' type="text"></td>
 
    </tr>
 
    <tr>
      <th scope="row">Prix</th>
      <td><input class='form-control' value='<?php echo $vetement->prix()?>' type="number"> </td>
    </tr>

    
    <tr>
      <th scope="row">Catégorie</th>
      <td class='form-inline'>
         
          <select class='form-control col-sm-7' name="etat" id="selectEtat">
      
              <?php foreach ($listCate as $categorie) {?>
               
                <option value="<?php echo $categorie->id() ?>"> <?php echo $categorie->nom() ?></option>
                <?php  } ?>
          
          </select>
              
        </td>

    </tr>
 

    <tr class='paiementInfo'>
      <th scope="row"> Information du paiement </th>
      <td> 
          <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 
          <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 
          <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 

       
        </td>
    </tr>
  


    
    <tr>
      <th scope="row">Genre </th>
      <td> <?php echo $vetement->Genre()->libelle() ?> </td>
    </tr>

    
    <tr>
      <th scope="row">Motif </th>
      <td> <?php echo $vetement->motifPosition() ?> </td>
    </tr>

    <tr>
      <th scope="row">Description </th>
      <td> <?php echo $vetement->description() ?> </td>
    </tr>

    <tr>
      <th scope="row">Action</th>
      
        <td class='form-inline'> 
            <form action="" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer ce vetement ?")' type='submit' name='supprimerVetement'> Supprimer le vetement </button> </form>
        </td> 
    </tr>
 
  </tbody>
</table>



<!--------------- Info du vetement ---------------->
<table class="table" id="disponibiliteInfo">
  <thead class="thead-dark">
    <tr>
    <th scope="col" colspan="2" >Disponibilités </th>
    </tr>
  </thead>
  <tbody>

    <form action="" id='ajouterTaille' method="POST"> 
    <tr>
      <th scope="row">Taille Disponible</th>
      <td class='form-inline'>
         
          <select class='form-control col-sm-7' name="etat" id="selectEtat">
      
              <?php foreach ($listTaille as $taille) {?>
               
                <option value="<?php echo $taille->libelle() ?>"> <?php echo $taille->libelle() ?></option>
                <?php  } ?>
          
          </select>
                <input type="submit" value='Modifier' name='ajouterTaille' class='form-control btn-primary col-sm-5'>
        </td>

    </tr>
    </form>

    <form action="" id='ajouterCouleur' method="POST"> 
    <tr>
      <th scope="row">Couleur Disponible</th>
      <td class='form-inline'>
         
          <select class='form-control col-sm-7' name="etat" id="selectEtat">
      
              <?php foreach ($vetement->listeCouleurDispo() as $couleur) {?>
               
                <option value="<?php echo $couleur->num() ?>"> <?php echo $couleur->nom() ?></option>
                <?php  } ?>
          
          </select>
                <input type="submit" value='Modifier' name='ajouterCouleur' class='form-control btn-primary col-sm-5'>
        </td>

    </tr>
    </form>

   
 
  </tbody>
</table>





<script>
    //Attribuer automatiquement l'etat de la vetement au menu de selection
    //$("#selectEtat").val(<?php //echo $vetement->Etat()->id() ; ?>) ;
</script>

