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
 /* ED141010 : add RECORD_MODEL=$RECORD
 */
-->*}
{strip}
<table class="summary-table">
	<tbody>
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
		{if $FIELD_MODEL->get('name') neq 'modifiedtime' && $FIELD_MODEL->get('name') neq 'createdtime'}
			<tr class="summaryViewEntries">
				<td class="fieldLabel" style="width:30%"><label class="muted">{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}</label></td>
				<td class="fieldValue" style="width:70%">
					<div class="row-fluid">
						<span class="value span10" style="word-wrap: break-word;">
							{if $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
								{$RECORD->getDisplayValue($FIELD_NAME)}
							{else}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							{/if}
						</span>
						{if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true' && $FIELD_MODEL->get('uitype') neq 69}
							<span class="summaryViewEdit cursorPointer span2">
								<i class="icon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i>
							</span>
							<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
								{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
								{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
								{else}
									<input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
								{/if}
							</span>
						{/if}
					</div>
				</td>
			</tr>
		{/if}
	{/foreach}
	</tbody>
</table>
{include file=vtemplate_path('SummaryViewContentsFooter.tpl',$MODULE_NAME)}
{/strip}