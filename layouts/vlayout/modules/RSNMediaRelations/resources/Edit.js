/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Edit_Js("RSNMediaRelations_Edit_Js",{
   
},{
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		var thisInstance = this;
		
		jQuery('input[name="mediacontactid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.referenceSelectionEventHandler(data, container);
		});
	},
	
	/**
	 * Reference Fields Selection Event Handler
	 * On Confirmation It will copy the address details
	 */
	referenceSelectionEventHandler :  function(data, container) {
		var $mediaid = jQuery('input[name="rsnmediaid"]', container);
		if($mediaid.length && !$mediaid.val()){
			$.ajax({
				url: "?action=GetData&module=RSNMediaContacts",
				data: data,
				success: function(data){
					result = data.result.data;
					$mediaid.val(result['rsnmediaid']);
					
					$mediaid = jQuery('input#rsnmediaid_display', container);
					$mediaid.val(result['rsnmediaid_nom']);
					
					//alert(result['rsnmediaid'] + " : " + result['rsnmediaid_nom']);
				},
				dataType: "json"
			})
		}
	},	
	
	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents : function(container) {
		this._super(container);
		this.registerReferenceSelectionEvent(container);
	}
});