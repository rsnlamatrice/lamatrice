
{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <!--
    All final details are stored in the first element in the array with the index name as final_details
    so we will get that array, parse that array and fill the details
    -->
    {assign var="FINAL" value=$RELATED_PRODUCTS.1.final_details}

    {*ED141021 inversion de la valeur par defaut*}
    {assign var="IS_INDIVIDUAL_TAX_TYPE" value=true}
    {assign var="IS_GROUP_TAX_TYPE" value=false}

    {if $FINAL.taxtype eq 'group'}
        {assign var="IS_GROUP_TAX_TYPE" value=true}
        {assign var="IS_INDIVIDUAL_TAX_TYPE" value=false}
    {/if}

    <table class="table table-bordered blockContainer lineItemTable" id="lineItemTab">
        <tr>
            <th><span class="inventoryLineItemHeader">{vtranslate('LBL_ITEM_DETAILS', $MODULE)}</span>
		{* ED1506022 remise type *}
			</th><th>{assign var=FIELD_MODEL value=$RECORD->getField('accountdiscounttype')}
		{if $FIELD_MODEL}
			<a id="inventory_accountdiscounttype_setter">{vtranslate('LBL_ACCOUNT_DISCOUNT_TYPE')}</a>
			<div id="inventory_accountdiscounttype_holder">
			{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
			</div>
		{/if}
		{if $RECORD->get('typedossier') === 'Avoir'
		|| $RECORD->get('typedossier') === 'Remboursement'}
		    </th><th><b style="border: 1px solid red; padding: 1px 2px;">Ceci est un {vtranslate($RECORD->get('typedossier'), $MODULE)}</b>
		{elseif $MODULE === 'SalesOrder'
		&& $RECORD->get('typedossier') === 'Facture'}
		    </th><th>En facture de dépôt-vente, <br>les quantités doivent être saisies en négatif
		{/if}				
	    </th>
            <td colspan="1" class="chznDropDown">
			{if ! $SHOW_ITEMS_HEADER_DETAILS}<div class="hide">{* ED150906 hide *}{/if}
                <b>{$APP.LBL_CURRENCY}</b>&nbsp;&nbsp;
                {assign var=SELECTED_CURRENCY value=$CURRENCINFO}
				{* Lookup the currency information if not yet set - create mode *}
				{if $SELECTED_CURRENCY eq ''}
					{assign var=USER_CURRENCY_ID value=$USER_MODEL->get('currency_id')}
					{foreach item=currency_details from=$CURRENCIES}
						{if $currency_details.curid eq $USER_CURRENCY_ID}
							{assign var=SELECTED_CURRENCY value=$currency_details}
						{/if}
					{/foreach}
				{/if}

                <select class="chzn-select" id="currency_id" name="currency_id">
                    {foreach item=currency_details key=count from=$CURRENCIES}
                        <option value="{$currency_details.curid}" class="textShadowNone" data-conversion-rate="{$currency_details.conversionrate}" {if $SELECTED_CURRENCY.currency_id eq $currency_details.curid} selected {/if}>
                            {$currency_details.currencylabel|@getTranslatedCurrencyString} ({$currency_details.currencysymbol})
                        </option>
                    {/foreach}
                </select>

				{assign var="RECORD_CURRENCY_RATE" value=$RECORD_STRUCTURE_MODEL->getRecord()->get('conversion_rate')}
				{if $RECORD_CURRENCY_RATE eq ''}
					{assign var="RECORD_CURRENCY_RATE" value=$SELECTED_CURRENCY.conversionrate}
				{/if}
                <input type="hidden" name="conversion_rate" id="conversion_rate" value="{$RECORD_CURRENCY_RATE}" />
                <input type="hidden" value="{$SELECTED_CURRENCY.currency_id}" id="prev_selected_currency_id" />
                <!-- TODO : To get default currency in even better way than depending on first element -->
                <input type="hidden" id="default_currency_id" value="{$CURRENCIES.0.curid}" />
			{if ! $SHOW_ITEMS_HEADER_DETAILS}</div>{/if}
            </td>
            <td colspan="3" class="chznDropDown">
			{if ! $SHOW_ITEMS_HEADER_DETAILS}<div class="hide">{* ED150906 hide *}{/if}
                <div class="pull-right">
                    <div class="inventoryLineItemHeader">
                        <span class="alignTop">{vtranslate('LBL_TAX_MODE', $MODULE)}</span>
                    </div>
                    <select class="chzn-select lineItemTax" id="taxtype" name="taxtype" >
                        <OPTION value="individual" {if $IS_INDIVIDUAL_TAX_TYPE}selected{/if}>{vtranslate('LBL_INDIVIDUAL', $MODULE)}</OPTION>
                        <OPTION value="group" {if $IS_GROUP_TAX_TYPE}selected{/if}>{vtranslate('LBL_GROUP', $MODULE)}</OPTION>
                    </select>
                </div>
			{if ! $SHOW_ITEMS_HEADER_DETAILS}</div>{/if}
            </td>
        </tr>
        <tr>
            <td><b>{vtranslate('LBL_TOOLS',$MODULE)}</b></td>
            <td><span class="redColor">*</span><b>{vtranslate('LBL_ITEM_NAME',$MODULE)}</b></td>
            <td><b>{vtranslate('LBL_QTY',$MODULE)}</b></td>
            <td><b>{vtranslate('LBL_LIST_PRICE',$MODULE)}</b></td>
            <td><b class="pull-right">{vtranslate('LBL_TOTAL',$MODULE)}</b></td>
            <td><b class="pull-right">{vtranslate('LBL_NET_PRICE',$MODULE)}</b></td>
        </tr>
        <tr id="row0" class="hide lineItemCloneCopy">
            {include file="LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=0 data=[]}
        </tr>
        {foreach key=row_no item=data from=$RELATED_PRODUCTS}
            <tr id="row{$row_no}" class="lineItemRow"
				{if $data["entityType$row_no"] eq 'Products'}data-quantity-in-stock="{$data["qtyInStock$row_no"]}"{/if}
				{if array_key_exists('priceBookDetails', $data)} data-pricebookdetails="{htmlentities(Zend_Json::encode($data['priceBookDetails']))}"{/if}
			>
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=$row_no data=$data}
            </tr>
        {/foreach}
        {if count($RELATED_PRODUCTS) eq 0}
            <tr id="row1" class="lineItemRow">
                {include file="LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=1 data=[]}
            </tr>
	    {if $MODULE neq 'SalesOrder'}
		<tr id="row2" class="lineItemRow">
		    {include file="LineItemsContent.tpl"|@vtemplate_path:'Inventory' row_no=2 data=["entityType2"=>"Services"]}
		</tr>
	    {/if}
        {/if}

    </table>


    <div class="row-fluid verticalBottomSpacing">
        <div>
            {if $PRODUCT_ACTIVE eq 'true' && $SERVICE_ACTIVE eq 'true'}
                <div class="btn-toolbar">
                    <span class="btn-group">
                        <button type="button" class="btn addButton" id="addProduct">
                            <i class="icon-plus icon-white"></i><strong>{vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                        </button>
                    </span>
                    <span class="btn-group">
                        <button type="button" class="btn addButton" id="addService">
                            <i class="icon-plus icon-white"></i><strong>{vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                        </button>
                    </span>
                </div>
            {elseif $PRODUCT_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn addButton" id="addProduct">
                        <i class="icon-plus icon-white"></i><strong> {vtranslate('LBL_ADD_PRODUCT',$MODULE)}</strong>
                    </button>
                </div>
            {elseif $SERVICE_ACTIVE eq 'true'}
                <div class="btn-group">
                    <button type="button" class="btn addButton" id="addService">
                        <i class="icon-plus icon-white"></i><strong> {vtranslate('LBL_ADD_SERVICE',$MODULE)}</strong>
                    </button>
                </div>
            {/if}
        </div>
    </div>
    <table class="table table-bordered blockContainer lineItemTable" id="lineItemResult">
        <tr class="hide{*ED160111*}">
            <td  width="83%">
                <div class="pull-right"><strong>{vtranslate('LBL_ITEMS_TOTAL',$MODULE)}</strong></div>
            </td>
            <td>
                <div id="netTotal" class="pull-right netTotal">{if !empty($FINAL.hdnSubTotal)}{$FINAL.hdnSubTotal}{else}0.00{/if}</div>
            </td>
        </tr>
        <tr class="hide{*ED160111*}">
            <td width="83%">
                <span class="pull-right">(-)&nbsp;<b><a href="javascript:void(0)" id="finalDiscount"
		    {if $FINAL.discount_final_source}
			title="{$FINAL.discount_final_source}"
			style="color: red;"
		    {/if}
		    >{vtranslate('LBL_DISCOUNT',$MODULE)}</a></b></span>
            </td>
            <td>
                <span id="discountTotal_final" class="pull-right discountTotal_final">{if $FINAL.discountTotal_final}{$FINAL.discountTotal_final}{else}0.00{/if}</span>

                <!-- Popup Discount Div -->
                <div id="finalDiscountUI" class="finalDiscountUI hide">
                    {assign var=DISCOUNT_TYPE_FINAL value="zero"}
                    {if !empty($FINAL.discount_type_final)}
                        {assign var=DISCOUNT_TYPE_FINAL value=$FINAL.discount_type_final }
                    {/if}
                    <input type="hidden" id="discount_type_final" name="discount_type_final" value="{$DISCOUNT_TYPE_FINAL}" />
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
                        <thead>
                            <tr>
                                <th id="discount_div_title_final"><b>{vtranslate('LBL_SET_DISCOUNT_FOR',$MODULE)}:{$data.$productTotal}</b></th>
                                <th>
                                    <button type="button" class="close closeDiv">x</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="zero" {if $DISCOUNT_TYPE_FINAL eq 'zero'}checked{/if} />&nbsp; {vtranslate('LBL_ZERO_DISCOUNT',$MODULE)}</td>
                                <td class="lineOnTop">
                                    <!-- Make the discount value as zero -->
                                    <input type="hidden" class="discountVal" value="0" />
                                </td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="percentage" {if $DISCOUNT_TYPE_FINAL eq 'percentage'}checked{/if} />&nbsp; % {vtranslate('LBL_OF_PRICE',$MODULE)}</td>
                                <td><span class="pull-right">&nbsp;%</span><input type="text" id="discount_percentage_final" name="discount_percentage_final" value="{$FINAL.discount_percentage_final}" class="discount_percentage_final smallInputBox pull-right discountVal {if $DISCOUNT_TYPE_FINAL neq 'percentage'}hide{/if}" /></td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="amount" {if $DISCOUNT_TYPE_FINAL eq 'amount'}checked{/if} />&nbsp;{vtranslate('LBL_DIRECT_PRICE_REDUCTION',$MODULE)}</td>
                                <td><input type="text" id="discount_amount_final" name="discount_amount_final" value="{$FINAL.discount_amount_final}" class="smallInputBox pull-right discount_amount_final discountVal {if $DISCOUNT_TYPE_FINAL neq 'amount'}hide{/if}" /></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success finalDiscountSave" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
                <!-- End Popup Div -->
            </td>
        </tr>
        <tr class="hide">{*ED151201 hide*}
            <td width="83%">
                <span class="pull-right">(+)&nbsp;<b>{vtranslate('LBL_SHIPPING_AND_HANDLING_CHARGES',$MODULE)} </b></span>
            </td>
            <td>
                <span class="pull-right">
		    <input id="shipping_handling_charge" name="shipping_handling_charge" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" type="text" class="lineItemInputBox" value="{if $FINAL.shipping_handling_charge}{$FINAL.shipping_handling_charge}{else}0.00{/if}" />
		    {* ED150529 TODO déplacer dans .js, convertir le input avec le plugin vu pour les pourcentages *}
		    <a id="shipping_handling_charge_plus" data-offset="5">+</a>
		    <a id="shipping_handling_charge_minus" data-offset="-5">-</a>
		</span>
            </td>
        </tr>
		<tr>
			<td width="83%">
				<span class="pull-right"><b>{vtranslate('LBL_PRE_TAX_TOTAL', $MODULE_NAME)} </b></span>
			</td>
			<td>
				{assign var=PRE_TAX_TOTAL value=$FINAL.preTaxTotal}
				<span class="pull-right" id="preTaxTotal">{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0.00{/if}</span>
				<input type="hidden" id="pre_tax_total" name="pre_tax_total" value="{if $PRE_TAX_TOTAL}{$PRE_TAX_TOTAL}{else}0.00{/if}"/>
			</td>
        </tr>
		<!-- Group Tax - starts -->
        <tr id="group_tax_row" valign="top" class="{if $IS_INDIVIDUAL_TAX_TYPE}hide{/if}">
            <td width="83%">
                <span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="finalTax">{vtranslate('LBL_TAX',$MODULE)}</a></b></span>
                <!-- Pop Div For Group TAX -->
                <div class="hide finalTaxUI" id="group_tax_div">
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable">
                        <tr>
                            <th id="group_tax_div_title" colspan="2" nowrap align="left" >{vtranslate('LBL_GROUP_TAX',$MODULE)}</th>
                            <th align="right">
                                <button type="button" class="close closeDiv">x</button>
                            </th>
                        </tr>
                        {foreach item=tax_detail name=group_tax_loop key=loop_count from=$TAXES}
                            <tr>
                                <td align="left" class="lineOnTop">
                                    <input type="text" size="5" name="{$tax_detail.taxname}_group_percentage" id="group_tax_percentage{$smarty.foreach.group_tax_loop.iteration}" value="{$tax_detail.percentage}" class="smallInputBox groupTaxPercentage" />&nbsp;%
                                </td>
                                <td align="center" class="lineOnTop"><div class="textOverflowEllipsis">{$tax_detail.taxlabel}</div></td>
                                <td align="right" class="lineOnTop">
                                    <input type="text" size="6" name="{$tax_detail.taxname}_group_amount" id="group_tax_amount{$smarty.foreach.group_tax_loop.iteration}" style="cursor:pointer;" value="{$tax_detail.amount}" readonly class="cursorPointer smallInputBox groupTaxTotal" />
                                </td>
                            </tr>
                        {/foreach}
                        <input type="hidden" id="group_tax_count" value="{$smarty.foreach.group_tax_loop.iteration}" />
                    </table>
                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
                <!-- End Popup Div Group Tax -->
            </td>
            <td><span id="tax_final" class="pull-right tax_final">{if $FINAL.tax_totalamount}{$FINAL.tax_totalamount}{else}0.00{/if}</span></td>
        </tr>
        <!-- Group Tax - ends -->
        <tr {*ED150529 TODO 'hide' should be configurable *}class="hide">
            <td width="83%">
                <span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="shippingHandlingTax">{vtranslate('LBL_TAX_FOR_SHIPPING_AND_HANDLING',$MODULE)} </a></b></span>

                <!-- Pop Div For Shipping and Handling TAX -->
                <div class="hide" id="shipping_handling_div">
                    <table class="table table-nobordered popupTable">
                        <thead>
                            <tr>
                                <th id="sh_tax_div_title" colspan="2" nowrap align="left" >{vtranslate('LBL_SET_SHIPPING_AND_HANDLING_TAXES_FOR',$MODULE)}: {if $FINAL.shipping_handling_charge}{$FINAL.shipping_handling_charge}{else}0.00{/if}</th>
                                <th align="right">
                                    <button type="button" class="close closeDiv">x</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach item=tax_detail name=sh_loop key=loop_count from=$SHIPPING_TAXES}
                                <tr>
									<td><div class="textOverflowEllipsis">{$tax_detail.taxlabel}</div></td>
                                    <td>
                                        <input type="text" name="{$tax_detail.taxname}_sh_percent" id="sh_tax_percentage{$smarty.foreach.sh_loop.iteration}" value="{$tax_detail.percentage}" class="smallInputBox shippingTaxPercentage" />&nbsp;%
                                    </td>
                                    <td>
                                        <input type="text" name="{$tax_detail.taxname}_sh_amount" id="sh_tax_amount{$smarty.foreach.sh_loop.iteration}" class="cursorPointer smallInputBox shippingTaxTotal pull-right" value="{$tax_detail.amount}" readonly />
                                    </td>
                                </tr>
                            {/foreach}
                        <input type="hidden" id="sh_tax_count" value="{$smarty.foreach.sh_loop.iteration}" />
                        </tbody>
                    </table>
                    <div class="modal-footer lineItemPopupModalFooter modal-footer-padding">
                        <div class=" pull-right cancelLinkContainer">
                            <a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
                        </div>
                        <button class="btn btn-success finalTaxSave" type="button" name="lineItemActionSave"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
                    </div>
                </div>
                <!-- End Popup Div for Shipping and Handling TAX -->
            </td>
            <td>
                <span class="pull-right shipping_handling_tax" id="shipping_handling_tax">{if $FINAL.shtax_totalamount}{$FINAL.shtax_totalamount}{else}0.00{/if}</span>
            </td>
        </tr>
        <tr valign="top">
            <td width="83%" >
                <div class="pull-right"><b>{vtranslate('LBL_ADJUSTMENT',$MODULE)}&nbsp;&nbsp;</b>
                    <div class="radio pull-right">
                        <input type="radio" name="adjustmentType" option value="-" {if $FINAL.adjustment lt 0}checked{/if} disabled="disabled">{vtranslate('LBL_DEDUCT',$MODULE)}
                    </div>
                    <div class="radio pull-right">
                        <input type="radio" name="adjustmentType" option value="+" {if $FINAL.adjustment gte 0}checked{/if} disabled="disabled">{vtranslate('LBL_ADD',$MODULE)}&nbsp;&nbsp;
                    </div>
                </div>
            </td>
            <td>
                <span class="pull-right"><input id="adjustment" name="adjustment" type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="lineItemInputBox" value="{if $FINAL.adjustment lt 0}{abs($FINAL.adjustment)}{elseif $FINAL.adjustment}{$FINAL.adjustment}{else}0.00{/if}" disabled="disabled"></span>
            </td>
        </tr>
        <tr valign="top">
            <td  width="83%">
                <span class="pull-right"><b>{vtranslate('LBL_GRAND_TOTAL',$MODULE)}</b></span>
            </td>
            <td>
                <span id="grandTotal" name="grandTotal" class="pull-right grandTotal">{$FINAL.grandTotal}</span>
            </td>
        </tr>
        {if $MODULE eq 'Invoice' or $MODULE eq 'PurchaseOrder'}
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        {if $MODULE eq 'Invoice'}
			    {if $RECORD->get('typedossier') === 'Avoir'
				|| $RECORD->get('typedossier') === 'Remboursement'}
			        <b>{vtranslate('LBL_REFUND',$MODULE)}</b>
			    {else}
			        <b>{vtranslate('LBL_RECEIVED',$MODULE)}</b>
			    {/if}
                        {else}
                            <b>{vtranslate('LBL_PAID',$MODULE)}</b>
                        {/if}
                    </div>
		    {* ED141226
		    * ajout des champs complémentaires du règlement
		    *}
		    {if $MODULE eq 'Invoice'}
			<div class="pull-right" id="invoice-recu">
                            {foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				{if $BLOCK_LABEL eq 'LBL_RSNREGLEMENTS'}
				    <table>
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
					<tr id="{$FIELD_MODEL->get('name')}">
					    <td class="fieldLabel">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</td>
					    <td>
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
					    </td>
					</tr>
					{/foreach}
				    </table>
				    {break}
				{/if}
			    {/foreach}
			</div>
		    {/if}
                </td>
                <td>
					<span class="pull-right">
                    {if $MODULE eq 'Invoice'}
                        <input id="received" name="received" type="text" class="lineItemInputBox" value="{if $RECORD->getDisplayValue('received')}{$RECORD->getDisplayValue('received')}{else}0.00{/if}">
                    {else}
                        <input id="paid" name="paid" type="text" class="lineItemInputBox" value="{if $RECORD->getDisplayValue('paid')}{$RECORD->getDisplayValue('paid')}{else}0.00{/if}">
                    {/if}
						<a id="received_set_balance">=</a>
					</span>
                </td>
            </tr>
            <tr valign="top">
                <td width="83%" >
                    <div class="pull-right">
                        <b>{vtranslate('LBL_BALANCE',$MODULE)}</b>
                    </div>
                </td>
                <td>
                    <span class="pull-right"><input id="balance" name="balance" type="text" class="lineItemInputBox" value="{if $RECORD->getDisplayValue('balance')}{$RECORD->getDisplayValue('balance')}{else}0.00{/if}" readonly></span>
                </td>
            </tr>
        {/if}
    </table>
    <input type="hidden" name="totalProductCount" id="totalProductCount" value="{$row_no}" />
    <input type="hidden" name="subtotal" id="subtotal" value="" />
    <input type="hidden" name="total" id="total" value="" />
{/strip}