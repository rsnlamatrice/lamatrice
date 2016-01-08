{*<!--
/*********************************************************************************
  ** ED160108
  *
 ********************************************************************************/
 */
-->*}
{strip}
<hr>
<div class="row-fluid">
	<div class="span4 toggleViewByMode">
		{assign var="CURRENT_VIEW" value="full"}
		{assign var="CURRENT_MODE_LABEL" value="{vtranslate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
		<button type="button" class="btn changeDetailViewMode cursorPointer"><strong>{vtranslate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}</strong></button>
		{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'} }
		<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}"
			data-full-url="{$FULL_MODE_URL}"  />
	</div>
	<div class="span8">
		<div class="pull-right">
			<div>
				<p>
					<small>
						<em>{vtranslate('LBL_CREATED_ON',$MODULE_NAME)} <b>{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('createdtime'))}</b>
						{if $RECORD->get('createdby') && $RECORD->get('createdby') != 1}&nbsp;{vtranslate('LBL_BY')} {$RECORD->getDisplayValue('createdby')}{/if}</em>
					</small>
				</p>
			</div>
			<div>
				<p>
					<small>
						<em>{vtranslate('LBL_MODIFIED_ON',$MODULE_NAME)} <b>{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('modifiedtime'))}</b>
						{if $RECORD->get('modifiedby') && $RECORD->get('createdby') neq $RECORD->get('modifiedby')}&nbsp;par {$RECORD->getDisplayValue('modifiedby')}{/if}</em>
					</small>
				</p>
			</div>
		</div>
	</div>
</div>
{/strip}