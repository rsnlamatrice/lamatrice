{strip}
<div class="relatedContainer rsnstatistics">
    <input type="hidden" name="currentPageNum" value="{$PAGING->getCurrentPage()}" />
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE->get('name')}" />
    <input type="hidden" value="{$ORDER_BY}" id="orderBy">
    <input type="hidden" value="{$SORT_ORDER}" id="sortOrder">
    <input type="hidden" value="{$RELATED_ENTIRES_COUNT}" id="noOfEntries">
    <input type='hidden' value="{$PAGING->getPageLimit()}" id='pageLimit'>
    <input type='hidden' value="{$TOTAL_ENTRIES}" id='totalCount'>
    <div class="relatedHeader ">
        <div class="btn-toolbar row-fluid">
            <div class="span6">
				{if $RELATED_MODULE->get('name') === $MODULE}
					<span class="btn-toolbar span4">
						{include file='ListViewHeaderViewSelector.tpl'|@vtemplate_path}
					</span>
				{else}
					&nbsp;
				{/if}
            </div>
            <div class="span6">
                <span class="row-fluid">
                    <span class="span7 pushDown">
                        <a class="btn" id="UpdateStatistics" type="button" href="{$UPDATE_STATS_URL}">{vtranslate('LBL_UPDATE_STATS', $RELATED_MODULE->get('name'))}</a>
                        &nbsp;
						<a class="btn" id="UpdateStatistics" type="button" href="{$UPDATE_STATS_THIS_YEAR_URL}">{vtranslate('LBL_THIS_YEAR', $RELATED_MODULE->get('name'))}</a>
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
            {foreach item=RELATED_STATISTIC from=$RELATED_STATISTICS}
                <thead>
                    <tr class="listViewHeaders">
                        <th nowrap class="{$WIDTHTYPE}">
                            {vtranslate($RELATED_STATISTIC->getName(), $RELATED_MODULE->get('name'))}
                        </th>
                        {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                            <th nowrap class="{$WIDTHTYPE}">
                                {$RELATED_RECORD->getDisplayValue('name', false, $UNKNOWN_FIELD_RETURNS_VALUE)} 
                                {*({$RELATED_RECORD->getDisplayValue('begin_date', false, $UNKNOWN_FIELD_RETURNS_VALUE)} / {$RELATED_RECORD->getDisplayValue('end_date', false, $UNKNOWN_FIELD_RETURNS_VALUE)})*}
                            </th>
                        {/foreach}
                    </tr>
                </thead>
                {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                    {assign var=HEADERNAME value=$HEADER_FIELD->get('name')}
					{if $HEADER_FIELD->get('rsnstatisticsid') && ($RELATED_STATISTIC->getId() eq $HEADER_FIELD->get('rsnstatisticsid'))}
                    {*if $HEADERNAME neq 'name' and $HEADERNAME neq 'begin_date' and $HEADERNAME neq 'end_date'*}
                        <tr class="listViewEntries" data-id='{$HEADER_FIELD->getId()}' data-recordUrl='{RSNStatistics_Record_Model::getStatFieldDetailViewUrl($HEADER_FIELD->get("name"))}'>
                            <td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
                                {vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
                            </td>
                            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                                <td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
                                    {$RELATED_RECORD->getDisplayValue($HEADERNAME, false, $UNKNOWN_FIELD_RETURNS_VALUE)}
                                </td>
                            {/foreach}
                        </tr>
                    {/if}
                {/foreach}
            {/foreach}
	    </table>
        </div>
    </div>
</div>
{/strip}
