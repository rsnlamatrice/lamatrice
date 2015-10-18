/*+***********************************************************************************
 * 
 *************************************************************************************/

Vtiger_Detail_Js("RSNStatistics_Detail_Js",{},{
		
		
	/**
	 * Function to reload filtering related data with in custom view as crmids
	 */
	registerCustomViewSelectionEvent : function(){
		var thisInstance = this;
		jQuery('.detailViewContainer').on('change', 'select.#customFilter',function(e){
			var tabElement = thisInstance.getSelectedTab()
			, url = tabElement.data('url')
			, data = {};
			tabElement.click();
			//thisInstance.loadContents(url, data);
		});
	},
	
	loadContents : function(url,data) {
		var $select = jQuery('.detailViewContainer select.#customFilter');
		if ($select.length) {
			url += '&related_viewname=' + $select.val();
		}
		return this._super(url,data);
	},
	
	
	/**
	 * Function which will register all the events
	 */
	registerEvents : function() {
		this._super();
		this.registerCustomViewSelectionEvent();
	}
	
	
})