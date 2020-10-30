<div id="navAdmin" class="sidebar">
  <h5>Espace Admin</h5>
  <a href="admin/vetement">Gérer les vêtements</a>
  <a href="admin/commande">Gérer les commandes</a>
</div>


<script>

var dossierActive = window.location.pathname;
var nomDossierDeIndex = "BHL_Clothes"; // Nom du dossier où se trouve l'index (Dossier qui regroupe tous le projet)
var indexTronquer = dossierActive.indexOf(nomDossierDeIndex)+nomDossierDeIndex.length+1 ;  //Trouver l'index à tronquer de la page active
var hrefActive = dossierActive.substring(indexTronquer); // Deonne le chemain relative du dossier actif

lien = $("#navAdmin").find("a[href='"+hrefActive.toLowerCase()+"']")


lien.addClass("active"); // Ajout d'un style au href possède le chemain actif ( ou page active)


</script>
