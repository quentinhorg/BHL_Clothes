<script>    
$(document).ready(function(){
      $('#tableVetement').DataTable({
         dom:'ftpl',
         language:{
               url:"public/script/Datatable/french.json"
         }
      })
   });
</script>


<div id="vetement" class="contenutab">
    <h3>Mes Vetements</h3>
    <table class="display" id="tableVetement">
        <thead>
            <th>Id </th> <th>Nom </th>  <th>Prix</th> <th>Catégorie</th> <th>Genre</th> <th> Status </th>  <th> Avis </th>  <th> Action </th>
        </thead>
        <?php foreach ($vetementList as $vetement){ ?>
         <tr>   
               <td> <?php echo $vetement->id(); ?> </td> 
               <td> <?php echo $vetement->nom();?>   </td> 
               <td> <?php echo $vetement->prix()."€";?>   </td> 
               <td> <?php echo $vetement->Categ()->nom() ?>   </td>
               <td> <?php echo $vetement->Genre()->libelle() ?>   </td>
               <td class='vetStatus'> 
                  <?php if($vetement->dispoPourVendre() ) {echo "<span style='color:green;'> <i class='fa fa-check'></i> En vente </span>" ;} else{ echo "<span style='color:red;'> <i class='fa fa-times' ></i> Rupture de stock </span>" ;} ?>   
               </td>
         
               <td> <?php echo $vetement->nbAvis()." avis" ?>   </td>
               <td class='form-inline'> 
                  <a href="admin/vetement/<?php echo $vetement->id() ?>"> <button type='button' class='form-control'> Modifier </button> </a> 
                  <form action="admin/vetement/<?php echo $vetement->id() ?>" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer cette vetement ?")' type='submit' name='supprimerVetement' class='form-control'> Supprimer </button> </form>
               </td>
         </tr>
      <?php } ?>

    </table>
</div>

