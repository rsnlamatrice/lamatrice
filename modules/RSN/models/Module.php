<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport('~~/vtlib/Vtiger/Module.php');

/**
 * Calendar Module Model Class
 */
class RSN_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function returns the default view for the Calendar module
	 * @return <String>
	 */
	public function getDefaultViewName() {
		return $this->getRSNViewName();
	}

	/**
	 * Function returns the calendar view name
	 * @return <String>
	 */
	public function getRSNViewName() {
		return 'RSN';
	}

	/**
	 *  Function returns the url for Calendar view
	 * @return <String>
	 */
	public function getRSNViewUrl() {
		return 'index.php?module='.$this->get('name').'&view='.$this->getRSNViewName();
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function to get list of field for related list
	 * @return <Array> empty array
	 */
	public function getConfigureRelatedListFields() {
		return array();
	}

	/**
	 * Function to get list of field for summary view
	 * @return <Array> empty array
	 */
	public function getSummaryViewFieldsList() {
		return array();
	}
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array();
		foreach($this->getOutilsList() as $sub){
			$quickLink = array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => $sub['label'],
				'linkurl' => $this->getOutilsViewUrl($sub['sub'], @$sub['params']),
				'linkicon' => '',
			);
			if(isset($sub['children'])){
				$children = array();
				foreach($sub['children'] as $childQuickLink){
					$children[] = array(
						'linktype' => 'SIDEBARLINK',
						'linklabel' => $childQuickLink['label'],
						'linkurl' => $this->getOutilsViewUrl($childQuickLink['sub'], @$childQuickLink['params']),
						'linkicon' => '',
					);
				}
				$quickLink['children'] = $children;
			}
		
			$quickLinks[] = $quickLink;
		}
		//var_dump($quickLinks);
		foreach($quickLinks as $quickLink) {
			$link = Vtiger_Link_Model::getInstanceFromValues($quickLink);
			$links['SIDEBARLINK'][] = $link;
			if($link->get('children')){
				$children = array();
				foreach($link->get('children') as $childLink) {
					$children[] = Vtiger_Link_Model::getInstanceFromValues($childLink);
				}
				$link->set('children', $children);
			}
		}

		/* $quickWidgets */
		$quickWidgets = array();

		/*if ($linkParams['ACTION'] == 'Outils') {
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_ACTIVITY_TYPES',
				'linkurl' => 'module='.$this->get('name').'&view=ViewTypes&mode=getViewTypes',
				'linkicon' => ''
			);
		}

		if ($linkParams['ACTION'] == 'SharedCalendar') {
			$quickWidgets[] = array(
				'linktype' => 'SIDEBARWIDGET',
				'linklabel' => 'LBL_ADDED_CALENDARS',
				'linkurl' => 'module='.$this->get('name').'&view=ViewTypes&mode=getSharedUsersList',
				'linkicon' => ''
			);
		}

		$quickWidgets[] = array(
			'linktype' => 'SIDEBARWIDGET',
			'linklabel' => 'LBL_RECENTLY_MODIFIED',
			'linkurl' => 'module='.$this->get('name').'&view=IndexAjax&mode=showActiveRecords',
			'linkicon' => ''
		);*/

		foreach($quickWidgets as $quickWidget) {
			$links['SIDEBARWIDGET'][] = Vtiger_Link_Model::getInstanceFromValues($quickWidget);
		}

		return $links;
	}

	/**
	 * Function returns the url that shows Calendar Import result
	 * @return <String> url
	 */
	public function getImportResultUrl() {
		return 'index.php?module='.$this->getName().'&view=ImportResult';
	}

	/**
	 * Function to get the url to view Details for the module
	 * @return <String> - url
	 */
	public function getDetailViewUrl($id) {
		return 'index.php?module=RSN&view='.$this->getDetailViewName().'&record='.$id;
	}

	/**
	 *  Function returns the url for Outils view
	 * @return <String>
	 */
	public function getOutilsViewUrl($sub = 'List', $params = FALSE) {
		if(is_array($params))
			foreach($params as $key=>$value)
				$sub .= "&$key=$value";
		return 'index.php?module='.$this->get('name').'&view=Outils&sub=' . $sub;
	}

	
	
	/*
	 * Liste des templates dans vlayout/modules/RSN/Outils/
	 */
	public function getOutilsList(){
		$list = array(
			array(
				'sub' => 'List',
				'label' => 'Le Moulin'
			)
		);
		
		$list[] = array(
				'sub' => 'Import4D',
				'label' => 'Importation 4D'
			)
		;
		
		$list[] = array(
				'sub' => 'ImportCogilog',
				'label' => 'Importation Cogilog',
				
				'children' => array(
					array(
						'sub' => 'ImportCogilog/Factures',
						'label' => 'Factures'
					),
					array(
						'sub' => 'ImportCogilog/Affaires',
						'label' => 'Affaires vers coupons'
					),
					array(
						'sub' => 'ImportCogilog/ProduitsEtServices',
						'label' => 'Produits et services'
					),
					array(
						'sub' => 'ImportCogilog/Comptes',
						'label' => 'Comptes'
					),
					array(
						'sub' => 'ImportCogilog/Clients',
						'label' => 'Clients',
					),
					array(
						'sub' => 'DataRowsTable',
						'label' => 'Une table',
						'params' => array(
							'tablename' => 'gclien00002',
						)
					),
				)
			)
		;
		
		$list[] = array(
				'sub' => 'Purge',
				'label' => 'Grande purge'
			)
		;
		
		$list[] = array(
				'sub' => 'EditCustomView',
				'label' => 'Edition de vue',
				'params' => array(
					'viewid' => 120
					, 'viewmodule' => 'Contacts'
				)
			)
		;
		
		$list[] = array(
				'sub' => 'TestsED',
				'label' => 'Tests ED'
			)
		;
		
		$list[] = array(
				'sub' => 'DefineMissingLabels',
				'label' => 'Affectation des labels manquants'
			)
		;
		
		return $list;
	}
	


	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Calendar_Record_Model
	 */
	public function getRecentRecords($limit=10) {
		return array();
		/*
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = parent::getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());

		$query = 'SELECT * FROM vtiger_crmentity ';
		if($nonAdminQuery){
			$query .= " INNER JOIN vtiger_activity ON vtiger_crmentity.crmid = vtiger_activity.activityid ".$nonAdminQuery;
		}
		$query .= ' WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);
		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;*/
	}

	/**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = array();

		/*if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_FIELDS',
					'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
					'linkicon' => Vtiger_Theme::getImagePath('LayoutEditor.gif')
			);

		$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
					'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module='.$this->getName(),
					'linkicon' => ''
			);
		}*/
		return $settingLinks;
	}
	
	
}
