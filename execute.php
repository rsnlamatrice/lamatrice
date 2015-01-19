<?php
include_once('vtlib/Vtiger/Module.php');
/*
$moduleInstance = Vtiger_Module::getInstance('Contacts');
$fieldInstance = new Vtiger_Field();
$fieldInstance->name = 'rsncollectif';
$fieldInstance->table = 'vtiger_contactdetails';
$fieldInstance->column = 'rsncollectif';
$fieldInstance->columntype = 'TINYINT(1)';
$fieldInstance->uitype = 15;
$fieldInstance->typeofdata = 'V~O';
$blockInstance = Vtiger_Module::getInstance('Contacts');
var_dump($blockInstance->addField($fieldInstance));
*/
/*
$moduleInstance = Vtiger_Module::getInstance('Campaigns');
$targetModuleInstance = Vtiger_Module::getInstance('Documents');
$relationLabel  = 'Documents de campagne';
$moduleInstance->unsetRelatedList($targetModuleInstance);
//$moduleInstance->setRelatedList(     $targetModuleInstance, $relationLabel, Array('ADD','SELECT') );
*/

/*include("modules/RSN/RSN.php");
RSN::contact_addField_accountboss();*/

/*$moduleInstance = Vtiger_Module::getInstance('Critere4D');
$moduleInstance = Vtiger_Module::getInstance('Contacts');

$moduleInstance->addLink('DETAILVIEWWIDGET', 'Critère 4D', 'index.php?module=Contacts&view=ShowWidget&name=History');
*/
//$moduleInstance->deleteLink("HEADERSCRIPT", 'Critere4D_HEADERSCRIPT', 'layouts/vlayout/modules/Critere4D/resources/RelatedList.js');
                         
//$targetModuleInstance = Vtiger_Module::getInstance('Contacts');
///* unset
// */
//$moduleInstance->unsetRelatedList($targetModuleInstance);
//$targetModuleInstance->unsetRelatedList($moduleInstance);
///* */
//
//$relationLabel  = 'Contacts ayant le critere 4D';
//$moduleInstance->setRelatedList(
//      $targetModuleInstance, $relationLabel, Array('ADD','SELECT')
//);
//$relationLabel  = 'Criteres 4D';
//$targetModuleInstance->setRelatedList(
//      $moduleInstance, $relationLabel, Array('ADD','SELECT')
//);

?>