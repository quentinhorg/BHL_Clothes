<?php

class FactureManager extends DataBase{

    private $reqBase = "SELECT f.*, 
    (SELECT ch.solde FROM client_histo ch WHERE ch.date_histo = f.datePaiement) AS 'soldeAvantPaiement',  
    (SELECT ROUND(ch.solde-calcCmdTTC(f.numCmd),2) FROM client_histo ch WHERE ch.date_histo = f.datePaiement) AS  'soldeApresPaiement' 
    FROM facture f";
    
    public function getFacture($numCmd){
     
        $req = $this->reqBase." WHERE f.numCmd = ?";
        $this->getBdd();
    
        return @$this->getModele($req, [$numCmd], "Facture")[0];
    }

    public function getListeFacture(){
        $req = $this->reqBase;
        $this->getBdd();
        return $this->getModele($req, ["*"], "Facture");
    }
    
    public function supprimerFacture($numCmd){
        $this->getBdd(); 
        $req = "DELETE FROM facture WHERE numCmd = ?" ;
        $this->execBdd($req, [$numCmd]);
    }
}

?>