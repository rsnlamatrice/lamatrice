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
	}
})