/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Edit_Js("PurchaseOrder_Edit_Js",{},{

	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);

		if(sourceFieldElement.attr('name') == 'contact_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="vendor_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			}
        }
        return params;
    },

	/**
	 * Function to search module names
	 */
	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}
		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if (params.search_module == 'Contacts') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="vendor_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0) {
				var closestContainer = parentIdElement.closest('td');
				params.parent_id = parentIdElement.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			}
		}

		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		var thisInstance = this;
		
		jQuery('input[name="vendor_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.referenceSelectionEventHandler(data, container, !thisInstance.isEmptyAddress(container));
			thisInstance.vendorSelectionEventHandler(data, container); //ED150716
		});
	},
    
	/** ED150716
	 * Vendor Selection Event Handler
	 * Set subject
	 * 
	 */
	vendorSelectionEventHandler : function(data,container){
		var thisInstance = this
		, $subject = container.find('input[name="subject"]:first')
		, poType = container.find('input[name="potype"]:first').val()
		, subject = app.vtranslate('JS_SUBJECT_AUTO_POTYPE_' + poType) + ' ' + data.selectedName;
		if ($subject.val() && $subject.attr('data-isauto') === undefined) 
			return;
		$subject.val(subject).attr('data-isauto', 1);
	},
	
	registerEvents: function(){
		this._super();
		this.registerEventForCopyAddress();
	}
});


