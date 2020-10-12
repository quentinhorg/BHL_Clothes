### Nos variables globales
 

| Variables  | Description |
| ------------- | ------------- |
| $GLOBALS["client_en_ligne"]  | Retourne l'object d'un Client en ligne ou NULL si personne n'est connecté: (utilisable sur toutes les pages)  |

---

### Base données :

| Reqûete  | Description |
| ------------- | ------------- |
| SHOW CREATE PROCEDURE myProc  | Affiche la requête de création de la procédure  |
| HOW CREATE FUNCTION myFunc |  Affiche la requête de création de la fonction |

---

### Architecture du projet (dossier) :
_Nous utilisons une architechure MVC_

| Dossier / fichier  | Rôle | Contenu principal |
| ------------- | ------------- | ------------- |
| Modèle (Hors Manager)  |  Gère la modélisation des données sous forme de classe  | Classe des modèles, méthode, attributs, accesseurs... |
| Modèle (Manager) | Prend en charge les données  | Requêtes SQL, données du site, Action sur les données... |
| Vue  | Gère l'affichage des données des pages  | HTML / Design / Contenus ... |
| Controlleur  | Contrôle les données et les vues  | Conditions d'affichage, conditions sur les actions liées à aux données |
| Private  | Contient les fichiers perso du projet  | Tâches / Maquêtes / Cahier des charges ... |

