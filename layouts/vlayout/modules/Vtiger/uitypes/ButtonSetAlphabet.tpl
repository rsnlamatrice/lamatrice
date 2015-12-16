{*<!--
/*********************************************************************************
  ** ED150903
  * In ListViewContent context, shows header "alphabet" for a field model
 ********************************************************************************/
-->*}
{strip}
{if !$MODULE_MODEL}
	MODULE_MODEL manquant
{/if}
{assign var=FIELD_MODEL value=$ALPHABET_FIELD}
{assign var=SELECTED_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		
<div class="alphabetSorting noprint" {*ED150903*}data-searchkey="{$FIELD_MODEL->getFieldName()}" data-searchoperator="e">
<table width="100%" class="table-bordered" style="border: 1px solid #ddd;table-layout: fixed;">
	<tbody>
		<tr>
		{assign var=PICKLIST_LABELS value=$MODULE_MODEL->getPicklistValuesDetailsForHeaderFilter($FIELD_MODEL->getFieldName())}
		
		{foreach item=PICKLIST_ITEM key=PICKLIST_KEY from=$PICKLIST_LABELS}
				{if is_array($PICKLIST_ITEM)}
					{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
					{if isset($PICKLIST_ITEM['class'])}
						{assign var=PICKLIST_CLASS value=$PICKLIST_ITEM['class']}
					{else}
						{assign var=PICKLIST_CLASS value=''}
					{/if}
					{assign var=PICKLIST_ICON value=$PICKLIST_ITEM['icon']}
					{assign var=PICKLIST_TITLE value=$FIELD_LABEL|cat:' '|cat:$PICKLIST_ITEM['title']}
				{else}
					{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
				{/if}
				{*<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_KEY)}"
				</option>*}
				{assign var=IS_SELECTED_ITEM value=trim(decode_html($SELECTED_VALUE)) eq trim($PICKLIST_KEY)}
				<td class="alphabetSearch textAlignCenter cursorPointer {if $IS_SELECTED_ITEM} fontBold highlightBackgroundColor {/if}"
					style="padding : 0px !important;
					{if $PICKLIST_ITEM['uicolor']}
						{if IS_SELECTED_ITEM}
								border: 4px inset {$PICKLIST_ITEM['uicolor']};
						{else}
								border: 3px solid {$PICKLIST_ITEM['uicolor']};
						{/if}
					{else}
						{if $IS_SELECTED_ITEM}
								border-style: inset;
						{/if}
					{/if}">
						
					<a href="#" 
						{if $PICKLIST_ITEM['operator']} data-searchoperator="{$PICKLIST_ITEM['operator']}"{/if}
						{if isset($PICKLIST_ITEM['searchvalue'])}
							data-searchvalue="{$PICKLIST_ITEM['searchvalue']}"
							data-searchinput="{if $PICKLIST_KEY === 0}0{elseif $PICKLIST_KEY neq '%'}{$PICKLIST_KEY}{/if}"
						{else}
							data-searchvalue="{if $PICKLIST_KEY === 0}0{elseif $PICKLIST_KEY neq '%'}{$PICKLIST_KEY}{/if}"
						{/if}>
						<label class="{$PICKLIST_CLASS}" title="{$PICKLIST_TITLE}">
						{if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>&nbsp;{/if}
						{$PICKLIST_LABEL}</label>
					</a>
				</td>
        {/foreach}
		</tr>
	</tbody>
</table>
</div>
{/strip}