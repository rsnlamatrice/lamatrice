<?php
/*+**********************************************************************************
 * ED151008
 ************************************************************************************/

class Contacts_FindDuplicate_Model extends Vtiger_FindDuplicate_Model {
	
	
	/* Fields to find duplicates
	 */
	public function getFindDuplicateFields(){
		return array('email');
	}
}