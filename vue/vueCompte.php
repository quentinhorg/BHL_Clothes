    
<?php
    echo "<h2>".$clientActif->getNom()." ".$clientActif->getPrenom()."</h2>";
?>

<div class="tab">
    <button class="lientab" onclick="ouvririnfo(event, 'email')" >Email</button>
    <button class="lientab" onclick="ouvririnfo(event, 'mdp')"id="defaut">Mot de passe</button>
    <button class="lientab" onclick="ouvririnfo(event, 'livraison')">Livraison</button>
    <button class="lientab" onclick="ouvririnfo(event, 'commande')">Gérer mes commandes</button>
</div>

<div id="email" class="contenutab">
    <h3>Email</h3>
    <p>Votre email est actuellement <a><?php echo $clientActif->email();?></a> </p> 
    <p>Si vous souhaitez le modifier, entrez votre nouveau mot de passe ci dessous </p>
    <form action="" method="POST">
        <input type="email" name="changeMail" placeholder="Entrez votre nouvelle adresse Email"><br>
        <input type="email" name="changeMail2" placeholder="Confirmez votre nouvelle adresses Email"><br>
        <input type="submit" value="Changer l'adresse Email " name="submitMail">
    </form>
    <?php if ($message==null) {
      
    }else{
    echo $message;
    } ?>
</div>

<div id="mdp" class="contenutab">
    <h3>Mot de passe</h3>
    <p>Si vous souhaitez changer votre mot de passe veuillez saisir ces informations</p> 
    <form action="" method="POST">
        <input type="password" name="ancienMdp" placeholder="Entrez votre ancien mot de passe"><br>
        <input type="password" name="changeMdp" placeholder="Entrez le nouveau mot de passe"><br>
        <input type="password" name="changeMdp2" placeholder="Confirmez le nouveaut mot de passe"><br>
        <input type="submit" value="Changer le mot de passe" name="submitMdp">
    </form>
    <?php if ($message==null) {
      
    }else{
    echo $message;
    } ?>
</div>

<div id="livraison" class="contenutab">
    <h3>Livraison</h3>
    <p>Votre email est actuellement <a><?php echo $clientActif->getAdresse();?></a> </p> 
    <p>Si vous souhaitez le modifier, entrez votre nouveau mot de passe ci dessous </p>
    <form action="" method="POST">
        <input type="password" name="ancienMdp" placeholder="Entrez votre ancien mot de passe"><br>
        <input type="password" name="changeMdp" placeholder="Entrez le nouveau mot de passe"><br>
        <input type="password" name="changeMdp2" placeholder="Confirmez le nouveaut mot de passe"><br>
        <input type="submit" value="Changer le mot de passe" name="submitMdp">
    </form>
    <?php if ($message==null) {
      
    }else{
    echo $message;
    } ?>
</div>

<div id="commande" class="contenutab">
    <h3>Mes Commandes</h3>
    <table>
        <tr>
            <th>Numéro de Commande</th> <th>Détail</th> <th>Date de la commande</th>
        </tr>
        <?php
        foreach ($clientActif->getListCmd() as $commande){
           // var_dump($commande);

            //echo "<td>".$commande->num()."</td> <td></td> <td></td>";
        }

        ?>
    </table>
</div>

<script>
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
        event.currentTarget.className += " active";
    }

    // Obtient l'élément avec l'id="defaut" and click on it
    document.getElementById("defaut").click();
</script>

