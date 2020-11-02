 
    <?php
        //var_dump($clientActif);
        echo "<h2>".$clientActif->nom()." ".$clientActif->prenom()."</h2>";
    ?>

    <div class="tab">
        <button class="lientab email" onclick="ouvririnfo(event, 'email')"id="defaut" >Email</button>
        <button class="lientab mdp" onclick="ouvririnfo(event, 'mdp')">Mot de passe</button>
        <button class="lientab livraison" onclick="ouvririnfo(event, 'livraison')">Livraison</button>
        <button class="lientab commande" onclick="ouvririnfo(event, 'commande')">Gérer mes commandes</button>
    </div>

    <div id="email" class="contenutab">
        <h3>Email</h3>
        <p>Votre email est actuellement <a><?php echo $clientActif->email();?></a> </p> 
        <p>Si vous souhaitez le modifier, entrez votre nouvelle adresse Email puis confirmez le</p>
        <form action="compte#email" method="POST">
          
            <input disabled type="email" name="changeMail" placeholder="Entrez votre nouvelle adresse Email"  ><br>
            <input disabled type="email" name="cha   ngeMail2" placeholder="Confirmez votre nouvelle adresses Email"  ><br>
            <input disabled type="submit" value="Changer l'adresse Email " name="submitMail">
            <br> Indisponible pour le moment
        </form>
    </div>

    <div id="mdp" class="contenutab">
        <h3>Mot de passe <svg width="1em" height="2em" viewBox="0 0 16 16" class="bi bi-lock-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path d="M2.5 9a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2V9z"/>
  <path fill-rule="evenodd" d="M4.5 4a3.5 3.5 0 1 1 7 0v3h-1V4a2.5 2.5 0 0 0-5 0v3h-1V4z"/>
</svg></h3>
        <p>Si vous souhaitez changer votre mot de passe veuillez saisir ces informations</p> 
        <form class="form" action="compte#mdp" method="POST">
            <div class="form-group">
                <label for="IdAncienMdp">Ancien mot de passe</label>
                <input type="password" class="form-control" id="IdAncienMdp" name="ancienMdp">
            </div>
            <div class="form-group">
                <label for="IdNewMdp">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="IdNewMdp" name="changeMdp">
            </div>
            <div class="form-group">
                <label for="IdNewMdp2">Confirmer le nouveau mot de passe</label>
                <input type="password" class="form-control" id="IdNewMdp2" name="changeMdp2">
            </div>
            <input id="IdSubmitMdp" type="submit" class="btn btn-primary" name="submitMdp" value="Changer le mot de passe">
        </form> 
    </div>

    <div id="livraison" class="contenutab">
        <h3>Livraison <svg width="1em" height="2em" viewBox="0 0 16 16" class="bi bi-truck" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
  <path fill-rule="evenodd" d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
</svg></h3>
        <p>Votre adresse est actuellement <a><?php echo $clientActif->rue()." ".$clientActif->CodePostal()->cp();?></a> <br> Si vous souhaitez la modifier, entrez votre adresse de livraison ci dessous </p> 
        <form class="form" action="compte#livraison" method="POST">
            <div class="form-group">
                    <label for="IdNewRue">Nouvelle adresse</label>
                    <input type="text" class="form-control" id="IdNewRue" name="changeRue">
                </div>
                <div class="form-group">
                    <label for="IdNewRue2">Confirmer nouvelle adresse</label>
                    <input type="text" class="form-control" id="IdNewRue2" name="changeRue2">
                </div>
        
            <div class="form-group">
                <label for="CodePostal">Code Postal</label>
                <select class="form-control" name="changeCP" id="CodePostal">
                        <?php

                        foreach ($listeCP as $codePostal) {
                            echo "<option value='".$codePostal->cp()."'> ".$codePostal->cp()." - ".$codePostal->libelle()." </option>";
                        }
                        
                        ?>
                
                </select>
                </div>
                <input id="IdSubmitAdresse" type="submit" class="btn btn-primary" name="submitAdresse" value="Changer d'adresse">

        </form>

        <form >

        </form> 
    </div>

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
                <th>Numéro de Commande</th> <th>Date de la commande</th> <th>Détail</th> <th>Suivi de commande</th>
            </thead>
            <?php
                foreach ($clientActif->listCmdPaye() as $commande){?>
            <tr>
                <td> <?php echo $commande->num(); ?> </td> <td> <?php echo $commande->dateCreation('d/m/Y à H\hi'); ?> </td> <td> <a href="facture/<?php echo $commande->num() ?>"> Voir ma facture </a> </td> <td> <a href="compte/suivi/<?php echo $commande->num() ?>"> Suivre ma commande </a> </td>
            </tr>

        <?php } ?>

        </table>
    </div>

<script>
    
    //Sélection du code postal sur le select
     $('#CodePostal').val('<?php echo $clientActif->codePostal()->cp() ; ?>'); 
     

    function ouvririnfo(event, parametre) {
        var i, tabcontent, lientab;
        tabcontent = document.getElementsByClassName("contenutab");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        lientab = document.getElementsByClassName("lientab");
        for (i = 0; i < lientab.length; i++) {
            lientab[i].className = lientab[i].className.replace(" active", "");
          
        }
        document.getElementById(parametre).style.display = "block";
        //event.currentTarget.className += " active";
        
        $("button."+parametre).addClass("active");
    }

    // Obtient l'élément avec l'id="defaut" et le selectionne

    document.getElementById("defaut").click();
    var url = window.location.href ;

   
  
    if(url.lastIndexOf('#') != -1){
        var id = url.substring(url.lastIndexOf('#') + 1);
        ouvririnfo(event, id);
    }



</script>


