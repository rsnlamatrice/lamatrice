/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("RSNAboRevues_Detail_Js",{},{
		
	
	/* ED151222
	 * fieldDetailList peut être un jsobject de champs
	 *
	 * La mise à jour de la date de fin peut provoquer le changement de statut "Est abonné(e)".
	 * Ici, on intercepte le retour sur SaveAjax pour mettre à jour le champ IsAbonne
	 */
	saveFieldValues : function (fieldDetailList) {
		var aDeferred = jQuery.Deferred();

		this._super(fieldDetailList).then(
			function(reponseData){
				if (reponseData.success) {
					var $td = $('#RSNAboRevues_detailView_fieldValue_isabonne,#_detailView_fieldValue_isabonne')
					, $input = $td.find(':input.fieldname[value="isabonne"]')
					, prevValue = $input.data('prev-value');
					var newValue = reponseData.result.isabonne.value;
					if (prevValue != newValue) {
						var newHtml = reponseData.result.isabonne.display_value;
						$input.data('prev-value', newValue);
						$td.children('.value').html(newHtml);
					}
				}
				aDeferred.resolve(reponseData);
			}
		);

		return aDeferred.promise();
	},
	
	/**
	 * Function which will register all the events
	 */
	registerEvents : function() {
		var form = this.getForm();
		this._super();
	}
	
	
})