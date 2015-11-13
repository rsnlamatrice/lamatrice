<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger MenuStructure Model
 */
class Vtiger_MenuStructure_Model extends Vtiger_Base_Model {

	protected $limit = 8; // Max. limit of persistent top-menu items to display.
	protected $enableResponsiveMode = true; // Should the top-menu items be responsive (width) on UI?

	const TOP_MENU_INDEX = 'top';
	const MORE_MENU_INDEX = 'more';

	/**
	 * Function to get all the top menu models
	 * @return <array> - list of Vtiger_Menu_Model instances
	 */
	public function getTop() {
		return $this->get(self::TOP_MENU_INDEX);
	}
	
	/**
	 * Function to get all the more menu models
	 * @return <array> - Associate array of Parent name mapped to Vtiger_Menu_Model instances
	 */
	public function getMore() {
		$moreTabs = $this->get(self::MORE_MENU_INDEX);
		foreach($moreTabs as $key=>$value){ 
			if(!$value){ 
				unset($moreTabs[$key]); 
			}
		} 
		return $moreTabs;
	}

	/**
	 * Function to get the limit for the number of menu models on the Top list
	 * @return <Number>
	 */
	public function getLimit() {
		return $this->limit;
	}
	
	/**
	 * Function to determine if the structure should support responsive UI.
	 */
	public function getResponsiveMode() {
		return $this->enableResponsiveMode;
	}

	/**
	 * Function to get an instance of the Vtiger MenuStructure Model from list of menu models
	 * @param <array> $menuModelList - array of Vtiger_Menu_Model instances
	 * @return Vtiger_MenuStructure_Model instance
	 *
	 * ED151112 Les modules ont désormais un champ tabsequenceinparent en relation avec vtiger_parenttabrel
	 * 	initialisé par Vtiger_Module_Model::getAll
	 * 
	 */
	public static function getInstanceFromMenuList($menuModelList, $selectedMenu='') {
		$structureModel = new self();
		$topMenuLimit = $structureModel->getResponsiveMode() ? 0 : $structureModel->getLimit();
		$currentTopMenuCount = 0;

		$menuListArray = array();
		$menuListArray[self::TOP_MENU_INDEX] = array();
		$menuListArray[self::MORE_MENU_INDEX] = array();//$structureModel->getEmptyMoreMenuList();

		//Menu TOP utilisateur
		foreach($menuModelList as $menuModel) {
			if(($menuModel->get('tabsequence') != -1 && (!$topMenuLimit || $currentTopMenuCount < $topMenuLimit)) ) {
				$menuListArray[self::TOP_MENU_INDEX][$menuModel->get('name')] = $menuModel;
				$currentTopMenuCount++;
			}
		}
		//Tri selon le menu Tout
		usort($menuModelList, array('Vtiger_MenuStructure_Model', 'sortMenuItemsBySequenceInParent'));
		//Menu Tout
		foreach($menuModelList as $menuModel) {
			$parent = $menuModel->get('parent');
			if($parent == 'Sales' || $parent == 'Marketing'){
				$parent = 'MARKETING_AND_SALES';
			}
			$menuListArray[self::MORE_MENU_INDEX][strtoupper($parent)][$menuModel->get('name')] = $menuModel;
		}
		
		//Ajout du module courant
		if(!empty($selectedMenu) && !array_key_exists($selectedMenu, $menuListArray[self::TOP_MENU_INDEX])) {
			$selectedMenuModel = $menuModelList[$selectedMenu];
			if($selectedMenuModel) {
				$menuListArray[self::TOP_MENU_INDEX][$selectedMenuModel->get('name')] = $selectedMenuModel;
			}
		}
		
		//echo '<br><br><br><br>';
		//echo_callstack();
		//var_dump($menuModelList, $menuListArray[self::MORE_MENU_INDEX]);
		/*// Apply custom comparator
		foreach ($menuListArray[self::MORE_MENU_INDEX] as $parent => &$values) {
			//var_dump($parent);
			uksort($values, array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));
		}
		
		uksort($menuListArray[self::MORE_MENU_INDEX], array('Vtiger_MenuStructure_Model', 'sortMenuItemsParentsByProcess'));
		*/
		//uksort($menuListArray[self::TOP_MENU_INDEX], array('Vtiger_MenuStructure_Model', 'sortMenuItemsByProcess'));
		return $structureModel->setData($menuListArray);
	}
	
	//ED151112 tri par champ tabsequenceinparent
	static function sortMenuItemsBySequenceInParent($a, $b){
		return ($a->get('tabsequenceinparent') - $b->get('tabsequenceinparent'));
	}
	
	
	//ED151112 n'est plus utilisé ici mais par ailleurs (tri des QuickCreate) TODO
	/**
	 * Custom comparator to sort the menu items by process.
	 * Refer: http://php.net/manual/en/function.uksort.php
	 */
	static function sortMenuItemsByProcess($a, $b) {
		static $order = NULL;
		if ($order == NULL) {
			$order = array(
				'Campaigns',
				'Leads',
				'Contacts',
				'Accounts',
				'Potentials',
				'Quotes',
				'Invoice',
				'SalesOrder',
				'HelpDesk',
				'Faq',
				'Project',
				'Assets',
				'ServiceContracts',
				'Products',
				'Services',
				'PriceBooks',
				'Vendors',
				'PurchaseOrder',
				'MailManager',
				'Calendar',
				'Documents',
				'SMSNotifier',
				'RecycleBin'				
			);
		}
		$apos  = array_search($a, $order);
		$bpos  = array_search($b, $order);
	
		if ($apos === false) return PHP_INT_MAX;
		if ($bpos === false) return -1*PHP_INT_MAX;
	
		return ($apos - $bpos);
	}
	
	//ED151112 n'est plus utilisé
	/**
	 * Custom comparator to sort the menu items by process.
	 * Refer: http://php.net/manual/en/function.uksort.php
	 */
	//static function sortMenuItemsParentsByProcess($a, $b) {
	//	static $order = NULL;
	//	if ($order == NULL) {
	//		$order = array(
	//			'CONTACTS',
	//			'MARKETING_AND_SALES',
	//			'INVENTORY',
	//			'BOUTIQUE',
	//			'COMPTA',
	//			'NONUK',
	//			'MEDIA_PRESSE',
	//			'ANALYTICS',
	//			'SUPPORT',
	//			'SETTINGS',
	//			'TOOLS',			
	//		);
	//	}
	//	$apos  = array_search($a, $order);
	//	$bpos  = array_search($b, $order);
	//
	//	if ($apos === false) return PHP_INT_MAX;
	//	if ($bpos === false) return -1*PHP_INT_MAX;
	//
	//	return ($apos - $bpos);
	//}

	//ED151112 n'est plus utilisé
	//private function getEmptyMoreMenuList(){
	//	return array('CONTACTS'=>array()
	//		     ,'MARKETING_AND_SALES'=>array()
	//		     ,'INVENTORY'=>array()
	//		     ,'NONUK'=>array()
	//		     ,'TOOLS'=>array()
	//		     ,'ANALYTICS'=>array()
	//		     ,'SUIVI'=>array()
	//		     ,'MEDIA_PRESSE'=>array()
	//		     ,'SUPPORT'=>array()
	//	);
	//}
}
