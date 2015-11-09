<?php
/*********************************************************************************
 ** ED151104
 * Lettre de remerciement pour nouveau prélèvement
 ********************************************************************************/

include_once 'modules/Accounts/pdf/LetterToAccount/PDFController.php';
include_once 'modules/RSNBanques/RSNBanques.php';

class Vtiger_RsnPrelevements_PDFController extends Vtiger_LetterToAccount_PDFController {

	function getContentText(){
		//TODO stocker quelque part
		$text = 
"	Bonjour,

	Nous avons bien reçu votre autorisation de prélèvement SEPA de : [[montant]] euros[[periodicite]], sur votre compte [[banque]] [[IBAN]] au nom de [[depositaire]].

	La référence unique de ce mandat (RUM) est : [[separum]]. Notre numéro identifiant Créancier SEPA (ICS) est : [[dest_ICS]] ([[dest_NOM]]). 
	Le prélèvement sera effectué le 8[[mois]].

	Nous vous remercions sincèrement de ce soutien. Il nous permet de compter sur des rentrées d'argent régulières, indispensables pour assurer notre indépendance financière... et donc notre totale liberté de parole et d'action !

	Les prélèvements permettent également de limiter nos dépenses administratives puisque vous ne recevrez plus d'appel à participation financière. Tous les ans, un reçu fiscal récapitulatif du total de vos dons (pour l'année civile) vous est envoyé automatiquement au mois de mars de l'année suivante. Enfin, vous recevez tous nos courriers de campagnes et vous êtes abonné-e gratuitement à la revue trimestrielle \"Sortir du nucléaire\".
	Si vous souhaitez suspendre votre prélèvement automatique, il vous suffit de nous le signaler par simple courrier ou par mail (et en cas d'urgence par téléphone). Votre demande sera prise en compte immédiatement.

	Merci de ne pas adresser de demande d'arrêt à votre banque. En effet, pour réaliser cette opération, les organismes bancaires prélèvent des frais sur votre compte ainsi que sur le nôtre. En vous adressant directement à nous, l'arrêt est réalisé gratuitement.

	En vous remerciant de votre fidélité.

	Cordialement,
	Annie Orenga
";

		$banque = RSNBanques::getEntityNameFromCode($this->focusColumnValue('codebanque'));
		$IBAN = $this->focusColumnValue('sepaibanpays').$this->focusColumnValue('sepaibancle').$this->focusColumnValue('sepaibanbban');
		
		$dest_ICS = $this->organizationDetails['sepa::ics'];
		$dest_nom = $this->organizationDetails['sepa::emetteur'];
		
		$periodicite = preg_replace('/^(\D+)(\s\d+)?$/', '$1', $this->focusColumnValue('periodicite'));
		$mois = preg_replace('/^(\D+)(\s\d+)?$/', '$2', $this->focusColumnValue('periodicite'));
		$months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');//tmp hardcode !!!!
		switch($periodicite){
		case 'Annuel':
			$periodicite = ' par an';
			$mois = ' du mois de ' . $months[(int)$mois -1];
			break;
		case 'Semestriel':
			$periodicite = ' par semestre';
			$mois = ' des mois de ' . $months[(int)$mois -1] .' et ' . $months[(int)$mois -1 + 6];
			break;
		case 'Trimestriel':
			$periodicite = ' par trimestre';
			$mois = ' des mois de ' . $months[(int)$mois -1] .', '. $months[(int)$mois -1 + 3].', '.$months[(int)$mois -1 + 6].' et ' . $months[(int)$mois -1 + 9];
			break;
		case 'Bimestriel':
			$periodicite = ' par bimestre';
			$mois = ' des mois de ' . $months[(int)$mois -1] .', '.$months[(int)$mois -1 + 2].', '.$months[(int)$mois -1 + 4].', '.$months[(int)$mois -1 + 6].', '.$months[(int)$mois -1 + 8].' et ' . $months[(int)$mois -1 + 10];
			break;
		case 'Mensuel':
			$periodicite = ' par mois';
			$mois = ' de chaque mois';
			break;
		case 'Une seule fois':
			$periodicite = '';
			$mois = ' du mois courant ou prochain';
			break;
		default:
			$periodicite = '';
			$mois = ' du mois';
			break;
		}
		
		$montant = str_replace('.', ',', $this->focusColumnValue('montant'));
		
		$mapping = array(
			'montant' => $montant,
			'periodicite' => $periodicite,
			'banque' => $banque,
			'IBAN' => $IBAN,
			'depositaire' => $this->focusColumnValue('nom'),
			'separum' => $this->focusColumnValue('separum'),
			'dest_ICS' => $dest_ICS,
			'dest_NOM' => $dest_nom,
			'mois' => strtolower($mois),
		);
	
		foreach($mapping as $field => $value)
			$text = preg_replace('/\[\[\s*'.preg_quote($field).'\s*\]\]/i', $value, $text);
			
		return $text;
	}
	function getLetterSubject(){
		return "Remerciement";
	}
	function buildPagerModel() {
		return false;
	}
}
?>