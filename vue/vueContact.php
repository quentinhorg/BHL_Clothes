<h1 style="text-align: center;margin-bottom: 20px;">Nous contacter</h1>
<p style="margin-bottom: 40px;">Pour tout renseignement commercial sur l’un de nos modèles ou pour tout autre demande,
nos conseillers sont là pour vous répondre.</p>

<div class="contact col">
    <form method="POST">

        <?php 
            $disabled= "";
            $nom="";
            $mail="";
            $tel="";
            if ($GLOBALS["user_en_ligne"] != null) {
                $disabled= "disabled";

                $clientEnLigne = $GLOBALS["user_en_ligne"];
                $nom= $clientEnLigne->nom()." ".$clientEnLigne->prenom();
                $mail=$clientEnLigne->email();
                $tel= $clientEnLigne->tel();
            } ?>


            <div class="form-group">
                <label for="exampleInputEmail1">Nom et prénom</label>
                <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $nom ?>" size="30" maxlength="15" name="nom" <?php echo $disabled ?>>
            </div>
            <div class="form-group mail">
                <label for="exampleInputEmail1">Mail</label>
                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" value="<?php echo $mail ?>" size="30" name="email" <?php echo $disabled ?>>
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Numéro</label>
                <input type="text" class="form-control" id="exampleInputPassword1" value="<?php echo $tel ?>" size="30" maxlength="10" name="tel"  <?php echo $disabled ?>>
            </div>

        
        
        <div class="form-group objet">
            <label for="exampleInputPassword1">Objet</label>
            <select class="form-control" name="sujet">
                <option value="sujet">Sujet</option>

                <option class="categSujet" disabled value="X">PROBLEMES LIES AU SITE</option>
                    <option>Problème lié au compte</option>
                    <option>Identifiants oubliés</option>
                    <option>Problème lié au paiement</option>

                <option class="categSujet" disabled value="X">MA COMMANDE</option>
                    <option>Suivi de commande</option>
                    <option>Retour d'un article</option>
                    <option>Demande de remboursement</option>
                    <option>Question</option>
            </select>
        </div>

        <div class="form-group">
            <label for="exampleFormControlTextarea1">Message</label>
            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3" placeholder="Message" name="message"></textarea>
        </div>
        <div class="bouton" style="text-align: center;">
            <button type="submit" class="btn btn-primary" name="Envoyer" onclick="envoiMessage()" id="envoiMessage">Envoyer</button>
        </div>
    </form>
</div>

