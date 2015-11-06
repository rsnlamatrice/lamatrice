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
 /**
  * ED151017 Moved from ListViewHeader.tpl
  */
-->*}
{strip}
	{if !$CURRENT_USER_MODEL}{assign var=CURRENT_USER_MODEL value=$USER_MODEL}{/if}
				<span class="customFilterMainSpan btn-group">
					{if $CUSTOM_VIEWS|@count gt 0}
						{if !$VIEWID && $RELATED_VIEWNAME}
							{assign var=VIEWID value=$RELATED_VIEWNAME}
						{/if}
						<select id="customFilter" style="width:350px;">
							{foreach key=GROUP_LABEL item=GROUP_CUSTOM_VIEWS from=$CUSTOM_VIEWS}
							<optgroup label=' {if $GROUP_LABEL eq 'Mine'} &nbsp; {else if} {vtranslate($GROUP_LABEL)} {/if}' >
									{foreach item="CUSTOM_VIEW" from=$GROUP_CUSTOM_VIEWS}
										<option  data-editurl="{$CUSTOM_VIEW->getEditUrl()}" data-deleteurl="{$CUSTOM_VIEW->getDeleteUrl()}" data-approveurl="{$CUSTOM_VIEW->getApproveUrl()}"
										data-denyurl="{$CUSTOM_VIEW->getDenyUrl()}" data-editable="{$CUSTOM_VIEW->isEditable()}"
										data-deletable="{$CUSTOM_VIEW->isDeletable()}" data-pending="{$CUSTOM_VIEW->isPending()}"
										data-public="{$CUSTOM_VIEW->isPublic() && $CURRENT_USER_MODEL->isAdminUser()}"
										id="filterOptionId_{$CUSTOM_VIEW->get('cvid')}"
										value="{$CUSTOM_VIEW->get('cvid')}"
										data-id="{$CUSTOM_VIEW->get('cvid')}"
										{if $VIEWID neq '' && $VIEWID neq '0' && $VIEWID == $CUSTOM_VIEW->getId()} selected="selected"
										{elseif ($VIEWID == '' or $VIEWID == '0')&& $CUSTOM_VIEW->isDefault() eq 'true'} selected="selected"{/if}
										class="filterOptionId_{$CUSTOM_VIEW->get('cvid')}">
											{if $CUSTOM_VIEW->get('viewname') eq 'All'}
												{*vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)*}
												({vtranslate($MODULE, $MODULE)})
											{else}{vtranslate($CUSTOM_VIEW->get('viewname'), $MODULE)}
											{/if}
											{if $GROUP_LABEL neq 'Mine'} [ {$CUSTOM_VIEW->getOwnerName()} ]  {/if}
										</option>
									{/foreach}
								</optgroup>
							{/foreach}
							{if $FOLDERS neq ''}
								<optgroup id="foldersBlock" label='{vtranslate('LBL_FOLDERS', $MODULE)}' >
									{foreach item=FOLDER from=$FOLDERS}
										<option data-foldername="{$FOLDER->getName()}"
										{if decode_html($FOLDER->getName()) eq $FOLDER_NAME} selected="selected"{/if}
										data-folderid="{$FOLDER->get('folderid')}"
										data-deletable="{$FOLDER->isDeletable()}"
										class="filterOptionId_folder{$FOLDER->get('folderid')} folderOption{if $FOLDER->getName() eq 'Default'} defaultFolder {/if}"
										id="filterOptionId_folder{$FOLDER->get('folderid')}"
										data-id="{$DEFAULT_CUSTOM_FILTER_ID}"
										style="background-color: {$FOLDER->get('uicolor')};"{* ED141010 *}
										>{$FOLDER->getName()}</option>
									{/foreach}
								</optgroup>
							{/if}
						</select>
						{* ED151104 *}
						{if !$NOT_EDITABLE_CUSTOMVIEWS}
						<a id="customFilter-edit" href="" style="opacity:0.7;"><span class="ui-icon ui-icon-pencil"></span></a>
						<span class="filterActionsDiv hide">
							<hr>
							<ul class="filterActions">
								<li data-value="create" id="createFilter" data-createurl="{$CUSTOM_VIEW->getCreateUrl()}"><i class="icon-plus-sign"></i> {vtranslate('LBL_CREATE_NEW_FILTER')}</li>
							</ul>
						</span>
						<img class="filterImage" src="{'filter.png'|vimage_path}" style="display:none;height:13px;margin-right:2px;vertical-align: middle;">
						{/if}
					{else}
						<input type="hidden" value="0" id="customFilter" />
					{/if}
				</span>
{/strip}