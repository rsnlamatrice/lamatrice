/*+***********************************************************************************
 * ED151226
 *************************************************************************************/

Vtiger_RelatedList_Js("Products_RelatedList_Js",{},{
	
	/* TODO it is Pricebook only */
	
	_firstCellIndex : undefined,//browser diff
	getFirstCellIndex: function($th){
		if (this._firstCellIndex === undefined)
			this._firstCellIndex = this.getTable().find('> tbody > tr > *:first').get(0).cellIndex;
		return this._firstCellIndex;
	},
	
	
	getContainer: function(){
		return $('#related-pricebooks');
	},
	getForm: function(){
		return $('#detailView');
	},
	getTable: function(){
		return this.getContainer().find('table:first');
	},
	getQuantityHeaders: function(container){
		return this.getTable().find('> thead > tr > th.quantity');
	},
	getHeaderQuantity: function($th){
		var $input = $th.children('input');
		if ($input.length) {
			return this.parseFloat($input.val());
		}
		return this.parseFloat($th.text());
	},
	getPriceColumnCells: function($th){
		if ($th.length > 1) {
			var thisInstance = this
			, $cells = $();
			$th.each(function(){
				$cells.add(thisInstance.getPriceColumnCells($(this)));
			});
			return $cells;
		}
		var index = typeof $th === 'numeric' ? $th : $th.get(0).cellIndex + 1;
		return this.getTable().children('tbody').find('td:nth-child('+index+')');
	},
	getPriceCell: function($header, $row){
		var columnIndex = typeof $header === 'numeric' ? $header : $header.get(0).cellIndex + 1;
		return $row.find('td:nth-child('+columnIndex+')');
	},
	getDiscountType: function($cell){
		var $tr = $cell.parents('tr:first');
		return $tr.data('discounttype');
	},
	parseFloat: function(value){
		return value && typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;
	},
	getBasicPrice: function(){
		return this.parseFloat($('#product_price').val());
	},
	getPrice: function($cell){
		var $input = $cell.children('input');
		if ($input.length) {
			return this.parseFloat($input.val());
		}
		return this.parseFloat($cell.text());
	},
	getPriceUnit: function($cell){
		var $input = $cell.children('select');
		if ($input.length) {
			return $input.val();
		}
		var priceUnit = $cell.data('unit');
		if (!priceUnit) 
			priceUnit = 'HT';
		return priceUnit;
	},
	setPrice: function($cells, price, priceUnit){
		if (priceUnit === undefined) {
			priceUnit = this.getPriceUnit($cells);
		}
		price = this.parseFloat(price);
		$cells.each(function(){
			$(this)
				.data('unit', priceUnit)
				.data('original-value', price)
				.html(price + (price != '' && priceUnit ? ' ' + priceUnit : ''));
		});
	},
	
	addColumn: function(quantity, $afterHeader){
		var thisInstance = this;
		if ($afterHeader === undefined) {
			var $headers = thisInstance.getQuantityHeaders();
			if(!quantity)
				$afterHeader = $headers.last();
			else {
				//cherche la colonne précédente
				var $existingHeader = false;
				$headers.each(function(){
					var $header = $(this)
					, qty = thisInstance.getHeaderQuantity($header)
					;
					if (qty == quantity) {//existe déjà
						$existingHeader = $header;
						return false;
					}
					if (qty > quantity) {//dépassée
						return false;
					}
					$afterHeader = $header;//précédente
				});
				if ($existingHeader)
					return $existingHeader;
			}
		}
		if(!quantity)
			quantity = thisInstance.getHeaderQuantity($afterHeader) + 1;
		var $header = $('<th/>')
			.addClass('quantity')
			.attr('data-quantity', quantity)
			.html(quantity)
			.append('<a class="remove-quantity"><span class="ui-icon ui-icon-trash"></span></a>')
			.insertAfter( $afterHeader );
			
		var $afterCells = thisInstance.getPriceColumnCells($afterHeader);
		$afterCells.each(function(){
			var $cell = $(this)
			//, price = thisInstance.getPrice($cell)
			, newCell = $('<td/>')
				.addClass('price')
				//.html(price)
				.insertAfter( $cell );
		});
		return $header;
	},
	
	registerAddColumnEvent: function(container){
		var thisInstance = this;
		$(container).on('click', '#add-quantity', function(e){
			e.stopImmediatePropagation();
			var $headers = thisInstance.getQuantityHeaders();
			//empêche l'ajout si une saisie est en cours
			if ($headers.find('input:visible').length) 
				return;
			$header = thisInstance.addColumn();
			$header.click();
		});
	},
	registerRemoveColumnEvent: function(container){
		var thisInstance = this;
		$(container).on('click', '.remove-quantity', function(e){
			e.stopImmediatePropagation();
			var $header = $(e.target).parents('th:first')
			, $lastCells = thisInstance.getPriceColumnCells($header)
			;
			$header.remove();
			$lastCells.remove();
		});
	},
	registerClearRowEvent: function(container){
		var thisInstance = this;
		$(container).on('click', '.clear-row', function(e){
			e.stopImmediatePropagation();
			var $row = $(e.target).parents('tr:first')
			, $cells = $row.children('td.price:gt(0)')
			, basicPrice = thisInstance.getBasicPrice();
			thisInstance.setPrice($cells, basicPrice, 'HT');
		});
	},
	
	
	registerEditQuantityEvent: function(container){
		var thisInstance = this;
		$(container).on('click', 'th.quantity', function(e){
			e.stopImmediatePropagation();
			if (e.target.tagName === 'INPUT')
				return;
			var $cell = $(this)
			, qty = thisInstance.getHeaderQuantity($cell)
			, minimalQuantity = qty == 1 ? qty : thisInstance.getHeaderQuantity($cell.prev()) + 1;
			if (qty == 1) //non éditable, sauf si TODO fraction...
				return;
			$cell
				.data('original-value', qty)
				.html(
					$('<input autofocus />')
						.data('minimalQuantity', minimalQuantity)
						.keyup(thisInstance.quantityInputKeyEventHandler)
						.blur(thisInstance.quantityInputValidateHandler)
						.val(qty)
						.select()
						.focus()
				);
		});
	},
	quantityInputKeyEventHandler : function(e){
		var $input = $(this)
		, $cell = $input.parents('th:first');
		if (e.keyCode == 13) {
			e.stopImmediatePropagation();
			$input.blur();
		}
		else if (e.keyCode == 27) {//escape
			e.stopImmediatePropagation();
			var value = $cell.data('original-value');
			$cell
				.html(value)
				.append('<a class="remove-quantity"><span class="ui-icon ui-icon-trash"></span></a>');
		}
		return true;
	},
	/* blur */
	quantityInputValidateHandler : function(e){
		var $input = $(this)
		, $cell = $input.parents('th:first')
		, value = $input.val().replace(',', '.').trim()
		//TODO pouvoir saisir dans le désordre. Il faut surtout contrôler les doublons.
		, minimalQuantity = parseFloat($input.data('minimalQuantity'));
		if(!value || isNaN(value)){
			alert("Veuillez saisir une valeur numerique, ou appuyer sur Echap pour annuler la saisie.");
			$input.focus();
			return false;
		}
		if(minimalQuantity > parseFloat(value)){
			alert("Veuillez saisir une valeur minimale de " + minimalQuantity + ".");
			$input.focus();
			return false;
		}
		
		$cell
			.data('original-value', value)
			.attr('data-quantity', value)
			.html(value)
			.append('<a class="remove-quantity"><span class="ui-icon ui-icon-trash"></span></a>');
		return true;
	},
	
	getUnitSelectorHtml: function(priceUnit){
		var units = eval('(' + $('#unittypes').val() + ')');
		var $select = $('<select/>');
		for(var i in units)
			$select.append('<option value="' + units[i] + '" '
						   + (units[i] == priceUnit ? ' selected="selected"' : '')
						   + '>'
						   + units[i]
						   + '</option>');
		return $select;
	},
	
	registerEditPriceCellEvent: function(container){
		var thisInstance = this;
		$(container).on('click', 'td.price', function(e){
			e.stopImmediatePropagation();
			if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT')
				return;
			var $cell = $(this)
			, price = thisInstance.getPrice($cell)
			, priceUnit = thisInstance.getPriceUnit($cell);
			$cell
				.data('original-value', price)
				.html(
					$('<input />')
						.keyup(thisInstance.priceInputKeyEventHandler)
						.blur(thisInstance.priceInputValidateHandler)
						.val(price)
						.select()
				)
				.append(thisInstance.getUnitSelectorHtml(priceUnit));
		});
	},
	priceInputKeyEventHandler : function(e){
		var $input = $(this)
		, $cell = $input.parents('td:first');
		if (e.keyCode == 13) {
			e.stopImmediatePropagation();
			$input.blur();
		}
		else if (e.keyCode == 27) {//escape
			e.stopImmediatePropagation();
			var value = $cell.data('original-value');
			$cell
				.html(value);
		}
		return true;
	},
	/* blur */
	priceInputValidateHandler : function(e){
		var thisInstance = Products_RelatedList_Js.getInstance();
		var $input = $(this)
		, $cell = $input.parents('td:first')
		, $unit = $cell.children('select')
		, unit = $unit.val()
		, value = $input.val().replace(',', '.').trim();
		if(value && isNaN(value)){
			alert("Veuillez saisir une valeur numerique, ou appuyer sur Echap pour annuler la saisie.");
			$input.focus();
			return false;
		}
		thisInstance.setPrice($cell, value);
		return true;
	},
	
	/* Submit */
	registerSubmitEvent : function(container){
		var thisInstance = this;
		$(container).on('click', 'button[name="saveButton"]', function(e){
			e.stopImmediatePropagation();
			e.preventDefault();
			
			var $form = thisInstance.getForm();
			if ($form.length === 0) {
				alert('Form introuvable ! erreur html depuis le template');
				return false;
			}
			var related_data = thisInstance.getSubmitData();
			console.log(JSON.stringify(related_data));
			$form.find('input[name="related_data"]').val(JSON.stringify(related_data));
			
			var element = jQuery(e.currentTarget);
			element.progressIndicator({});
			
			var params = $form.serializeFormData();
			
			AppConnector.request(params).then(
				function(data) {
					element.progressIndicator({'mode': 'hide'});
					if(data.result){
						Vtiger_Helper_Js.showPnotify('Ok, c\'est enregistré');
					}else{
						Vtiger_Helper_Js.showMessage(data);
					}
				}
			);
			
			return false;
		});
	},
	getSubmitData : function(){
		var thisInstance = this;
		var related_data = {}
		, related_data_last = {}
		, $headers = thisInstance.getQuantityHeaders()
		, basicPrice = thisInstance.getBasicPrice();
		
		//TODO commencer par trier les colonnes dans l'ordre
		
		//chaque colonne de quantité
		$headers.each(function(){
			var $header = $(this)
			, $cells = thisInstance.getPriceColumnCells($header)
			, quantity = thisInstance.getHeaderQuantity($header);
			//chaque cellule de la colonne
			$cells.each(function(){
				var $cell = $(this)
				, price = thisInstance.getPrice($cell)
				, priceUnit = thisInstance.getPriceUnit($cell)
				, priceLabel = price + ' ' + priceUnit
				, discountType = thisInstance.getDiscountType($cell);
				if (price == "" //vide
				|| ((price == basicPrice && priceUnit == 'HT')//même prix que la base
					&& (quantity == 1 //et 1er
					|| !related_data[discountType] //ou toujours aucun
				))
				|| (related_data[discountType] && related_data_last[discountType] === priceLabel) //même prix que le précédent
				)
					return;
				if(!related_data[discountType])
					related_data[discountType] = {};
				related_data[discountType][quantity] = {price: price, unit: priceUnit};
				related_data_last[discountType] = priceLabel;
			});
		})
		return related_data;
	},
	
	/* init */
	loadData : function(container){
		var thisInstance = this
		, $inputs = container.find('input.related-pricebooks[value]')
		, $table = this.getTable()
		, $headers = this.getQuantityHeaders();
		$inputs.each(function(){
			var related_data = eval("(" + this.value + ")");
			if (!related_data.minimalqty) 
				related_data.minimalqty = 1;
			if (!related_data.discounttype) 
				related_data.discounttype = "0";
			var discountType = related_data.discounttype
			, qty = related_data.minimalqty;
			var $discountRow = $table.find('tbody tr[data-discounttype="'+discountType+'"]');
			var $qtyHeader = $headers.filter('[data-quantity=' + qty + ']');
			if ($qtyHeader.length === 0) {
				$qtyHeader = thisInstance.addColumn(qty);
				$headers.add($qtyHeader);
			}
			var $cell = thisInstance.getPriceCell($qtyHeader, $discountRow);
			thisInstance.setPrice($cell, related_data.listprice, related_data.listpriceunit);
		});
	},
	
	
	registerEventForPriceBooks : function(){
		var container = $('#related-pricebooks:visible');
		if (container.length) {
			this.registerAddColumnEvent(container);
			this.registerRemoveColumnEvent(container);
			this.registerClearRowEvent(container);
			this.registerEditPriceCellEvent(container);
			this.registerEditQuantityEvent(container);
			this.registerSubmitEvent(container);
			
			this.loadData(container);
		}
	},
})