<?php
require('public/FPDF-master/fpdf.php');

// #1 initialise les informations de base
//
// adresse de l'entreprise qui émet la facture
$adresse = "BHL Clothes - Entreprise\n97480 Saint-Joseph\n\nbhl_clothes@gmail.com\n(+33) 3 89 68 27 54";
// adresse du client
$adresseClient = "Nom : ".$client->getNom()."\nPrénom : ".$client->getPrenom()."\nTéléphone : ".$client->getTel()."\nAdresse : ".$client->rue()." ".$client->codePostal().' ('.$listeCp[$client->codePostal()].")";
// initialise l'objet facturePDF
$pdf = new facturePDF($adresse, $adresseClient, "BHL Clothes - Entreprise - 97480 Saint-Joseph - bhl_clothes@gmail.com - (+33) 3 89 68 27 54\nLes produits livrés demeurent la propriété exclusive de notre entreprise jusqu'au paiement complet de la présente facture.\nRCS : 245-532-578- RE / TVA Intracomunautaire : FR 02 4578 1455 5578 3254 / SIRET 887 547 259 974 125");
// défini le logo
$pdf->setLogo('public/media/bhl_clothes/logo.png');
// entete des produits
$pdf->productHeaderAddRow('Article', 87, 'L');
$pdf->productHeaderAddRow('Taille', 20, 'C');
$pdf->productHeaderAddRow('Couleur', 30, 'L');
$pdf->productHeaderAddRow('Quantité', 20, 'C');
$pdf->productHeaderAddRow('Prix', 25, 'R');
// entete des totaux
$pdf->totalHeaderAddRow(40, 'L');
$pdf->totalHeaderAddRow(30, 'R');
// element personnalisé
$pdf->elementAdd('', 'traitEnteteProduit', 'content');
$pdf->elementAdd('', 'traitBas', 'footer');

// #2 Créer une facture
//


// numéro de facture, date, texte avant le numéro de page
$pdf->initFacture("Commande n° ".$commande->num(), "97480 - Saint-Joseph, Fait le ".$commande->datePaye(), "Page ");
// produit

//Liste des articles
foreach ($commande->panier() as $article) {
   $pdf->productAdd(array( $article->nom(), $article->Taille()->libelle(), $article->Couleur()->nom(), $article->qte(), $article->prixTotalArt()));
}

// ligne des totaux
$pdf->totalAdd(array('Type de paiement', $commande->typePaiement()));
$pdf->totalAdd(array('Total article', $commande->totalArticle()));
$pdf->totalAdd(array('Livraison', "0".' EUR'));
$pdf->totalAdd(array('Total TTC', $commande->prixTTC()." EUR") );



// #3 Importe le gabarit
//
// coordonnée de l'entreprise
$pdf->template['header']['fontSize'] = 11;
$pdf->template['header']['lineHeight'] = 5;
$pdf->template['header']['margin'] = array(33, 0, 0, 10);
// numéro de page
$pdf->template['infoPage']['margin'] = array(5, 5, 0, 120);
$pdf->template['infoPage']['align'] = 'R';
// numéro de facture
$pdf->template['infoFacture']['margin'] = array(10, 5, 0, 120);
$pdf->template['infoFacture']['fontSize'] = 15;
$pdf->template['infoFacture']['align'] = 'R';
$pdf->template['infoFacture']['fontFace'] = 'B';
// date
$pdf->template['infoDate']['margin'] = array(15, 5, 0, 100);
$pdf->template['infoDate']['align'] = 'R';
// client
$pdf->template['client']['margin'] = array(35, 0, 0, 120);
// pied de page
$pdf->template['footer']['fontSize'] = 9;
$pdf->template['footer']['color'] = array('r'=>100, 'g'=>100, 'b'=>100);
$pdf->template['footer']['backgroundColor'] = array('r'=>245, 'g'=>245, 'b'=>245);
$pdf->template['footer']['align'] = 'C';
$pdf->template['footer']['margin'] = array(265, 10, 5, 10);
$pdf->template['footer']['padding'] = array(4, 5, 0, 5);
// entete de produit
$pdf->template['productHead']['fontFace'] = 'B';
$pdf->template['productHead']['backgroundColor'] = array('r'=>50, 'g'=>50, 'b'=>50);
$pdf->template['productHead']['color'] = array('r'=>230, 'g'=>230, 'b'=>230);
$pdf->template['productHead']['margin'] = array(20, 0, 0, 10);
$pdf->template['productHead']['padding'] = array(0, 4, 0, 4);
// liste des produit
$pdf->template['product']['backgroundColor'] = array('r'=>235, 'g'=>235, 'b'=>235);
$pdf->template['product']['backgroundColor2'] = array('r'=>245, 'g'=>245, 'b'=>245);
$pdf->template['product']['color'] = array('r'=>0, 'g'=>0, 'b'=>0);
$pdf->template['product']['color2'] = array('r'=>20, 'g'=>20, 'b'=>20);
$pdf->template['product']['margin'] = array(1, 0, 0, 10);
$pdf->template['product']['padding'] = array(1, 4, 1, 4);
// entete des totaux
$pdf->template['totalHead']['lineHeight'] = 0;
$pdf->template['totalHead']['margin'] = array(3, 0, 0, 0);
// liste des totaux
$pdf->template['total']['margin'] = array(1, 0, 1, 130);
// element personnalisé


// #4 Finalisation
// construit le PDF
$pdf->buildPDF();
// télécharge le fichier
$pdf->Output();

?>