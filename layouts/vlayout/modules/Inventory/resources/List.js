/*+***********************************************************************************
 * ED150625 : affichage de la liste des produits au survol du premier produit
 * TODO : en conflit avec le popup de base quand on survole une référence quelconque
  *************************************************************************************/

Vtiger_List_Js("Inventory_List_Js",{
	
	/**
	 * Function to trigger tooltip feature.
	 */
	registerEventForProductListToolTip : function() {
		var productCells = jQuery('td.listViewEntryValue[data-field-name="productid"]');
		var lastPopovers = [];

		// Fetching reference fields often is not a good idea on a given page.
		// The caching is done based on the URL so we can reuse.
		var CACHE_ENABLED = true; // TODO - add cache timeout support.

		function prepareAndShowTooltipView() {
			hideAllTooltipViews();

			var el = jQuery(this);
			var url = document.location.href;
			
			var $tr = el.parents('tr:first')
			,   recordId = $tr.data('id')
			,   title = $tr.find('td[data-field-name="invoice_no"]').text();
			
			// Rewrite URL to retrieve Tooltip view.
			url = url.replace('view=', 'xview=') + '&view=TooltipAjax';
			url = url.replace('mode=', 'xmode=') + '&mode=ProductList';
			url = url.replace('record=', 'xrecord=') + '&record=' + recordId;

			var cachedView = CACHE_ENABLED ? jQuery('[data-url-cached="'+url+'"]') : null;
			if (cachedView && cachedView.length) {
				showTooltip(el, cachedView.html(), title);
			} else {
				AppConnector.request(url).then(function(data){
					cachedView = jQuery('<div>').css({display:'none'}).attr('data-url-cached', url);
					cachedView.html(data);
					jQuery('body').append(cachedView);
					showTooltip(el, data, title);
				});
			}
		}
		
		function showTooltip(el, data, title) {
			el.popover({
				title: 'Facture ' + title, 
				trigger: 'manual',
				content: data,
				animation: false,
				template: '<div class="popover popover-tooltip"><div class="arrow"></div><div class="popover-inner"><button name="vtTooltipClose" class="close" style="color:white;opacity:1;font-weight:lighter;position:relative;top:3px;right:3px;">x</button><h3 class="popover-title"></h3><div class="popover-content"><div></div></div></div></div>'
			});
			lastPopovers.push(el.popover('show'));
			registerToolTipDestroy();
		}

		function hideAllTooltipViews() {
			// Hide all previous popover
			var lastPopover = lastPopovers.pop();
			while (lastPopover) {
				lastPopover.popover('hide');
				lastPopover = lastPopovers.pop();
			}
		}

		productCells.each(function(index, el){
			jQuery(el).hoverIntent({
				interval: 150,
				sensitivity: 7,
				timeout: 10,
				over: prepareAndShowTooltipView,
				out: hideAllTooltipViews
			})
				//remove reference original tooltip
				.find('a[data-field-type]')
					.removeAttr('data-field-type')
			;
		});

		function registerToolTipDestroy() {
			jQuery('button[name="vtTooltipClose"]').on('click', function(e){
				var lastPopover = lastPopovers.pop();
				lastPopover.popover('hide');
			});
		}
	},
	
	registerEvents : function(){
		this.registerEventForProductListToolTip();
		app.listenPostAjaxReady(this.registerEventForProductListToolTip);
		this._super();
	}
});