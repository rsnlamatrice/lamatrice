/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Vtiger_Detail_Js("RSNContactsPanels_Detail_Js",{},{
		
	
	
	/**
	 * Function to register recordpostsave event
	 * refresh variables widget on query change
	 * ED150110
	 */
	registerRecordPostSaveEvent : function(form){
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		form.on(this.fieldUpdatedEvent,'.summaryView [name="query"]', function(e, data) {
			//alert(typeof(data));
			// ED150110
			var relatedModuleName = 'RSNPanelsVariables';
			var $related = form
			    .find('.summaryWidgetContainer input.relatedModuleName[value="' + relatedModuleName + '"]');
			if ($related.length === 0) {
			    //normal en DetailView sans summary
			    //alert('Impossible de retrouver le $related ' + relatedModuleName);
			    return;
			}
			
			thisInstance.refreshWidget( $related );
			
		});
	},
	
	
	/**
	 * Function to register event for changing related variable value
	 */
	registerVariablesInputChangeEvent : function(){
	    var thisInstance = this;
	    var detailContentsHolder = this.getContentHolder();
	    detailContentsHolder.on('change',':input[name]',function(e){
		    var $this = $(this);
		    var $tr = $this.parents('[data-id]:first');
		    var $widgetHolder = $this.parents('.summaryWidgetContainer:first');
		    
		    var newValue = $this.attr('type') == 'checkbox' ? (this.checked ? 1 : 0) : $this.val();
		    
		    e.stopImmediatePropagation();
		    var relatedRecordId =  $tr.data('id');
		    var params = {
			    'module' : $widgetHolder.find('.relatedModuleName:first').val(),
			    'record' : relatedRecordId,
			    'value' : newValue,
			    'action' : 'SaveAjax',
			    'mode' : 'updateVariableValue'
		    }
		    AppConnector.request(params).then(
			    function(responseData){
				    if(responseData.result[0]){
					    Vtiger_Helper_Js.showPnotify('Ok, c\'est enregistré');
				    }
				    else {
					    var params = {
						    title : app.vtranslate('JS_MESSAGE'),
						    text: 'Erreur !',
						    animation: 'show',
						    type: 'error'
					    };
					    Vtiger_Helper_Js.showPnotify(params);
				    
					    $this.validationEngine('showPrompt','La valeur n\'a pas pu &ecirc;tre modifi&eacute;e !','',"topLeft",true);
				    }
			    },

			    function(textStatus, errorThrown){
				    alert(textStatus + "\r" + errorThrown);
			    }
		    );
	    });
	},
	
	/**
	 * Function which will register all the events
	 */
	registerEvents : function() {
		this.registerVariablesInputChangeEvent();
		var form = this.getForm();
		this._super();
		this.registerRecordPostSaveEvent(form);
	}
	
	
})