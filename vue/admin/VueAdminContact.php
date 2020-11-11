<script>
$(document).ready(function(){
      $('#tableContact').DataTable({
         dom:'ftpl',
         language:{
               url:"public/script/Datatable/french.json"
         }
      })
   });
</script>

<h3>Messages</h3>

   <table class="display" id="tableContact">
      <thead class="thead-dark">
         <tr>
            <th scope="col">Id</th>
            <th scope="col">Nom</th>
            <th scope="col">Mail</th>
            <th scope="col">Tél</th>
            <th scope="col">Objet</th>
            <th scope="col">Dernière réponse</th>
            <th scope="col">Réponse</th>
            <th scope="col">Action</th>
         </tr>
      </thead>
      <tbody>
         <?php foreach ($contactList as $contact) { ?>
            <tr>
               <th scope="row"> <?php echo $contact->idContact();  ?> </th>
               <td><?php echo $contact->nom();  ?></td>
               <td><?php echo $contact->email();  ?></td>
               <td><?php echo $contact->numero();  ?></td>
               <td><?php echo $contact->sujet();  ?></td>
               <td><?php echo $contact->date('d/m/Y à H\hi');  ?></td>
               <td><?php echo "aaa" ?></td>
               <td>
                  <a href="admin/contact/<?php echo $contact->idContact(); ?>"><button type='button' class='form-control'> Voir le message </button></a>
                  <form action="" method='POST'> <button onclick='return confirm("Voulez-vous vraiment supprimer ce message ?")' type='submit' value="<?php echo $contact->idContact(); ?>" name='supMessage' class='form-control'> Supprimer </button> </form>

               </td>
            </tr>
         <?php } ?>
        
         <?php     ?>
        
      </tbody>
   </table>
    