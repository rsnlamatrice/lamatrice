{*<!--
/*********************************************************************************
** notez que le <style/> est fin de page 
********************************************************************************/
-->*}
{strip} 
    <div class="relatedContainer">
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
    </div>
&nbsp;
<div id="related-pricebooks" class="relatedContents contents-bottomscroll">
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	<input type="hidden" id="product_price" value="{$PRODUCT_PRICE}"/>
	<input type="hidden" id="product_price_ttc" value="{$PRODUCT_PRICE_TAXED}"/>
	<input type="hidden" id="unittypes" value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($PRICE_UNITS))}"/>
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<input type="hidden" class="related-pricebooks" data-id="{$RELATED_RECORD->getId()}"
			   value="{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($RELATED_RECORD->getData()))}"/>
	{/foreach}
	<input type="hidden" name="module" value="{$MODULE}" />
	<input type="hidden" name="related_module" value="{$RELATED_MODULE->getName()}" />
	<input type="hidden" name="action" value="RelationAjax" />
	<input type="hidden" name="record" value="{$PARENT_RECORD->getId()}" />
	<input type="hidden" name="mode" value="saveRelatedPriceBooks" />
	<input type="hidden" name="related_data" value="" />
	<table class="table table-bordered listViewEntriesTable">
		<caption>Tarif de base : {$PRODUCT_PRICE} &euro;HT {if $PRODUCT_PRICE_TAXED != PRODUCT_PRICE}, {$PRODUCT_PRICE_TAXED} &euro;TTC{/if}
		<div class="pull-right">
			<button class="btn btn-success"  name="saveButton"><strong>Enregistrer</strong></button>
		</div>
		</caption>
		<thead>
			<tr class="listViewHeaders">
				<th class="discounttype" style="text-align: center">
					<span style="float: right">Quantité</span>
					/
					<span style="float: left">Remise</span>
				</th>
				<th class="quantity" data-quantity="1">1</th>
				<th class="span1"><a id="add-quantity" class="pull-right" title="cliquer ici pour ajouter une quantité"><b>+</b></a></th>
			</tr>
		</thead>
		{foreach item=DISCOUNT_TYPE key=DISCOUNT_TYPE_ID from=$DISCOUNT_TYPES}
			<tr class="listViewEntries" data-discounttype='{$DISCOUNT_TYPE_ID}'>
				<td class="discounttype">{$DISCOUNT_TYPE['label']}</td>
				<td class="price">{$PRODUCT_PRICE} &euro;HT</td>
				<td class="span1"><a class="clear-row pull-right"><span class="ui-icon ui-icon-trash"></span></a></td>
			</tr>
		{/foreach}		
	</table>
	<div class="commentaires">
		Les tarifs par quantité sont entendus à l'unité.
		<br>Exemple : le tarif de 100 autocollants doit être saisi à 0,50 €HT, et non 50,00 €HT.
	</div>
</div>
{/strip}
<style>
	#related-pricebooks table {
		width: 600px;
	}
	#related-pricebooks table .discounttype {
		max-width: 8em;
	}
	#related-pricebooks a
	, #related-pricebooks th.quantity
	, #related-pricebooks td.price{
		cursor: pointer;
	}
	#related-pricebooks th.quantity
	, #related-pricebooks td.price
	, #related-pricebooks tr > .discounttype {
		width: 12em;
	}
	#related-pricebooks th.quantity
	, #related-pricebooks td.price {
		padding-left: 8px;
	}
	#related-pricebooks .remove-quantity {
		margin: 0 16px;
		padding: 2px;
	}
	#related-pricebooks .btn-success {
		margin-bottom: 4px;
	}
	#related-pricebooks .price input
	, #related-pricebooks .quantity input{
		width: 45px;
	}
	#related-pricebooks .price select {
		width: 60px;
	}
	#related-pricebooks .commentaires {
		margin-top : 2em;
		font-style: italic;
		font-family: Courier New;
	}
	#related-pricebooks #add-quantity {
		font-size: large;
		padding: 6px;
	}
</style>