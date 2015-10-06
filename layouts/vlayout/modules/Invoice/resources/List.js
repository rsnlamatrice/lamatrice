/*+***********************************************************************************
 * ED151006
 * Gestion de la génération des données pour la compta
 * Aussi héritée par PurchaseOrder
 *************************************************************************************/

Inventory_List_Js("Invoice_List_Js",{
        
        //ED150930 Overrided for send2Compta
	postMassEdit : function(massEditContainer) {
		var thisInstance = this
                , $form = massEditContainer.find('form');
                if( ! $form.is('.send2Compta') )
                    return this._super(massEditContainer);
                
                this.registerDownloadSend2ComptaEvent(massEditContainer);
                
                //send2Compta
		$form.on('submit', function(e){
			e.preventDefault();
			var form = jQuery(e.currentTarget);
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length == 0){
				form.find('[name="saveButton"]').attr('disabled',"disabled");
			}
			var invalidFields = form.data('jqv').InvalidFields;
			if(invalidFields.length > 0){
				return;
			}
			thisInstance.massActionSave(form, false).then(
				function(data) {
					//ED150618
					if (typeof data.result === 'string') {
						var message = data.result;
						var params = {
							text: message,
							type: data.success ? 'info' : 'error'
						};
						Vtiger_Helper_Js.showMessage(params);
					}
					thisInstance.getListViewRecords();
					Vtiger_List_Js.clearList();
				},
				function(error,err){
				}
			);
		});
	},
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerDownloadSend2ComptaEvent : function(container) {
		var thisInstance = this
                , $form = container.find('form');
                
                //send2Compta
		$form.on('click', 'a.downloadSend2Compta', function(e){
			thisInstance.prepareDownloadSend2Compta(e);
			
		});
	},

        /* remplace le href du lien de téléchargement */
	prepareDownloadSend2Compta : function(e){
		var aDeferred = jQuery.Deferred();
		var $a = $(e.target)
                , form = $a.parents('form:first')
                , params = form.serializeFormData();
		params['mode'] = 'downloadSend2Compta';
                //set massActionUrl to href
                $a.attr('href', '?' + $.param(params));
	},
});