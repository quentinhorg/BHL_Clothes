 
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
        <h3>Mot de passe</h3>
        <p>Si vous souhaitez changer votre mot de passe veuillez saisir ces informations</p> 
        <form action="compte#mdp" method="POST">
            <input type="password" name="ancienMdp" placeholder="Entrez votre ancien mot de passe"  ><br>
            <input type="password" name="changeMdp" placeholder="Entrez le nouveau mot de passe"  ><br>
            <input type="password" name="changeMdp2" placeholder="Confirmez le nouveaut mot de passe"  ><br>
            <input type="submit" value="Changer le mot de passe" name="submitMdp">
        </form>
    </div>

    <div id="livraison" class="contenutab">
        <h3>Livraison</h3>
        <p>Votre adresse est actuellement <a><?php echo $clientActif->rue()." ".$clientActif->CodePostal()->cp();?></a> </p> 
        <p>Si vous souhaitez la modifier, entrez votre adresse de livraison ci dessous </p>
        <form action="compte#livraison" method="POST">
            <input  class="form-control" type="text" name="changeRue" placeholder="Entrez la nouvelle rue" ><br>
            <input  class="form-control" type="text" name="changeRue2" placeholder="Confirmez la nouvelle rue" ><br> <br>

            <select class="form-control" name="changeCP" id="CodePostal">
                    <?php

                    foreach ($listeCP as $codePostal) {
                        echo "<option value='".$codePostal->cp()."'> ".$codePostal->cp()." - ".$codePostal->libelle()." </option>";
                    }
                    
                    ?>
            
                </select>

            <input type="submit" value="Changer l'adresse" name="submitAdresse">
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

    <?php   
        if ($message==null) {

        }   else{
                echo "alert(\"".$message."\");";
        } 
    ?>

</script>


