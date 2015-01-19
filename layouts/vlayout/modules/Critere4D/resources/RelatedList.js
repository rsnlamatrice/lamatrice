/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_RelatedList_Js("Critere4D_RelatedList_Js",{
	
	

	deleteRelation : function(relatedIdList, relatedDateApplicationList) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = "deleteRelation";
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
	
	addRelation : function(relatedIdList) {
		var aDeferred = jQuery.Deferred();
		var params = {};
		params['mode'] = "addRelation";
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
	 * Function to edit related DateApplication for modules of critere4d
	 */
	registerEventToEditDateApplication : function(){
		var thisInstance = this;
		jQuery('.dateapplication').on('click',function(e){
			e.stopImmediatePropagation();
		});
		var $input = jQuery('.dateapplication');
		$input.on('change',':input:visible',function(e){
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
				'mode' : 'updateDateApplication'
			}
			element.progressIndicator({});
			AppConnector.request(params).then(
				function(responseData){
					if(responseData.result[0]){
						element.progressIndicator({'mode': 'hide'});
						element.attr('dateapplication', newValue);
						//modifie tous les inputs relatifs
						element.parents('td:first').nextAll('td')
							.find('[dateapplication="' + oldValue + '"]')
								.attr('dateapplication', newValue);
					}
					else
						element.validationEngine('showPrompt','La date n\'a pas pu &ecirc;tre modifi&eacute;e. Peut-&ecirc;tre fait-elle doublon ?','',"topLeft",true);
				},

				function(textStatus, errorThrown){
					alert(textStatus + "\r" + errorThrown);
				}
			);
		});
	},
	/**
	 * Function to edit l'information data qui associe le contact et le critère à la date d'application donnée
	 */
	registerEventToEditInformation : function(){
		var thisInstance = this;
		jQuery('.rel_data').on('click',function(e){
			e.stopImmediatePropagation();
		});
	
		var $input = jQuery('.rel_data');
		$input.on('change', null, function(e){
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
				'rel_data' : value,
				'module' : app.getModuleName(),
				'action' : 'RelationAjax',
				'sourceRecord' : thisInstance.parentRecordId,
				'mode' : 'updateRelData'
			}
			element.progressIndicator({});
			AppConnector.request(params).then(
				function(responseData){
					if(responseData.result[0]){
						element.progressIndicator({'mode': 'hide'});
					}
				},

				function(textStatus, errorThrown){
					alert(textStatus + "\r" + errorThrown);
				}
			);
		});
	},
	
	registerEvents : function(){
		this.registerEventToEditDateApplication();
		this.registerEventToEditInformation();
	}
})