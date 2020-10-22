<div class="container padding-bottom-3x mb-1">

        <div class="card mb-3">
          <div class="p-4 text-center text-white text-lg bg-dark rounded-top"><span class="text-uppercase">Suivi de la commande Numéros - </span><span class="text-medium"><?php echo $infoCommande->num() ?></span></div>
          <div class="d-flex flex-wrap flex-sm-nowrap justify-content-between py-3 px-2 bg-secondary">
            <div class="w-100 text-center py-1 px-2"><span class="text-medium">Type de paiement via:</span> <?php echo $infoCommande->typePaiement() ?> </div>
            <div class="w-100 text-center py-1 px-2"><span class="text-medium">Total d'articles:</span> <?php echo $infoCommande->totalArticle() ?> </div>
            <div class="w-100 text-center py-1 px-2"><span class="text-medium">Date d'achat:</span> <span><?php echo $infoCommande->dateCreation() ?> </span> </div>
          </div>
          <div class="card-body">
            <div class="steps d-flex flex-wrap flex-sm-nowrap justify-content-between padding-top-2x padding-bottom-1x">

<?php           $classCompleted = "completed"; $trouver = false; 
            
                foreach ($listeEtat as $etat) {    
                    if($etat->id() == $infoCommande->Etat()->id() ){ 
                        $trouver = true;
                    }
                    else if($trouver == true) {
                        $classCompleted = "" ;
                    } 
?>
                    
                
                    <div class="step <?php echo $classCompleted ?>">
                        <div class="step-icon-wrap">
                        <div class="step-icon">  <i class="<?php echo $etat->classIcon() ?>"></i> </div>
                        </div>
                        <h4 class="step-title"> <?php echo $etat->libelle() ?> </h4>
                    </div>
<?php                }   
?>
            
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap flex-md-nowrap justify-content-center justify-content-sm-between align-items-center">
          <div class="custom-control custom-checkbox mr-3">
     
          </div>
          <a class="btn btn-outline-primary btn-rounded btn-sm" target='_bank' href="facture/<?php echo $infoCommande->num() ?>">Voir la facture</a>
        </div>
      </div>







