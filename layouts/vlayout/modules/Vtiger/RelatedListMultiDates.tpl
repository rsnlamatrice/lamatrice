{*<!--
/*********************************************************************************
** ED140918 copie de Vtiger/RelatedList.tpl
    specifique aux critere4d ou documents du contact
*
********************************************************************************/
-->*}
{strip}
    <div class="relatedContainer multidates {if $WIDGET_INSIDE} widget-content critere4d{/if}">
	<param id="relationIsMultiDates" value="1" />
      {if !$WIDGET_INSIDE}
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
                            <button type="button" class="btn addButton
                            {if $IS_SELECT_BUTTON eq true} selectRelation {/if} "
                        {if $IS_SELECT_BUTTON eq true} data-moduleName={$RELATED_LINK->get('_module')->get('name')} {/if}
                        {if ($RELATED_LINK->isPageLoadLink())}
                        {if $RELATION_FIELD} data-name="{$RELATION_FIELD->getName()}" {/if}
                        data-url="{$RELATED_LINK->getUrl()}"
                    {/if}
			    {if $IS_SELECT_BUTTON neq true}name="addButton"{/if}>{if $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}&nbsp;<strong>{$RELATED_LINK->getLabel()}</strong></button>
		    </div>
		{/foreach}
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
    {/if}
<div class="relatedContents contents-bottomscroll">
    <div class="bottomscroll-div">
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
        <table class="table table-bordered listViewEntriesTable">
            {if !$WIDGET_INSIDE}
	    <thead>
                <tr class="listViewHeaders">
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
			<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap class="{$WIDTHTYPE}">
                            {if $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
                                <a href="javascript:void(0);" class="noSorting">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}</a>
                            {elseif $HEADER_FIELD->get('column') eq 'time_start'}
                            {else}
                                <a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}{$NEXT_SORT_ORDER}{else}ASC{/if}" data-fieldname="{$HEADER_FIELD->get('column')}">{vtranslate($HEADER_FIELD->get('label'), $RELATED_MODULE->get('name'))}
                                    &nbsp;&nbsp;{if $COLUMN_NAME eq $HEADER_FIELD->get('column')}<img class="{$SORT_IMAGE} icon-white">{/if}
                                </a>
                            {/if}
                        </th>
                    {/foreach}
		    <th/>
                </tr>
	    </thead>
            {/if}
	    {if $DATE_FORMAT eq ''}{assign var=DATE_FORMAT value='dd-mm-yyyy'}{/if}
            {foreach item=RELATED_RECORD from=$RELATED_RECORDS}
                <tr class="listViewEntries" data-id='{$RELATED_RECORD->getId()}' data-recordUrl='{$RELATED_RECORD->getDetailViewUrl()}'>
                    {foreach item=HEADER_FIELD from=$RELATED_HEADERS}
                        {assign var=RELATED_HEADERNAME value=$HEADER_FIELD->get('name')}
			    <td class="{$WIDTHTYPE}" data-field-type="{$HEADER_FIELD->getFieldDataType()}" nowrap>
                            {if $HEADER_FIELD->isNameField() eq true or $HEADER_FIELD->get('uitype') eq '4'}
                                <a href="{$RELATED_RECORD->getDetailViewUrl()}">{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}</a>
                            {elseif $RELATED_HEADERNAME eq 'access_count'}
                                {$RELATED_RECORD->getAccessCountValue($PARENT_RECORD->getId())}
                            {elseif $RELATED_HEADERNAME eq 'time_start'}
			    {*ED140906
			    * dateapplication
			    *
			    *	dateapplication est fourni comme tableau de DateTime
			    *	
			    *}
                            {elseif $RELATED_HEADERNAME eq 'dateapplication'}
				{assign var=FIELD_NAME value=$RELATED_HEADERNAME}
                                <div class="input-append span3 row-fluid dateapplication">
				    {assign var=I value=0}
				    {assign var=DATE_IDS value=$RELATED_RECORD->get($FIELD_NAME)}
				    {foreach item=DATE_ID from=$DATE_IDS}
					<div class="row-fluid date">
						{if $WIDGET_INSIDE}
						    {if is_object($DATE_ID)}
								{$DATE_ID->format('d/m/Y')}
							{else}
								{$DATE_ID}
							{/if}
						{else}
						<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$I}" type="text"
						    class="span5 dateField"
						    name="{$HEADER_FIELD->getFieldName()}" data-date-format="{$DATE_FORMAT}" 
						    value="{if is_object($DATE_ID)}{$DATE_ID->format('d/m/Y')}{else}{$DATE_ID}{/if}"
						    dateapplication="{if is_object($DATE_ID)}{$DATE_ID->format('Y-m-d H:i:s')}{else}{$DATE_ID}{/if}"
						    data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
						/>
						<span class="add-on"><i class="icon-calendar"></i></span>
						{/if}
					</div>
					{assign var=I value=$I+1}
				    {/foreach}
				</div>
			    {*ED140906
			    * rel_data
			    *
			    *	rel_data est fourni comme tableau de string, 1 par dateapplication
			    *	
			    *}
			    {elseif $RELATED_HEADERNAME eq 'rel_data'}
                                <div class="input-append row-fluid">
				{assign var=FIELD_NAME value=$RELATED_HEADERNAME}
                                {assign var=I value=0}
				{foreach item=DATA from=$RELATED_RECORD->get($FIELD_NAME)}
				    <div>
					{if $WIDGET_INSIDE}
					    {$DATA}
					{else}
					    <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$I}"
					    class="span4 rel_data"
					    value="{$DATA}"
					    dateapplication="{if is_object($DATE_IDS[$I])}{$DATE_IDS[$I]->format('Y-m-d H:i:s')}{else}{$DATE_IDS[$I]}{/if}"/>
					{/if}
				    </div>
				    {assign var=I value=$I+1}
				{/foreach}
				</div>
					
                            
							{* ED141224 *}
							{elseif ($RELATED_HEADERNAME eq 'folderid')}
									<div style="background-color:{$RELATED_RECORD->get('uicolor')}; margin-left:0;" class="picklistvalue-uicolor">&nbsp;</div>
								{$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME)}
                            {else}
                                {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME, false, true)}
                            {/if}
                            {if $HEADER_FIELD@last && !$WIDGET_INSIDE}
			    <td nowrap class="{$WIDTHTYPE}">
				{if $IS_DELETABLE}
				    <div class="pull-right actions">
					<div class="sub-field">
					    {assign var=I value=0}
					    {foreach item=DATA from=$RELATED_RECORD->get($FIELD_NAME)}
						<div>
						<a class="relationDelete"
						    dateapplication="{if is_object($DATE_IDS[$I])}{$DATE_IDS[$I]->format('Y-m-d H:i:s')}{else}{$DATE_IDS[$I]}{/if}">
						    <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
						    {if $IS_EDITABLE && $I==0}
							&nbsp;&nbsp;<a class="relationAdd"><i title="Ajoute une nouvelle application du crit&eacute;re" class="icon-plus alignMiddle"></i></a>
						    {/if}
					
						</div>
						{assign var=I value=$I+1}
					    {/foreach}
					</div>
				    </div>
				{/if}
			    </td>
			    </td><td nowrap class="{$WIDTHTYPE}">
                                <div class="pull-right actions">
                                    <span class="actionImages">
                                        <a href="{$RELATED_RECORD->getFullDetailViewUrl()}"><i title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE)}" class="icon-th-list alignMiddle"></i></a>&nbsp;
                                        {if $IS_EDITABLE}
                                            <a href='{$RELATED_RECORD->getEditViewUrl()}'><i title="{vtranslate('LBL_EDIT', $MODULE)}" class="icon-pencil alignMiddle"></i></a>
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
{/strip}
