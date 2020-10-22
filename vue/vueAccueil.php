<div class="genre">

    <?php foreach ($listeGenre as $genre) { ?>
        
            <div class="blocGenre imgGenre">
                <a href="catalogue/<?php echo $genre->code(); ?>"> <img src="public/media/accueil/modele/<?php echo $genre->code(); ?>.jpg" alt="" class="col"> </a>

                    <div class="textGenre"> <?php echo "<a href='catalogue/".$genre->code()."'>".$genre->libelle()."</a>"; ?> </div>
               
            </div>
        
    <?php }  ?>
    
</div>





<div>
    <div class="container mt-100 mt-60">
        
        <div class="row">
            <div class="col-12 text-center">
                <div class="section-title mb-4 pb-2">
                    <h4 class="title mb-4">Nos dernières nouveautés</h4>
                </div>
            </div><!--end col-->
        </div><!--end row-->
        <div class="row">
        <?php  foreach($nouvVetement as $vetement){ ?>

                <div class="col-lg-4 col-md-6 mt-4 pt-2">
                    <div class="blog-post rounded border" style='height: 100%;'>
                        <div class="blog-img d-block overflow-hidden position-relative">
                            <a href="vetement/<?php echo $vetement->id() ?>"><img src="public/media/vetement/id<?php echo $vetement->id() ?>.jpg" class="img-fluid rounded-top" alt=""></a>
                        </div>
                        <div class="content p-3" >
                            <small class="text-muted p float-right" style="color: black!important;font-size: 20px;"><?php echo $vetement->prix();?>€</small>
                            <small><a href="catalogue/<?php  echo $vetement->genre()->code()."/". $vetement->categ()->id() ;?>" class="text-primary" style="color: grey!important;text-decoration: underline;"><?php echo $vetement->categ()->nom();?></a></small>

                            <h4 class="mt-2"><a href="vetement/<?php echo $vetement->id() ?>" clas  s="text-dark title"><?php echo $vetement->nom();?></a></h4>
                            <p class="text-muted mt-2" style="color: black!important;"><?php echo $vetement->description();?></p>
                            
                        </div>
                    </div><!--end blog post-->
                </div><!--end col-->   
    <?php } ?>
        </div><!--end row-->
    </div>
</div>