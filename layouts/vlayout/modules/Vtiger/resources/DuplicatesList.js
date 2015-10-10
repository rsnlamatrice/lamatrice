/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_List_Js("Vtiger_DuplicatesList_Js",{
	
	registerEvents : function(){
		this._super();
		this.registerMergeClickEvent();
	},
	
	registerMergeClickEvent : function(){
		var thisInstance = this;
		jQuery('input[name="merge"]').on('click', function(e){
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
			, url = 'module='+app.getModuleName()+'&view='+view+'&records='+records
			, popupInstance = Vtiger_Popup_Js.getInstance();
			thisInstance.popupWindowInstance = popupInstance.show(url, '', '', '', function(params){
				thisInstance.mergeRecordPopupCallback(params);
			});
			return false;
		});
	},
	
	mergeRecordPopupCallback  :function(params){
		//alert(JSON.stringify(params));
	}
});