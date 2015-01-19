<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

vimport ('~~/include/Webservices/Query.php');

class Calendar_Feed_Action extends Vtiger_BasicAjax_Action {

	public function process(Vtiger_Request $request) {
		try {
			$result = array();

			$start = $request->get('start');
			$end   = $request->get('end');
			$type = $request->get('type');
			$userid = $request->get('userid');
			switch ($type) {
				case 'Events': $this->pullEvents($start, $end, $result, $request->get('cssClass'),$userid,$request->get('color'),$request->get('textColor')); break;
				case 'Tasks': $this->pullTasks($start, $end, $result, $request->get('cssClass')); break;
				case 'Potentials': $this->pullPotentials($start, $end, $result, $request->get('cssClass')); break;
				case 'Contacts':
							if($request->get('fieldname') == 'support_end_date') {
								$this->pullContactsBySupportEndDate($start, $end, $result, $request->get('cssClass'));
							}else{
								$this->pullContactsByBirthday($start, $end, $result, $request->get('cssClass'));
							}
							break;

				case 'Invoice': $this->pullInvoice($start, $end, $result, $request->get('cssClass')); break;
				case 'MultipleEvents' : $this->pullMultipleEvents($start,$end, $result,$request->get('mapping'));break;
				case 'Project': $this->pullProjects($start, $end, $result, $request->get('cssClass')); break;
				case 'ProjectTask': $this->pullProjectTasks($start, $end, $result, $request->get('cssClass')); break;
			}
			echo json_encode($result);
		} catch (Exception $ex) {
			echo $ex->getMessage();
		}
	}
    
    protected function fillCustomFieldsArrays(&$customfields,&$cfarray,$eventtype) {
		//$customfields = array();
		//$cfarray = array();
		switch ($eventtype) {
			case 'Events' :
				$eventfields = getColumnFields('Events');
				$tabid = getTabid('Events');
				break;
			case 'Tasks' :
				$eventfields = getColumnFields('Calendar');
				$tabid = getTabid('Calendar');
				break;
			default :
				
				$eventfields = array();
		}	
		foreach ($eventfields as $fldnm=>$v) {			
			if (strpos ($fldnm,'cf_')!==false && strpos ($fldnm,'cf_') === 0) {
				array_push($customfields,$fldnm);	
					$cfid = getFieldid($tabid,$fldnm);
					$cfrealtabid = getSingleFieldValue('vtiger_field','tabid','fieldid',$cfid);
					if ($cfrealtabid==$tabid) {
						$cflbl = getSingleFieldValue('vtiger_field','fieldlabel','fieldid',$cfid);
						$cfarray[$fldnm]=$cflbl;
					}
				}		
			}
	}	
    
	protected function getGroupsIdsForUsers($userId) {
        vimport('~~/include/utils/GetUserGroups.php');
        
        $userGroupInstance = new GetUserGroups();
        $userGroupInstance->getAllUserGroups($userId);
        return $userGroupInstance->user_groups;
    }

	protected function queryForRecords($query, $onlymine=true) {
		$user = Users_Record_Model::getCurrentUserModel();
		if ($onlymine) {
            $groupIds = $this->getGroupsIdsForUsers($user->getId());
            $groupWsIds = array();
            foreach($groupIds as $groupId) {
                $groupWsIds[] = vtws_getWebserviceEntityId('Groups', $groupId);
            }
			$userwsid = vtws_getWebserviceEntityId('Users', $user->getId());
            $userAndGroupIds = array_merge(array($userwsid),$groupWsIds);
			$query .= " AND assigned_user_id IN ('".implode("','",$userAndGroupIds)."')";
		}
		// TODO take care of pulling 100+ records
		return vtws_query($query.';', $user);
	}

	protected function pullEvents($start, $end, &$result, $cssClass,$userid = false, $color = null,$textColor = 'white') {
		$dbStartDateOject = DateTimeField::convertToDBTimeZone($start);
		$dbStartDateTime = $dbStartDateOject->format('Y-m-d H:i:s');
		$dbStartDateTimeComponents = explode(' ', $dbStartDateTime);
		$dbStartDate = $dbStartDateTimeComponents[0];
		
		$dbEndDateObject = DateTimeField::convertToDBTimeZone($end);
		$dbEndDateTime = $dbEndDateObject->format('Y-m-d H:i:s');
		
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Events');
		if($userid){
			$focus = new Users();
			$focus->id = $userid;
			$focus->retrieve_entity_info($userid, 'Users');
			$user = Users_Record_Model::getInstanceFromUserObject($focus);
			$userName = $user->getName();
			$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);
		}else{
			$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		}
		
		//SG140908 ajout des 'cf_xxx','contact_id', 'activitytype' et 'parent_id' dans la liste de fields du querygenerator
		//$basicfields est le tableau de champs utilisé dans la version originale auquel on a ajouté 'contact_id', 'activitytype' et 'parent_id'
		//$customfields est la liste des Customfields du module Events
		//$cfarray est un tableau associatif $customfieldname=>$customfieldlabel (par exemple cf_703=>Véhicules")
		//Ces tableaux sont utilisés pour enrichir l'info sur les Events envoyée à fullcalendar.js
			$basicfields = array('subject', 'eventstatus', 'visibility','date_start','time_start','due_date','time_end','assigned_user_id','id','contact_id'
					     , 'activitytype','parent_id');
			$finalfields = array();
			$customfields = array('uicolor');
			$cfarray = array();
			$this->fillCustomFieldsArrays($customfields,$cfarray,'Events');
				
			$finalfields = array_merge($customfields,$basicfields);	
						
			$queryGenerator->setFields($finalfields);
			
		//$queryGenerator->setFields(array('subject', 'eventstatus', 'visibility','date_start','time_start','due_date','time_end','assigned_user_id','id'));
		$query = $queryGenerator->getQuery();

		$query.= " AND vtiger_activity.activitytype NOT IN ('Emails','Task') AND ";
		// TODO IFNULL(due_date)
		$query.= " ((concat(date_start, '', time_start)  >= '$dbStartDateTime' AND concat(due_date, '', time_end) < '$dbEndDateTime') OR ( due_date >= '$dbStartDate'))";
		
		$params = array();
			if(empty($userid)){
		    $eventUserId  = $currentUser->getId();
		}else{
		    $eventUserId = $userid;
		}
		$params = array_merge(array($eventUserId), $this->getGroupsIdsForUsers($eventUserId));
		$query.= " AND vtiger_crmentity.smownerid IN (".  generateQuestionMarks($params).")";
		
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['activityid'];
			$visibility = $record['visibility'];
			$item['id'] = $crmid;
			$item['visibility'] = $visibility;
			if($visibility == 'Private' && $userid && $userid != $currentUser->getId()) {
				$item['title'] = decode_html($userName).' - '.decode_html(vtranslate('Busy','Events')).'*';
				$item['url']   = '';
			} else {
				$item['title'] = decode_html($record['subject']) . ' - (' . vtranslate($record['eventstatus'],'Calendar') . ')';
				$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			}

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$dateTimeFieldInstance = new DateTimeField($record['due_date'] . ' ' . $record['time_end']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue($currentUser);
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $currentUser->get('date_format'));
			$item['end']   =  $dataBaseDateFormatedString.' '. $dateTimeComponents[1];


			$item['className'] = $cssClass;
			$item['allDay'] = false;
			$item['color'] = $record['uicolor'] == null ? $color : $record['uicolor'];
			$item['textColor'] = $textColor;
			
			
			// SG140904 Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
				$item['cflbls'] = $cfarray;
					foreach ($cfarray as $cfnm=>$cflbl) {				
						$item[$cfnm] = $record[$cfnm];
					}
						
				if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
				if ($record['crmid']) {
					$pt = getSalesEntityType($record['crmid']);		
					$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);				
					if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
								$item['parentname'] = getCampaignName($record['crmid']);
										}
					if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
								$item['parentname'] = getPotentialName($record['crmid']);
										}
					if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								$item['parentname'] = getAccountName($record['crmid']);
									}
									}
					if ($record['activitytype']) $item['activitytype'] = vtranslate($record['activitytype'],'Calendar');

			// END of SG1409
			
			$result[] = $item;
			}
		}

	protected function pullMultipleEvents($start, $end, &$result, $data) {

		foreach ($data as $id=>$backgroundColorAndTextColor) {
			$userEvents = array();
			$colorComponents = explode(',',$backgroundColorAndTextColor);
			$this->pullEvents($start, $end, $userEvents ,null,$id, $colorComponents[0], $colorComponents[1]);
			$result[$id] = $userEvents;
		}
	}

	protected function pullTasks($start, $end, &$result, $cssClass) {
		$user = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		$moduleModel = Vtiger_Module_Model::getInstance('Calendar');
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $user);

		//SG140908 ajout des 'cf_xxx','contact_id', 'activitytype' et 'parent_id' dans la liste de fields du querygenerator
		//$basicfields est le tableau de champs utilisé dans la version originale auquel on a ajouté 'contact_id', 'activitytype' et 'parent_id'
		//$customfields est la liste des Customfields des evenements considérés
		//$cfarray est un tableau associatif $customfieldname=>$customfieldlabel (par exemple cf_703=>Véhicules")
		//Ces tableaux sont utilisés pour enrichir l'info sur les Events ou les Tasks envoyée à fullcalendar.js
			$basicfields = array('subject', 'taskstatus','date_start','time_start','due_date','time_end','assigned_user_id','id','contact_id', 'activitytype','parent_id');
			$finalfields = array();
			$customfields = array('uicolor');
			$cfarray = array();
			$this->fillCustomFieldsArrays($customfields,$cfarray,'Tasks');
			$finalfields = array_merge($customfields,$basicfields);			
			$queryGenerator->setFields($finalfields);
		// END

		//$queryGenerator->setFields(array('subject', 'taskstatus', 'date_start','time_start','due_date','time_end','id'));
		$query = $queryGenerator->getQuery();

		$query.= " AND vtiger_activity.activitytype = 'Task' AND ";
		$query.= " ((date_start >= '$start' AND IFNULL(due_date,date_start) < '$end') OR ( IFNULL(due_date,date_start) >= '$start'))";
		$params = $userAndGroupIds;
		$query.= " AND vtiger_crmentity.smownerid IN (".generateQuestionMarks($params).")";
		
		$queryResult = $db->pquery($query,$params);
		
		/*echo("SQL : ");
		print_r($query);
		echo("<br>Params : ");
		print_r($params);*/
		
		while($record = $db->fetchByAssoc($queryResult)){
			/*echo("<br>$record : ");
			print_r($record);*/
			$item = array();
			$crmid = $record['activityid'];
			$item['title'] = decode_html($record['subject']) . ' - (' . vtranslate($record['status'],'Calendar') . ')';

			$dateTimeFieldInstance = new DateTimeField($record['date_start'] . ' ' . $record['time_start']);
			$userDateTimeString = $dateTimeFieldInstance->getDisplayDateTimeValue();
			$dateTimeComponents = explode(' ',$userDateTimeString);
			$dateComponent = $dateTimeComponents[0];
			//Conveting the date format in to Y-m-d . since full calendar expects in the same format
			$dataBaseDateFormatedString = DateTimeField::__convertToDBFormat($dateComponent, $user->get('date_format'));
			$item['start'] = $dataBaseDateFormatedString.' '. $dateTimeComponents[1];

			$item['end']   = $record['due_date'];
			$item['url']   = sprintf('index.php?module=Calendar&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			/*ED141010*/
			$item['color'] = $record['uicolor'];
			
			// SG140904 Ajout info evenement : item.cflbls = array associatif cfname=>cflbl (construit par fillCustomFieldsArrays()) 
			// pour chaque custom field, item.cfname = valeur du custom field 
				$item['cflbls'] = $cfarray;
					foreach ($cfarray as $cfnm=>$cflbl) {				
						$item[$cfnm] = $record[$cfnm];
					}
						
				if ($record['contactid']) {$item['contactname'] = decode_html(getContactName($record['contactid']));}
			//record['crmid'] est l'id de l'entité liée à l'évènement issue de la table vtiger_seactivityrel. Ne pas confondre avec $crmid qui est l'id de cet event.
				if ($record['crmid']) {
					$pt = getSalesEntityType($record['crmid']);		
					$item['parenttype'] = vtranslate('SINGLE_'.$pt,$pt);				
					if (getCampaignName($record['crmid']) && getCampaignName($record['crmid'])!='') {
								$item['parentname'] = getCampaignName($record['crmid']);
										}
					if (getPotentialName($record['crmid']) && getPotentialName($record['crmid'])!='') {
								$item['parentname'] = getPotentialName($record['crmid']);
										}
					if (getAccountName($record['crmid']) && getAccountName($record['crmid'])!='') {
								$item['parentname'] = getAccountName($record['crmid']);
									}
									}
			// END of SG1409

			$result[] = $item;
		}
	}

	protected function pullPotentials($start, $end, &$result, $cssClass) {
		$query = "SELECT potentialname,closingdate FROM Potentials";
		$query.= " WHERE closingdate >= '$start' AND closingdate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['potentialname']);
			$item['start'] = $record['closingdate'];
			$item['url']   = sprintf('index.php?module=Potentials&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			$result[] = $item;
		}
	}

	protected function pullContacts($start, $end, &$result, $cssClass) {
		$this->pullContactsBySupportEndDate($start, $end, $result, $cssClass);
		$this->pullContactsByBirthday($start, $end, $result, $cssClass);
	}

	protected function pullContactsBySupportEndDate($start, $end, &$result, $cssClass) {
		$query = "SELECT firstname,lastname,support_end_date FROM Contacts";
		$query.= " WHERE support_end_date >= '$start' AND support_end_date <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $record['support_end_date'];
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			$result[] = $item;
		}
	}

	protected  function pullContactsByBirthday($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$startDateComponents = split('-', $start);
		$endDateComponents = split('-', $end);
        
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$params = $userAndGroupIds;
        
		$year = $startDateComponents[0];

		$query = "SELECT firstname,lastname,birthday,crmid FROM vtiger_contactdetails";
		$query.= " INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid";
		$query.= " INNER JOIN vtiger_crmentity ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid";
		$query.= " WHERE vtiger_crmentity.deleted=0 AND smownerid IN (".  generateQuestionMarks($params) .") AND";
		$query.= " ((CONCAT('$year-', date_format(birthday,'%m-%d')) >= '$start'
						AND CONCAT('$year-', date_format(birthday,'%m-%d')) <= '$end')";

        
		$endDateYear = $endDateComponents[0];
		if ($year !== $endDateYear) {
			$query .= " OR
						(CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) >= '$start'
							AND CONCAT('$endDateYear-', date_format(birthday,'%m-%d')) <= '$end')";
		}
		$query .= ")";

		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$recordDateTime = new DateTime($record['birthday']);

			$calendarYear = $year;
			if($recordDateTime->format('m') < $startDateComponents[1]) {
				$calendarYear = $endDateYear;
			}
			$recordDateTime->setDate($calendarYear, $recordDateTime->format('m'), $recordDateTime->format('d'));
			$item['id'] = $crmid;
			$item['title'] = decode_html(trim($record['firstname'] . ' ' . $record['lastname']));
			$item['start'] = $recordDateTime->format('Y-m-d');
			$item['url']   = sprintf('index.php?module=Contacts&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			$result[] = $item;
		}
	}

	protected function pullInvoice($start, $end, &$result, $cssClass) {
		$query = "SELECT subject,duedate FROM Invoice";
		$query.= " WHERE duedate >= '$start' AND duedate <= '$end'";
		$records = $this->queryForRecords($query);
		foreach ($records as $record) {
			$item = array();
			list ($modid, $crmid) = vtws_getIdComponents($record['id']);
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['subject']);
			$item['start'] = $record['duedate'];
			$item['url']   = sprintf('index.php?module=Invoice&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user projects
	 * @param type $startdate
	 * @param type $actualenddate
	 * @param type $result
	 * @param type $cssClass
	 */
	protected function pullProjects($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$params = $userAndGroupIds;
		//$db->setDebug(true);
		$query = "SELECT projectname, startdate, targetenddate, crmid, uicolor
			FROM vtiger_project
			INNER JOIN vtiger_crmentity ON vtiger_project.projectid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_projectcf ON vtiger_project.projectid = vtiger_projectcf.projectid
			WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .")
			AND ((startdate >= '$start' AND IFNULL(targetenddate,startdate) < '$end') OR ( IFNULL(targetenddate,startdate) >= '$start'))";
		$queryResult = $db->pquery($query, $params);

		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projectname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['targetenddate'];
			$item['url']   = sprintf('index.php?module=Project&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			/*ED141010*/
			$item['color'] = $record['uicolor'];
			$result[] = $item;
		}
	}

	/**
	 * Function to pull all the current user porjecttasks
	 * @param type $startdate
	 * @param type $enddate
	 * @param type $result
	 * @param type $cssClass
	 */
	protected function pullProjectTasks($start, $end, &$result, $cssClass) {
		$db = PearDatabase::getInstance();
		$user = Users_Record_Model::getCurrentUserModel();
		$userAndGroupIds = array_merge(array($user->getId()),$this->getGroupsIdsForUsers($user->getId()));
		$params = $userAndGroupIds;
		
		$query = "SELECT projecttaskname, startdate, enddate, crmid, uicolor
			FROM vtiger_projecttask
			INNER JOIN vtiger_crmentity ON vtiger_projecttask.projecttaskid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_projecttaskcf ON vtiger_projecttask.projecttaskid = vtiger_projecttaskcf.projecttaskid
			WHERE vtiger_crmentity.deleted=0 AND smownerid IN (". generateQuestionMarks($params) .")
			AND ((startdate >= '$start' AND IFNULL(enddate, startdate) < '$end') OR ( IFNULL(enddate, startdate) >= '$start'))";
		$queryResult = $db->pquery($query, $params);
		
		/*echo("SQL : ");
		print_r($query);
		echo("<br>Params : ");
		print_r($params);*/
		
		while($record = $db->fetchByAssoc($queryResult)){
			$item = array();
			$crmid = $record['crmid'];
			$item['id'] = $crmid;
			$item['title'] = decode_html($record['projecttaskname']);
			$item['start'] = $record['startdate'];
			$item['end'] = $record['enddate'];
			$item['url']   = sprintf('index.php?module=ProjectTask&view=Detail&record=%s', $crmid);
			$item['className'] = $cssClass;
			/*ED141010*/
			$item['color'] = $record['uicolor'];
			$result[] = $item;
		}
	}

}