{*<!--
/*********************************************************************************
** 
*
********************************************************************************/
-->*}
{strip}
    <div class="control-group margin0px">
        <span class="paddingLeft10px" {if !$IS_SEARCH_FIRST_ITEM}style="color: transparent;"{/if}>
                <strong>{vtranslate('LBL_SEARCH_FOR')}</strong>
        </span>
        <span class="paddingLeft10px"></span>
        <input type="text" placeholder="{vtranslate('LBL_TYPE_SEARCH')}" class="searchvalue"
            {if $SEARCH_VALUE}value="{$SEARCH_VALUE}"{/if}
            {if $IS_SEARCH_LAST_ITEM} autofocus{/if} />
        <span class="paddingLeft10px"><strong>{vtranslate('LBL_IN')}</strong></span>
        <span class="paddingLeft10px help-inline pushDownHalfper">
            <select style="width: 150px;" class="chzn-select help-inline searchableColumnsList">
                {foreach key=block item=fields from=$RECORD_STRUCTURE}
                    {foreach key=fieldName item=fieldObject from=$fields}
                            <option value="{$fieldName}"
                            {if $SEARCH_KEY eq $fieldName}selected="selected"{/if}
                            >{vtranslate($fieldObject->get('label'),$MODULE)}</option>
                    {/foreach}
                {/foreach}
            </select>
        </span>
        {if $IS_SEARCH_LAST_ITEM}
            <span class="paddingLeft10px cursorPointer help-inline" id="popupSearchButton"><img src="{vimage_path('search.png')}" alt="{vtranslate('LBL_SEARCH_BUTTON')}" title="{vtranslate('LBL_SEARCH_BUTTON')}" /></span>
        {else}
            <span class="paddingLeft10px cursorPointer help-inline" style="min-width: 20px;">&nbsp;</span>
        {/if}
        <!-- TODO span class="paddingLeft10px cursorPointer help-inline" id="popupAddSearchButton"><img src="{vimage_path('plus.png')}" alt="{vtranslate('LBL_ADDSEARCH_BUTTON')}" title="{vtranslate('LBL_SEARCH_BUTTON')}" /></span-->
    </div>
{/strip}
