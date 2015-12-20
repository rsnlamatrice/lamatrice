/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Contacts_Detail_Js",{},{
		
	
	loadRelatedList : function(pageNumber){
		var relatedListInstance = new Contacts_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		var params = {'page':pageNumber};
		this.clearSelectedRecords();
		relatedListInstance.loadRelatedList(params);
	},
	
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		var primaryEmailField = jQuery('[name="email"]');
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		form.on(this.fieldPreSave,'[name="portal"]', function(e, data) {
			var portalField = jQuery(e.currentTarget);
			
			var primaryEmailValue = primaryEmailField.val();
			var isAlertAlreadyShown = jQuery('.ui-pnotify').length;
					
			
			if(portalField.is(':checked')){
				if(primaryEmailField.length == 0){
					if(isAlertAlreadyShown <= 0) {
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS'));
					}
					e.preventDefault();
				} 
				if(primaryEmailValue == ""){
					if(isAlertAlreadyShown <= 0) {
						Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER'));
					}
					e.preventDefault();
 				} 
			}
		})
	},
	
	
	
	registerEventForRelatedListPagination : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','#relatedListNextPageButton',function(e){
			var element = jQuery(e.currentTarget);
			if(element.attr('disabled') == "disabled"){
				return;
			}
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler().then(function(data){
				switch (relatedModuleName) {
				  case "Critere4D":
				  case "Documents":
					var relatedMultiDatesCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedMultiDatesCntModule){
						thisInstance.registerRelatedMultiDatesCntActions(true);
					}
					break;
				  case "Contacts":
					var relatedContactsCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedContactsCntModule){
						thisInstance.registerRelatedContactsCntActions(true);
					}
					break;
				  default:
					break;
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPreviousPageButton',function(){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.previousPageHandler().then(function(data){
				switch (relatedModuleName) {
				  case "Critere4D":
				  case "Documents":
					var relatedMultiDatesCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedMultiDatesCntModule){
						thisInstance.registerRelatedMultiDatesCntActions(true);
					}
					break;
				  case "Contacts":
					var relatedContactsCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedContactsCntModule){
						thisInstance.registerRelatedContactsCntActions(true);
					}
					break;
				  default:
					break;
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPageJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.getRelatedPageCount();
		});
		detailContentsHolder.on('click','#relatedListPageJumpDropDown > li',function(e){
			e.stopImmediatePropagation();
		}).on('keypress','#pageToJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.pageJumpHandler(e).then(function(data){
				switch (relatedModuleName) {
				  case "Critere4D":
				  case "Documents":
					var relatedMultiDatesCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedMultiDatesCntModule){
						thisInstance.registerRelatedMultiDatesCntActions(true);
					}
					break;
				  case "Contacts":
					var relatedContactsCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedContactsCntModule){
						thisInstance.registerRelatedContactsCntActions(true);
					}
					break;
				  default:
					break;
				}
			});
		});
	},
	

	/**
	 * Function to register Event for Sorting
	 */
	registerEventForRelatedList : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','.relatedListHeaderValues',function(e){
			var element = jQuery(e.currentTarget);
			/*
			var selectedTabElement = thisInstance.getSelectedTab();
			var widgetHolder = $(this).parents('.summaryWidgetContainer:first');
			if (widgetHolder.length === 0) 
			    widgetHolder = false;
			var relatedModuleName = thisInstance.getRelatedModuleName(widgetHolder);
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);*/
			var relatedController = thisInstance.getRelatedListController(e);
			relatedController.sortHandler(element);
		});
		
		detailContentsHolder.on('click', 'button.selectRelation', function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			/*var widgetHolder = $(this).parents('.summaryWidgetContainer:first');
			if (widgetHolder.length === 0) 
			    widgetHolder = false;
			var relatedModuleName = thisInstance.getRelatedModuleName(widgetHolder);
			switch (relatedModuleName ){
			case "Critere4D":
			case "Documents":
			case "Contacts":
				var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			      break;
			default:
			      var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			      break;
			}*/
			var relatedController = thisInstance.getRelatedListController(e);
			
			relatedController.showSelectRelationPopup().then(function(data){
				var emailEnabledModule = jQuery(data).find('[name="emailEnabledModules"]').val();
				if(emailEnabledModule){
					thisInstance.registerEventToEditRelatedStatus();
				}
				/* ED14122
				 * ré-enregistre les événements des éléments de la liste (sélection de date, saisie du champ de données)
				 */
				/*switch (relatedModuleName ){
				case "Critere4D":
				case "Documents":
					relatedController.registerEventsMultiDates();
					break;
				case "Contacts":
					relatedController.registerEventsContacts();
					break;
				default:
				      break;
				}*/
				relatedController.registerEvents();
			});
		});

		detailContentsHolder.on('click', 'a.relationDelete', function(e){
			e.stopImmediatePropagation();
			
			// ED141008
			var widgetHolder = $(this).parents('.summaryWidgetContainer:first');
			if (widgetHolder.length === 0) 
			    widgetHolder = false;
			var relatedModuleName = thisInstance.getRelatedModuleName(widgetHolder);
			switch (relatedModuleName ){
			  case "Critere4D":
		      case "Documents":
				return thisInstance.deleteRelatedListMultiDates_OnClick.call(this, thisInstance, e);
			  case "Contacts":
				return thisInstance.deleteRelatedListContacts_OnClick.call(this, thisInstance, e);
			  default:
				break;
			}
			
			var element = jQuery(e.currentTarget);
			var instance = Vtiger_Detail_Js.getInstance();
			var key = instance.getDeleteMessageKey();
			var message = app.vtranslate(key);
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
				function(e) {
					var row = element.closest('tr');
					var relatedRecordid = row.data('id');
					var selectedTabElement = thisInstance.getSelectedTab();
					var relatedModuleName = thisInstance.getRelatedModuleName();
					var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
					relatedController.deleteRelation([relatedRecordid]).then(function(response){
						relatedController.loadRelatedList();
					});
				},
				function(error, err){
				}
			);
		});

		/* ED50312
		 * Contact -> ContactAddresses related list -> Delete (see layouts\vlayout\modules\Contacts\RelatedListContactAddresses.tpl)
		*/
		detailContentsHolder.on('click', 'a.relatedRecordDelete', function(e){
			e.stopImmediatePropagation();
			
			var element = jQuery(e.currentTarget);
			var row = element.closest('tr');
			var relatedRecordid = row.data('id');
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var deleteRecordActionUrl = "index.php?module="+relatedModuleName+"&action=Delete&record="+relatedRecordid;
			
			var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
			Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(function(data) {
					AppConnector.request(deleteRecordActionUrl+'&ajaxDelete=true').then(
					function(data){
						if(data.success == true){
							var selectedTabElement = thisInstance.getSelectedTab();
							var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
							relatedController.deleteRelation([relatedRecordid]).then(function(response){
								relatedController.loadRelatedList();
							});
						}else{
							Vtiger_Helper_Js.showPnotify(data.error.message);
						}
					});
				},
				function(error, err){
				}
			);
		});

		/* ED50528
		 * Contact -> mailingstreet2 synchronized
		*/
		detailContentsHolder.on('change', 'input[name="mailingstreet2"]', function(e){
			$('.' + this.getAttribute('name') + '-synchronized').html(this.value);
			
		});

		// ED141008
		var relatedModuleName = this.getSelectedTab().data('module');
		switch (relatedModuleName ){
		  case "Documents":
		  case "Critere4D":
			// Critere4D add relation
			detailContentsHolder.on('click', 'a.relationAdd', function(e){
				e.stopImmediatePropagation();
				var element = jQuery(e.currentTarget);
				var instance = Vtiger_Detail_Js.getInstance();
				var key = instance.getDeleteMessageKey();
				var message = app.vtranslate(key);
				var row = element.closest('tr');
				var relatedRecordid = row.data('id');
				var selectedTabElement = thisInstance.getSelectedTab();
				var widgetHolder = $(this).parents('.summaryWidgetContainer:first');
				if (widgetHolder.length === 0) 
				    widgetHolder = false;
				var relatedModuleName = thisInstance.getRelatedModuleName(widgetHolder);
				var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
				relatedController.addRelationMultiDates([relatedRecordid]).then(function(response){
					relatedController.loadRelatedList().then(function(data){
						thisInstance.registerRelatedMultiDatesCntActions(true);
					});
				});
			});
			
			var selectedTabElement = this.getSelectedTab();
			var relatedController = new Contacts_RelatedList_Js(this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			//TODO relatedController n'est pas initialisé comme d'autres objects, il manque les propriétés, en particulier relatedModulename et parentRecordId
			if(!relatedController.relatedModulename)
				Vtiger_RelatedList_Js.prototype.init.call(relatedController, this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.registerEventsMultiDates();
			break;
		
		  case "Contacts":
			// Contacts add relation
			detailContentsHolder.on('click', 'a.relationAdd', function(e){
				e.stopImmediatePropagation();
				var element = jQuery(e.currentTarget);
				var instance = Vtiger_Detail_Js.getInstance();
				var key = instance.getDeleteMessageKey();
				var message = app.vtranslate(key);
				var row = element.closest('tr');
				var relatedRecordid = row.data('id');
				var selectedTabElement = thisInstance.getSelectedTab();
				var relatedModuleName = thisInstance.getRelatedModuleName();
				var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
				relatedController.addRelationContacts([relatedRecordid]).then(function(response){
					relatedController.loadRelatedList().then(function(data){
						thisInstance.registerRelatedContactsCntActions(true);
					});
				});
			});
			
			var selectedTabElement = this.getSelectedTab();
			var relatedController = new Contacts_RelatedList_Js(this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			//TODO relatedController n'est pas initialisé comme d'autres objects, il manque les propriétés, en particulier relatedModulename et parentRecordId
			if(!relatedController.relatedModulename)
				Vtiger_RelatedList_Js.prototype.init.call(relatedController, this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.registerEventsContacts();
			break;
		  default:
			break;
		}
	},
	
	/**
	 * Function to register Event for Contacts
	 */
	deleteRelatedListContacts_OnClick : function(thisInstance, e){
		var element = jQuery(e.currentTarget);
		var instance = Vtiger_Detail_Js.getInstance();
		var key = instance.getDeleteMessageKey();
		var message = app.vtranslate(key);
		var dateapplication = this.getAttribute('dateapplication');
		Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
			function(e) {
				var row = element.closest('tr');
				var relatedRecordid = row.data('id');
				var selectedTabElement = thisInstance.getSelectedTab();
				var relatedModuleName = thisInstance.getRelatedModuleName();
				var relatedController = new Contacts_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
				relatedController.deleteRelationContacts([relatedRecordid], [dateapplication]).then(function(response){
					relatedController.loadRelatedList().then(function(data){
						thisInstance.registerRelatedContactsCntActions(true);
					});
				});
			},
			function(error, err){
			}
		);
	},
	
	/**
	 * Function to register related actions
	 * init_fields for datepicker on navigation changes
	 */
	registerRelatedContactsCntActions : function(init_fields) {
		this.registerEventForRelatedList();
		if (init_fields) { /*ED141007*/
			app.registerEventForDatePickerFields(this.detailViewContentHolder);
		}
	},
	
	
	
	
	/* Enregistre les événements
	 * en particulier le click sur un onglet de table liée
	 */
	registerEventForRelatedTabClick : function(){
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var detailContainer = detailContentsHolder.closest('div.detailViewInfo');
		app.registerEventForDatePickerFields(detailContentsHolder);
		//Attach time picker event to time fields
		app.registerEventForTimeFields(detailContentsHolder);

		jQuery('.related', detailContainer).on('click', 'li', function(e, urlAttributes){
			
			var tabElement = jQuery(e.currentTarget);
			var element = jQuery('<div></div>');
			element.progressIndicator({
				'position':'html',
				'blockInfo' : {
					'enabled' : true,
					'elementToBlock' : detailContainer
				}
			});
			var url = tabElement.data('url');
			if(typeof urlAttributes != 'undefined'){
				var callBack = urlAttributes.callback;
				delete urlAttributes.callback;
			}
			thisInstance.loadContents(url,urlAttributes).then(
				function(data){
					thisInstance.deSelectAllrelatedTabs();
					thisInstance.markTabAsSelected(tabElement);
					Vtiger_Helper_Js.showHorizontalTopScrollBar();
					element.progressIndicator({'mode': 'hide'});
					if(typeof callBack == 'function'){
						callBack(data);
					}
					//Summary tab is clicked
					if(tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
						thisInstance.loadWidgets();
						thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
					}

					// Let listeners know about page state change.
					app.notifyPostAjaxReady();
					
					// ED141008
					thisInstance.registerEventForRelatedList();
				},
				function (){
					//TODO : handle error
					element.progressIndicator({'mode': 'hide'});
				}
			);
		});
	},
	
	/**
	 * Function which will register all the events
	 */
	registerEvents : function() {
		this.registerEventForRelatedList();
		var form = this.getForm();
		this._super();
		this.registerRecordPreSaveEvent(form);
		//ED150515
		Vtiger_Edit_Js.getInstance().registerEventOnAccountReferenceStatusChanging(form);
	}
	
	
})