/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_Edit_Js",{

	//Event that will triggered when reference field is selected
	referenceSelectionEvent : 'Vtiger.Reference.Selection',

	//Event that will triggered when reference field is selected
	referenceDeSelectionEvent : 'Vtiger.Reference.DeSelection',

	//Event that will triggered before saving the record
	recordPreSave : 'Vtiger.Record.PreSave',

	refrenceMultiSelectionEvent : 'Vtiger.MultiReference.Selection',

	preReferencePopUpOpenEvent : 'Vtiger.Referece.Popup.Pre',

	editInstance : false,

	/**
	 * Function to get Instance by name
	 * @params moduleName:-- Name of the module to create instance
	 */
	getInstanceByModuleName : function(moduleName){
		if(typeof moduleName == "undefined"){
			moduleName = app.getModuleName();
		}
		var parentModule = app.getParentModuleName();
		if(parentModule == 'Settings'){
			var moduleClassName = parentModule+"_"+moduleName+"_Edit_Js";
			if(typeof window[moduleClassName] == 'undefined'){
				moduleClassName = moduleName+"_Edit_Js";
			}
			var fallbackClassName = parentModule+"_Vtiger_Edit_Js";
			if(typeof window[fallbackClassName] == 'undefined') {
				fallbackClassName = "Vtiger_Edit_Js";
			}
		} else {
			moduleClassName = moduleName+"_Edit_Js";
			fallbackClassName = "Vtiger_Edit_Js";
		}
		if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new window[fallbackClassName]();
		}
		return instance;
	},


	getInstance: function(){
		if(Vtiger_Edit_Js.editInstance == false){
			var instance = Vtiger_Edit_Js.getInstanceByModuleName();
			Vtiger_Edit_Js.editInstance = instance;
			return instance;
		}
		return Vtiger_Edit_Js.editInstance;
	}

},{

	formElement : false,

	getForm : function() {
		if(this.formElement == false){
			this.setForm(jQuery('#EditView'));
		}
		return this.formElement;
	},

	setForm : function(element){
		this.formElement = element;
		return this;
	},

	getPopUpParams : function(container) {
	    var params = {};
	    var sourceModule = app.getModuleName();
	    var popupReferenceModule = jQuery('input[name="popupReferenceModule"]',container).val();
	    var sourceFieldElement = jQuery('input[class="sourceField"]',container);
	    var sourceField = sourceFieldElement.attr('name');
	    var sourceRecordElement = jQuery('input[name="record"]');
	    var sourceRecordId = '';
	    if(sourceRecordElement.length > 0) {
		sourceRecordId = sourceRecordElement.val();
	    }
    
	    var isMultiple = false;
	    if(sourceFieldElement.data('multiple') == true){
		isMultiple = true;
	    }
    
	    var params = {
		    'module' : popupReferenceModule,
		    'src_module' : sourceModule,
		    'src_field' : sourceField,
		    'src_record' : sourceRecordId
	    }
	    
	    //ED150625
	    var fieldInfo = sourceFieldElement.data('fieldinfo');
	    if (fieldInfo.search_key) {
		params.search_key = fieldInfo.search_key;
		params.search_value = fieldInfo.search_value;
	    }
    
	    if(isMultiple) {
		params.multi_select = true ;
	    }
	    return params;
	},


	openPopUp : function(e){
		var thisInstance = this;
		var parentElem = jQuery(e.target).closest('td');

		var params = this.getPopUpParams(parentElem);
	
		var isMultiple = false;
		if(params.multi_select) {
		    isMultiple = true;
		}
	
		var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);
	
		var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
		sourceFieldElement.trigger(prePopupOpenEvent);
	
		if(prePopupOpenEvent.isDefaultPrevented()) {
		    return ;
		}
	
		var popupInstance =Vtiger_Popup_Js.getInstance();
		popupInstance.show(params,function(data){
			var responseData = JSON.parse(data);
			var dataList = new Array();
			for(var id in responseData){
				var data = {
					'name' : responseData[id].name,
					'id' : id
				}
				dataList.push(data);
				if(!isMultiple) {
				    thisInstance.setReferenceFieldValue(parentElem, data);
				}
			}
		
			if(isMultiple) {
			    sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
			}
		});
	},

	setReferenceFieldValue : function(container, params) {
		var sourceField = container.find('input[class="sourceField"]').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var sourceFieldDisplay = sourceField+"_display";
		var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
		var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();

		var selectedName = params.name;
		var id = params.id;

		fieldElement.val(id)
		fieldDisplayElement.val(selectedName).attr('readonly',true);
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});

		fieldDisplayElement.validationEngine('closePrompt',fieldDisplayElement);
	},

	proceedRegisterEvents : function(){
		if(jQuery('.recordEditView').length > 0){
			return true;
		}else{
			return false;
		}
	},

	referenceModulePopupRegisterEvent : function(container){
		var thisInstance = this;
		container.find('.relatedPopup').on("click",function(e){
			thisInstance.openPopUp(e);
		});
		container.find('.referenceModulesList').chosen().change(function(e){
			var element = jQuery(e.currentTarget);
			var closestTD = element.closest('td').next();
			var popupReferenceModule = element.val();
			var referenceModuleElement = jQuery('input[name="popupReferenceModule"]', closestTD);
			var prevSelectedReferenceModule = referenceModuleElement.val();
			referenceModuleElement.val(popupReferenceModule);

			//If Reference module is changed then we should clear the previous value
			if(prevSelectedReferenceModule != popupReferenceModule) {
				closestTD.find('.clearReferenceSelection').trigger('click');
			}
		});
	},

	getReferencedModuleName : function(parenElement){
		return jQuery('input[name="popupReferenceModule"]',parenElement).val();
	},

	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}
		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},
			function(error){
				//TODO : Handle error
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},

	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields : function(container) {
		var thisInstance = this;
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = thisInstance.getReferencedModuleName(tdElement);
				params.search_module = searchModule
				params.search_value = searchValue;
				thisInstance.searchModuleNames(params).then(function(data){
					var reponseDataList = new Array();
					var serverDataFormat = data.result
					if(serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							'label' : app.vtranslate('JS_NO_RESULTS_FOUND'),
							'type'  : 'no results'
						});
					}
					for(var id in serverDataFormat){
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select' : function(event, ui ){
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if(typeof selectedItemData.type != 'undefined' && selectedItemData.type=="no results"){
					return false;
				}
				selectedItemData.name = selectedItemData.value;
				var element = jQuery(this);
				var tdElement = element.closest('td');
				thisInstance.setReferenceFieldValue(tdElement, selectedItemData)
			},
			'change' : function(event, ui) {
				var element = jQuery(this);
				//if you dont have readonly attribute means the user didnt select the item
				if(element.attr('readonly')== undefined) {
					element.closest('td').find('.clearReferenceSelection').trigger('click');
				}
			},
			'open' : function(event,ui) {
				//To Make the menu come up in the case of quick create
				jQuery(this).data('autocomplete').menu.element.css('z-index','100001');

			}
		});
	},


	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearReferenceSelectionEvent : function(container) {
		container.find('.clearReferenceSelection').on('click', function(e){
			var element = jQuery(e.currentTarget);
			var parentTdElement = element.closest('td');
			var fieldNameElement = parentTdElement.find('.sourceField');
			var fieldName = fieldNameElement.attr('name');
			fieldNameElement.val('');
			parentTdElement.find('#'+fieldName+'_display').removeAttr('readonly').val('');
			element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
			e.preventDefault();
		})
	},

	/**
	 * Function which will register event to prevent form submission on pressing on enter
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerPreventingEnterSubmitEvent : function(container) {
		container.on('keypress', function(e){
            //Stop the submit when enter is pressed in the form
            var currentElement = jQuery(e.target);
            if(e.which == 13 && (!currentElement.is('textarea'))) {
                e. preventDefault();
            }
		})
	},

	/**
	 * Function which will give you all details of the selected record
	 * @params - an Array of values like {'record' : recordId, 'source_module' : searchModule, 'selectedName' : selectedRecordName}
	 */
	getRecordDetails : function(params) {
		var aDeferred = jQuery.Deferred();
		var url = "index.php?module="+app.getModuleName()
			+ "&action=GetData&record="+params['record']
			+ "&source_module="+params['source_module']
			+ (params['related_data'] ? "&related_data="+params['related_data'] : '')
			;
		AppConnector.request(url).then(
			function(data){
				if(data['success']) {
					aDeferred.resolve(data);
				} else {
					aDeferred.reject(data['message']);
				}
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},


	registerTimeFields : function(container) {
		app.registerEventForTimeFields(container);
	},

	referenceCreateHandler : function(container) {
		var thisInstance = this;
		var postQuickCreateSave  = function(data) {
		    var params = {};
		    params.name = data.result._recordLabel;
		    params.id = data.result._recordId;
		    thisInstance.setReferenceFieldValue(container, params);
		}
	
		var referenceModuleName = this.getReferencedModuleName(container);
		var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
		if(quickCreateNode.length <= 0) {
		    Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'))
		}
		quickCreateNode.trigger('click',{'callbackFunction':postQuickCreateSave});
	},

	/**
	 * Function which will register event for create of reference record
	 * This will allow users to create reference record from edit view of other record
	 */
	registerReferenceCreate : function(container) {
		var thisInstance = this;
		container.find('.createReferenceRecord').on('click', function(e){
			var element = jQuery(e.currentTarget);
			var controlElementTd = element.closest('td');

			thisInstance.referenceCreateHandler(controlElementTd);
		})
	},

	/**
	 * Function to register the event status change event
	 */
	registerEventStatusChangeEvent : function(container){
		var followupContainer = container.find('.followUpContainer');
		container.find('select[name="eventstatus"]').on('change',function(e){
			var selectedOption = jQuery(e.currentTarget).val();
			if(selectedOption == 'Held'){
				followupContainer.show();
			} else{
				followupContainer.hide();
			}
		});
	},

	/** ED150713
	 * Function to register the SNA address check button click event
	 */
	registerEventSNAButtonClickEvent : function(container){
		var thisInstance = this;
		container.find('button.address-sna-check').on('click',function(e){
			var $sourceBlock = jQuery(e.currentTarget).parents('.blockContainer:first')
			, values = thisInstance.getAddressBlockValuesForAddressCheck($sourceBlock);
			
			var params = {
				url : "index.php?module="+app.getModuleName()
					+ "&view=AddressCheckAjax",
				data : values
			};
			var progressIndicatorElement = jQuery.progressIndicator({
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			AppConnector.request(params).then(
				function(data){
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					if (typeof data !== 'string') 
						data = JSON.stringify(data);	
					$div = $('<div></div>')
						.html(data)
						.dialog({
							title: 'Contrôle externe de l\'adresse',
							width: 'auto',
							height: 'auto',
						});
					thisInstance.registerAddressCheckFormEvents($div, $sourceBlock);
				},
				function(error){
					progressIndicatorElement.progressIndicator({'mode': 'hide'});
					Vtiger_Helper_Js.showPnotify(error)
				}
			);
			return false;
		});
	},
	// Affiche l'adresse dans les pages blanches
	registerPagesBlanchesButtonClickEvent : function(container){
		var thisInstance = this;
		container.find('button.address-pagesblanches').on('click',function(e){
			thisInstance.openPagesBlanches(e);
			return false;
		});
	},
	
	openPagesBlanches: function(e){
		var thisInstance = this;
		var $sourceBlock = jQuery(e.currentTarget).parents('.blockContainer:first')
		, values = thisInstance.getAddressBlockValuesForAddressCheck($sourceBlock);
		var zipcode = values.address_mailingzip
		, city = values.address_mailingcity
		;
		$sourceBlock = jQuery(e.currentTarget).parents('form:first').find('.blockContainer:first');
		values = thisInstance.getAddressBlockValuesForAddressCheck($sourceBlock);
		var lastname = values.address_lastname
		, firstname = values.address_firstname
		;
		
		var url = "http://www.pagesjaunes.fr/pagesblanches/recherche"
			+ "?quoiqui=" + (firstname ? firstname.replace(' ', '+') : firstname)
				+ "+" + (lastname ? lastname.replace(' ', '+') : '')
			+ "&ou=" + (city.replace(' ', '+'))
				+ "+(" + (zipcode.replace(' ', '+')) + ")"
		;
		var win = window.open(url, '_blank');
		win.focus();
	},
	
	/** ED150713
	 * Function to register the SNA button click event
	 */
	getAddressBlockValuesForAddressCheck : function($block){
		var values = {};
		$block.find('input[name]:visible').each(function(){
			if (this.value
			&& this.getAttribute('name').indexOf('npai') === -1
			&& this.getAttribute('name').indexOf('format') === -1)
				values['address_' + this.getAttribute('name')] = this.value;
		});
		return values;
	},
	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerAddressCheckFormEvents : function(container, $sourceBlock) {
		var thisInstance = this
		, $form = container.find('form:first');
		$form
			.find('input[type="radio"]')
				.click(function(e){
					var $radio = $(this)
					, $tr = $radio.parents('tr:first')
					, $fieldName = $radio.attr('name')
					, newValue = $tr.find('.address-value.' + $radio.val() + '-value:first').text()
					, $input = $tr.find('input.address-result:first')
					;
					$input.val(newValue)
				})
				.end()
			.submit(function(e){
				var $form = $(e.target);
				thisInstance.setAddressBlockValuesFromAddressCheck($sourceBlock, $form);
				
				container.dialog('close');
				return false;
			})
			.end()
		;
	},
	
	/** ED150713
	 * Function to set the new address values
	 */
	setAddressBlockValuesFromAddressCheck : function($sourceBlock, $form){
		$form.find('input.address-result[name]:visible').each(function(){
			$('input[name="' + this.getAttribute('name') + '"]')
				.val(this.value)
				.change();
		});
	},
	
	/** ED150707
	 * Copy from Detail.js
	 */
	registerBlockAnimationEvent : function(){
		var detailContentsHolder = this.getForm();
		detailContentsHolder.on('click','.blockToggle',function(e){
			var currentTarget =  jQuery(e.currentTarget);
			var blockId = currentTarget.data('id');
			var closestBlock = currentTarget.closest('.blockContainer');
			var bodyContents = closestBlock.find('tbody');
			var data = currentTarget.data();
			var module = app.getModuleName();
			var hideHandler = function() {
				bodyContents.hide('slow');
				app.cacheSet(module+'.'+blockId, 0)
			}
			var showHandler = function() {
				bodyContents.show();
				app.cacheSet(module+'.'+blockId, 1)
			}
			var data = currentTarget.data();
			if(data.mode == 'show'){
				hideHandler();
				currentTarget.hide();
				closestBlock.find("[data-mode='hide']").show();
			}else{
				showHandler();
				currentTarget.hide();
				closestBlock.find("[data-mode='show']").show();
			}
		});

		//cases à cocher dans la barre de titre du bloc et qui le déploiement du bloc si actif
		//utilisé pour ContactEditView
		detailContentsHolder.on('click','.blockToggler > :input[type="checkbox"]',function(e){
			if (!e.currentTarget.checked) 
				return;
			var currentTarget =  jQuery(e.currentTarget);
			var block = currentTarget.parents('.blockHeader:first');
			var blockToggle = block.find("[data-mode='hide']:visible");
			blockToggle.click();
		});

	},

	registerCheckClose : function() {
		var checkClose = function() {
			return confirm('warning');
		};

	    window.onbeforeunload = checkClose;//TMP Why it does not work each time ?
		//window.onunload = checkClose2;
	},

	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents : function(container) {
		this.referenceModulePopupRegisterEvent(container);
		this.registerAutoCompleteFields(container);
		this.registerClearReferenceSelectionEvent(container);
		this.registerPreventingEnterSubmitEvent(container);
		this.registerTimeFields(container);
		//Added here instead of register basic event of calendar. because this should be registered all over the places like quick create, edit, list..
		this.registerEventStatusChangeEvent(container);
		this.registerRecordAccessCheckEvent(container);
		this.registerEventForPicklistDependencySetup(container);

		//ED151029
		this.setBrowserTitle();
		//this.registerCheckClose();
	},

	/**
	 * Function to register event for image delete
	 */
	registerEventForImageDelete : function(){
		var formElement = this.getForm();
		var recordId = formElement.find('input[name="record"]').val();
		formElement.find('.imageDelete').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var parentTd = element.closest('td');
			var imageUploadElement = parentTd.find('[name="imagename[]"]');
			var fieldInfo = imageUploadElement.data('fieldinfo');
			var mandatoryStatus = fieldInfo.mandatory;
			var imageData = element.closest('div').find('img').data();
			var params = {
				'module' : app.getModuleName(),
				'action' : 'DeleteImage', 
				'imageid' : imageData.imageId,
				'record' : recordId

			}
			AppConnector.request(params).then(
				function(data){
					if(data.success ==  true){
						element.closest('div').remove();
						var exisitingImages = parentTd.find('[name="existingImages"]');
						if(exisitingImages.length < 1 && mandatoryStatus){
							formElement.validationEngine('detach');
							imageUploadElement.attr('data-validation-engine','validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]');
							formElement.validationEngine('attach');
						}
					}
				},
				function(error){
					//TODO : Handle error
				}
			)
		});
	},

	triggerDisplayTypeEvent : function() {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if(widthType) {
			var elements = jQuery('#EditView').find('td');
			elements.addClass(widthType);
		}
	},

	registerSubmitEvent: function() {
		var editViewForm = this.getForm();

		editViewForm.submit(function(e){

			//Form should submit only once for multiple clicks also
			if(typeof editViewForm.data('submit') != "undefined") {
				return false;
			} else {
				var module = jQuery(e.currentTarget).find('[name="module"]').val();
				if(editViewForm.validationEngine('validate')) {
					//ED151225 : TODO à voir si ça a des conséquences (genre retour d'erreur côté serveur). Peut-être faut il déplacer cet affichage ?
					var progressIndicatorElement = jQuery.progressIndicator({
						'position' : 'html',
						'blockInfo' : {
							'enabled' : true
						}
					});
					//Once the form is submiting add data attribute to that form element
					editViewForm.data('submit', 'true');
					//on submit form trigger the recordPreSave event
					var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
					editViewForm.trigger(recordPreSaveEvent, {'value' : 'edit'});
					if(recordPreSaveEvent.isDefaultPrevented()) {
						//If duplicate record validation fails, form should submit again
						editViewForm.removeData('submit');
						e.preventDefault();
					}
				} else {
					//If validation fails, form should submit again
					editViewForm.removeData('submit');
					// to avoid hiding of error message under the fixed nav bar
					app.formAlignmentAfterValidation(editViewForm);
				}
			}
		});
	},

	/*
	 * Function to check the view permission of a record after save
	 */

	registerRecordAccessCheckEvent : function(form) {

		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var assignedToSelectElement = jQuery('[name="assigned_user_id"]',form);
			if(assignedToSelectElement.data('recordaccessconfirmation') == true) {
				return;
			}else{
				if(assignedToSelectElement.data('recordaccessconfirmationprogress') != true) {
					var recordAccess = assignedToSelectElement.find('option:selected').data('recordaccess');
					if(recordAccess == false) {
						var message = app.vtranslate('JS_NO_VIEW_PERMISSION_AFTER_SAVE');
						Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
						function(e) {
							assignedToSelectElement.data('recordaccessconfirmation',true);
							assignedToSelectElement.removeData('recordaccessconfirmationprogress');
							form.append('<input type="hidden" name="returnToList" value="true" />');
							form.submit();
						},
						function(error, err){
							assignedToSelectElement.removeData('recordaccessconfirmationprogress');
							e.preventDefault();
						});
						assignedToSelectElement.data('recordaccessconfirmationprogress',true);
					} else {
						return true;
					}
				}
			}
			e.preventDefault();
		});
	},

	/**
	 * Function to register event for setting up picklistdependency
	 * for a module if exist on change of picklist value
	 */
	registerEventForPicklistDependencySetup : function(container){
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]',container);
		if(picklistDependcyElemnt.length <= 0) {
		    return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if(sourcePicklists.length <= 0){
			return;
		}

		var sourcePickListNames = "";
		for(var i=0;i<sourcePicklists.length;i++){
			sourcePickListNames += '[name="'+sourcePicklists[i]+'"],';
		}
		var sourcePickListElements = container.find(sourcePickListNames);

		sourcePickListElements.on('change',function(e){
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];

			if(typeof targetObjectForSelectedSourceValue == 'undefined'){
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap,function(targetPickListName,targetPickListValues){
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if(typeof targetPickListMap == "undefined"){
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[name="'+targetPickListName+'"]',container);
				if(targetPickList.length <= 0){
					return;
				}

				var listOfAvailableOptions = targetPickList.data('availableOptions');
				if(typeof listOfAvailableOptions == "undefined"){
					listOfAvailableOptions = jQuery('option',targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				var optionSelector = '';
				optionSelector += '[value=""],';
				for(var i=0; i<targetPickListMap.length; i++){
					optionSelector += '[value="'+targetPickListMap[i]+'"],';
				}
				var targetOptions = listOfAvailableOptions.filter(optionSelector);
				//Before updating the list, selected option should be updated
				var targetPickListSelectedValue = '';
				var targetPickListSelectedValue = targetOptions.filter('[selected]').val();
				targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("liszt:updated");
			})
		});

		//To Trigger the change on load
		sourcePickListElements.trigger('change');
	},

	registerEvents: function(){
		var editViewForm = this.getForm();
		var statusToProceed = this.proceedRegisterEvents();
		if(!statusToProceed){
			return;
		}

		this.registerBasicEvents(editViewForm);
		this.registerEventForImageDelete();
		this.registerSubmitEvent();

		app.registerEventForDatePickerFields('#EditView');
		editViewForm.validationEngine(app.validationEngineOptions);
		this.registerReferenceCreate(editViewForm);
		//this.triggerDisplayTypeEvent();
	},
	
	
	
	/** ED150713
	 */
	setBrowserTitle : function(){
		var title = $('.contentHeader h3:first').text();
		if (title)
			document.title = title;
	},
});