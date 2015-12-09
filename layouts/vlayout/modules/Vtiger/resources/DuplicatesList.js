/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Vtiger_DuplicatesList_Js",{
	
	//ED151009 overrides parent
	requestViewClass : 'DuplicatesList',

	registerEvents : function(){
		this._super();
		this.registerMergeClickEvent();
		this.registerUpdateStatusClickEvent();
	},
	
	registerMergeClickEvent : function(){
		var thisInstance = this;
		
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','input[name="merge"]',function(e){
		//jQuery('div.bodyContents').on('click', 'input[name="merge"]', function(e){ NE PERMET PAS D'ARRÊTER LE CLICK SUR LA LIGNE ENTIERE, enfin, je n'ai pas réussi
			
			var element = jQuery(e.currentTarget)
			, $td = element.parents('td:first')
			, $tr = $td.parent()
			, nRows = parseInt($td.attr('rowspan'))
			, crmid = $tr.data('id')
			, records  = [crmid]
			, $nextRow = $tr.next();
			while(records.length < nRows){
				records.push( $nextRow.data('id') );
				$nextRow = $nextRow.next();
			}
			//ED150910
			var view = element.data('view') ? element.data('view') : 'MergeRecord'
			, url = 'module='+app.getModuleName()+'&view='+view+'&records='+records;
			thisInstance.showMergePopup(url).then(
				function(data){
					var msg;
					if (data.result.Modifications !== undefined)
						msg = 'Ok (' + data.result.Modifications + ')';
					else{
						if (data.result !== true){
							msg = JSON.stringify(data.result);
							msg = 'Ok (' + msg + ')';
						}
						else
							msg = 'Ok';
					}
					Vtiger_Helper_Js.showPnotify(app.vtranslate(msg));
					//Supprime les lignes
					for (var i = 1; i < nRows; i++) {
						$tr.next().remove();
					}
					$tr.remove();
				}, function(error, err){
				}
			);
			return false;
		});
	},
	showMergePopup : function(url){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var popupInstance = Vtiger_Popup_Js.getInstance();
		popupInstance.show(url, function(responseString){
				//jQuery('<pre/>').html(responseString).dialog({ title: "showSelectRelationPopup"});
				var responseData = JSON.parse(responseString);
				aDeferred.resolve(responseData);
			}, function(){
				alert(JSON.stringify(arguments));
			}
		);
		return aDeferred.promise();
	},
	
	registerUpdateStatusClickEvent : function(){
		var thisInstance = this;
		//jQuery('div.bodyContents').on('click', 'a.updatestatus[data-status]', function(e){//NE PERMET PAS D'ARRÊTER LE CLICK SUR LA LIGNE ENTIERE, enfin, je n'ai pas réussi
		
		var listViewContentDiv = this.getListViewContentContainer();
		listViewContentDiv.on('click','a.updatestatus[data-status]',function(e){
			var element = jQuery(e.currentTarget)
			, duplicatestatus = element.data('status')
			, $td = element.parents('td:first')
			, $tr = $td.parent()
			, nRows = parseInt($td.attr('rowspan'))
			, crmid = $tr.data('id')
			, records  = [crmid]
			, $nextRow = $tr.next()
			, $rows = [$tr];
			while(records.length < nRows){
				records.push( $nextRow.data('id') );
				$rows.push($nextRow);
				$nextRow = $nextRow.next();
			}
			//ED150910
			var action = 'FindDuplicate'
			, mode = 'updateDuplicatesStatus'
			, postData = {
				"module": app.getModuleName(),
				"action": action,
				"mode": mode,
				"records": records,
				"duplicatestatus": duplicatestatus,
			};
			AppConnector.request(postData).then(
				function(data){
					if (data.success && data.result.updated) {		
						Vtiger_Helper_Js.showPnotify(app.vtranslate('Ok (' + data.result.updated + ')'));
						$.each($rows, function(){ $(this).remove(); });
					}
				},
				function(error,err){

				}
			);
			return false;
		});
	},
});