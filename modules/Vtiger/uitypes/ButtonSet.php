<?php
/*
 * ED141009
 */
class Vtiger_ButtonSet_UIType extends Vtiger_Base_UIType {

    
	/**
	 * Function to get the Template name for the current UI Type Object and for Edit
	 * @return <String> - Input Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/ButtonSetEdit.tpl';
	}
	/**
	 * Function to get the Template name for the current UI Type Object and for Detail view
	 * @return <String> - Template Name
	 */
	public function getDetailViewTemplateName() {
		return 'uitypes/ButtonSetDetail.tpl';
	}
	
	/** ED150413
	 * Function to get the header filter input template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getHeaderFilterTemplateName() {
		return 'uitypes/ButtonSetHeaderFilter.tpl';
	}
	
}