<?php
/*+***********************************************************************************
 * ED141021
 *************************************************************************************/

/**
 * RsnReglements Entity Record Model Class
 */
class RsnReglements_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		$name = number_format( $this->get('amount'), 2);
		$name .= ' &euro;';
		$date  = new DateTime($this->get('dateregl'));
		$name .= ' - ' . $date->format('d M Y');//date('d M Y', $this->get('dateregl'));
		return $name;
	}

	/**
	 * Function to set data of parent record model to this record
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return Inventory_Record_Model
	 */
	public function setParentRecordData(Vtiger_Record_Model $parentRecordModel) {
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $parentRecordModel->getModuleName();

		$data = array();
		
		//ED150605
		if($moduleName === 'Accounts'){
			$data['account_id'] = $parentRecordModel->getId();
		}
		//ED150500
		if($moduleName === 'Contacts'){
			$accountRecordModel = $parentRecordModel->getAccountRecordModel();
			$data['account_id'] = $accountRecordModel->getId();
		}
		
		if($moduleName === 'Invoice'
		|| $moduleName === 'SalesOrder'){
			$data['account_id'] = $parentRecordModel->get('account_id');
		}
		return $this->setData($data);
	}

}
