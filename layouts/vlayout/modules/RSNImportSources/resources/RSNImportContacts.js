if (typeof(RSNImportContactsJs) == 'undefined') {
    /*
	 * Namespaced javascript class for RSNImport
	 */
    RSNImportContactsJs = {
	
    	registerEvent: function() {
    		this.registerSelectContactEvent();
    		this.registerSNAButtonEvent();
			this.registerCellClickEvent();
			this.registerCellDoubleClickEvent();
			this.registerCellHOverEvent();
			this.registerHeaderFiltersEvent();
			this.registerValidatePreImportRowsEvent();
			this.registerContactSelectionEvent();
			this.registerAllRowsSelectionEvent();
    	},
		
		getContainer: function() {
    		return jQuery('.importContents[data-module="Contacts"]');
    	},

		getPageContainer: function() {
    		return this.getContainer().parents('table.importPreview');
    	},
		
		getCell: function(e) {
			var $target = e.jquery ? e : e.target ? $(e.target) : $(e);
    		return $target.is('td,th') ? $target : $target.parents('td:first,th:first');
    	},
		
	/* registers */
		
    	//Sélection d'un contact ou "annuler" ou "créer"
		registerContactSelectionEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('change', 'input.contact-mode-selection[type="radio"]', function(e){
				thisInstance.checkValidateRow(e);
			});
		},
		
    	//Coche de toutes les lignes
		registerAllRowsSelectionEvent: function() {
			var thisInstance = this;
			thisInstance.getPageContainer().on('change', 'input.all-rows-selection[type="checkbox"]', function(e){
				thisInstance.checkAllRows(e);
			});
		},
		
    	//Modifications des valeurs de filtres
		registerHeaderFiltersEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('change', '.header-filters :input:visible', function(e){
				RSNImportSourcesJs.reloadPreviewData(e);
			});
		},
		
    	//Clic sur sélection d'un contact
		registerSelectContactEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('click', '.select-contact a', function(e){
				thisInstance.selectContact(e);
				return false;
			});
		},

		//Clic sur SNA
		registerSNAButtonEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('click', '.address-sna-check', function(e){
				thisInstance.checkSNA(e);
				return false;
			});
		},

		//Clic sur une cellule : sélection de la valeur
		registerCellClickEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('click', '.preimport-row > td[data-fieldname]', function(e){
				thisInstance.toggleSelectedCellValue(e);
				return false;
			});
			thisInstance.getContainer().on('click', '.contact-row > td[data-fieldname]', function(e){
				thisInstance.toggleSelectedCellValue(e);
				return false;
			});
		},

		//Double-clic : édition
		registerCellDoubleClickEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('dblclick', '.preimport-row > td[data-fieldname]', function(e){
				thisInstance.editCellValue(e);
				return false;
			});
		},

		//Survol d'une cellule : boîte à outils
		registerCellHOverEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('hover', '.preimport-row > td[data-fieldname]', function(e){
				var $toolbox = thisInstance.getPreImportRowCellToolBox(e, true);
				if ($toolbox) 
					$toolbox.show();
			});
			thisInstance.getContainer().on('mouseleave', '.preimport-row > td[data-fieldname]', function(e){
				var $td = $(this)
				, $toolbox = thisInstance.getPreImportRowCellToolBox(e, false);
				if ($toolbox) 
					$toolbox.hide();
			});
		},
		
    	//Validation des lignes
		registerValidatePreImportRowsEvent: function() {
			var thisInstance = this;
			thisInstance.getPageContainer().on('click', 'button[name="validate-preimport-rows"]', function(e){
				thisInstance.validatePreImportRows(e);
			});
		},
		
	/* functions */
		
		// Obtention de la valeur d'une cellule
		getCellValue: function($cell){
			if (!$cell.jquery)
				$cell = $($cell);
			var $value = $cell.is('.value') ? $cell : $cell.find('.value:first')
			, $input = $cell.find(':input:first');
			if ($input.length) {
				return $input.val();
			}
			return $value.html();
		},
		// Affectation de la valeur d'une cellule
		setCellValue: function($cell, value, setChangedValueCell, setSelectedCell){
			if (!$cell.jquery)
				$cell = $($cell);
			var $value = $cell.is('.value') ? $cell : $cell.find('.value:first');
			if(setChangedValueCell)
				this.setChangedValueCell($value);
			if(setSelectedCell)
				this.selectCellValue($cell);
			return $value.html(value);
		},
		
		// Sélection d'un contact
		selectContact: function(e){
			alert('TODO');
		},
		
		// Contrôle de l'adresse fournie par le SNA
		checkSNA: function(e){
			alert('TODO');
		},
		
		/* remplace l'affichage de valeur par un input */
		editCellValue: function(e){
			var thisInstance = this
			, $td = this.getCell(e)
			, fieldName = $td.data('fieldname')
			, $value = $td.children('.value')//TODO thisInstance.getCellValue(
			, value = thisInstance.getCellValue($value)
			, $input = $value.children('input:visible');
			if($input.length){
				thisInstance.setCellValue($value, value);//scratch input
				return;
			}
			if (!$value.data('original-value')) 
				$value
					.data('original-value', value);
					
			thisInstance.createCellEditor(e);
			thisInstance.selectCellValue($td);
			e.preventDefault();
		},
		
		createCellEditor: function(e){
			var thisInstance = this
			, $td = this.getCell(e)
			, fieldName = $td.data('fieldname')
			, $value = $td.children('.value')
			, value = thisInstance.getCellValue($value);
					
			switch (fieldName){
			case 'rsnnpai' :
				var picklistValues = eval($('#' + fieldName + '-picklistvalues').val())
				, $editor = 
					$('<select/>')
						.val(value)
						.focus()
						.click(function(e){ return false; })
						.keyup(function(e){
							if (e.keyCode === 27) {
								var $value = $(this.parentNode);
								thisInstance.setCellValue($value, $value.data('original-value'), false);
							}
							else if (e.keyCode === 13) {
								var $value = $(this.parentNode);
								thisInstance.setCellValue($value, this.value, true, true);
							}
						})
						.blur(function(e){
							var $value = $(this.parentNode);
							thisInstance.setCellValue($value, this.value, true, true);
						})
				;
				for(picklistValueId in picklistValues) {
					$editor.append(
						'<option value="' + picklistValueId + '"'
						+ (picklistValueId == value ? ' selected="selected"' : '')
						+'>'
						+ picklistValues[picklistValueId].label
						+ '</option>'
					);
				}
				break;
			default :
				var $editor = 
					$('<input/>')
						.val(value)
						.focus()
						.select()
						.click(function(e){ return false; })
						.dblclick(function(e){ return false; })
						.keyup(function(e){
							if (e.keyCode === 27) {
								var $value = $(this.parentNode);
								thisInstance.setCellValue($value, $value.data('original-value'), false);
							}
							else if (e.keyCode === 13) {
								var $value = $(this.parentNode);
								thisInstance.setCellValue($value, this.value, true, true);
							}
						})
						.blur(function(e){
							var $value = $(this.parentNode);
							thisInstance.setCellValue($value, this.value, true, true);
						})
				;
				break;
			}
			$value.html($editor);
			return $editor;
		},
		
		//Retourne la boîte à outils d'une cellule
		getPreImportRowCellToolBox: function(e, createIfNone){
			var $td = this.getCell(e)
			, $toolbox = $td.children('.cell-toolbox');
			if($toolbox.length)
				return $toolbox;
			if (createIfNone !== undefined && !createIfNone)
				return false;
			return this.createPreImportRowCellToolBox($td);
		},
		
		//Initialise la boîte à outils d'une cellule
		createPreImportRowCellToolBox: function($td){
			var thisInstance = this
			, $toolbox = $('<div class="cell-toolbox"></div>')
			;
			//swap left
			if ($td.prev().is('td') && !$td.is('.no-swap-value, .no-swap-value-left')) {
				$toolbox
					.append($('<a><span class="ui-icon ui-icon-arrowthick-1-w" title="échanger à gauche"></span></a>')
						.click(function(e){
							thisInstance.swapCellValues(e, 'left');
							return false;
						})
					)
				;
			}
			//swap right
			if ($td.next().is('td') && !$td.is('.no-swap-value, .no-swap-value-right')) {
				$toolbox
					.append($('<a><span class="ui-icon ui-icon-arrowthick-1-e" title="échanger à droite"></span></a>')
						.click(function(e){
							thisInstance.swapCellValues(e, 'right');
							return false;
						})
					)
				;
			}
			//trash
			if (!$td.is( '.not-trashable')) {
				$toolbox
					.append($('<a class="trash"><span class="ui-icon ui-icon-trash"></span></a>')
						.click(function(e){
							thisInstance.clearCellValue(e);
							return false;
						})
					)
				;
			}
			//append
			$td
				.css({position: 'relative'})
				.prepend($toolbox);
				
			return $toolbox;
		},
		
		//Marque la cellule comme ayant eu sa valeur modifiée
		setChangedValueCell: function(e){
			var $cell = this.getCell(e);
			$cell.addClass('changed-value');
		},
		
		//Echange les valeurs de deux cellules mitoyennes
		swapCellValues: function(e, direction){
			var $action = $(e.target)
			, $td1 = $action.parents('td:first')
			, $value1 = $td1.children('.value')
			, value1 = this.getCellValue($value1)
			, $td2 = direction === 'left' ? $td1.prev('td') : $td1.next('td')
			, $value2 = $td2.children('.value')
			, value2 = this.getCellValue($value2)
			;
			$td1.children('.value').html(value2);
			$value1.data('original-value', false);
			this.selectCellValue($td1);
			this.setChangedValueCell($td1);
			
			$td2.children('.value').html(value1);
			$value2.data('original-value', false);
			this.selectCellValue($td2);
			this.setChangedValueCell($td2);
			
			this.checkContactRow(e);
		},
		
		//Supprime la valeur de la cellule
		clearCellValue: function(e){
			var $action = $(e.target)
			, $td = $action.parents('td:first');
			$td.toggleClass('cell-deleted');
			this.unselectCellValue(e);
			this.checkContactRow(e);
			this.setChangedValueCell($td);
		},
		
		//Inverse la sélection d'une cellule
		toggleSelectedCellValue: function(e){
			var thisInstance = this
			, $td = this.getCell(e);
			if ($td.is('.selected-value')) 
				thisInstance.unselectCellValue(e);
			else{
				thisInstance.selectCellValue(e);
			}
		},
		
		//Sélection d'une cellule
		selectCellValue: function(e){
			var thisInstance = this
			, $td = this.getCell(e);
			$td.addClass('selected-value').removeClass('not-selected-value');
			
			var $tr = $td.parent()
			, fieldName = $td.data('fieldname')
			, rowId = $tr.data('rowid')
			, $others = $tr.siblings('[data-rowid="'+ rowId +'"]')
			;
			$others.children('td[data-fieldname="'+ fieldName +'"]')
				.removeClass('selected-value')
				.addClass('not-selected-value')
			;
			
			thisInstance.checkContactRow(e);
		},
		
		//Désélection d'une cellule
		unselectCellValue: function(e){
			var thisInstance = this
			, $td = this.getCell(e);
			if(!$td.is('.selected-value'))
				return;
			$td.removeClass('selected-value');
			
			var $tr = $td.parent()
			, fieldName = $td.data('fieldname')
			, rowId = $tr.data('rowid')
			, $others = $tr.siblings('[data-rowid="'+ rowId +'"]')
			;
			$others.children('td[data-fieldname="'+ fieldName +'"]')
				.removeClass('not-selected-value');
		},
	
		//Coche la ligne pour mise à jour
		checkContactRow: function(e){
			var thisInstance = this
			, $td = this.getCell(e)
			, $tr = $td.parent();
			if ($tr.is('.contact-row')) {
				var rowId = $tr.data('rowid')
				,$radio = $tr.find('th input[type="radio"][name="contact_related_to_'+rowId+'"]');
				if ($radio.length)
					$radio[0].checked = true;
			}
			thisInstance.checkValidateRow(e);
		},
		
		//Coche la ligne pour mise à jour
		checkValidateRow: function(e){
			var thisInstance = this
			, $td = this.getCell(e)
			, $tr = $td.parent();
			while ($tr.is('.contact-row'))
				$tr = $tr.prev();
			var $checkbox = $tr.find('input.row-selection[type="checkbox"]');
			if ($checkbox.length)
				$checkbox[0].checked = true;
		},
		
		//Coche la ligne pour mise à jour
		checkAllRows: function(e){
			var $thisElement = e.jquery ? e : $(e.target)
			, checked = $thisElement[0].checked
			, $destTablePage = $thisElement.parents('table.importPreview:first')
			, $destTableData = $destTablePage.find('table.importContents:first')
			, $preImportRows = $destTableData.find('tr.preimport-row[data-rowid]')
			, $preImportRowCheckboxes = $preImportRows.find('input.row-selection[type="checkbox"]')
			;
			$preImportRowCheckboxes.each(function(){
				this.checked = checked;
			});
		},
		
		/**
		 * Enregistrement des validations de l'utilisateur
		 * Bascule les lignes courantes dans un état prêt à importer
		 */
		validatePreImportRows : function (e) {
			var $thisElement = e.jquery ? e : $(e.target)
			, $destTablePage = $thisElement.parents('table.importPreview:first')
			, $destTableData = $destTablePage.find('table.importContents:first')
			, $preImportRows = $destTableData.find('tr.preimport-row[data-rowid]')
			, rowsData = this.getPreImportRowsData(e)
			, params = $.param({rows: rowsData})
			, url = $destTablePage.find('param[name="VALIDATE_PREIMPORT_URL"]').attr('value');
			
			AppConnector.request(url + '&' + params).then(
				function(data){
					RSNImportSourcesJs.reloadPreviewData($destTableData);
				},
				function(error){
					//on peut arriver ici si des espaces seuls sont retournés
					//vu alors que le fichier modules\Contacts\ContactsHandler.php contenait des espaces après le "?>" final
				}
			);
			
			return false;
		},

		/**
		 * Données de validation des lignes de pré-imports
		 */
		getPreImportRowsData : function (e) {
			var thisInstance = this
			, $thisElement = e.jquery ? e : $(e.target)
			, $destTablePage = $thisElement.parents('table.importPreview:first')
			, $destTableData = $destTablePage.find('table.importContents:first')
			, $preImportRows = $destTableData.find('tr.preimport-row[data-rowid]')
			, rowsData = {};
			//pour chaque ligne de pré-import
			$preImportRows.each(function(){
				var $tr = $(this);
				if ($tr.find('input.row-selection:checked').length === 0) 
					return true;//skip
				var rowId = $tr.data('rowid')
				, $nextRows = $tr.nextAll('tr') /*not .contact-row parce qu'on sort de la boucle quand ce n'est plus la bonne classe*/
				, selectedContactId = false
				, proposedContactIds = ''
				;
				rowsData[rowId] = {};
				//cherche le contact sélectionné dans les lignes suivantes
				$nextRows.each(function(){
					if (!/contact-row/.test(this.className)) 
						return false; // stop. arrête de parcourir les lignes suivantes
					var $contact = $(this)
					, $contactChecked = $contact.find('input[type="radio"]:checked');
					//Contact connu
					if ($contact.data('contactid')) {
						proposedContactIds += ',' + $contact.data('contactid');
						if ($contactChecked.length){//Contact sélectionné
							selectedContactId = $contact.data('contactid');
							rowsData[rowId].update = {
								'_contactid_status' : RSNImportSourcesJs.RECORDID_STATUS_SELECT,//ligne prête à être importée
								'_contactid' : selectedContactId
							};
						}
						//cellules sélectionnées depuis les données du contact connu
						var $selectedCells = $contact.find('td.selected-value[data-fieldname]');
						$selectedCells.each(function(){
							rowsData[rowId].update[this.getAttribute('data-fieldname')] = thisInstance.getCellValue(this);
						});
					}
					//Contact inconnu (Créer ou Annuler)
					else if($contactChecked.length) {
						//Ne conserve la référence au contact que dans le rétablissement de l'état "A vérififer"
						if($contactChecked.data('status') != RSNImportSourcesJs.RECORDID_STATUS_CHECK)
							proposedContactIds = '';
						rowsData[rowId].update = {
							'_contactid_status' : $contactChecked.data('status'),//RSNImportSourcesJs.RECORDID_STATUS_CREATE | SKIP | CHECK,
							'_contactid' :  proposedContactIds 
						};
					}
					return true;
				});
				
				//cellules sélectionnées ou modifiées depuis les données de l'import
				var $selectedCells = $tr.find('td.selected-value[data-fieldname],td.changed-value[data-fieldname]');
				$selectedCells.each(function(){
					var value
					, fieldName = this.getAttribute('data-fieldname');
					if (/cell-deleted/.test(this.className)){
					    if (rowsData[rowId].update
						&& rowsData[rowId].update[fieldName])
							return;//supprimée mais déjà attribuée (par contact)
					    value = '';
					}
					else if (/changed-value/.test(this.className)
					&& rowsData[rowId].update
					&& rowsData[rowId].update[fieldName])
					    return; //modifiée mais déjà attribuée (par contact)
					else
					    value = thisInstance.getCellValue(this);
					//Si on connait le contact final, mise à jour de la fiche Contact
					if (/selected-value/.test(this.className)
					&& selectedContactId) {
						if(!rowsData[rowId].updateContacts)
							rowsData[rowId].updateContacts = { id : selectedContactId };
						rowsData[rowId].updateContacts[fieldName] = value;
					}
					
					//Mise à jour des données d'import, au cas où la valeur ait changé	
					if(!rowsData[rowId].update)
						rowsData[rowId].update = {};
					rowsData[rowId].update[fieldName] = value;
					
				});
				
			});
			return rowsData;
		},
		
	}

	jQuery(document).ready(function() {
		RSNImportContactsJs.registerEvent();
	});
}