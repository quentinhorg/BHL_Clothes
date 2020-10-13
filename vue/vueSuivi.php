<section id="suivi">
<h1> Commande numéro : <?php echo $infoCommande->num() ?></h1>


<?php if($infoCommande->Etat()->id() != 1) {?>
    <span> Fait le <?php echo $infoCommande->date() ?> </span>
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

