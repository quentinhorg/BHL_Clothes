<?php

?>

<?php

        echo "<h2>".$clientActif->getNom()." ".$clientActif->getPrenom()."</h2>";

?>

<div class="tab">
    <button class="lientab" onclick="ouvririnfo(event, 'email')" id="defaut">Email</button>
    <button class="lientab" onclick="ouvririnfo(event, 'mdp')">Mot de passe</button>
    <button class="lientab" onclick="ouvririnfo(event, 'livraison')">Livraison</button>
    <button class="lientab" onclick="ouvririnfo(event, 'commande')">Gérer mes commandes</button>
</div>

<div id="email" class="contenutab">
    <h3>Email</h3>
    <p>Votre email est actuellement *insererPHP*</p>
    <p>Si vous souhaitez le changer <a href="" style="color:blue;text-decoration: underline">cliquez ici</a></p>
</div>

<div id="mdp" class="contenutab">
    <h3>Mot de Passe</h3>
    <p>Votre mot de passe est actuellement *insererPHP*</p>
    <p>Si vous souhaitez le changer <a href="" style="color:blue;text-decoration: underline">cliquez ici</a></p>
</div>

<div id="livraison" class="contenutab">
    <h3>Livraison</h3>
    <p>Votre adresse de livraison est actuellement *insererPHP*</p>
    <p>Si vous souhaitez le changer <a href="" style="color:blue;text-decoration: underline">cliquez ici</a></p>
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

