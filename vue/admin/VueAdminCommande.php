<script>    

$(document).ready(function(){
      $('#tableCommande').DataTable({
         dom:'ftpl',
         order: [[ 1, "asc  " ]], //Order des dates en premier
         language:{
               url:"public/script/Datatable/french.json"
         }
      })
   });
</script>
<div id="commande" class="contenutab">
    <h3>Mes Commandes</h3>
    <table class ="display" id="tableCommande">
        <thead>
            <th>Numéro </th> <th>Date </th>  <th>Propriétaire</th> <th>Etats</th> <th>Action</th>
        </thead>
        <?php
            foreach ($commandeList as $commande){ ?>
        <tr> 
            <td> <?php echo $commande->num(); ?> </td> 
            <td data-sort="<?php echo $commande->dateCreation('Y-m-d à H\:i');?>"><?php echo $commande->dateCreation('d/m/Y à H\hi');?>   </td> 
            <td> <?php echo $commande->Client()->nom()." ".$commande->Client()->prenom() ;?>   </td>
            <td style='<?php echo "color:".$commande->Etat()->colorCode().";" ?>'> <?php echo "<i class='".$commande->Etat()->classIcon()."'> </i> ".$commande->Etat()->libelle()  ?>  </td>
            <td class='form-inline'> <a href="admin/commande/<?php echo $commande->num() ?>"> <button type='button' class='form-control'> <i class='fa fa-pencil'></i> </button> </a> 
            <form action="admin/commande/<?php echo $commande->num() ?>" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer cette commande ?")' type='submit' name='supprimerCommande' class='form-control'> <i class='fa fa-trash'></i> </button> </form>
            <a href="admin/commande/<?php echo $commande->num() ?>/facture"> <button <?php if($commande->getFacture() == null){echo "disabled" ;} ?> type='button' class='form-control'> <i class='fa fa-file-text-o'></i></button> </a>  </td>
        </tr>
      

    <?php } ?>

    </table>
</div>
