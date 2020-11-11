# BHL_Clothes
E-commerce website, school project. Site de vêtement en ligne pour ados et adulte dans le secteur de La Réunion.

# Installation / Utilisation 

1) Téléchargez la  [dernière version](https://github.com/quentinhorg/BHL_Clothes/releases/latest) du site, et glissez les fichiers dans un dossier où le php est actif (ex: htdocs ou www)
    
2) Pour finir, importez la base de données (MySQL) : 
[bhl_clothes_mysql.sql](https://github.com/quentinhoareau/BHL_Clothes/blob/master/private/bhl_clothes_mysql.sql)
_(N'oubliez pas de changer les identifiants de connexion à la base de données par [ici](https://github.com/quentinhoareau/BHL_Clothes/blob/ec3706c4cda75250fe356e34177baf10b848e058/Modele/DataBase.php#L11-L14))_

3) Vous pouvez les dossiers/fichiers, innutiles pour le fonctionnement du site : 
    - ".gitattributes"
    - "gitignore"
    - ".github"
    - "private"
    
# A savoir
Le site a besoin d'un protocole SMTP pour les envois d'envoi d'email, mais il faudra configurer votre serveur pour permettre l'envoi d'email à l'aide de la fonction php 'mail(...)'.
Le protocole/configuration SMTP d'envoi d'email, n'est pas fourni avec le code source
