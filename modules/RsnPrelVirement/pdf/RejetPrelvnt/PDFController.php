<?php
/*********************************************************************************
 ** ED151104
 *
 ********************************************************************************/

include_once 'modules/Accounts/pdf/LetterToAccount/PDFController.php';
include_once 'modules/RsnPrelVirement/pdf/RejetPrelvnt/ContentViewer.php';
include_once 'modules/RSNBanques/RSNBanques.php';

class Vtiger_RejetPrelvnt_PDFController extends Vtiger_LetterToAccount_PDFController {

	function getContentViewer() {
		$contentViewer = new Vtiger_PDF_RejetPrelvntContentViewer();
		$contentViewer->setContentModels($this->buildContentModels());
		$contentViewer->setSummaryModel($this->buildSummaryModel());
		$contentViewer->setAfterSummaryModel($this->buildAfterSummaryModel());//ED151020
		$contentViewer->setLabelModel($this->buildContentLabelModel());
		$contentViewer->setWatermarkModel($this->buildWatermarkModel());
		return $contentViewer;
	}
	
	function buildContentModels() {
		$contentModels = parent::buildContentModels();
		
		$model = $contentModels[0];
		$model->set('coupon', $this->getContentCoupon());
		
		return $contentModels;
	}
	
	function getContentText(){
		//TODO stocker quelque part
		//TODO bug des indentations en mode Justifié
		$text = 
"	Madame, Monsieur,

	Vous avez autorisé notre association à prélever sur votre compte [[NomDeLaBanque]] numéro [[NuméroCompte]] au nom de [[depositaire]] [[periodicite]], la somme de [[montant]] euros. Nous sommes reconnaissants du soutien que vous nous apportez en nous accordant ce don régulier.

	Cependant, je me permets de vous signaler que votre prélèvement à l’échéance du [[dateexport]] a été rejeté pour le motif suivant : \"[[MotifRejet]]\".
	Afin d'éviter des frais bancaires répétés, merci de me préciser vos intentions pour les prochains prélèvements en me retournant le coupon-réponse ci-dessous par courrier ou par mail.
	Merci de votre compréhension.

	Pour le service de  comptabilité,
	Annie Orenga
";


		$banque = RSNBanques::getEntityNameFromCode($this->focusColumnValue('codebanque'));
		$IBAN = $this->focusColumnValue('sepaibanpays').$this->focusColumnValue('sepaibancle').$this->focusColumnValue('sepaibanbban');
		$periodicite = preg_replace('/^(\D+)(\s\d+)?$/', '$1', $this->focusColumnValue('periodicite'));
		switch($periodicite){
		case 'Annuel':
			$periodicite = 'chaque année';
			break;
		case 'Semestriel':
			$periodicite = 'chaque semestre';
			break;
		case 'Trimestriel':
			$periodicite = 'chaque trimestre';
			break;
		case 'Bimestriel':
			$periodicite = 'tous les deux mois';
			break;
		case 'Mensuel':
			$periodicite = 'chaque mois';
			break;
		case 'Une seule fois':
			$periodicite = 'une fois';
			break;
		default:
			$periodicite = '';
			break;
		}
		
		$montant = str_replace('.', ',', $this->focusColumnValue('montant'));
		
		$imageSignature = 'layouts/vlayout/modules/RSN/resources/images/signature_compta.png';
		
		$mapping = array(
			'NomDeLaBanque' => $banque,
			'montant' => $montant,
			'periodicite' => $periodicite,
			'NuméroCompte' => $IBAN,
			'depositaire' => $this->focusColumnValue('nom'),
			'separum' => $this->focusColumnValue('separum'),
			'dateexport' => $this->focusColumnValue('dateexport'),
			'MotifRejet' => $this->focusColumnValue('rsnprelvirstatus'),
			'image signature' => $imageSignature,
		);
	
		foreach($mapping as $field => $value)
			$text = preg_replace('/\[\[\s*'.preg_quote($field).'\s*\]\]/i', $value, $text);
			
		return $text;
	}
	
	function getContentCoupon(){
		//Attention, HTML strict : fermer les balises proprement
		$text = "<hr/>
		<h3 style=\"text-align: center;\">Souhaitez-vous poursuivre vos dons par prélèvement automatique ?</h3>
<p style=\"font-style: italic;\">Coupon-réponse à renvoyer
<br/>- dans l'enveloppe T jointe, à Réseau \"Sortir du nucléaire\", Parc Benoît - Bâtiment B, 65/69 rue Gorge de Loup 69009 Lyon,
<br/>- ou par mail à annie.orenga@sortirdunucleaire.fr
</p>
<table style=\"width: 100%\">
<tr><td style=\"width: 30mm\">Réf :</td>
	<td style=\"width: 160mm\">[[référence du contact]]</td></tr>
<tr><td style=\"width: 30mm\">Prénom et nom :</td>
	<td style=\"width: 160mm\">[[nom du contact]]</td></tr>
<tr><td style=\"width: 30mm\">Adresse :</td>
	<td style=\"width: 160mm\">[[adresse du contact]]</td></tr>
</table>
<br>
Suite au rejet de mon prélèvement le [[dateexport]], je vous précise que <i>(cochez la case)</i> :
<br>
<table style=\"width: 100%\">
<tr><td style=\"width: 6mm\">O</td>
	<td style=\"width: 180mm\">Vous pouvez continuer à effectuer des prélèvements sur ce compte, il s'agissait d'un rejet accidentel.</td></tr>
<tr><td style=\"width: 6mm\">O</td>
	<td style=\"width: 180mm\">Je vous transmets mes nouvelles coordonnées bancaires et vous autorise à poursuivre les prélèvements sur ce nouveau compte (je joins un RIB)</td></tr>
<tr><td style=\"width: 6mm\">O</td>
	<td style=\"width: 180mm\">Je souhaite mettre fin à mon autorisation de prélèvement, merci de ne plus effectuer de prélèvement sur ce compte</td></tr>
<tr><td style=\"width: 6mm\">O</td>
	<td style=\"width: 180mm\">Autre : </td></tr>
</table>
<div style=\"text-align: center;\">Signature :</div>
";

		$montant = str_replace('.', ',', $this->focusColumnValue('montant'));
		$contact_no = $this->focusColumnValue('contact_no');
		$contact_nom = $this->focusColumnValue('contact_name');
		$contact_address = $this->buildContactAddress();
		
		$mapping = array(
			'dateexport' => $this->focusColumnValue('dateexport'),
			'référence du contact' => $contact_no,
			'nom du contact' => $contact_nom,
			'adresse du contact' => $contact_address,
		);
	
		foreach($mapping as $field => $value)
			$text = preg_replace('/\[\[\s*'.preg_quote($field).'\s*\]\]/i', $value, $text);
			
		return $text;
	}
	function getLetterSubject(){
		return "Rejet de prélèvement périodique";
	}
	function getAfterSummaryContent(){
		return false;
	}
	function buildPagerModel() {
		return false;
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