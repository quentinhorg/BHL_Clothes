<h1 style="text-align: center;margin-bottom: 45px;">Nous contacter</h1>
<form method="POST">
    <div class="form-group">
        <label for="exampleInputEmail1">Votre nom</label>
        <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Nom" size="30" maxlength="15" name="nom">
    </div>
    <div class="form-group">
        <label for="exampleInputEmail1">Votre mail</label>
        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="exemple@gmail.com" size="30" maxlength="15" name="email">
    </div>
    <div class="form-group">
        <label for="exampleInputPassword1">Votre numéro</label>
        <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Numéro" size="30" maxlength="10" name="tel">
    </div>
    
    <div class="form-group">
        <label for="exampleInputPassword1">Sélectionnez votre problème</label>
        <select class="form-control" name="sujet">
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
    </div>

    <div class="form-group">
        <label for="exampleFormControlTextarea1">Votre message</label>
        <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" placeholder="Message" name="message"></textarea>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary" name="Envoyer" onclick="envoiMessage()" id="envoiMessage">Envoyer</button>
    </div>
</form>


<hr class="contact">

<div class="infoContact">
    <h2 style="margin-bottom: 25px;">Suivez-nous sur les réseaux</h2>
    <ul>
        <li>Facebook</li>
        <li>Instagram</li>
        <li>Twitter</li>
        <li>0262 53 47 89</li>
        <li>bhl.clothes@gmail.com</li>
    </ul>
</div>

<script>
    function envoiMessage() {
        alert("Votre message a bien été envoyé.");
    }

    
    // function ajoutArticle() {
    //     alert("Votre article a bien été ajouté au panier.");
    // }



    // $("#envoiMessage").click(function(){
    //     var form = $("#vetementChoisi");
    //     var serializedData = form.serialize();
    //     $.ajax({
    //         url : "panier",
    //         data : "ajouterArticle=Ok&"+serializedData+"&idVet=<?php // echo $infoVetement->id() ?>",
    //         type : 'POST',
    //         dataType : 'json',
    //         success : function(result) {
    //             $("#qtePanierNav span .nbQte").text(result['totalQtePanier']) ; 
    //         }
    //     });

    // }) ;



</script>