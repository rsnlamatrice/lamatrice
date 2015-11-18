<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Inventory_SendEmail_View extends Vtiger_ComposeEmail_View {

    /**
     * Function which will construct the compose email
     * This will handle the case of attaching the invoice pdf as attachment
     * @param Vtiger_Request $request 
     */
    public function composeMailData(Vtiger_Request $request) {
        
        $inventoryRecordId = $request->get('record');
        $recordModel = Vtiger_Record_Model::getInstanceById($inventoryRecordId, $request->getModule());
        
        switch($request->getModule()){
        case 'Invoice':
            $request->set('selected_ids', array($recordModel->get('contact_id')));
            $request->set('selectedFields', array('email'));
            break;
        //Dépôt-vente
        case 'SalesOrder':
            $request->set('selected_ids', array( $recordModel->get('accountid') ));
            $request->set('selectedFields', array('email'));
            break;
        }
        
        
        parent::composeMailData($request);

        $viewer = $this->getViewer($request);
        $pdfFileName = $recordModel->getPDFFileName();
        
        $fileComponents = explode('/', $pdfFileName);
        
        $fileName = $fileComponents[count($fileComponents)-1];
        //remove the fileName
        array_pop($fileComponents);

        $attachmentDetails = array(array(
            'attachment' =>$fileName,
            'path' => implode('/',$fileComponents),
            'size' => filesize($pdfFileName),
            'type' => 'pdf',
            'nondeletable' => true
        ));

        $viewer->assign('ATTACHMENTS', $attachmentDetails);
        echo $viewer->view('ComposeEmailForm.tpl', 'Emails', true);
    }
    
}


?>