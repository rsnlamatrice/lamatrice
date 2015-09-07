/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("Critere4D_Detail_Js",{},{
	
	loadRelatedList : function(pageNumber){
		var relatedListInstance = new Critere4D_RelatedList_Js(this.getRecordId(), app.getModuleName(), this.getSelectedTab(), this.getRelatedModuleName());
		var params = {'page':pageNumber};
		this.clearSelectedRecords();
		relatedListInstance.loadRelatedList(params);
	},
	
	/**
	 * Function to clear selected records
	 */
	clearSelectedRecords : function() {
		jQuery('[name="selectedIds"]').data('selectedIds',"");
		jQuery('[name="excludedIds"]').data('excludedIds',"");
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
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.nextPageHandler().then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
				
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPreviousPageButton',function(){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.previousPageHandler().then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
				}
			});
		});
		detailContentsHolder.on('click','#relatedListPageJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.getRelatedPageCount();
		});
		detailContentsHolder.on('click','#relatedListPageJumpDropDown > li',function(e){
			e.stopImmediatePropagation();
		}).on('keypress','#pageToJump',function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.pageJumpHandler(e).then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
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
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.sortHandler(element).then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
				}
			});
		});
		
		detailContentsHolder.on('click', 'button.selectRelation', function(e){
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.showSelectRelationPopup().then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
				}
			});
		});

		detailContentsHolder.on('click', 'a.relationDelete', function(e){
			e.stopImmediatePropagation();
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
					var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
					relatedController.deleteRelation([relatedRecordid], [dateapplication]).then(function(response){
						relatedController.loadRelatedList().then(function(data){
							thisInstance.registerRelatedCritere4DCntActions(true);
						});
					});
				},
				function(error, err){
				}
			);
		});

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
			var relatedController = new Critere4D_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.addRelation([relatedRecordid]).then(function(response){
				relatedController.loadRelatedList().then(function(data){
					thisInstance.registerRelatedCritere4DCntActions(true);
				});
			});
		});
	},
	
	/**
	 * Function to register event for adding related record for module
	 */
	registerEventForAddingRelatedRecord : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','[name="addButton"]',function(e){
			var element = jQuery(e.currentTarget);
			var selectedTabElement = thisInstance.getSelectedTab();
			var relatedModuleName = thisInstance.getRelatedModuleName();
			var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ relatedModuleName +'"]');
			if(quickCreateNode.length <= 0) {
				window.location.href = element.data('url');
				return;
			}
            
			var relatedController = new Vtiger_RelatedList_Js(thisInstance.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
			relatedController.addRelatedRecord(element).then(function(data){
				var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
				if(relatedCritere4DCntModule){
					thisInstance.registerRelatedCritere4DCntActions(true);
				}
			});
		});
	},
	
	/**
	 * Function to register related actions
	 * init_fields for datepicker on navigation changes
	 */
	registerRelatedCritere4DCntActions : function(init_fields) {
		var moduleName = app.getModuleName();
		var className = moduleName+"_List_Js";
		var listInstance = new window[className]();
		listInstance.registerEvents();
		listInstance.markSelectedRecords();
		this.registerRelatedListEvents();
		if (init_fields) { /*ED140917*/
			app.registerEventForDatePickerFields(this.detailViewContentHolder);
		}
	},
	
	registerEventForRelatedTabClick : function(){
		var thisInstance = this;
		var detailContentsHolder = thisInstance.getContentHolder();
		var detailContainer = detailContentsHolder.closest('div.detailViewInfo');
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
					element.progressIndicator({'mode': 'hide'});
					var relatedCritere4DCntModule = jQuery(data).find('[name="dateapplication"]:first').val();
					if(relatedCritere4DCntModule){
						var listInstance = new Critere4D_List_Js();
						listInstance.registerEvents();
						thisInstance.registerRelatedListEvents();
					}
					if(typeof callBack == 'function'){ 
						callBack(data);
					}
					//Summary tab is clicked
					if(tabElement.data('linkKey') == thisInstance.detailViewSummaryTabLabel) {
						thisInstance.loadWidgets();
						thisInstance.registerSummaryViewContainerEvents(detailContentsHolder);
					}
				},
				function (){
					//TODO : handle error
					element.progressIndicator({'mode': 'hide'});
				}
			);
		});
	},
	
	registerEventTransformAsNewDocument : function(){
		var thisInstance = this;
		$('body').on('click','#Critere4D_detailView_moreAction_LBL_TRANSFORM_AS_NEW_DOCUMENT',function(e){
			var url = $(this).children('[href]').attr('href');
			e.stopImmediatePropagation();

			var postData = {
				"selected_ids": [thisInstance.getRecordId()],
				"viewname" : 0,
				"title": app.vtranslate('JS_TRANSFORM_AS_NEW_DOCUMENT'),
			};
			var params = {
				"url": "index.php?module=Documents&view=MoveDocuments",//Select folder
				"data" : postData
			};
			AppConnector.request(params).then(
				function(data) {
					var callBackFunction = function(data){
						thisInstance.registrerSelectFolderSubmit(url);
					}
					app.showModalWindow(data,callBackFunction);
				}
			);
			
			return false;
		});
	},
	
	registrerSelectFolderSubmit : function(saveUrl){
		var thisInstance = this;
		jQuery('#moveDocuments').on('submit',function(e){
			var formData = jQuery(e.currentTarget).serializeFormData();
			var params = {
				url: saveUrl,
				data: {
					folderId: formData['folderid'],
				}
			};
				
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : app.vtranslate('JS_TRANSFORM_AS_NEW_DOCUMENT'),
				'position' : 'html'
			});
			
			AppConnector.request(params).then(
				function(data) {
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
					if(data && data.success){
						var result = data.result;
						app.hideModalWindow();
						var  params = {
							title : app.vtranslate('JS_TRANSFORM_AS_NEW_DOCUMENT'),
							text : result.message,
							delay: '2000',
							type: 'success'
						}
						Vtiger_Helper_Js.showPnotify(params);
						if(result.href)
							document.location.href = result.href;
					} else {
						var  params = {
							title : app.vtranslate('JS_OPERATION_DENIED'),
							text : result.message,
							delay: '2000',
							type: 'error'
						}
						Vtiger_Helper_Js.showPnotify(params);
					}
				}
				, function(error, err){
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
				}
			);
			e.preventDefault();
		});
		
	},
	
	registerEventTransferToDocument : function(){
		var thisInstance = this;
		$('body').on('click','#Critere4D_detailView_moreAction_LBL_TRANSFER_TO_DOCUMENT',function(e){
			var url = $(this).children('[href]').attr('href');
			e.stopImmediatePropagation();

			thisInstance.showSelectDocumentPopup(url);
			
			return false;
		});
	},
	
	showSelectDocumentPopup : function(saveUrl){
		var thisInstance = this;
		var params = {
			'module' : 'Documents',
			'src_module' : app.getModuleName(),
			'src_record' : thisInstance.getRecordId(),
			'multi_select' : false
		}
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(params, function(responseString){
			var responseData = JSON.parse(responseString);
			var relatedIdList = Object.keys(responseData);
			var params = {
				url: saveUrl,
				data: {
					documentId: relatedIdList[0],
				}
			};
			
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : app.vtranslate('JS_TRANSFER_TO_DOCUMENT'),
				'position' : 'html'
			});
			AppConnector.request(params).then(
				function(data) {
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
					if(data && data.success){
						var result = data.result;
						app.hideModalWindow();
						var  params = {
							title : app.vtranslate('JS_TRANSFER_TO_DOCUMENT'),
							text : result.message,
							delay: '2000',
							type: 'success'
						}
						Vtiger_Helper_Js.showPnotify(params);
						if(result.href)
							document.location.href = result.href;
					} else {
						var  params = {
							title : app.vtranslate('JS_OPERATION_DENIED'),
							text : result.message,
							delay: '2000',
							type: 'error'
						}
						Vtiger_Helper_Js.showPnotify(params);
					}
				}
			);
		});
	},
	
	
	/**
	 * Function to register related list events
	 */
	registerRelatedListEvents : function(){
		var selectedTabElement = this.getSelectedTab();
		var relatedModuleName = this.getRelatedModuleName();
		var relatedController = new Critere4D_RelatedList_Js(this.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName);
		relatedController.registerEvents();
	},
	
	registerEvents : function(){
		this.registerRelatedListEvents();
		this._super();
		//Calling registerevents of critere4ds list to handle checkboxs click of related records
		var listInstance = Vtiger_List_Js.getInstance();
		listInstance.registerEvents();
		//ED150907
		this.registerEventTransformAsNewDocument();
		this.registerEventTransferToDocument();
	}
})