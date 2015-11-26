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
<div id="toggleButton" class="toggleButton" title="Left Panel Show/Hide"> 
	<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-right {else} icon-chevron-left{/if}"></i>
</div>&nbsp
    <div style="padding-left: 15px;">
        <form id="exportForm" class="form-horizontal row-fluid" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
            <input type="hidden" name="action" value="ExportData" />
            <input type="hidden" name="viewname" value="{$VIEWID}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" id="page" name="page" value="{$PAGE}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
            <div class="row-fluid">
                <div class="span">&nbsp;</div>
                <div class="span8">
                    <h4>{vtranslate('LBL_EXPORT_RECORDS',$MODULE)}</h4>
                    <div class="well exportContents marginLeftZero">
                        <div class="row-fluid">
                            <div class="row-fluid" style="height:30px">
                                <div class="span6 textAlignRight row-fluid">
                                    <div class="span8">{vtranslate('LBL_EXPORT_SELECTED_RECORDS',$MODULE)}&nbsp;</div>
                                    <div class="span3"><input type="radio" name="mode" value="ExportSelectedRecords" {if !empty($SELECTED_IDS)} checked="checked" {else} disabled="disabled"{/if}/></div>
                                </div>
                            {*<!-- AV151023 : Display the nunber of rows -->*}
                            {if empty($SELECTED_IDS)}&nbsp; <span class="redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>
                            {else} <span >({$COUNT_SELECTED})</span>{/if}
                        </div>
                        {*<!-- AV151023 : Display the nunber of rows -->*}
                        {*<!-- <div class="row-fluid" style="height:30px">
                            <div class="span6 textAlignRight row-fluid">
                                <div class="span8">{vtranslate('LBL_EXPORT_DATA_IN_CURRENT_PAGE',$MODULE)}&nbsp;</div>
                                <div class="span3"><input type="radio" name="mode" value="ExportCurrentPage" /></div>
                            </div>
                            
                            <span >({$COUNT_CURRENT_PAGE})</span>
                        </div> -->*}
                        <div class="row-fluid" style="height:30px">
                            <div class="span6 textAlignRight row-fluid">
                                <div class="span8">{vtranslate('LBL_EXPORT_ALL_DATA',$MODULE)}&nbsp;</div>
                                <div class="span3"><input type="radio"  name="mode" value="ExportAllData"  {if empty($SELECTED_IDS)} checked="checked" {/if} /></div>
                            </div>
                            {*<!-- AV151023 : Display the nunber of rows -->*}
                            <span >({$COUNT_ALL})</span>
                        </div>
                    </div>
                    {*<!-- AV151026 add export type list -->*}
                     <div class="row-fluid" style="height:30px">
                            <div class="span6 textAlignRight row-fluid">
                                <div class="span8">{vtranslate('LBL_EXPORT_TYPE',$MODULE)}&nbsp;</div>
                                <div class="span3">
                                    <!-- TMP $EXPORT_LIST ... -->
                                    {if sizeof($EXPORT_LIST) gt 0}
                                        <select id="SelectExportDropdown" name="ExportClassName" style="width: 420px;">
                                            {if sizeof($EXPORT_LIST) gt 1}
                                                <option disabled selected></option>
                                            {/if}
                                            {foreach item=EXPORT from=$EXPORT_LIST}
                                                {assign var=DESCRIPTION value=htmlentities($EXPORT['description']|cat:$EXPORT['lastimport'])}
                                                <option value="{$EXPORT['classname']}" {if $DEFAULT_EXPORT eq $EXPORT['classname']}selected="selected"{/if}
                                                    title="{$DESCRIPTION}">
                                                    {$EXPORT['exportname']|@vtranslate:$MODULE}<!-- ({$EXPORT['exporttype']|@vtranslate:$MODULE})-->
                                                </option>
                                            {/foreach}
                                        </select>

                                    {else}
                                        <strong>{'LBL_NO_EXPORT_AVAILABLE'|@vtranslate:$MODULE}</stron>
                                    {/if}
                                </div>
                            </strong>
                        </div>
                    </div>
                    <br> 
                    <div class="textAlignCenter">
                        <button name="export-preview" class="btn btn-primary">{vtranslate('LBL_PREVIEW', $MODULE)}</button>
                        <button class="btn btn-success" type="submit"><strong>{vtranslate($MODULE, $MODULE)}&nbsp;{vtranslate($EXPORT_LIST_MODULE, $MODULE)}</strong></button>
                        <a class="cancelLink" type="reset" onclick='window.history.back()'>{vtranslate('LBL_CANCEL', $MODULE)}</a>
                    </div>
                </div>
            </div>
            <div id="preview-container" style="width:96%; overflow:scroll; margin:0px 2%;">
            </div>
        </div>
    </form>
</div>
{/strip}
