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
            <th scope="col">Message</th>
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
               <td><a href="admin/contact/<?php echo $contact->idContact(); ?>"><button type='button' class='form-control'> Voir le message </button></a></td>
            </tr>
         <?php } ?>
        
         <?php     ?>
        
      </tbody>
   </table>
    