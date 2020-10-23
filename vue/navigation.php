<div id="navigation">
<nav class="navbar navbar-expand-sm navbar-light bg-light">
  <a class="navbar-brand" href="accueil"><span id="logo">  BHL Clothes <span> </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
  <form class="form-inline" method="POST">
      <input class="form-control mr-sm-2" type="search" name="motCle" placeholder="Que chercher vous ?" aria-label="Search">
      
      
  </form>
  
  
    <ul class="navbar-nav mr-auto mt-2 mt-lg-0">



     
        <?php  if($clientEnLigne != null){  ?>
            <li class="nav-item">
                <a class="nav-link" href="authentification/deconnexion"> 
                   Deconnexion
                </a>
            </li> 

            <li class="nav-item">
                <a class="nav-link" href="compte"> 
               
                    <?php echo $clientEnLigne->prenom(); ?> 
                </a>
            </li> 
                  
            <li class="nav-item">
                <a class="nav-link" href="compte"> 
                <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-person-circle" fill="currentColor"><path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/><path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/> <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/></svg>
                    Mon compte 
                </a>
            </li> 
        <?php }else{ ?>
            <li class="nav-item">
                <a class="nav-link" href="authentification/connexion" >
                    <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-person-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg"> <path d="M13.468 12.37C12.758 11.226 11.195 10 8 10s-4.757 1.225-5.468 2.37A6.987 6.987 0 0 0 8 15a6.987 6.987 0 0 0 5.468-2.63z"/> <path fill-rule="evenodd" d="M8 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/> <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/> </svg>
                    Connexion 
                </a>
            </li> 
            <li class="nav-item">
                <a class="nav-link" href="authentification/inscription">  <span>Inscription</span> </a> 
            </li> 
                <?php } ?>
     
      <li class="nav-item">
         <a class="nav-link"  id='qtePanierNav' href="panier" style="color:#dc5a20"> 
         <svg width="1.2rem" height="1.2rem" viewBox="0 0 16 16" class="bi bi-bag-fill" fill="currentColor">
         <path fill-rule="evenodd" d="M8 1a2.5 2.5 0 0 0-2.5 2.5V4h5v-.5A2.5 2.5 0 0 0 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5z"/>
         </svg>
         <span> Mon panier (<span class='nbQte'><?php echo $qtePanier ;?></span>) </span>
         </a>
      </li>
    </ul>
    
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


<script>


//str.lastIndexOf("planet");
var dossierActive = window.location.pathname;
var nomDossierDeIndex = "BHL_Clothes"; // Nom du dossier où se trouve l'index (Dossier qui regroupe tous le projet)
var indexTronquer = dossierActive.indexOf(nomDossierDeIndex)+nomDossierDeIndex.length+1 ;  //Trouver l'index à tronquer de la page active
var hrefActive = dossierActive.substring(indexTronquer); // Deonne le chemain relative du dossier actif

lien = $("#navCateg").find("a[href='"+hrefActive.toLowerCase()+"']")


lien.addClass("active"); // Ajout d'un style au href possède le chemain actif ( ou page active)

// BONUS -> Si le lien actif est présent dans un menu déroulant alors on ajoute le style au parent aussi (Le genre)
if(lien.parent().parent().attr("class") == "dropdown" ){
    lien.parent().parent().find("a.genreNav").addClass("active");
}

    

</script>




