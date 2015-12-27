<?php

/*
 * Bons de réception fournisseurs exportées depuis Cogilog (menu Analyse / Détails fournisseurs)
 *
 *
 *
Numéro	Date	Code fournisseur	Fournisseur	Code produit	Produit	Prix unitaire ht	Qté	Unité	Remise	Montant ht
1	03/07/2012	TERREV	Terre Vivante		BON DE RÉCEPTION 120001 du 02/07/2012					
1	03/07/2012	TERREV	Terre Vivante		BON DE COMMANDE 120001 du 29/06/2012					
1	03/07/2012	TERREV	Terre Vivante	LMSIM	Ma maison solaire, ici et maintenant	9,0200	20,0	1		180,40
1	03/07/2012	TERREV	Terre Vivante	ZMAF	Arrondis sur facture	-0,0300	1,0	1		-0,03

 */ 


//TODO : end the implementation of this import!
class RSNImportSources_ImportCMDFournFromCogilog_View extends RSNImportSources_ImportFactFournFromCogilog_View {

	protected $potype = 'order';
	protected $potypePrefix = 'CMF';
}