/*+***********************************************************************************
 * ED151109
 *************************************************************************************/

Vtiger_Popup_Js("Vtiger_MergeRecord_Js",{
	
	registerEvents : function(){
		this._super();
		this.registerSelectMainRecordEvent();
		this.registerRemoveRecordEvent();
	},
	
	//Sélection de la colonne d'un enregistrement, activation de toutes les options
	registerSelectMainRecordEvent : function(){
		var thisInstance = this;
		
		$('.listViewHeaders').on('click','input[type="radio"]',function(e){
			var $this = $(this)
			, $header = $this.parents('th:first')
			, columnIndex = $header.get(0).cellIndex
			, $table = $header.parents('table:first')
			, $bodyCells = $table.find('tbody td:nth-child('+(columnIndex + 1)+')');
			$bodyCells.find('input[type="radio"]:not(:checked)').each(function(){
				this.checked = true;
			});
		});
	},
	
	//Suppression de la colonne d'un enregistrement
	registerRemoveRecordEvent : function(){
		var thisInstance = this;
		
		$('.listViewHeaders').on('click','.ui-icon-trash',function(e){
			var $this = $(this)
			, $header = $this.parents('th:first')
			, $isSelected = $header.find('input[type="radio"]:checked')
			, columnIndex = $header.get(0).cellIndex 
			, $table = $header.parents('table:first')
			, $bodyCells = $table.find('tbody td:nth-child('+columnIndex+')')
			, $headerToSelect;
			if ($isSelected.length) {
				if (columnIndex === 1) 
					$headerToSelect = $header.next();
				else
					$headerToSelect = $header.prev();
				var $radioToSelect = $headerToSelect.find('input[type="radio"]');
				if ($radioToSelect.length)
					$radioToSelect.click().get(0).checked = true;
			}
			$bodyCells.remove();
			$header.remove();
		});
	},
	
	/*//TODO or not TODO...
	//Affectation de la couleur pour les valeurs différentes de la colonne de référence
	showValuesAreDifferents : function(){
		var $headers = $('thead.listViewHeaders > th.record')
		, $selectedRadio = $headers.find('input[type="radio"]:checked')
		, $selectedHeader = $headers.find('input[type="radio"]:checked')
		, columnIndex = $selectedHeader.get(0).cellIndex 
		, $table = $headers.parents('table:first')
		, $bodyRows = $table.find('tbody > tr:not(.listViewHeaders)');
		$bodyRows.each(function(){
			
		});
	},*/
});