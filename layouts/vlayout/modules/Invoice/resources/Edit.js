/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Inventory_Edit_Js("Invoice_Edit_Js",{},{
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		var thisInstance = this;
		
		jQuery('input[name="account_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.referenceSelectionEventHandler(data, container);
		});
		
		jQuery('input[name="notesid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.couponSelectionEventHandler(data, container);
		});
	},
	
	/* ED150515
	 * Sélection d'un coupon : affectation de la campagne, chargement de la liste des produits et services
	 */
	couponSelectionEventHandler : function(data, container){
		
		var thisInstance = this
		, $note = container.find('input[name="notesid"]:first')
		, notesid = $note.val();
		if (notesid) {
			var params = {
				'record' : notesid
				, 'source_module' : 'Documents'
				, 'related_data' : 'Campaigns,Products,Services'
			};
			var selectedName = data['selectedName'];
			
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : selectedName + '...',
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			
			//set subject if empty (see inventory)
			var $subject = container.find('input[name="subject"]');
			if (!$subject.val() || $subject.is('.auto-filled')) {
				var $typeDossier = container.find(':input[name="typedossier"]')
				, typeDossier = $typeDossier.length ? $typeDossier.val() : ''
				, subject = (typeDossier && typeDossier != 'Facture' ? typeDossier + ' / ' : '') + selectedName
				;
				$subject
					.addClass('auto-filled')
					.val(subject);
			}
			thisInstance.getRecordDetails(params).then(
				function(data){
					progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
					
					var response = data['result']
					, campaigns = response['related_data']['Campaigns']
					, products = response['related_data']['Products']
					, services = response['related_data']['Services'];
					
					thisInstance.setCampaign(container, campaigns);
					thisInstance.setProductsAndServices(container, jQuery.extend(products, services));
				},
				function(error, err){
					progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
				}
			);
		}
	},
	
	setCampaign : function(container, campaigns) {
		if (!campaigns) return;
		var $dest = container.find('input[name="campaign_no"]');
		if ($dest.length == 0) {
			alert('Zone de sélection de la Campagne introuvable');
			return;
		}
		if ($dest.val() && !$dest.attr('auto-filled')) {
			//console.log('Campagne déjà sélectionnée');
			return;
		}
		var campaign = false;
		for(var i in campaigns) {
			//if(campaigndate < campaigns[i]['date']
			campaign = campaigns[i];
			break;
		}
		if (!campaign) {
			//alert('Coupon sans Campagne');
			return;
		}
		$dest
			.val(campaign.id)
			.attr('auto-filled', 1);
		$dest.nextAll('.input-prepend:first').children('input:first')
			.val(campaign.campaignname);
	},
	
	setProductsAndServices : function(container, products) {
		if (!products) return;
		var thisInstance = this
		, $items = this.getLineItemContentsContainer();
		for(var id in products) {
			var product = products[id];
			if ($items.find('.selectedModuleId[value="' + id + '"]').length) 
				continue;
			var productName = product['productname']
			,   productcode = product['productcode'];
				
			if (productName){
				jQuery('#addProduct', container).click();
			}
			else{
				productName = product['servicename'];
				jQuery('#addService', container).click();
			}
			var sequenceNumber = thisInstance.rowSequenceHolder
			,   $row = $items.find('#row' + sequenceNumber + '.' + thisInstance.rowClass)
			, $input = $row.find('#productName' + sequenceNumber)
			, $inputId = $row.find('#hdnProductId' + sequenceNumber);
			
			$input.val(thisInstance.htmlDecode(productName));
			$inputId.val(id);
			thisInstance.autoCompleteSelected($input, {type: true, id: id});
			thisInstance.setQuantityValue($row, 0);
		}
		//remove empty product row
		$input = $items.find('.selectedModuleId').filter(function() { return !this.value; });
		if ($input.length) 
			$input.parents('.' + thisInstance.rowClass + ':not(#row0)').remove();
	},
	//ED150625
	htmlDecode : function(value) {
	   return (typeof value === 'undefined') ? '' : $('<div/>').html(value).text();
	},
	
	/**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
		var sourceFieldElement = jQuery('input[class="sourceField"]',container);

		if(sourceFieldElement.attr('name') == 'contact_id') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="account_id"]');
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
			var parentIdElement  = form.find('[name="account_id"]');
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

	registerEvents: function(){
		this._super();
		this.registerForTogglingBillingandShippingAddress();
		this.registerEventForCopyAddress();
	}
});


