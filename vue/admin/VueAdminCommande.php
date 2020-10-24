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
         <form action="" method="POST"> 
            <td> <?php echo $commande->num(); ?> </td> 
            <td> <?php echo $commande->dateCreation();?>   </td> 
            <td> <?php echo $commande->Client()->nom()." ".$commande->Client()->prenom() ;?>   </td>
            <td style='<?php echo "color:".$commande->Etat()->colorCode().";" ?>'> <?php echo "<i class='".$commande->Etat()->classIcon()."'> </i> ".$commande->Etat()->libelle()  ?>  </td>
            <td> <a href="admin/commande/<?php echo $commande->num() ?>"> <button type='button' class='btn-primary'> Modifier / Consulter </button> </a> 
            <a href="admin/commande/<?php echo $commande->num() ?>?action=supprimer"> <button type='button' class='btn-primary'> Spprimer </button> </a> 
            <a href="facture/<?php echo $commande->num() ?>"> <button type='button' class='btn-primary'> Voir facture </button> </a>  </td>
                
         </form>
        </tr>
      

    <?php } ?>

    </table>
</div>