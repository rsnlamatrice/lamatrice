/*+***********************************************************************************
 * ED151201
 *************************************************************************************/
Vtiger_Edit_Js("RSNAboRevues_Edit_Js",{},{

	//override for QuickCreate
	getForm : function() {
		if(this.formElement == false){
			var $form = jQuery('#EditView');
			if ($form.length === 0)
				$form = jQuery('#globalmodal form');
			this.setForm($form);
		}
		return this.formElement;
	},
	
	/**
	 * Function which will register event for Reference Fields Selection
	 */
	registerAboTypeChangeEvent : function(container) {
		var thisInstance = this;
		thisInstance.getAboTypeInput(container).on('change', thisInstance.aboTypeChangeEventHandler);
	},
	
	aboTypeChangeEventHandler : function(e){
		var params = {
			'mode' : 'getTypeIsAbonnable'
			, 'module' : 'RSNAboRevues'
			, 'action' : 'GetData'
			, 'rsnabotype' : $(this).val()
		};
		AppConnector.request(params).then(
			function(data){
				if (data.success) {
					var thisInstance = Vtiger_Edit_Js.getInstanceByModuleName('RSNAboRevues')
					, container = thisInstance.getForm()
					, $input = thisInstance.getIsAbonneInput(container, data.result);
					if ($input.length){
						$input.get(0).checked = true;
						$input.parents('.ui-buttonset:first').buttonset('refresh');
					}
					else {
						console.log('input length === 0');
						console.log('container length === '+container.length);
					}
				}
			},
			function(error){
			}
		)
	},
	
	getIsAbonneInput : function(container, value){
		var selector = value === undefined ? ':checked' : '[value="'+value+'"]';
		return jQuery(':input[name="isabonne"]'+selector, container);
	},
	
	getAboTypeInput : function(container){
		return jQuery(':input[name="rsnabotype"]', container);
	},
	
	checkIsAbonneSelected : function(container){
		if (this.getIsAbonneInput(container).length === 0) {
			this.aboTypeChangeEventHandler.call(this.getAboTypeInput(container));
		}
	},
	
	registerBasicEvents : function(container){
		this._super(container);
		this.registerAboTypeChangeEvent(container);
		this.checkIsAbonneSelected(container);
	}
})