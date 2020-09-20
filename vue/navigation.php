<div id="navigation">

   <nav id="navPrincipale">
         <span id="logo">
            <a href="accueil"> BHL Clothes</a>
         </span>

         <div id="recherche">
            <form action="">
               <input type="search" placeholder="Que cherchez vous ?" name="chercher" id="chercher">
            </form>
         </div>

         <div id="utilisateur">
             <?php
                if($clientEnLigne != null) {
                    echo $clientEnLigne->getNom();
                }
                ?>

            <?php 
               if($clientEnLigne != null){
                  ?>
                  <a href="compte"> 
            <svg width="1.6em" height="1.6em" viewBox="0 0 16 16" class="bi bi-person-fill" fill="currentColor" >
               <path fill-rule="evenodd" d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
            </svg>
               Mon compte 
            </a>
            <?php
               }else{
                  ?>
                  <a href="authentification/connexion">Connexion </a>
                  <?php
               }
               ?>

            <a href="panier"> 
               <svg width="1.4em" height="1.4em" viewBox="0 0 16 16" class="bi bi-bag-fill" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4h5v-.5A2.5 2.5 0 0 0 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
               </svg>
               Mon panier (<?php echo $qtePanier ;?>)
            </a>
            
         </div>
   </nav>

   <hr>
   <nav id="navCateg">
   <a href="catalogue" class="genreNav">  Parcourir tout le catalogue</a> 
      <?php foreach($listeGenre as $genre){ ?>
         
         <div class="dropdown">
            <a class="genreNav" href="catalogue/<?php echo strtolower($genre->libelle()); ?>"><?php echo $genre->libelle() ?></a>
            <div class="dropdown-content">
               <?php foreach ($genre->listeCateg() as $categ) { ?>
                  <a href="catalogue/<?php echo strtolower($genre->code()); ?>/<?php echo $categ->id() ?>"><?php echo $categ->nom() ?></a>
               <?php } ?>
            </div>
         </div>
      <?php } ?>
   </nav>
</div>





