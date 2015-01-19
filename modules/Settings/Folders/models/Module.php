<?php
/*+***********************************************************************************
 * ED141226
 *************************************************************************************/

class Settings_Folders_Module_Model extends Settings_Vtiger_Module_Model {
	
	var $name = 'Folders';

	/**
	 * Function to save the folders structure
	 * called from modules\Settings\Folders\actions\Save.php
	 */
	public function saveFolders($folders) {
		$db = PearDatabase::getInstance();
		
		$deleteIds = array();
		$updateParams = array(
			'foldername' => array(),
			'description' => array(),
			'sequence' => array(),
			'uicolor' => array(),
		);
		
		$updateQueries = array();
		$updateParams = array();
		
		$inserted = FALSE;
		
		foreach($folders as $folder)
			/* delete */
			if($folder['deleted'])
				$deleteIds[] = $folder['folderid'];
			/* update */
			else if($folder['changed'] && $folder['folderid'] && $folder['folderid'] != 'new'){
				$updateQueries[] = "UPDATE vtiger_attachmentsfolder
					SET foldername = ?
					, description = ?
					, sequence = ?
					, uicolor = ?
					WHERE folderid = ?";
				$updateParams[] = array($folder['foldername'], $folder['description'], $folder['sequence'], $folder['uicolor'], $folder['folderid']);
			}
			/* insert */
			else if($folder['changed'] && $folder['folderid'] == 'new'){
				$inserted = TRUE;
				$updateQueries[] = "INSERT INTO vtiger_attachmentsfolder
					(foldername, description, sequence, uicolor)
					VALUES(?, ?, ?, ?)";
				$updateParams[] = array($folder['foldername'], $folder['description'], $folder['sequence'], $folder['uicolor']);
			}
		
		// d'abord les suppressions
		if(count($deleteIds)){
			
			$deleteQuery = "DELETE FROM vtiger_attachmentsfolder WHERE folderid IN (" . generateQuestionMarks($deleteIds) . ")";
			
			$result = $db->pquery($deleteQuery, $deleteIds);
			
			//Déplacement des documents vers le dossier par défaut
			$updateQueries[] = "UPDATE vtiger_notes
				SET folderid = (
					SELECT folderid
					FROM vtiger_attachmentsfolder
					WHERE foldername = 'Default' /* TODO */
				)
				WHERE folderid NOT IN (
					SELECT folderid
					FROM vtiger_attachmentsfolder
				)";
			$updateParams[] = array();
		}
		
		if($inserted){
			//Mise à jour de la sequence
			$updateQueries[] = "UPDATE vtiger_attachmentsfolder_seq (id)
				SELECT MAX(folderid) FROM vtiger_attachmentsfolder";
			$updateParams[] = array();
		}
		// mises à jour
		for($i = 0; $i < count($updateQueries); $i++){
			$result = $db->pquery($updateQueries[$i], $updateParams[$i]);
		}
		
	}
}
