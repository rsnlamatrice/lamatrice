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
    	},
		
		getContainer: function() {
    		return jQuery('.importContents[data-module="Contacts"]');
    	},
		
		getCell: function(e) {
    		return e.target.tagName == 'TD' ? $(e.target) : $(e.target).parents('td:first');
    	},
		
	/* registers */
		
    	registerSelectContactEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('click', '.select-contact a', function(e){
				thisInstance.selectContact(e);
				return false;
			});
		},
		
		selectContact: function(e){
			
		},

		registerSNAButtonEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('click', '.address-sna-check', function(e){
				thisInstance.checkSNA(e);
				return false;
			});
		},

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


		registerCellDoubleClickEvent: function() {
			var thisInstance = this;
			thisInstance.getContainer().on('dblclick', '.preimport-row > td[data-fieldname]', function(e){
				thisInstance.editCellValue(e);
				return false;
			});
		},

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
		
	/* functions */
	
		checkSNA: function(e){
			
		},
		
		/* remplace de l'affichage de valeur par un input */
		editCellValue: function(e){
			var $td = this.getCell(e)
			, $value = $td.children('.value')
			, $input = $value.children('input');
			if($input.length){
				$value.html($input.val());
				return;
			}
			if (!$value.data('original-value')) 
				$value
					.data('original-value', $value.text());
			$value.html(
				$('<input/>')
					.val($value.text())
					.css({ width: '100%', 'min-width': '8em' })
					.focus()
					.select()
					.click(function(e){ return false; })
					.dblclick(function(e){ return false; })
					.keyup(function(e){
						if (e.keyCode === 27) {
							var $value = $(this.parentNode);
							$value.html($value.data('original-value'));
						}
						else if (e.keyCode === 13) {
							var $value = $(this.parentNode);
							$value.html(this.value);
						}
					})
					.blur(function(e){
						var $value = $(this.parentNode);
						$value.html(this.value);
					})
			);
		},
		
		getPreImportRowCellToolBox: function(e, createIfNone){
			var $td = this.getCell(e)
			, $toolbox = $td.children('.cell-toolbox');
			if($toolbox.length)
				return $toolbox;
			if (createIfNone !== undefined && !createIfNone)
				return false;
			return this.createPreImportRowCellToolBox($td);
		},
		
		createPreImportRowCellToolBox: function($td){
			var thisInstance = this
			, $toolbox = $('<div></div>')
				.addClass('cell-toolbox')
				.css({
					position: 'absolute',
					top: '0px',
					right: '0px',
					margin: '5px',
				})
				.append($('<a><span class="ui-icon ui-icon-trash"></span></a>')
					.click(function(e){
						thisInstance.clearCellValue(e);
						return false;
					})
				)
			$td
				.css({position: 'relative'})
				.prepend($toolbox);
				
			return $toolbox;
		},
		
		clearCellValue: function(e){
			var $action = $(e.target)
			, $td = $action.parents('td:first');
			$td.toggleClass('cell-deleted');
			this.unselectCellValue(e);
		},
		
		toggleSelectedCellValue: function(e){
			var thisInstance = this
			, $td = this.getCell(e);
			if ($td.is('.selected-value')) 
				thisInstance.unselectCellValue(e);
			else{
				thisInstance.selectCellValue(e);
			}
		},
		
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
		},
		
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
	}

	jQuery(document).ready(function() {
		RSNImportContactsJs.registerEvent();
	});
}