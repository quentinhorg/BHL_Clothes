<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
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
                        <div class="step-icon">  <span class="glyphicon glyphicon-shopping-cart"></span> </div>
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
            <input class="custom-control-input" type="checkbox" id="notify_me" checked="">
            <label class="custom-control-label" for="notify_me">Notify me when order is delivered</label>
          </div>
          <div class="text-left text-sm-right"><a class="btn btn-outline-primary btn-rounded btn-sm" href="orderDetails" data-toggle="modal" data-target="#orderDetails">View Order Details</a></div>
        </div>
      </div>







<section id="suivi">
<h1> Commande numéro : <?php echo $infoCommande->num() ?></h1>


<?php if($infoCommande->Etat()->id() != 1) {?>
    <span> Fait le <?php echo $infoCommande->dateCreation() ?> </span>
<div>
    <div >
        <div class="hh-grayBox">
            <div class="justify-content-between">

            <?php 
               $classCompleted = "completed";
                $trouver = false;

                    foreach ($listeEtat as $etat) {   
                        if($etat->id() == $infoCommande->Etat()->id() ){ 
                            $trouver = true;
                        }
                        else if($trouver == true) {
                            $classCompleted = "" ;
                        } ?>
                        
                        <div class='order-tracking <?php echo $classCompleted; ?>'>
                      
                    <span class="is-complete"></span>
                    <p> <?php echo $etat->libelle() ?> <br>
                    <span>  <?php if($etat->id() == $infoCommande->Etat()->id() ){ 
                            echo $etat->description(); 
                        }?>
                    </span>
                    </p>
                </div>

                
            <?php } ?>
            </div>
        </div>
    </div>
</div>
 <?php }
 
 else{ echo "<p>".$infoCommande->Etat()->description()."</p>" ;}
 
 ?>

<a href="compte#commande">Gérer mes commandes</a>

</section>

