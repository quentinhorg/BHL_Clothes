<div id="navigation">

   <nav id="navPrincipale">
         <span id="logo">
            <a href="accueil"> BHL Clothes</a>
         </span>

         <div id="recherche">
            <form action="catalogue" method="POST">
               <input type="search" placeholder="Que cherchez vous ?" name="motCle" id="chercher">
            </form>
         </div>

         <div id="utilisateur">
            
             <?php
                  if($clientEnLigne != null) {
                     echo "<a href='compte&deco=ok'>  <img src='public/media/bhl_clothes/deconnexion.png' alt='Deconnexion' title='Déconnexion' style='width: 23px; margin-right: 3px;
                     margin-top: 3px;'>Déconnexion <a>" ;

                     echo $clientEnLigne->getNom();
                  }
            
                ?>

            <?php 
               if($clientEnLigne != null){
                  ?>
                  <a href="compte"> 
                  <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-person-circle" fill="currentColor">
                     <path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
                     <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                     <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
                  </svg>
               Mon compte 
            </a>
            <?php
               }else{
                  ?>
                  
                  <a href="authentification/connexion" >
                  <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-person-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                     <path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/>
                     <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                     <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
                  </svg>
                     Connexion 
                  </a>
                  <br>
                  <span> <a href="authentification/inscription"> Inscription </a> </span>
                  <?php
               }
               ?>

            <a id='qtePanierNav' href="panier" style="color:#dc5a20"> 
               <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-bag-fill" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4h5v-.5A2.5 2.5 0 0 0 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
               </svg>
               <span> Mon panier (<span class='nbQte'><?php echo $qtePanier ;?></span>) </span>
            </a>
            
         </div>
   </nav>

   <hr>
   <nav id="navCateg">
   <a href="catalogue" class="genreNav">  Parcourir tout le catalogue</a> 
      <?php foreach($listeGenre as $genre){ ?>
         
         <div class="dropdown">
            <a class="genreNav" href="catalogue/<?php echo strtolower($genre->code()); ?>"><?php echo $genre->libelle() ?></a>
            <div class="dropdown-content">
               <?php foreach ($genre->listeCateg() as $categ) { ?>
                  <a href="catalogue/<?php echo strtolower($genre->code()); ?>/<?php echo $categ->id() ?>"><?php echo $categ->nom() ?></a>
               <?php } ?>
            </div>
         </div>
      <?php } ?>
   </nav>
</div>





