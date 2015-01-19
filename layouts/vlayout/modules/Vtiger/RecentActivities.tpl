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
<div class="recentActivitiesContainer">
	<div>
		{if !empty($RECENT_ACTIVITIES)}
			<ul class="unstyled">
				{foreach item=RECENT_ACTIVITY from=$RECENT_ACTIVITIES}
					{if $RECENT_ACTIVITY->isCreate()}
						<li>
							<div>
								<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getName()}</strong> {vtranslate('LBL_CREATED', $MODULE_NAME)}</span>
								<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getParent()->get('createdtime'))}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getParent()->get('createdtime'))}</small></p></span>
							</div>
						</li>
					{else if $RECENT_ACTIVITY->isUpdate()}
						<li>
							<div>
								<span><strong>{$RECENT_ACTIVITY->getModifiedBy()->getDisplayName()}</strong> {vtranslate('LBL_UPDATED', $MODULE_NAME)}</span>
								<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECENT_ACTIVITY->getActivityTime())}">{Vtiger_Util_Helper::formatDateDiffInStrings($RECENT_ACTIVITY->getActivityTime())}</small></p></span>
							</div>

							{foreach item=FIELDMODEL from=$RECENT_ACTIVITY->getFieldInstances()}
								{if $FIELDMODEL && $FIELDMODEL->getFieldInstance() && $FIELDMODEL->getFieldInstance()->isViewableInDetailView()}
									<div class='font-x-small updateInfoContainer'>
										<i>{vtranslate($FIELDMODEL->getName(),$MODULE_NAME)}</i> :&nbsp;
										{if $FIELDMODEL->get('prevalue') neq ''}
											{$FIELDMODEL->getDisplayValue($FIELDMODEL->get('prevalue'))}&nbsp;{vtranslate('LBL_TO', $MODULE_NAME)}&nbsp;
										{else}
											{* First time change *}
										{/if}
										<b>{$FIELDMODEL->getDisplayValue($FIELDMODEL->get('postvalue'))}</b>
									</div>
								{/if}
							{/foreach}

						</li>
					{else if $RECENT_ACTIVITY->isRelationLink()}
						<li>
							<div class="row-fluid">{* ED141210 : gestion des enregistrements qui n'existent plus *}
								{assign var=URELATION value=$RECENT_ACTIVITY->getRelationInstance()}
								{assign var=RELATED_RECORD value=$URELATION->getUnLinkedRecord()}
								{if $RELATED_RECORD}
									<span>{vtranslate($RELATED_RECORD->getModuleName(), $RELATED_RECORD->getModuleName())} {vtranslate('LBL_ADDED', $MODULE_NAME)}
										<strong>{$RELATED_RECORD->getName()}</strong></span>
									<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATED_RECORD->get('createdtime'))}">
										{Vtiger_Util_Helper::formatDateDiffInStrings($RELATED_RECORD->get('createdtime'))}</small></p></span>
								{else}
									<span>{vtranslate($URELATION->get('targetmodule'), $URELATION->get('targetmodule'))} {vtranslate('LBL_DELETED', $MODULE_NAME)} <strong></strong></span>
									<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($URELATION->get('changedon'))}">
										{Vtiger_Util_Helper::formatDateDiffInStrings($URELATION->get('changedon'))}</small></p></span>
								{/if}
							</div>
						</li>
					{else if $RECENT_ACTIVITY->isRelationUnLink()}
						<li>
							<div class="row-fluid">{* ED141210 : gestion des enregistrements qui n'existent plus *}
								{assign var=URELATION value=$RECENT_ACTIVITY->getRelationInstance()}
								{assign var=RELATED_RECORD value=$URELATION->getUnLinkedRecord()}
								{if $RELATED_RECORD}
									<span>{vtranslate($RELATED_RECORD->getModuleName(), $RELATED_RECORD->getModuleName())} {vtranslate('LBL_DELETED', $MODULE_NAME)} <strong>{$RELATED_RECORD->getName()}</strong></span>
									<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($RELATED_RECORD->get('modifiedtime'))}">
										{Vtiger_Util_Helper::formatDateDiffInStrings($RELATED_RECORD->get('modifiedtime'))}</small></p></span>
								{else}
									<span>{vtranslate($URELATION->get('targetmodule'), $URELATION->get('targetmodule'))} {vtranslate('LBL_DELETED', $MODULE_NAME)} <strong></strong></span>
									<span class="pull-right"><p class="muted"><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString($URELATION->get('changedon'))}">
										{Vtiger_Util_Helper::formatDateDiffInStrings($URELATION->get('changedon'))}</small></p></span>
								{/if}
							</div>
						</li>
					{else if $RECENT_ACTIVITY->isRestore()}
						<li>

						</li>
					{/if}
				{/foreach}
			</ul>
			{else}
				<div class="summaryWidgetContainer">
					<p class="textAlignCenter">{vtranslate('LBL_NO_RECENT_UPDATES')}</p>
				</div>
		{/if}
	</div>
	{if $PAGING_MODEL->isNextPageExists()}
		<div class="row-fluid">
			<div class="pull-right">
				<a href="javascript:void(0)" class="moreRecentUpdates">{vtranslate('LBL_MORE',$MODULE_NAME)}..</a>
			</div>
		</div>
	{/if}
	<span class="clearfix"></span>
</div>
{/strip}