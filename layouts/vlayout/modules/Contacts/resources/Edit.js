/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Contacts_Edit_Js",{},{
	
	//Will have the mapping of address fields based on the modules
	addressFieldsMapping : {'Accounts' :
			{'mailingstreet' : 'bill_street',  
			'otherstreet' : 'ship_street', 
			'otherstreet2' : 'ship_street2', 
			'otherstreet3' : 'ship_street3', 
			'mailingpobox' : 'bill_pobox',
			'otherpobox' : 'ship_pobox',
			'mailingcity' : 'bill_city',
			'othercity' : 'ship_city',
			'mailingstate' : 'bill_state',
			'otherstate' : 'ship_state',
			'mailingzip' : 'bill_code',
			'otherzip' : 'ship_code',
			'mailingcountry' : 'bill_country',
			'othercountry' : 'ship_country',
			'mailingaddressformat' : 'bill_addressformat',
			'otheraddressformat' : 'ship_addressformat',
			}
	},
							
	//Address field mapping within module
	addressFieldsMappingInModule : {
		'otherstreet' : 'mailingstreet',
		'otherstreet2' : 'mailingstreet2',
		'otherstreet3' : 'mailingstreet3',
		'otherpobox' : 'mailingpobox',
		'othercity' : 'mailingcity',
		'otherstate' : 'mailingstate',
		'otherzip' : 'mailingzip',
		'othercountry' : 'mailingcountry',
		'otheraddressformat' : 'mailingaddressformat',
	},
	
	//ED150312
	//Address field mapping with contactAdresses
	addressFieldsMappingInContactAddresses : {
		'mailingstreet' : 'street',
		'mailingstreet2' : 'street2',
		'mailingstreet3' : 'street3',
		'mailingpobox' : 'pobox',
		'mailingcity' : 'city',
		'mailingstate' : 'state',
		'mailingzip' : 'zip',
		'mailingcountry' : 'country',
		'mailingaddressformat' : 'addressformat'
	},
	
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerReferenceSelectionEvent : function(container) {
		var thisInstance = this;
		
		jQuery('input[name="account_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			thisInstance.referenceSelectionEventHandler(data, container);
		});
	},
	
	/**
	 * Reference Fields Selection Event Handler
	 * On Confirmation It will copy the address details
	 *
	 * ED141016 : transposition de la question à l'utilisateur aprés test de l'existence d'une adresse à dupliquer 
	 * 
	 */
	referenceSelectionEventHandler :  function(data, container) {
		var thisInstance = this;
		var sourceModule = data['source_module'];
		if (sourceModule == 'Accounts') {
			var selectedName = data['selectedName'];
			var isQuickCreate = container.is('[name="QuickCreate"]');
			//Conflit avec QuickCreate et progressIndicator
			if (!isQuickCreate) {
				var progressIndicatorElement = jQuery.progressIndicator({
				   'message' : selectedName + '...',
				   'position' : 'html',
				   'blockInfo' : {
					   'enabled' : true
				   }
			   });
			}
			data['related_data'] = 'MainContacts';
			thisInstance.getRecordDetails(data).then(
				function(recordDetails){
					if(!isQuickCreate)
						progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
					thisInstance.copyAddressDetails(data, container, !isQuickCreate, recordDetails);
					thisInstance.checkAccountReferent(data, container, recordDetails);
				},
				function(error, err){
					if(!isQuickCreate)
						progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
				}
			);
		}
	},
	
	/** ED150515
	 * Function which will set account reference value
	 */
	checkAccountReferent : function(data, container, recordDetails) {
		var thisInstance = this
		, sourceModule = data['source_module']
		, selectedName = data['selectedName']
		, getRecordDetailsCallBack = function(data){
	
			var response = data['result']
			, mainContacts = response['related_data']['MainContacts'];
			if (!mainContacts) 
				return;
			var message = ''
			, thisContactWasMain = false
			, otherContactWasMain = false
			, thisContactId = container.find('input[name="record"]').val() //TODO Achtung if duplicating
			, accountId = container.find(':input[name="account_id"]').val();
			if (!thisContactId) //DetailView or may be empty if new
				thisContactId = $('#recordId').val();
			if (!accountId) //DetailView
				accountId = container.find(':input[name="account_id"]').attr('data-value');
			if (!accountId)
				return;
			for (var contactId in mainContacts) {
				if (thisContactId == contactId) 
					thisContactWasMain = true;
				else
					otherContactWasMain = true;
			}
			$inputs = container.find(':input[name="reference"]');
			if(otherContactWasMain)
				$inputs.filter('[value="0"]')[0].checked = true;
			else 
				$inputs.filter('[value="1"]')[0].checked = true;
			$inputs.first().parents('.ui-buttonset:first')
				.buttonset('refresh');
		};
		if (recordDetails)
			getRecordDetailsCallBack.call(this, recordDetails)
		else {
			data['related_data'] = 'MainContacts';
			thisInstance.getRecordDetails(data).then(
				getRecordDetailsCallBack
				, function(error, err){}
			);
		}
	},
	
	/**
	 * Function which will copy the address details - without Confirmation
	 *
	 * ED150515 : adds recordDetails is already requested
	 */
	copyAddressDetails : function(data, container, askUser, recordDetails) {
		var thisInstance = this
		, sourceModule = data['source_module']
		, selectedName = data['selectedName']
		, getRecordDetailsCallBack = function(data){
			var response = data['result'];
			
			addressDetails=thisInstance.addressFieldsMapping[sourceModule];
			
			if (askUser && !thisInstance.hasAddressDetails(addressDetails, response['data'])) 
				return;
			if (askUser) {
				var message = app.vtranslate('OVERWRITE_EXISTING_MSG1')+ ' (' + app.vtranslate('SINGLE_'+sourceModule)+' ' + selectedName+') '
					+ app.vtranslate('OVERWRITE_EXISTING_MSG2');
				Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
					function(e) {
						thisInstance.mapAddressDetails(addressDetails, response['data'], container);
					},
					function(error, err){}
				);
			}
			else
				thisInstance.mapAddressDetails(addressDetails, response['data'], container);
		};
		if (recordDetails)
			getRecordDetailsCallBack.call(this, recordDetails)
		else
			thisInstance.getRecordDetails(data).then(
				getRecordDetailsCallBack
				, function(error, err){}
			);
	},
	
	/**
	 * Function which will map the address details of the selected record
	 */
	mapAddressDetails : function(addressDetails, result, container) {
		for(var key in addressDetails) {
			container.find('[name="'+key+'"]').val(result[addressDetails[key]]);
			container.find('[name="'+key+'"]').trigger('change');
		}
	},
	
	/**
	 * Function which will check is the address details is not empty
	 * ED141016
	 */
	hasAddressDetails : function(addressDetails, result) {
		
		for(var key in addressDetails) {
			if(result[addressDetails[key]])
				return true;
		}
		return false;
	},
	
	/**
	 * Function to swap array
	 * @param Array that need to be swapped
	 */ 
	swapObject : function(objectToSwap){
		var swappedArray = {};
		var newKey,newValue;
		for(var key in objectToSwap){
			newKey = objectToSwap[key];
			newValue = key;
			swappedArray[newKey] = newValue;
		}
		return swappedArray;
	},
	
	/**
	 * Function to copy address between fields
	 * @param strings which accepts value as either odd or even
	 */
	copyAddress : function(swapMode, container){
		var thisInstance = this;
		var addressMapping = this.addressFieldsMappingInModule;
		if(swapMode == "false"){
			for(var key in addressMapping) {
				var fromElement = container.find('[name="'+key+'"]');
				var toElement = container.find('[name="'+addressMapping[key]+'"]');
				toElement.val(fromElement.val());
			}
		} else if(swapMode){
			var swappedArray = thisInstance.swapObject(addressMapping);
			for(var key in swappedArray) {
				var fromElement = container.find('[name="'+key+'"]');
				var toElement = container.find('[name="'+swappedArray[key]+'"]');
				toElement.val(fromElement.val());
			}
		}
	},
	
	
	/**
	 * Function to register event for copying address between two fileds
	 */
	registerEventForCopyingAddress : function(container){
		var thisInstance = this;
		var swapMode;
		jQuery('[name="copyAddress"]').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var target = element.data('target');
			if(target == "other"){
				swapMode = "false";
			} else if(target == "mailing"){
				swapMode = "true";
			}
			thisInstance.copyAddress(swapMode, container);
		})
	},
	
	
	/** ED150312
	 * Function to register event on address changing between two fields
	 */
	registerEventOnAddressChanging : function(container){
		var thisInstance = this;
		$('table.blockContainer.current-address :input').on('change', function(e){
			var element = jQuery(e.currentTarget);
			var $chkArchive = element.parents('table.blockContainer').find(':input[name="_archive_address"]');
			if($chkArchive.length == 0){
				$chkArchive = $('<input type="checkbox" checked="checked" name="_archive_address"/>')
					.appendTo(
						$('<label>archiver l\'adresse avant modification&nbsp;</label>')
							.css('float', 'right')
							.appendTo(element.parents('table.blockContainer').find('th.blockHeader'))
					)
				;
			}
		});
	},

	/** ED150515
	 * Function to register event on account reference status changing
	 * Also used from Detail.js
	 */
	registerEventOnAccountReferenceStatusChanging : function(container){
		var thisInstance = this;
		jQuery(document.body).on('change', ':input[name="reference"]',function(e){
			var element = jQuery(e.currentTarget)
			, isSetToTrue = element.attr('value') == '1'
			, thisContactId = container.find('input[name="record"]').val() //TODO Achtung if duplicating
			, accountId = container.find(':input[name="account_id"]').val();
			
			if (!thisContactId){ //DetailView
				thisContactId = $('#recordId').val();
				//may be empty on new
			}
			if (!accountId){ //DetailView
				accountId = container.find(':input[name="account_id"]').attr('data-value');
			}
			if (!accountId)
				return;
			var params = {
				'record' : accountId
				, 'source_module' : 'Accounts'
				, 'related_data' : 'MainContacts'
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : 'Contrôle en cours...',
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			thisInstance.getRecordDetails(params).then(
				function(data){
					progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
			
					var response = data['result']
					, mainContacts = response['related_data']['MainContacts'];
					if (!mainContacts) 
						return;
					var message = ''
					, thisContactWasMain = false
					, otherContactWasMain = false;
					for (var contactId in mainContacts) {
						if (thisContactId == contactId) {
							if (isSetToTrue) 
								continue;
							thisContactWasMain = true;
						}
						else if (isSetToTrue){
							var contact_html = '<a class="contact" recordid="' + contactId + '">'
								+ (mainContacts[contactId]['firstname'] + ' ' + mainContacts[contactId]['lastname']).trim()
								+ ' (' + mainContacts[contactId]['contact_no'] + ')</a>';
							if (message) message += '<br>';
							message += 'Actuellement, le contact ' + contact_html
								+ ' est défini comme référent du compte.'
								+ '<br>-> Ce contact ' + contact_html + ' va perdre son statut de référent.';
						}
						else
							otherContactWasMain = true;
					}
					if(!message && thisContactWasMain && !otherContactWasMain)
						message += 'Actuellement, ce contact est défini comme référent du compte.<br>Attention, le compte n\'aura plus de référent !';
					if (message) {
						Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
							function(e) {
								
							},
							function(error, err){
								//user canceled
								if(isSetToTrue)
									container.find(':input[name="reference"][value="0"]')[0].checked = true;
								else 
									container.find(':input[name="reference"][value="1"]')[0].checked = true;
								element.parents('.ui-buttonset:first')
									.buttonset('refresh');
							});
					}
				},
				function(error, err){
					progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
				}
			);
		});
	},
	
	
	/** ED150526
	 * Function to register event for synchronising group name and mailingstreet2
	 */
	registerEventForSynchronizeGroupNameMailingStreet2 : function(container){
		var thisInstance = this;
		//synchronise les deux zones de saisie
		jQuery('#Contacts_editView_fieldName_mailingstreet2_synchronize, #Contacts_editView_fieldName_mailingstreet2').on('change',function(e){
			var other = /_synchronize$/.test(this.id)
				? this.id.replace(/_synchronize$/, '')
				: this.id + '_synchronize';
			jQuery('#'+other).val(this.value);
			//refresh header
			jQuery('.mailingstreet2-synchronized').html(this.value);
		});
		//buttonset du format de l'adresse
		jQuery('input[name="mailingaddressformat"], input[name="mailingaddressformat_synchronize"]').on('change',function(e){
			var name = this.getAttribute('name')
			, other = /_synchronize$/.test(name)
				? name.replace(/_synchronize$/, '')
				: name + '_synchronize';
			jQuery('input[name="' + other + '"][value="' + this.value + '"]').attr("checked","checked").button('refresh');
		});
		//affiche/masque la saisie sous "isgroup"
		jQuery('input[name="isgroup"]').on('change',function(e){
			if(this.checked && this.value == 1)
				$('.mailingstreet2-synchronize-holder').show();
			else
				$('.mailingstreet2-synchronize-holder').hide();
		});
	},
	
	/**
	 * Function to check for Portal User
	 */
	checkForPortalUser : function(form){
		var element = jQuery('[name="portal"]',form);
		var response = element.is(':checked');
		var primaryEmailField = jQuery('[name="email"]');
		var primaryEmailValue = primaryEmailField.val();
		if(response){
			if(primaryEmailField.length == 0){
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS'));
				return false;
			}
			if(primaryEmailValue == ""){
				Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER'));
				return false;
			}
		}
		return true;
	},

	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var result = thisInstance.checkForPortalUser(form);
			if(!result){
				e.preventDefault();
			}
		})
	},

	/**
	 * Function to register recordpresave event
	 */
	registerDONOTMassChangeEvent : function(form){
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}
		var $element = $('#change-all-donot');
		$element
			.buttonset()
			//.find('input[type="radio"]')
				.change(function(e){
					var $block = $(this).parents('.blockContainer:first')
					, $buttons = $block.find('> tbody .buttonset input[type="radio"]')
					, forceValue = e.target.value;//$(this).val();//this.value;
					$buttons
						.filter(forceValue == '0' ? ':first-child' : ':not(:first-child)')
							.prop('checked', true)
							.next()
								.addClass("ui-state-active")
								.end()
							.end()
						.filter(forceValue == '1' ? ':first-child' : ':not(:first-child)')
							.prop('checked', false)
							.next()
								.removeClass("ui-state-active")
								.end()
						;
				})
		;
	},
	
	registerBasicEvents : function(container){
		this._super(container);
		this.registerReferenceSelectionEvent(container);
		this.registerEventForCopyingAddress(container);
		this.registerRecordPreSaveEvent(container);
		//ED150312
		this.registerEventOnAddressChanging(container);
		this.registerEventOnAccountReferenceStatusChanging(container);
		this.registerEventForSynchronizeGroupNameMailingStreet2(container);
		this.registerEventSNAButtonClickEvent(container);
		this.registerBlockAnimationEvent(); /*ED150707*/
		this.registerDONOTMassChangeEvent(); /*ED150912*/
	}
})