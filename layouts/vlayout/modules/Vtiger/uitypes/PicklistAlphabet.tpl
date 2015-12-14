{*<!--
/*********************************************************************************
  ** ED150904
  *
 ********************************************************************************/
-->*}{*
ED141024
		argument PICKLIST_VALUES may be passed
		PICKLIST_VALUE may be an array. Then, gets 'label' property.
*}
{strip}
{assign var=FIELD_MODEL value=$ALPHABET_FIELD}
{if !isset($PICKLIST_VALUES)}
	{assign var=PICKLIST_DATA value=array()}
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues($PICKLIST_DATA)}
{/if}
{assign var=SELECTED_VALUE value=$FIELD_MODEL->get('fieldvalue')}

		
<div class="alphabetSorting noprint" data-searchkey="{$FIELD_MODEL->getFieldName()}" data-searchoperator="{if $FIELD_MODEL->get('uitype') eq '33'}c{else}e{/if}">
<table width="100%" class="table-bordered" style="border: 1px solid #ddd;table-layout: fixed">
	<tbody>
		<tr>
			{if $FIELD_MODEL->isEmptyPicklistOptionAllowed() && !array_key_exists('', $PICKLIST_DATA)}
				<td class="alphabetSearch textAlignCenter cursorPointer" style="padding : 0px !important;">
					<a href="#" data-searchvalue="" data-searchoperator="c">
						<label title="{vtranslate('LBL_ALL')}">({vtranslate('LBL_ALL')})</label>
					</a>
				</td>
			{/if}
			{*var_dump($PICKLIST_DATA)*}
			{foreach item=PICKLIST_ITEM key=PICKLIST_NAME from=$PICKLIST_DATA}
				{assign var=IS_SELECTED_ITEM value=trim(decode_html($SELECTED_VALUE)) eq trim($PICKLIST_NAME)}
				{if is_array($PICKLIST_ITEM)}
					{if array_key_exists('label', $PICKLIST_ITEM)}
						{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
					{else}
						{assign var=PICKLIST_LABEL value=$PICKLIST_NAME}
					{/if}
				{else}
					{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
				{/if}
				<td class="alphabetSearch textAlignCenter cursorPointer {if $IS_SELECTED_ITEM} fontBold highlightBackgroundColor {/if}"
					style="padding : 0px !important;
					{if $IS_SELECTED_ITEM}
						border-style: inset;
					{/if}">
					<a href="#" data-searchvalue="{$PICKLIST_NAME}">
						<label>
						{if is_array($PICKLIST_ITEM) && $PICKLIST_ITEM['uicolor']}
							<div class="picklistvalue-uicolor" style="background-color:{$PICKLIST_ITEM['uicolor']}">&nbsp;</div>
							<br>
						{/if}
						{if is_array($PICKLIST_ITEM) && $PICKLIST_ITEM['uiicon']}<span class="{$PICKLIST_ITEM['uiicon']}"></span>&nbsp;{/if}
						{vtranslate($PICKLIST_LABEL, $MODULE)}</label>
					</a>
				</td>
			{/foreach}
		</tr>
	</tbody>
</table>
</div>
{/strip}