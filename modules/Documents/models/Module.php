<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_Module_Model extends Vtiger_Module_Model {

	/**
	 * Functions tells if the module supports workflow
	 * @return boolean
	 */
	public function isWorkflowSupported() {
		return false;
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	/**
	 * Function returns the url which gives Documents that have Internal file upload
	 * @return string
	 */
	public function getInternalDocumentsURL() {
		return 'view=Popup&module=Documents&src_module=Emails&src_field=composeEmail';
	}

	/**
	 * Function returns list of folders
	 * @return <Array> folder list
	 */
	public static function getAllFolders($folderName = false) {
		$db = PearDatabase::getInstance();

		$params = array();
		$query = 'SELECT *
				FROM vtiger_attachmentsfolder';
		if($folderName){
			$query .= ' WHERE foldername = ?';
			$params[] = $folderName;
		}
		$result = $db->pquery($query, $params);

		$folderList = array();
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$folderList[] = Documents_Folder_Model::getInstanceByArray($row);
		}
		return $folderList;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if($sourceModule === 'Emails' && $field === 'composeEmail') {
			$condition = ' (( vtiger_notes.filelocationtype LIKE "%I%")) AND vtiger_notes.filename != "" AND vtiger_notes.filestatus = 1';
		} else {
			$condition = " vtiger_notes.notesid NOT IN (SELECT notesid FROM vtiger_senotesrel WHERE crmid = '$record') AND vtiger_notes.filestatus = 1";
		}
		$pos = stripos($listQuery, 'where');
		if($pos) {
			$split = preg_split('/where/i', $listQuery);
			$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
		} else {
			$overRideQuery = $listQuery. ' WHERE ' . $condition;
		}
		return $overRideQuery;
	}


	//ED151105 comments
//	/**
//	 * Funtion that returns fields that will be showed in the record selection popup
//	 * @return <Array of fields>
//	 */
//	public function getPopupFields() {
//		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
//		$popupFileds = parent::getPopupFields();
//
//		foreach ($popupFileds as $fieldLabel => $fieldName) {
//			if ($fieldName === 'folderid' || $fieldName === 'modifiedtime') {
//				unset($popupFileds[$fieldLabel]);
//			}
//		}
//
//		$reqPopUpFields = array('File Status' => 'filestatus',
//								'File Size' => 'filesize',
//								'File Location Type' => 'filelocationtype');
//
//		foreach ($reqPopUpFields as $fieldLabel => $fieldName) {
//            $fieldModel = Vtiger_Field_Model::getInstance($fieldName,$this);
//			if ($fieldModel->getPermissions('readwrite')) {
//				$popupFileds[$fieldLabel] = $fieldName;
//			}
//		}
//		return $popupFileds;
//	}

	/**
	 * Function to get the url for add folder from list view of the module
	 * @return <string> - url
	 */
	public function getAddFolderUrl() {
		return 'index.php?module='.$this->getName().'&view=AddFolder';
	}

	/**
	 * Function to get Alphabet Search Field
	 */
	public function getAlphabetSearchField(){
		return 'folderid,notes_title';
	}

	/**
     * Function that returns related list header fields that will be showed in the Related List View
     * @return <Array> returns related fields list.
     */
	public function getRelatedListFields() {
		$relatedListFields = parent::getRelatedListFields();
		//Adding filestatus, filelocationtype in the related list to be used for file download
		$relatedListFields['filestatus'] = 'filestatus';
		$relatedListFields['filelocationtype'] = 'filelocationtype';

		return $relatedListFields;
	}

	public function getSettingLinks() {
		vimport('~~modules/com_vtiger_workflow/VTWorkflowUtils.php');


		$layoutEditorImagePath = Vtiger_Theme::getImagePath('LayoutEditor.gif');
		$settingsLinks = array();

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_FIELDS',
			'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
			'linkicon' => $layoutEditorImagePath
		);

		$settingsLinks[] = array(
			'linktype' => 'LISTVIEWSETTING',
			'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
			'linkurl' => 'index.php?parent=Settings&module=Picklist&source_module='.$this->getName(),
			'linkicon' => ''
		);

		if($this->hasSequenceNumberField()) {
			$settingsLinks[] = array(
				'linktype' => 'LISTVIEWSETTING',
				'linklabel' => 'LBL_MODULE_SEQUENCE_NUMBERING',
				'linkurl' => 'index.php?parent=Settings&module=Vtiger&view=CustomRecordNumbering&sourceModule='.$this->getName(),
				'linkicon' => ''
			);
		}

		return $settingsLinks;
    }

	/* ED141018*/
	public function getConfigureRelatedListFields(){
		$ListFields = parent::getConfigureRelatedListFields();
		$ListFields['uicolor'] = 'uicolor';
		$ListFields['dateapplication'] = 'dateapplication';
		$ListFields['data'] = 'data';
		return $ListFields;
	}

	/* ED141223
	 * fields de la table senotesrel
	*/
	public function getRelationHeaders(){
		return Documents_RelationListView_Model::get_related_fields();
	}

	//Useless because date and data may be multiple and are initialized in RelationListView->getEntries
	///**
	// * Function to get relation query for particular module with function name
	// * @param <record> $recordId
	// * @param <String> $functionName
	// * @param Vtiger_Module_Model $relatedModule
	// * @return <String>
	// */
	//public function getRelationQuery($recordId, $functionName, $relatedModule) {
	//	$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
	//	//ED150323 : ajout des champs de la relation
	//	if(strpos($query, 'vtiger_senotesrel') !== FALSE)
	//		$query = preg_replace('/^\s*SELECT\s/', 'SELECT vtiger_senotesrel.dateapplication, vtiger_senotesrel.data, ', $query);
	//	//echo('<pre>APRES '.$query . '</pre>');
	//
	//	return $query;
	//}



	/**
	 * Function to get the field mode
	 * @param <String> $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName){
		if($fieldName === 'title')
			$fieldName = 'notes_title';
		return parent::getField($fieldName);
	}
}
?>