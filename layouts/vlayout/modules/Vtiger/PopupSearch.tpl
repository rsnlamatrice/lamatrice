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
    <input type="hidden" id="parentModule" value="{$SOURCE_MODULE}"/>
    <input type="hidden" id="module" value="{$MODULE}"/>
    <input type="hidden" id="parent" value="{$PARENT_MODULE}"/>
    <input type="hidden" id="sourceRecord" value="{$SOURCE_RECORD}"/>
    <input type="hidden" id="sourceField" value="{$SOURCE_FIELD}"/>
    <input type="hidden" id="url" value="{$GETURL}" />
    <input type="hidden" id="multi_select" value="{$MULTI_SELECT}" />
    <input type="hidden" id="currencyId" value="{$CURRENCY_ID}" />
    <input type="hidden" id="relatedParentModule" value="{$RELATED_PARENT_MODULE}"/>
    <input type="hidden" id="relatedParentId" value="{$RELATED_PARENT_ID}"/>
    <input type="hidden" id="view" value="{$VIEW}"/>
    <div class="popupContainer row-fluid">
        <div class="span12">
            <div class="row-fluid">
                <div class="span6 row-fluid">
                    <span class="logo span5"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/></span>
                </div>
                <div class="span6 pull-right">
                    <span class="pull-right"><b>{vtranslate($MODULE_NAME, $MODULE_NAME)}</b></span>
                </div>
            </div>
        </div>
    </div>
    <form class="form-horizontal popupSearchContainer">
        {if !$SEARCH_KEY}
            {include file=vtemplate_path('PopupSearchItem.tpl',$MODULE) SEARCH_KEY='' SEARCH_VALUE='' IS_SEARCH_FIRST_ITEM=true IS_SEARCH_LAST_ITEM=true}
        {else}
            {foreach item=SEARCH_KEY_ITEM from=$SEARCH_KEY name=search_list}
                {include file=vtemplate_path('PopupSearchItem.tpl',$MODULE) SEARCH_VALUE=$SEARCH_VALUE[$smarty.foreach.search_list.index] SEARCH_KEY=$SEARCH_KEY_ITEM IS_SEARCH_FIRST_ITEM=$smarty.foreach.search_list.first IS_SEARCH_LAST_ITEM=$smarty.foreach.search_list.last}
            {/foreach}
        {/if}
        {foreach item=ALPHABET_FIELD from=$ALPHABET_FIELDS}
            {include file=vtemplate_path($ALPHABET_FIELD->getUITypeModel()->getAlphabetTemplateName(),$MODULE)}
        {/foreach}
    </form>
    {*ED151208 if $SOURCE_MODULE neq 'PriceBooks'*}
        <div class="popupPaging">
            <div class="row-fluid">
                <span class="actions span6">&nbsp;
                    {if $MULTI_SELECT}
                    {if !empty($LISTVIEW_ENTRIES)}<button class="select btn"><strong>{vtranslate('LBL_SELECT', $MODULE)}</strong></button>{/if}
                {/if}
            </span>
            <span class="span6">
                <span class="pull-right">
                    <span class="pageNumbers alignTop" data-placement="bottom" data-original-title="">
                    {if !empty($LISTVIEW_ENTRIES)}{$PAGING_MODEL->getRecordStartRange()} {vtranslate('LBL_to', $MODULE)} {$PAGING_MODEL->getRecordEndRange()}{/if}
                </span>
                <span class="pull-right btn-group">
                    <button class="btn" id="listViewPreviousPageButton" {if !$PAGING_MODEL->isPrevPageExists()} disabled {/if}><span class="icon-chevron-left"></span></button>
                    <button class="btn dropdown-toggle" type="button" id="listViewPageJump" data-toggle="dropdown" {if $PAGE_COUNT eq 1} disabled {/if}>
                        <i class="vtGlyph vticon-pageJump" title="{vtranslate('LBL_LISTVIEW_PAGE_JUMP',$moduleName)}"></i>
                    </button>
                    <ul class="listViewBasicAction dropdown-menu" id="listViewPageJumpDropDown">
                        <li>
                            <span class="row-fluid">
                                <span class="span3 pushUpandDown2per"><span class="pull-right">{vtranslate('LBL_PAGE',$moduleName)}</span></span>
                                <span class="span4">
                                    <input type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGE_NUMBER}"/>
                                </span>
                                <span class="span2 textAlignCenter pushUpandDown2per">
                                    {vtranslate('LBL_OF',$moduleName)}&nbsp;
                                </span>
                                <span class="span2 pushUpandDown2per" id="totalPageCount">{$PAGE_COUNT}</span>
                            </span>
                        </li>
                    </ul>
                    <button class="btn" id="listViewNextPageButton" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if}><span class="icon-chevron-right"></span></button>
                </span>
            </span>
        </span>
    </div>
</div>

{*/if*}
{/strip}
