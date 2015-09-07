/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Critere4D_List_Js",{
		triggerTransformAsNewDocument : function(massActionUrl){
			var thisInstance = this,
				listInstance = Vtiger_List_Js.getInstance();
			var validationResult = listInstance.checkListRecordSelected();
			if(validationResult != true){
				
				var postData = {
					"selected_ids": [0],
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
							thisInstance.registrerSelectFolderSubmit(massActionUrl);
						}
						app.showModalWindow(data,callBackFunction);
					}
				);
				
			} else {
				listInstance.noRecordSelectedAlert();
			}
		},
		
		registrerSelectFolderSubmit : function(massActionUrl){
			var thisInstance = this;
			jQuery('#moveDocuments').on('submit',function(e){
				var formData = jQuery(e.currentTarget).serializeFormData();
				thisInstance.triggerTransformAsNewDocumentAction( massActionUrl, formData['folderid']);
				return false;
			});
		},
		
		triggerTransformAsNewDocumentAction : function(massActionUrl, folderId, callBackFunction){
			
			var listInstance = Vtiger_List_Js.getInstance();
			
			// Compute selected ids, excluded ids values, along with cvid value and pass as url parameters
			
			var pageNumber = jQuery('#pageNumber').val();
			var cvId = listInstance.getCurrentCvId();
			var selectedIds = listInstance.readSelectedIds(true);
			var excludedIds = listInstance.readExcludedIds(true);
			
			var postData = {
				'page' : pageNumber,
				'viewname' : cvId,
				'selected_ids' : selectedIds,
				'excluded_ids' : excludedIds,
				'folderId' : folderId,
			}
	
			var searchValue = listInstance.getAlphabetSearchValue();
		
			if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
				postData['search_key'] = listInstance.getRequestSearchField();
				postData['search_value'] = searchValue;
				postData['operator'] = listInstance.getSearchOperator();
			}
				
			var actionParams = {
				"type":"POST",
				"url":massActionUrl,
				"dataType":"html",
				"data" : postData
			};
	
			var loadingMessage = jQuery('.listViewLoadingMsg').text();
			var progressIndicatorElement = jQuery.progressIndicator({
				'message' : loadingMessage,
				'position' : 'html',
				'blockInfo' : {
					'enabled' : true
				}
			});
			
			AppConnector.request(actionParams).then(
				function(data) {
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
					if (typeof data === 'string' && /^\{[\s\S]*\}$/.test(data))
						data = eval('('+data+')');
					if(data && data.success) {
						app.hideModalWindow();
						var  params = {
							title : app.vtranslate('JS_TRANSFORM_AS_NEW_DOCUMENT'),
							text : data.message,
							delay: '2000',
							type: 'success'
						}
						Vtiger_Helper_Js.showPnotify(params);
						
						listInstance.getListViewContentContainer()
							.find('tr.listViewHeaders.filters')
								.hover()
								.find('.actionImages .icon-refresh')
									.click()
						;
					}
					else {
						var  params = {
							title : app.vtranslate('JS_OPERATION_DENIED'),
							text : typeof data === 'string' ? data : data.message,
							delay: '2000',
							type: 'error'
						}
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function(error,err){
					progressIndicatorElement.progressIndicator({
						'mode' : 'hide'
					});
					var  params = {
						title : app.vtranslate('JS_OPERATION_DENIED'),
						text : err,
						delay: '2000',
						type: 'error'
					}
					Vtiger_Helper_Js.showPnotify(params);
	
				}
			);
		},
	},{
	
	readSelectedIds : function(decode){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super(decode);
		}
		var selectedIdsElement = jQuery('#selectedIds');
		var selectedIdsDataAttr = 'selectedIds';
		var selectedIdsElementDataAttributes = selectedIdsElement.data();
		var selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		if (selectedIds == "" || selectedIds == null) {
			selectedIds = new Array();
			this.writeSelectedIds(selectedIds);
		} else {
			selectedIds = selectedIdsElementDataAttributes[selectedIdsDataAttr];
		}
		if(decode == true){
			if(typeof selectedIds == 'object'){
				return JSON.stringify(selectedIds);
			}
		}
		return selectedIds;
	},
	
	readExcludedIds : function(decode){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super(decode);
		}
		var exlcudedIdsElement = jQuery('#excludedIds');
		var excludedIdsDataAttr = 'excludedIds';
		var excludedIdsElementDataAttributes = exlcudedIdsElement.data();
		var excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		if (excludedIds == "" || excludedIds == null) {
			excludedIds = new Array();
			this.writeExcludedIds(excludedIds);
		}else{
			excludedIds = excludedIdsElementDataAttributes[excludedIdsDataAttr];
		}
		if(decode == true){
			if(typeof excludedIds == 'object') {
				return JSON.stringify(excludedIds);
			}
		}
		return excludedIds;
	},

	writeSelectedIds : function(selectedIds){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super(selectedIds);
			return;
		}
		jQuery('#selectedIds').data('selectedIds',selectedIds);
	},

	writeExcludedIds : function(excludedIds){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super(excludedIds);
			return;
		}
		jQuery('#excludedIds').data('excludedIds',excludedIds);
	},
	
	/**
	 * Function to mark selected records
	 */
	markSelectedRecords : function(){
		var thisInstance = this;
		var selectedIds = this.readSelectedIds();
		if(selectedIds != ''){
			if(selectedIds == 'all'){
				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
				});
				jQuery('#deSelectAllMsgDiv').show();
				var excludedIds = jQuery('[name="excludedIds"]').data('excludedIds');
				if(excludedIds != ''){
					jQuery('#listViewEntriesMainCheckBox').attr('checked',false);
					jQuery('.listViewEntriesCheckBox').each( function(index,element) {
						if(jQuery.inArray(jQuery(element).val(),excludedIds) != -1){
							jQuery(element).attr('checked', false).closest('tr').removeClass('highlightBackgroundColor');
						}
					});
				}
			} else {
				jQuery('.listViewEntriesCheckBox').each( function(index,element) {
					if(jQuery.inArray(jQuery(element).val(),selectedIds) != -1){
						jQuery(this).attr('checked', true).closest('tr').addClass('highlightBackgroundColor');
					}
				});
			}
			thisInstance.checkSelectAll();
		}
	},
	
	getRecordsCount : function(){
		var aDeferred = jQuery.Deferred();
		var view = jQuery('#view').val();
		if(view != "Detail"){
			return this._super();
		}
		var recordCountVal = jQuery("#recordsCount").val();
		if(recordCountVal != ''){
			aDeferred.resolve(recordCountVal);
		} else {
			var count = '';
			var cvId = this.getCurrentCvId();
			var module = app.getModuleName();
			var parent = app.getParentModuleName();
			var relatedModuleName = jQuery('[name="relatedModuleName"]').val();
			var recordId = jQuery('#recordId').val();
			var tab_label = jQuery('div.related').find('li.active').data('labelKey');
			var postData = {
				"module": module,
				"parent": parent,
				"action": "DetailAjax",
				"viewname": cvId,
				"mode": "getRecordsCount",
				"relatedModule" : relatedModuleName,
				'record' : recordId,
				'tab_label' : tab_label
			}
			AppConnector.request(postData).then(
				function(data) {
					jQuery("#recordsCount").val(data['result']['count']);
					count =  data['result']['count'];
					aDeferred.resolve(count);
				},
				function(error,err){

				}
			);
		}

		return aDeferred.promise();
	},
	
	/** 
	 * Function to register events
	 */
	registerEvents : function(){
		var view = jQuery('#view').val();
		if(view != "Detail"){
			this._super();
			return;
		}
		this.registerMainCheckBoxClickEvent();
		this.registerCheckBoxClickEvent();
		this.registerSelectAllClickEvent();
		this.registerDeselectAllClickEvent();
	}
})