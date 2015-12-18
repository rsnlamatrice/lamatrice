/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("Contacts_RelatedList_Js",{
	
	deleteRelationContacts : function(relatedIdList, relatedDateApplicationList) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = "deleteRelationContacts";
		params['module'] = this.parentModuleName;
		params['action'] = 'RelationAjax';

		params['related_module'] = this.relatedModulename;
		params['src_record'] = this.parentRecordId;
		params['related_record_list'] = JSON.stringify(relatedIdList);
		params['related_dateapplication_list'] = JSON.stringify(relatedDateApplicationList);
		AppConnector.request(params).then(
			function(responseData){
				aDeferred.resolve(responseData);
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
	
	addRelationContacts : function(relatedIdList) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = "addRelationContacts";
		params['module'] = this.parentModuleName;
		params['action'] = 'RelationAjax';

		params['related_module'] = this.relatedModulename;
		params['src_record'] = this.parentRecordId;
		params['related_record_list'] = JSON.stringify(relatedIdList);
		
		AppConnector.request(params).then(
			function(responseData){
				aDeferred.resolve(responseData);
				
			},

			function(textStatus, errorThrown){
				aDeferred.reject(textStatus, errorThrown);
			}
		);
		return aDeferred.promise();
	},
	
	/**
	 * Function to jump to contacts tab
	 */
	registerEventToNavigateToContactsTab : function(){
		var thisInstance = this;
		jQuery('.widget-content.contacts td').on('click',function(e){
			e.stopImmediatePropagation();
		});
	},
	
	/**
	 * Function to edit related DateApplication for modules of contacts
	 *
	 * ED151217, il semble que c'est Vtiger/resources/RelatedList.js::registerEventToEditMultiDatesDateApplication() qui est utilisé
	 */
	registerEventToEditContactsDateApplication : function(){
		var thisInstance = this;
		var listViewPageDiv = thisInstance.relatedContentContainer;
		listViewPageDiv.on('click', ':input', function(e){
			e.stopImmediatePropagation();
		});
		listViewPageDiv.on('click', ':input.dateapplication:visible',function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var inputid = this.id;
			try{
				var newValue = this.value.substr(6, 4) + '-' + this.value.substr(3, 2) + '-' + this.value.substr(0, 2);
			}
			catch(ex){
				alert('Erreur dans le format de date de ' + this.value
				      + '\r' + ex);
				return;
			}
			var oldValue = this.getAttribute('dateapplication');
			var relatedRecordId = element.closest('tr').data('id');
			var params = {
				'relatedModule' : thisInstance.relatedModulename,
				'relatedRecord' : relatedRecordId,
				'dateapplication' : newValue,
				'prevdateapplication' : oldValue,
				'module' : app.getModuleName(),
				'action' : 'RelationAjax',
				'sourceRecord' : thisInstance.parentRecordId,
				'mode' : 'updateDateApplicationContacts'
			}
			
			AppConnector.request(params).then(
				function(responseData){
					if(responseData.result[0]){
			
						Vtiger_Helper_Js.showPnotify('Ok, c\'est enregistré');
						element.attr('dateapplication', newValue);
						//modifie tous les inputs relatifs
						element.parents('tr:first')
							.find('[dateapplication="' + oldValue + '"]')
								.attr('dateapplication', newValue);
					}
					else {
			
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: 'Erreur !',
							animation: 'show',
							type: 'error'
						};
						Vtiger_Helper_Js.showPnotify(params);
						
						element.validationEngine('showPrompt','La date n\'a pas pu &ecirc;tre modifi&eacute;e. Peut-&ecirc;tre fait-elle doublon ?','',"topLeft",true);
					}
				},

				function(textStatus, errorThrown){
					alert(textStatus + "\r" + errorThrown);
				}
			);
		});
	},
	/**
	 * Function to edit l'information data qui associe les contacts à la date d'application donnée
	 */
	registerEventToEditContactsInformation : function(){
		var thisInstance = this;
		var listViewPageDiv = thisInstance.relatedContentContainer;
		listViewPageDiv.on('change', ':input.contreltype',function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var value = this.value;
			var dateapplication = this.getAttribute('dateapplication');
			var inputid = this.id;
			var relatedRecordId = element.closest('tr').data('id');
			var params = {
				'relatedModule' : thisInstance.relatedModulename,
				'relatedRecord' : relatedRecordId,
				'dateapplication' : dateapplication,
				'contreltype' : value,
				'module' : app.getModuleName(),
				'action' : 'RelationAjax',
				'sourceRecord' : thisInstance.parentRecordId,
				'mode' : 'updateRelDataContacts'
			}
			element.progressIndicator({});
			AppConnector.request(params).then(
				function(responseData){
					if(responseData.result[0]){
			
						Vtiger_Helper_Js.showPnotify('Ok, c\'est enregistré');
						
						element.progressIndicator({'mode': 'hide'});
					}
					else {
			
						var params = {
							title : app.vtranslate('JS_MESSAGE'),
							text: 'Erreur !',
							animation: 'show',
							type: 'error'
						};
						Vtiger_Helper_Js.showPnotify(params);
						
					}
				},

				function(textStatus, errorThrown){
					alert(textStatus + "\r" + errorThrown);
				}
			);
		});
	},
	
	registerEventsContacts : function(){
		this.registerEventToEditContactsDateApplication();
		this.registerEventToEditContactsInformation();
		this.registerEventToNavigateToContactsTab();
	}
})