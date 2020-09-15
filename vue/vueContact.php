<div class="formContact">
    <h1>Nous contacter</h1>

    <form action="" method="POST">
        <!--creation des champs-->
        <input type="text" placeholder="Nom" size="30" maxlength="15" name="nom" /> <br>
        <input type="email" placeholder="Adresse mail" size="30" maxlength="15" name="email" /> <br>
        <input type="text" placeholder="Numéro" size="30" maxlength="10" name="tel" /> <br>

        <!--creation de la liste déroulante-->
        <select name="sujet">

            <option value="sujet">Sujet</option>

            <option class="categSujet" value="probleme">PROBLEMES LIES AU SITE</option>
                <option value="compteVole">Compte volé</option>
                <option value="idOublie">Identifiants oubliés</option>

            <option class="categSujet" value="commande">MA COMMANDE</option>
                <option value="suiviCommande">Suivi de commande</option>
                <option value="retourVet">Retour d'un article</option>
                <option value="questionVet">Question sur un article</option>
                <option value="remboursement">Demande de remboursement</option>
        </select>
        <br>

        <textarea name="message" placeholder="Votre message" name="message"></textarea> <!--creation de la zone de texte--> <br>
        <input type="submit" name="Envoyer" class="bouton"> <!--creation du bouton envoyer-->
    </form>
</div>

<hr class="contact">

<div class="infoContact">
    <h2>Suivez-nous sur les réseaux</h2>
    <ul>
        <li>Facebook</li>
        <li>Instagram</li>
        <li>Twitter</li>
        <li>0262 53 47 89</li>
        <li>bhl.clothes@gmail.com</li>
    </ul>
</div>
