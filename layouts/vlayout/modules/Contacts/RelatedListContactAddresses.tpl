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
    <div class="relatedContainer">
        <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
        <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
        <input type="hidden" value="{$ORDER_BY}" id="orderBy">
        <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
        <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
        <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
        <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
        <div class="relatedHeader ">
            <div class="btn-toolbar row-fluid">
                <div class="span8">

                    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            {assign var=IS_DELETE_BUTTON value={$RELATED_LINK->get('_deleteRelation')}}
                            <button type="button" class="btn addButton
                            {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
			    {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
			    {if ($RELATED_LINK->isPageLoadLink())}
				{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
				data-url="{$RELATED_LINK->getUrl()}"
			    {/if}
			    {if $IS_DELETE_BUTTON eq true}name="deleteButton"
			    {elseif $IS_SELECT_BUTTON neq true}name="addButton"
			    {/if}>
			    {if $RELATED_LINK->get('linkicon')}
				<i class="{$RELATED_LINK->get('linkicon')} icon-white"></i>
			    {elseif $IS_DELETE_BUTTON eq true}
				<i class="icon-minus icon-white"></i>
			    {elseif $IS_SELECT_BUTTON eq false}
				<i class="icon-plus icon-white"></i>
			    {/if}
			    &nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
			</div>
			{if $IS_SELECT_BUTTON neq true && $IS_DELETE_BUTTON neq true}
			    {assign var=HAS_ADD_BUTTON value=true}
			{/if}
		    {/foreach}
		    {* ORGINAL
		    {foreach item=RELATED_LINK from=$RELATED_LIST_LINKS['LISTVIEWBASIC']}
                        <div class="btn-group">
                            {assign var=IS_SELECT_BUTTON value={$RELATED_LINK->get('_selectRelation')}}
                            <button type="button" class="btn addButton
                            {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
			    {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
			    {if ($RELATED_LINK->isPageLoadLink())}
				{if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
				data-url="{$RELATED_LINK->getUrl()}"
			    {/if}
			    {if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>
			    {if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
			    &nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
			</div>
		    {/foreach}*}
&nbsp;
</div>
<div class="span4">
    <span class="row-fluid">
        <span class="span7 pushDown">
            <span class="pull-right pageNumbers alignTop" data-placement="bottom" data-original-title="" style="margin-top: -5px">
            {*ED140907 if !empty($RELATED_RECORDS)} {$PAGING->getRecordStartRange()} {vtranslate('LBL_to', $RELATED_MODULE->get('name'))} {$PAGING->getRecordEndRange()}{/if*}
	    {if !empty($RELATED_RECORDS)}
		{assign var=START_RANGE value=$PAGING->getRecordStartRange()}
		{if $START_RANGE gt 1}
		    {$START_RANGE}&nbsp;{vtranslate('LBL_to', $RELATED_MODULE->get('name'))}&nbsp;
		{/if}
		{$PAGING->getRecordEndRange()}
	    {/if}
        </span>
    </span>
    <span class="span5 pull-right">
        <span class="btn-group pull-right">
            <button class="btn" id="relatedListPreviousPageButton" {if !$PAGING->isPrevPageExists()} disabled {/if} type="button"><span class="icon-chevron-left"></span></button>
            <button class="btn dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
                <i class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></i>
            </button>
            <ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown">
                <li>
                    <span class="row-fluid">
                        <span class="span3"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
                        <span class="span4">
                            <input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGING->getCurrentPage()}"/>
                        </span>
                        <span class="span2 textAlignCenter">
                            {vtranslate('LBL_OF',$moduleName)}
                        </span>
                        <span class="span2" id="totalPageCount">{$PAGE_COUNT}</span>
                    </span>
                </li>
            </ul>
            <button class="btn" id="relatedListNextPageButton" {if (!$PAGING->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button"><span class="icon-chevron-right"></span></button>
        </span>
    </span>
</span>
</div>
</div>
</div>
<div class="contents-topscroll">
    <div class="topscroll-div">
        &nbsp;
    </div>
</div>
<div class="relatedContents contents-bottomscroll">
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
		{assign var=IS_MAIN_ADDRESS value=$RELATED_RECORD->get('addresstype') eq 'LBL_CURRENT_ADDRESS'}
		
		{assign var=VIEW_URL value=$RELATED_RECORD->getDetailViewUrl()}
		{if $IS_MAIN_ADDRESS}{assign var=VIEW_URL value=str_replace('ContactAddresses', 'Contacts', $VIEW_URL)}{/if}
		<tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$VIEW_URL}'
		    {if $IS_MAIN_ADDRESS} style="background-color: rgb(185, 236, 185);" title="{vtranslate('LBL_CONTACT_CURRENT_ADDRESS', $MODULE)}"{/if}
		>
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
			    {if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>
			    {else}
				&nbsp;{$PICKLIST_LABEL}
			    {/if}</label>
				
			{elseif empty($UNKNOWN_FIELD_RETURNS_VALUE)}{*ED140907*}
			    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
			{else}{*ED140907*}
			    {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME, false, $UNKNOWN_FIELD_RETURNS_VALUE)}
			{/if}
			{if $HEADER_FIELD@last}
			    </td><td nowrap class="{$WIDTHTYPE}">
				<div class="pull-right actions">
				{if !$IS_MAIN_ADDRESS}
				    <span class="actionImages">
                                        {if $IS_EDITABLE && $HAS_ADD_BUTTON}{*ED150207*}
                                            {assign var=VIEW_URL value=$RELATED_RECORD->getDuplicateRecordUrl()}
					    {if $IS_MAIN_ADDRESS}{assign var=VIEW_URL value=$VIEW_URL|cat:'&source_module=Contacts'}{/if}
					    {if $IS_MAIN_ADDRESS}{assign var=VIEW_TITLE value='LBL_CREATE_ARCHIVE'}
					    {else}{assign var=VIEW_TITLE value='LBL_DUPLICATE'}{/if}
					    <a href='{$VIEW_URL}'><i title="{vtranslate($VIEW_TITLE, $MODULE)}" class="icon-plus alignMiddle"></i></a>
                                        {/if}
					{assign var=VIEW_URL value=$RELATED_RECORD->getFullDetailViewUrl()}
					{if $IS_MAIN_ADDRESS}{assign var=VIEW_URL value=str_replace('ContactAddresses', 'Contacts', $VIEW_URL)}{/if}
					<a href="{$VIEW_URL}"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
					{if $IS_EDITABLE}
					   {assign var=VIEW_URL value=$RELATED_RECORD->getEditViewUrl()}
					    {if $IS_MAIN_ADDRESS}{assign var=VIEW_URL value=str_replace('ContactAddresses', 'Contacts', $VIEW_URL)}{/if}
					     <a href='{$VIEW_URL}'><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></i></a>
					{/if}
					{if $IS_DELETABLE && !$IS_MAIN_ADDRESS}
					    <a class="relatedRecordDelete"><i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
					{/if}
				    </span>
				{/if}
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
{/strip}
