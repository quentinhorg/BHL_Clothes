<?php //var_dump($vetement->listeCouleur() ); ?>
<div class="row">
  <div class="col-md-5 col-md-push-5">
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
        <td><input class='form-control' disabled value='<?php echo $vetement->id()?>' type="text" name='idVet'></td>
      </tr>
      <tr>
        <th scope="row">Nom</th>
        <td><input class='form-control' value='<?php echo $vetement->nom()?>' type="text"></td>
      </tr>
      <tr>
        <th scope="row">Prix</th>
        <td><input class='form-control' step="any" value='<?php echo $vetement->prix()?>' style="width:100px" type="number"> €</td>
      </tr>
      <tr>
        <th scope="row">Catégorie</th>
        <td class='form-inline'>
          
            <select class='form-control col-sm-7' name="etat" id="selectCateg">
        
                <?php foreach ($listCate as $categorie) {?>
                
                  <option value="<?php echo $categorie->id() ?>"> <?php echo $categorie->nom() ?></option>
                  <?php  } ?>
            
            </select>
                
          </td>

      </tr>
  

      <tr class='paiementInfo'>
        <th scope="row"> Information </th>
        <td> 
            <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 
            <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 
            <div> <label> XXXX </label> <span>  <?php echo "XXXX" ?> <span> </div> 
          </td>
      </tr>
    


      
      <tr>
        <th scope="row">Genre </th>
        
        <td> 
          <?php foreach ( $listGenre as $Genre ) {  ?>
          
          <?php  } ?>
      
        </td>
      </tr>

      
      <tr>
        <th scope="row">Configuration du motif </th>
        
        <td> <input type="text" placeholder="Aucun" value='<?php echo $vetement->motifPosition() ?>'> </td>
      </tr>

      <tr>
        <th scope="row">Description </th>
        <td>
            <textarea name="description" id="" cols="30" rows="10"><?php echo $vetement->description() ?> </textarea>
          </td>
      </tr>

      <tr>
        <th scope="row">Action</th>
        
          <td class='form-inline'> 
              <form action="" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer ce vetement ?")' type='submit' name='supprimerVetement'> Supprimer le vetement </button> </form>
          </td>

      </tr>
  
    </tbody>
  </table>

  </div>
  <div class="col-md-6 col-md-pull-5">
    <div class="row">
    <table class="table disponibilite">
  <thead class="thead-dark">
    <tr>
      <th scope="col" > Nom  </th>
      <th scope="col" > Filtre CSS  </th>
      <th scope="col" > Disponible  </th>
      <th scope="col" > Action </th>
    </tr>
  </thead>
  <tbody>

  <?php foreach ($vetement->listeCouleur() as $couleur) {?>
    <form action="" id='modifierCouleur' method="POST"> 
    <tr>
      <td > <?php echo $couleur->nom() ?> </td>
      <td > <input placeholder='Aucun' value="<?php echo $couleur->filterCssCode() ?>" type="text">  </td>
      <td> Disponible
        <select name="clrDispo" id="">
          <option value="1"> Oui</option>
          <option value="0"> Non</option>
        </select>
      </td>
      <td> 
        <form action="" id='supprimerCouleur' method="POST"> <button style="background-color: #bf4c4c;" class="btn-primary form-control" type="submit" value="<?php echo $couleur->num() ?>"> <i class="fa fa-trash "></i> </button>  </form>  
        <button class="btn-primary form-control" type="submit" name="modifierCouleur"> <i class="fa fa-floppy-o"> </i>  </button>
      </td>

    </tr>
    </form>
    <script> $("select[name='clrDispo']").last().val(<?php echo $couleur->dispo() ?>) ;</script>
    <?php  } ?>

    <form action="" id='ajouterCouleur' method="POST"> 
    <tr>
        <td> <input type="text" name="nomClr" placeholder="Nom"> </td>
        <td> <input type="text" name="filterCssCodeClr" placeholder="hue-rotate(xx)"> </td>
        <td> Disponible
        <select name="dispoClr" id="">
          <option value="1"> Oui</option>
          <option value="0"> Non</option>
        </select>
      </td>
        <td>  
          <button style="background-color: #3bbf82;" class="btn-primary form-control" name="ajouterCouleur" type="submit"> <i class="fa fa-plus-circle"> </i> </button>
       </td>

    </tr>
    </form>

   
 
  </tbody>
</table>
   </div>
   <div class="row">
   <table class="table disponibilite">
  <thead class="thead-dark">
    <tr>
      <th scope="col"> Taille  </th>
      <th scope="col"> Action  </th>
    </tr>
  </thead>
  <tbody>

    <?php foreach ($vetement->listeTailleDispo() as $taille) { ?>
      <tr> 
        <td> <?php echo $taille->libelle() ?> </td>
        <td> <form action="" id='supprimerTaille' method="POST"> <button style="background-color: #bf4c4c;" class="btn-primary form-control" type="submit" value="<?php echo $taille->libelle() ?>"> <i class="fa fa-trash "></i>  </button>  </form> </td>
      </tr>
    <?php  } ?>

    <form action="" id='ajouterTaille' method="POST"> 
    <tr>
      <td>
         
          <select class='form-control' name="etat" id="selectEtat">
      
              <?php foreach ($listTaille as $taille) { ?>
                <option <?php if ($vetement->possedeTaille($taille->libelle()) == true) {echo "disabled"; }?> value="<?php echo $taille->libelle() ?>"> <?php echo $taille->libelle() ?></option>
                <?php  } ?>
          
          </select>
               
        </td>

        <td>  
          <button style="background-color: #3bbf82;" class="btn-primary form-control" type="submit"> <i class="fa fa-plus-circle"> </i> </button>
       </td>

    </tr>
    </form>



   
 
  </tbody>
</table>
   </div>
  </div>
</div>