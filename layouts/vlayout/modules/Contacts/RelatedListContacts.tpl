{*<!--
/*********************************************************************************
** ED141012 copie de Vtiger/RelatedList.tpl
    specifique aux contacts du contact
*
********************************************************************************/
-->*}
{strip}
    <div class="relatedContainer multidates{if $WIDGET_INSIDE} widget-content critere4d{/if}">
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
                        {assign var=HEADER_NAME value=$HEADER_FIELD->getFieldName()}
			{assign var=IS_GROUP_FIELD value=$HEADER_NAME eq "isgroup"}
			{assign var=IS_BUTTONSET value=$HEADER_FIELD->get('uitype') eq '402'}
			{if $IS_BUTTONSET}
			  {assign var=tmp value=$HEADER_FIELD->set('picklist_values',$RELATED_MODULE->getListViewPicklistValues($HEADER_NAME))}
			{/if}
			<th {if $HEADER_FIELD@last} colspan="2" {/if} nowrap class="{$WIDTHTYPE}">
                            {if $IS_GROUP_FIELD}
                            {elseif $HEADER_FIELD->get('column') eq 'access_count' or $HEADER_FIELD->get('column') eq 'idlists' }
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
			{assign var=IS_GROUP_FIELD value=$RELATED_HEADERNAME == "isgroup"}
			{assign var=IS_BUTTONSET value=$HEADER_FIELD->get('uitype') eq '402'}
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
						{if $WIDGET_INSIDE && is_object($DATE_ID)}
						    {$DATE_ID->format('d/m/Y')}
						{else}
						<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$I}" type="text"
						    class="span5 dateField"
						    name="{$HEADER_FIELD->getFieldName()}" data-date-format="{$DATE_FORMAT}" 
						    {if is_object($DATE_ID)}
						      value="{$DATE_ID->format('d/m/Y')}"
						      dateapplication="{$DATE_ID->format('Y-m-d H:i:s')}"
						    {/if}
						    data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
						/>
						<span class="add-on"><i class="icon-calendar"></i></span>
						{/if}
					</div>
					{assign var=I value=$I+1}
				    {/foreach}
				</div>
			    {*ED140906
			    * contreltype
			    *
			    *	rel_data est fourni comme tableau de string, 1 par dateapplication
			    *	
			    *}
			    {elseif $RELATED_HEADERNAME eq 'contreltype'}
                                <div class="input-append row-fluid">
				{assign var=FIELD_NAME value=$RELATED_HEADERNAME}
				
				{assign var=PICKLIST_VALUES value=$HEADER_FIELD->get('picklist_values')}
				
                                {assign var=I value=0}
				{assign var=DATE_IDS value=$RELATED_RECORD->get('dateapplication')}
				{foreach item=DATA from=$RELATED_RECORD->get($FIELD_NAME)}
				    {if $DATA eq null}{assign var=DATA value=$HEADER_FIELD->getDefaultFieldValue()}{/if}
				    {assign var=PICKLIST_ITEM_KNOWN value=$PICKLIST_VALUES && array_key_exists($DATA, $PICKLIST_VALUES)}
				    {if $PICKLIST_ITEM_KNOWN}
					    {assign var=PICKLIST_ITEM value=$PICKLIST_VALUES[$DATA]}
					    {if is_array($PICKLIST_ITEM) && array_key_exists('label', $PICKLIST_ITEM)}
					      {assign var=DATA value=$PICKLIST_ITEM['label']}
					    {/if}
				    {/if}
				    <div>
					{if $WIDGET_INSIDE}
					    {$DATA}
					{else}
					  {*
					  <select id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$I}"
					    class="contreltype chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}" name="{$HEADER_FIELD->getFieldName()}"
					    data-validation-engine="validate[{if $HEADER_FIELD->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					    data-fieldinfo='{$FIELD_INFO|escape}'
					    {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
					    data-selected-value='{$HEADER_FIELD->get('fieldvalue')}'
					    
					    {if $DATE_IDS[$I]}dateapplication="{$DATE_IDS[$I]->format('Y-m-d H:i:s')}"{/if}>
					      {if $HEADER_FIELD->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
					      {if !$PICKLIST_ITEM_KNOWN && $DATA}<option value="{$DATA}" selected="selected">{$DATA}</option>{/if}
					      {foreach item=PICKLIST_ITEM key=PICKLIST_NAME from=$PICKLIST_VALUES}
						<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($DATA)) eq trim($PICKLIST_NAME)} selected {/if}>
						{if is_array($PICKLIST_ITEM)}{$PICKLIST_ITEM['label']}{else}{$PICKLIST_ITEM}{/if}</option>
					      {/foreach}
					  </select>
					    *}
					    <input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}_{$I}"
					    class="span3 contreltype select2"
					    value="{$DATA}"
					    dateapplication="{if $DATE_IDS[$I]}{$DATE_IDS[$I]->format('Y-m-d H:i:s')}{/if}">
					      
					    </select>
					{/if}
				    </div>
				    {assign var=I value=$I+1}
				{/foreach}
				</div>
			    {* ED141010 *}
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
					
                            {*elseif $RELATED_HEADERNAME eq 'data'}
                                {($RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME))*}
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
						    dateapplication="{$DATE_IDS[$I]->format('Y-m-d H:i:s')}">
						    <i title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></i></a>
						    {*if $IS_EDITABLE && $I==0}
							&nbsp;&nbsp;<a class="relationAdd"><i title="Ajoute un nouvelle relation au crit&eacute;re" class="icon-plus alignMiddle"></i></a>
						    {/if*}
					
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
