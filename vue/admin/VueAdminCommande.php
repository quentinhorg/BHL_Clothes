<script>    

$(document).ready(function(){
      $('#tableCommande').DataTable({
         dom:'ftpl',
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
            foreach ($commandeList as $commande){?>

      
        <tr>   
      
            <td> <?php echo $commande->num(); ?> </td> 
            <td> <?php echo $commande->dateCreation();?>   </td> 
            <td> <?php echo $commande->Client()->nom()." ".$commande->Client()->prenom() ;?>   </td>
            <td style='<?php echo "color:".$commande->Etat()->colorCode().";" ?>'> <?php echo "<i class='".$commande->Etat()->classIcon()."'> </i> ".$commande->Etat()->libelle()  ?>  </td>
            <td class='form-inline'> <a href="admin/commande/<?php echo $commande->num() ?>"> <button type='button' class='form-control'> Modifier / Consulter </button> </a> 
            <form action="admin/commande/<?php echo $commande->num() ?>" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer cette commande ?")' type='submit' name='supprimerCommande' class='form-control'> Supprimer </button> </form>
            <a href="admin/commande/<?php echo $commande->num() ?>/facture"> <button <?php if($commande->getFacture() == null){echo "disabled" ;} ?> type='button' class='form-control'> Voir facture </button> </a>  </td>
                
  
        </tr>
      

    <?php } ?>

    </table>
</div>

<?php 
//Affiche le message du popup si il a été passé dans la vue et n'est pas null

?>