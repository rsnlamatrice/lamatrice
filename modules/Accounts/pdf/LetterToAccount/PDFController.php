<?php
/*********************************************************************************
 ** ED151104
 *
 ********************************************************************************/

include_once 'vtlib/Vtiger/PDF/models/Model.php';
include_once 'modules/Accounts/pdf/LetterToAccount/HeaderViewer.php';
include_once 'modules/Accounts/pdf/LetterToAccount/FooterViewer.php';
include_once 'modules/Accounts/pdf/LetterToAccount/ContentViewer.php';
include_once 'vtlib/Vtiger/PDF/viewers/PagerViewer.php';
include_once 'vtlib/Vtiger/PDF/PDFGenerator.php';
include_once 'data/CRMEntity.php';
include_once 'modules/Settings/Vtiger/models/CompanyDetails.php';

class Vtiger_LetterToAccount_PDFController {

	protected $module;
	protected $focus = null;
	
	function __construct($module) {
		$this->moduleName = $module;
	}

	function loadRecord($id) {
		global $current_user;
		$this->focus = $focus = CRMEntity::getInstance($this->moduleName);
		$focus->retrieve_entity_info($id,$this->moduleName);
		$focus->apply_field_security($this->moduleName);
		$focus->id = $id;
		
		if($this->moduleName === 'Accounts'){
			$focus->column_fields['accountid'] = $accountId = $id;
		}else{
			$accountId = $focus->column_fields['accountid'];
			if(!$accountId)
				$accountId = $focus->column_fields['account_id'];
			if($accountId){
				
				$accountFocus = CRMEntity::getInstance('Accounts');
				$accountFocus->retrieve_entity_info($accountId, 'Accounts');
				
				$focus->column_fields = array_merge($focus->column_fields, $accountFocus->column_fields);
			}
			
		}
		
		if($this->moduleName !== 'Contacts'){
			$contactId = $focus->column_fields['contactid'];
			if(!$contactId)
				$contactId = $focus->column_fields['contact_id'];
			if(!$contactId){
				$accountRecordModel = Vtiger_Record_Model::getInstanceById($accountId, 'Accounts');
				$contactRecordModel = $accountRecordModel->getRelatedMainContact();
				$contactId = $contactRecordModel->getId();
			}
			if($contactId){
				$contactFocus = CRMEntity::getInstance('Contacts');
				$contactFocus->retrieve_entity_info($contactId, 'Contacts');
				
				$focus->column_fields = array_merge($focus->column_fields, $contactFocus->column_fields);
			}
		}
	}

	function getPDFGenerator() {
		return new Vtiger_PDF_Generator();
	}

	function getContentViewer() {
		$contentViewer = new Vtiger_PDF_LetterToAccountContentViewer();
		$contentViewer->setContentModels($this->buildContentModels());
		$contentViewer->setSummaryModel($this->buildSummaryModel());
		$contentViewer->setAfterSummaryModel($this->buildAfterSummaryModel());//ED151020
		$contentViewer->setLabelModel($this->buildContentLabelModel());
		$contentViewer->setWatermarkModel($this->buildWatermarkModel());
		return $contentViewer;
	}

	function getHeaderViewer() {
		$headerViewer = new Vtiger_PDF_LetterToAccountHeaderViewer();
		$headerViewer->setModel($this->buildHeaderModel());
		return $headerViewer;
	}

	function getFooterViewer() {
		$footerViewer = new Vtiger_PDF_LetterToAccountFooterViewer();
		$model = $this->buildFooterModel();
		if(!$model)
			return false;
		$footerViewer->setModel($model);
		$footerViewer->setLabelModel($this->buildFooterLabelModel());
		$footerViewer->setOnLastPage();
		return $footerViewer;
	}

	function getPagerViewer() {
		$pagerViewer = new Vtiger_PDF_PagerViewer();
		$pagerViewer->setModel($this->buildPagermodel());
		return $pagerViewer;
	}

	function Output($filename, $type) {
		if(is_null($this->focus)) return;

		$pdfgenerator = $this->getPDFGenerator();

		$pdfgenerator->setPagerViewer($this->getPagerViewer());
		$pdfgenerator->setHeaderViewer($this->getHeaderViewer());
		$pdfgenerator->setFooterViewer($this->getFooterViewer());
		$pdfgenerator->setContentViewer($this->getContentViewer());

		$pdfgenerator->generate($filename, $type);
	}


	// Helper methods
	
	function buildContentModels() {
		
		$contentModel = new Vtiger_PDF_Model();
		
		$text = 'Lyon, le ' . date("j") . ' ' . strtolower(vtranslate('LBL_' . date("M"))) . ' ' . date("Y");
		$text = $this->purifyText($text);
		$contentModel->set('date', $text);
		
		$text = $this->getLetterSubject();
		if($text){
			$text = $this->purifyText('Objet : ' . $text);
			$contentModel->set('subject', $text);
		}
		
		$text = $this->getContactReference();
		if($text){
			$text = $this->purifyText('Votre référence : ' . $text);
			$contentModel->set('reference', $text);
		}
		
		$text = $this->getContentText();
		$text = $this->purifyText($text);
		$contentModel->set('text', $text);
		
		$contentModels[] = $contentModel;
		
		return $contentModels;
	}
	
	//ABSTRACT
	function getContentText(){
		
	}
	
	//ABSTRACT
	function getLetterSubject(){
		
	}
	
	function getContactReference(){
		return $this->focusColumnValues(array('contact_no'));
	}
	
	function purifyText($text){
		$text = decode_html($text);
		$text = vtlib_purify($text);
		$text = str_replace("\t", '      ', $text);
		$text = str_replace("\r\n", "\n", $text);
		//éviter le saut de ligne à cause d'un espace avant % ou €. TODO le char(160) n'est pas conservé comme tel dans tcpdf.php
		//$text = preg_replace('/ ([%€])/', chr(160).'$1', $text);
		return $text;
	}

	function buildContentLabelModel() {
		$labelModel = new Vtiger_PDF_Model();
		$labelModel->set('text', '');
		return $labelModel;
	}

	function buildSummaryModel() {
		return false;
	}

	function buildHeaderModel() {
		$headerModel = new Vtiger_PDF_Model();
		//$headerModel->set('title', $this->buildHeaderModelTitle());
		$modelColumns = array($this->buildHeaderModelColumnLeft(), $this->buildHeaderModelColumnCenter(), $this->buildHeaderModelColumnRight());
		$headerModel->set('columns', $modelColumns);
		
		$modelAddress = $this->buildHeaderDestinationAddress();
		$headerModel->set('destinationAddress', $modelAddress);

		return $headerModel;
	}

	function buildHeaderModelTitle() {
		return $this->moduleName;
	}

	function buildHeaderModelColumnLeft() {
		//global $adb;

		// Company information
		$organization = Settings_Vtiger_CompanyDetails_Model::getInstance();
		//$result = $adb->pquery("SELECT * FROM vtiger_organizationdetails", array());
		//$num_rows = $adb->num_rows($result);
		//if($num_rows) {
		//	$resultrow = $adb->fetch_array($result);
		//	//ED151020
		//	$this->organizationDetails = $resultrow;
			//ED151104
			$this->organizationDetails = $resultrow = $organization->getData();
			
			$addressValues = array();
			$addressValues[] = $resultrow['address'];
			if(!empty($resultrow['code'])) $addressValues[]= "\n".$resultrow['code'];
			if(!empty($resultrow['city'])) $addressValues[]= $resultrow['city'];
			//if(!empty($resultrow['state'])) $addressValues[]= ",".$resultrow['state'];
			//if(!empty($resultrow['country'])) $addressValues[]= "\n".$resultrow['country'];


			if(!empty($resultrow[strtolower($this->moduleName).'::header_text']))		$additionalCompanyInfo[]= "\n\n".$resultrow[strtolower($this->moduleName).'::header_text'];
			elseif(!empty($resultrow['lettertoaccount::header_text']))		$additionalCompanyInfo[]= "\n\n".$resultrow['lettertoaccount::header_text'];
			
			if(!empty($resultrow[strtolower($this->moduleName).'::phone']))		$additionalCompanyInfo[]= "\n".getTranslatedString("Phone: ", $this->moduleName). $resultrow[strtolower($this->moduleName).'::phone'];
			elseif(!empty($resultrow['phone']))		$additionalCompanyInfo[]= "\n".getTranslatedString("Phone: ", $this->moduleName). $resultrow['phone'];
			if(!empty($resultrow['fax']))		$additionalCompanyInfo[]= "\n".getTranslatedString("Fax: ", $this->moduleName). $resultrow['fax'];
			if(!empty($resultrow['website']))	$additionalCompanyInfo[]= "\n".getTranslatedString("Website: ", $this->moduleName). $resultrow['website'];

			//ED151020 Test a file name with -print pre-extension
			/*$logoFile = "test/logo/". pathinfo($resultrow['logoname'], PATHINFO_FILENAME) . '-print.' . pathinfo($resultrow['logoname'], PATHINFO_EXTENSION);
			global $root_directory;
			if(!file_exists($root_directory . $logoFile))
				$logoFile = "test/logo/".$resultrow['logoname'];*/
			if($resultrow['print_logoname'])
				$logoFile = "test/logo/".$resultrow['print_logoname'];
			else
				$logoFile = "test/logo/".$resultrow['logoname'];
			$modelColumnLeft = array(
					'logo' => $logoFile,
					'summary' => decode_html($resultrow['organizationname']),
					'content' => decode_html($this->joinValues($addressValues, ' '). $this->joinValues($additionalCompanyInfo, ' '))
			);
			
		//}
		//else
		//	$modelColumnLeft = array();
		return $modelColumnLeft;
	}

	function buildHeaderModelColumnCenter() {
		
		return false;
	}

	function buildHeaderModelColumnRight() {
		return false;
	}
	
	/** ED151020
	 * Bloc fixe contenant l'adresse de destination
	 */
	function buildHeaderDestinationAddress(){
		$billingAddressLabel = getTranslatedString('Address', $this->moduleName);
		$model = array(
			$billingAddressLabel  => $this->buildHeaderBillingAddress(),
		);
		return $model;
	}

	function buildFooterModel() {
		return false;
	}

	function buildFooterLabelModel() {
		$labelModel = new Vtiger_PDF_Model();
		return $labelModel;
	}

	function buildPagerModel() {
		$footerModel = new Vtiger_PDF_Model();
		$footerModel->set('format', '-%s-');
		return $footerModel;
	}

	function getWatermarkContent() {
		return '';
	}

	function buildWatermarkModel() {
		$watermarkModel = new Vtiger_PDF_Model();
		$watermarkModel->set('content', $this->getWatermarkContent());
		return $watermarkModel;
	}
	//ED151020
	function getAfterSummaryContent(){
		$text = $this->organizationDetails[strtolower($this->moduleName).'::lastpage_footer_text'];
		if($text)
			return $text;
		return $this->organizationDetails['lettertoaccount::lastpage_footer_text'];
	}
	function buildAfterSummaryModel() {
		$model = new Vtiger_PDF_Model();
		$text = $this->purifyText($this->getAfterSummaryContent());
		$model->set('content', $text);
		return $model;
	}
	
	function buildHeaderBillingAddress() {
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('accountid'), 'Accounts');
		$street2 = $this->focusColumnValue('bill_street2');
		$addressFormat = $this->focusColumnValue('bill_addressformat');
		$poBox	= $this->focusColumnValue('bill_pobox');
		$street = $this->focusColumnValue('bill_street');
		$street3 = $this->focusColumnValue('bill_street3');
		$zipCode =  $this->focusColumnValue('bill_code'); 
		$city	= $this->focusColumnValue('bill_city');
		$state	= $this->focusColumnValue('bill_state');
		$country = $this->focusColumnValue('bill_country');   
		
		return $this->buildAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress);
	}

	//ED151020
	function buildAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress) {
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
			$street3 .= $this->joinValues(array($street3, $pobox), '  ');
		elseif(!$street3 && $pobox)
			$street3 .= $pobox;
		
		$address .= "\n$street\n$street3\n$zipCode $city";
		if($country && strcasecmp($country, $this->organizationDetails['country']) !== 0)//ED151006
			$address .= "\n".$country;
		
		return preg_replace('/^\s+|\s*(\n)\s*|\s+$/', "\n", $address);
	}

	function buildCurrencySymbol() {
		global $adb;
		$currencyId = $this->focus->column_fields['currency_id'];
		if(!empty($currencyId)) {
			$result = $adb->pquery("SELECT currency_symbol FROM vtiger_currency_info WHERE id=?", array($currencyId));
			return decode_html($adb->query_result($result,0,'currency_symbol'));
		}
		return false;
	}

	function focusColumnValues($names, $delimeter="\n") {
		if(!is_array($names)) {
			$names = array($names);
		}
		$values = array();
		foreach($names as $name) {
			$value = $this->focusColumnValue($name, false);
			if($value !== false) {
				$values[] = $value;
			}
		}
		return $this->joinValues($values, $delimeter);
	}

	function focusColumnValue($key, $defvalue='') {
		$focus = $this->focus;
		if(isset($focus->column_fields[$key])) {
			return decode_html($focus->column_fields[$key]);
		}
		return $defvalue;
	}

	function setColumnValue($key, $value) {
		$focus = $this->focus;
		$focus->column_fields[$key] = $value;
		return $this;
	}

	function resolveReferenceLabel($id, $module=false) {
		if(empty($id)) {
			return '';
		}
		if($module === false) {
			$module = getSalesEntityType($id);
		}
		$label = getEntityName($module, array($id));
		return decode_html($label[$id]);
	}

	//ED151020
	function resolveReferenceFieldValue($id, $module=false, $fieldName) {
		if(empty($id)) {
			return '';
		}
		if($module === false) {
			$module = getSalesEntityType($id);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $module);
		return decode_html($recordModel->getDisplayValue($fieldName));
	}

	function joinValues($values, $delimeter= "\n") {
		$valueString = '';
		foreach($values as $value) {
			if(empty($value)) continue;
			$valueString .= $value . $delimeter;
		}
		return rtrim($valueString, $delimeter);
	}

	function formatNumber($value) {
		return number_format($value);
	}

	function formatPrice($value, $decimal=2) {
		global $current_user;
		return number_format((float)$value, $decimal, $current_user->currency_decimal_separator, ' ');
		/*ED151019
		$currencyField = new CurrencyField($value);
		return $currencyField->getDisplayValue(null, true);*/
	}

	function formatDate($value) {
		return DateTimeField::convertToUserFormat($value);
	}

}
?>