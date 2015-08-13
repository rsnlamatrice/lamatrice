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

{include file="Header.tpl"|vtemplate_path:$MODULE}
<title>Dépôt-vente de {$PARENT_RECORD->getName()} ({$PARENT_RECORD->get('contact_no')})</title>
<style>
    td[data-field-type="numeric"], td[data-field-type="currency"]{
	text-align: right;
    }
    .ui-icon {
	display: inline-block;
    }
</style>
    <h1>Dépôt-vente de {$PARENT_RECORD->getName()} <small>({$PARENT_RECORD->get('contact_no')})</small></h1>
    <button class="btn noprint"
	style="position: absolute; right: 3em; top: 0.5em;
	    background-color: #a26d1f;
	    background-image: -moz-linear-gradient(center top , #a26d1f, #a26d1f);
	    background-repeat: repeat-x;
	    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	    color: #ffffff;
	    text-shadow: none;"
	onclick="window.history.back(); return false;">retour</button>
    <div class="relatedContainer">
<div class="relatedContents contents-bottomscroll" data-related-module="{$RELATED_MODULE->getName()}">
    <div class="bottomscroll-div">
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
        <table class="table table-bordered listViewEntriesTable">
            <thead>
                <tr class="listViewHeaders">
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
			{assign var=HEADER_NAME value=$HEADER_FIELD->getFieldName()}
			<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap class="{$WIDTHTYPE}">
                            {if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
                                <a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
                            {elseif $HEADER_FIELD->get('column') eq 'time_start'}
                            {else}
				{assign var=IS_BUTTONSET value=$HEADER_FIELD->get('uitype') eq '402'}
				{if $IS_BUTTONSET}
				    {assign var=tmp value=$HEADER_FIELD->set('picklist_values',$RELATED_MODULE->getListViewPicklistValues($HEADER_NAME))}
				{/if}
				{if $HEADER_NAME neq 'isgroup'}
                                <a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
                                    &nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE} icon-white">{/if}
                                </a>
				{/if}
                            {/if}
                        </th>
                    {/foreach}
                </tr>
            </thead>
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                        {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
			{assign var=IS_BUTTONSET value=$HEADER_FIELD->get('uitype') eq '402'}
			<td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
			{if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
			    <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
			{elseif $RELATED_HEADERNAME eq 'access_count'}
			    {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
			{elseif $RELATED_HEADERNAME eq 'time_start'}
			{* ED141224 *}
			{elseif ($RELATED_HEADERNAME eq 'folderid')}
		    	    <div style="background-color:{$RELATED_RECORD->get('uicolor')}; margin-left:0;" class="picklistvalue-uicolor">&nbsp;</div>
			    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
			{* ED141222 *}
			{elseif $IS_BUTTONSET}
			    {assign var=PICKLIST_VALUES value=$HEADER_FIELD->get('picklist_values')}
			    {assign var=FIELD_VALUE value=$RELATED_RECORD->get($RELATED_HEADERNAME)}
			    {if is_array($FIELD_VALUE)}{assign var=FIELD_VALUE value=$FIELD_VALUE[0]}{/if}
			    {if $FIELD_VALUE eq null}{assign var=FIELD_VALUE value=$HEADER_FIELD->getDefaultFieldValue()}{/if}
			    {if $PICKLIST_VALUES && array_key_exists($FIELD_VALUE, $PICKLIST_VALUES)}
				    {assign var=PICKLIST_ITEM value=$PICKLIST_VALUES[$FIELD_VALUE]}
			    {else}
				    {assign var=PICKLIST_ITEM value=$FIELD_VALUE}
			    {/if}
			    {if is_array($PICKLIST_ITEM)}
				    {assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
				    {if isset($PICKLIST_ITEM['class'])}
					{assign var=PICKLIST_CLASS value=$PICKLIST_ITEM['class']}
				    {else}
					{assign var=PICKLIST_CLASS value=''}
				    {/if}
				    {assign var=PICKLIST_ICON value=$PICKLIST_ITEM['icon']}
			    {else}
				    {assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
				    {assign var=PICKLIST_ICON value=false}
				    {assign var=PICKLIST_CLASS value=false}
			    {/if}
			    <label for="{$UID}{$PICKLIST_KEY}" class="{$PICKLIST_CLASS}">
				{if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>&nbsp;{/if}
				{$PICKLIST_LABEL}
			    </label>
			    
			{*elseif ($RELATED_HEADERNAME eq '_details')}
			    <table><tr><td>
			    {str_replace("\t", '<td>', implode('<tr><td>', $RELATED_RECORD->get($RELATED_HEADERNAME)))}
			    </table>*}
			    
			{else}{*ED140907*}

			    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME, false, $UNKNOWN_FIELD_RETURNS_VALUE)}
			{/if}
			{if $HEADER_FIELD@last}
			    </td><td nowrap class="{$WIDTHTYPE}">
				<div class="pull-right actions">
				    <span class="actionImages">
                                        {if $IS_EDITABLE && $HAS_ADD_BUTTON}{*ED150207*}
                                            <a href='{$RELATED_RECORD->getDuplicateRecordUrl()}'><i title="{vtranslate('LBL_DUPLICATE', $MODULE)}" class="icon-plus alignMiddle"></i></a>
                                        {/if}
					<a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
					{if $IS_EDITABLE}
					    <a href='{$RELATED_RECORD->getEditViewUrl()}'><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></i></a>
					{/if}
					{if $IS_DELETABLE}
					    <a class="relationDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
					{/if}
				    </span>
				</div>
			    </td>
			{/if}
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
        </table>
    </div>
</div>
</div>

<script>
    $(document.body).ready(function(){
	window.print();
    });
</script>


</body></html>
{/strip}
