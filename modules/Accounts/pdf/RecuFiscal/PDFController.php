<?php
/*********************************************************************************
 ** ED151104
 *
 ********************************************************************************/

include_once 'modules/Accounts/pdf/LetterToAccount/PDFController.php';
include_once 'modules/Accounts/pdf/RecuFiscal/HeaderViewer.php';
include_once 'modules/Accounts/pdf/RecuFiscal/FooterViewer.php';
include_once 'modules/Accounts/pdf/RecuFiscal/ContentViewer.php';

class Vtiger_RecuFiscal_PDFController extends Vtiger_LetterToAccount_PDFController {

	function getContentViewer() {
		$contentViewer = new Vtiger_PDF_RecuFiscalContentViewer();
		$contentViewer->setContentModels($this->buildContentModels());
		$contentViewer->setSummaryModel($this->buildSummaryModel());
		$contentViewer->setAfterSummaryModel($this->buildAfterSummaryModel());//ED151020
		$contentViewer->setLabelModel($this->buildContentLabelModel());
		$contentViewer->setWatermarkModel($this->buildWatermarkModel());
		return $contentViewer;
	}

	function getHeaderViewer() {
		$headerViewer = new Vtiger_PDF_RecuFiscalHeaderViewer();
		$headerViewer->setModel($this->buildHeaderModel());
		return $headerViewer;
	}

	function getFooterViewer() {
		$footerViewer = new Vtiger_PDF_RecuFiscalFooterViewer();
		$model = $this->buildFooterModel();
		if(!$model)
			return false;
		$footerViewer->setModel($model);
		$footerViewer->setLabelModel($this->buildFooterLabelModel());
		$footerViewer->setOnLastPage();
		return $footerViewer;
	}
	
	function buildContentModels() {
		$contentModels = parent::buildContentModels();
		
		$model = $contentModels[0];
		$model->set('recu_fiscal', $this->getContentRecuFiscal());
		
		return $contentModels;
	}
	
	function buildHeaderBillingAddress() {
		
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('accountid'), 'Accounts');
		
		//Contrôle du champ "use_address2_for_recu_fiscal" disponible dans le contact
		if($this->focusColumnValue('use_address2_for_recu_fiscal')){
			$street2 = $this->focusColumnValue('otherstreet2');
			$addressFormat = $this->focusColumnValue('otheraddressformat');
			$poBox	= $this->focusColumnValue('otherpobox');
			$street = $this->focusColumnValue('otherstreet');
			$street3 = $this->focusColumnValue('otherstreet3');
			$zipCode =  $this->focusColumnValue('otherzip'); 
			$city	= $this->focusColumnValue('othercity');
			$state	= $this->focusColumnValue('otherstate');
			$country = $this->focusColumnValue('othercountry');
		}
		else {	//adresse principale synchronisée dans le compte
			$street2 = $this->focusColumnValue('bill_street2');
			$addressFormat = $this->focusColumnValue('bill_addressformat');
			$poBox	= $this->focusColumnValue('bill_pobox');
			$street = $this->focusColumnValue('bill_street');
			$street3 = $this->focusColumnValue('bill_street3');
			$zipCode =  $this->focusColumnValue('bill_code'); 
			$city	= $this->focusColumnValue('bill_city');
			$state	= $this->focusColumnValue('bill_state');
			$country = $this->focusColumnValue('bill_country');
		}
		return $this->buildAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress);
	}
	
	function getContentText(){
		//TODO stocker quelque part
		$text = 
"	Madame, Monsieur

	Vous trouverez ci-dessous le reçu fiscal correspondant aux dons que vous nous avez fait parvenir pour l'année [[année]]. Association libre et indépendante, le Réseau \"Sortir du nucléaire\" est financé exclusivement grâce aux dons et cotisations de ses membres. C'est donc grâce à votre aide que nous pouvons poursuivre nos actions et notre travail d'information. Nous vous remercions de votre généreuse présence à nos côtés.
	
	Si vous êtes soumis à l'impôt sur le revenu, que vous soyez un particulier ou une entreprise, vous pouvez déduire 66 % du montant de votre don de vos impôts sur le revenu (ou les bénéfices). Ainsi un don de 50 euros ne vous aura coûté finalement que 17 euros. En cas de dépassement du plafond (20 % du revenu imposable), l'excédent est reportable sur l'année suivante et jusqu'à 5 ans.
	
	Notre association n'envoie qu'un seul reçu fiscal récapitulatif par an, au mois de mars. Cependant, en cas de perte ou d'erreur de notre part, vous pouvez nous demander un duplicata ou un reçu rectifié. Notez qu'en respect de la loi, les reçus sont établis uniquement pour les dons, et non pas pour les abonnements ou l'achat de matériel. Pour toute question, n'hésitez pas à nous contacter au [[téléphone]] ou par mail à [[email]].
	
	Merci d'avoir contribué à notre action commune pour sortir du nucléaire.
	
	Cordialement,	
	L'équipe du Réseau \"Sortir du nucléaire\"
";

		$annee = $this->focusColumnValue('year');
		$montant = str_replace('.', ',', $this->focusColumnValue('montant'));
		$phone = $this->organizationDetails['phone'];
		$email = $this->organizationDetails['recufiscal::email'];
		
		$mapping = array(
			'année' => $annee,
			'téléphone' => $phone,
			'email' => $email,
			'montant' => $montant,
		);
	
		foreach($mapping as $field => $value)
			$text = preg_replace('/\[\[\s*'.preg_quote($field).'\s*\]\]/i', $value, $text);
			
		return $text;
	}
	
	function getContentRecuFiscal(){
		//Attention, HTML strict : fermer les balises proprement
		//✁ ne s'imprime pas
		$cancel_and_replace = $this->focusColumnValue('cancel_and_replace');

		$text = "<hr/>
<table style=\"width: 100%\" border=1><tr><td>
	<table><tr>
		<td style=\"width: 2mm\"> </td>
		<td style=\"width: 35mm; font-size: 9;\">Selon modèle Cerfa
			<br/>n° 11580-03
		</td>
		<td style=\"width: 105mm; text-align: center; font-size: 11;\"><b>Reçu pour déductibilité fiscale des dons</b>";

		if ($cancel_and_replace) {
			$text .= "<br/><b style=\"font-size: 9\">Annule et remplace le recu fiscal [[année]]/[[cancel_and_replace]].</b>";
		}

		$text .= "<div style=\"font-size: 9\">Articles 200-5, 238 bis et 885-0 V bis A du code Général des Impôts</div>
		</td>
		<td style=\"text-align: right; width: 40mm;\">Reçu [[année]]/[[n° reçu]] 
		</td>
		<td style=\"width: 2mm\"> </td>
	</tr></table>
</td></tr></table>

<h4>Bénéficiaire du don :</h4>
<table style=\"width: 100%\"><tr>
	<td style=\"width: 5mm\"> </td>
	<td style=\"width: 170mm\"><b>Réseau <i>\"Sortir du nucléaire\"</i></b>, 9 rue Dumenge, 69317 Lyon Cedex 04 - <i>Siret n° 418 092 094 00014</i></td>
</tr></table>

<table style=\"width: 100%\"><tr>
	<td style=\"width: 10mm\"> </td>
	<td style=\"width: 170mm; font-size: 9;\"><b>Objet social</b> : Engager toutes les réflexions et actions permettant à la France de sortir du nucléaire et notamment en
promouvant une autre politique énergétique.Association régie par la loi de 1901 : Organisme d'intérêt général, de caractère
éducatif et scientifique, non reconnu d'utilité publique par un décret du Journal Officiel.</td>
</tr></table>

<h4>Donateur : </h4>
<table style=\"width: 100%\"><tr>
	<td style=\"width: 5mm\"> </td>
	<td style=\"width: 160mm\"><b>[[nom du contact]]</b> [[adresse du contact]]</td>
	<td style=\"width: 15mm; text-align: right; font-size: small;\">[[référence du contact]]</td>
</tr></table>
<br/>
<b>Le Réseau <i>\"Sortir du nucléaire\"</i> reconnaît que les dons reçus</b>, en un ou plusieurs versements, au cours de
l'année [[année]], <b>pour la somme totale de</b> :
<div style=\"text-align: center;\"><b>[[montant]] euros</b>, soit <b>[[montant en toutes lettres]]</b></div>
<br>ouvrent droit à la réduction d'impôts prévue à l'article [[article CGI]] du Code Général des Impôts.
<div style=\"text-align: center\">Mode de paiement : chèque ou virement</div>
<br/>
<table style=\"width: 100%\"><tr>
	<td>Lyon, le [[date]]</td>
	<td style=\"text-align: center\">Le trésorier, <img src=\"[[image signature]]\" height=\"20mm\"/></td>
</tr></table>";

		$annee = $this->focusColumnValue('year');
		$montant = str_replace('.', ',', $this->focusColumnValue('montant'));
		$montant_lettres = montant_en_toutes_lettres((float)$this->focusColumnValue('montant'));
		$months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');//tmp hardcode !!!!
		$date = $this->focusColumnValue('recu_fiscal_date');
		if(!$date)
			$date = date('j') . ' ' . strtolower($months[(int)date('m') - 1]) . ' ' . $annee;
		$contact_no = $this->focusColumnValue('contact_no');
		$contact_nom = $this->resolveReferenceLabel($this->focusColumnValue('accountid'), 'Accounts');
		$contact_address = $this->buildContactAddress();
		$recufiscal_num = $this->focusColumnValue('recu_fiscal_num');; //TODO
		
		if($this->focusColumnValue('isgroup') > 0)
			$articleCGI = '238 bis du CGI (entreprises & asociations)';
		else
			$articleCGI = '200 du CGI (Particuliers)';
		$imageSignature = 'layouts/vlayout/modules/RSN/resources/images/signature_tresorier.png';
		
		$mapping = array(
			'année' => $annee,
			'n° reçu' => $recufiscal_num,
			'référence du contact' => $contact_no,
			'nom du contact' => $contact_nom,
			'adresse du contact' => $contact_address,
			'montant en toutes lettres' => $montant_lettres,
			'montant' => $montant,
			'date' => $date,
			'article CGI' => $articleCGI,
			'image signature' => $imageSignature,
			'cancel_and_replace' => $cancel_and_replace,
		);
	
		foreach($mapping as $field => $value)
			$text = preg_replace('/\[\[\s*'.preg_quote($field).'\s*\]\]/i', $value, $text);
			
		return $text;
	}
	function getLetterSubject(){
		return false;
	}
	function getAfterSummaryContent(){
		return false;
	}
	function buildPagerModel() {
		return false;
	}
	
	function buildHeaderModelColumnLeft() {
		// Company information
		$organization = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$this->organizationDetails = $resultrow = $organization->getData();
		
		$addressValues = array();
		$addressValues[] = $resultrow['address'];
		if(!empty($resultrow['code'])) $addressValues[]= "\n".$resultrow['code'];
		if(!empty($resultrow['city'])) $addressValues[]= $resultrow['city'];
		
		if(!empty($resultrow['recufiscal::header_text']))		$additionalCompanyInfo[]= "\n\n".$resultrow['recufiscal::header_text'];
		elseif(!empty($resultrow['lettertoaccount::header_text']))		$additionalCompanyInfo[]= "\n".$resultrow['lettertoaccount::header_text'];

		if($resultrow['print_logoname'])
			$logoFile = "test/logo/".$resultrow['print_logoname'];
		else
			$logoFile = "test/logo/".$resultrow['logoname'];
		$modelColumnLeft = array(
				'logo' => $logoFile,
				'summary' => decode_html($resultrow['organizationname']),
				'content' => decode_html($this->joinValues($addressValues, ' '). $this->joinValues($additionalCompanyInfo, ' '))
		);
		return $modelColumnLeft;
	}
	
	
	
	function buildContactAddress() {
		$contactName = '';//$this->resolveReferenceLabel($this->focusColumnValue('accountid'), 'Accounts');
		$street2 = $this->focusColumnValue('bill_street2');
		$addressFormat = $this->focusColumnValue('bill_addressformat');
		$poBox	= $this->focusColumnValue('bill_pobox');
		$street = $this->focusColumnValue('bill_street');
		$street3 = $this->focusColumnValue('bill_street3');
		$zipCode =  $this->focusColumnValue('bill_code'); 
		$city	= $this->focusColumnValue('bill_city');
		$state	= $this->focusColumnValue('bill_state');
		$country = $this->focusColumnValue('bill_country');   
		
		return $this->buildOneLineAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress);
	}

	//ED151020
	function buildOneLineAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress) {
		$address = '';
		switch($formatAddress){
		case 'CN1' : //street2 + name
			$address = $street2 . "\n" . $contactName;
			break;
		case 'C1' : //street2 without name
			$address = $street2;
			break;
		case 'N1' : //name without street2
			$address = $contactName;
			break;
		case 'NC1' ://name + street2
		default:
			$address = $contactName . "\n" . $street2;
			break;
		}
		//ED151006
		if($street3 && $pobox)
			$street3 .= $this->joinValues(array($street3, $pobox), ', ');
		elseif(!$street3 && $pobox)
			$street3 .= $this->joinValues(array($street3, $pobox), ', ');
		
		$address .= "\n$street\n$street3\n$zipCode $city";
		if($country && $country != 'France')//ED151006 // TODO France en constante	
			$address .= "\n".$country;
		
		return preg_replace('/(\s)\s*/', " ", preg_replace('/^\s+|\s*(\n)\s*|\s+$/', " ", $address));
	}
}
?>