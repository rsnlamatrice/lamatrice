{*<!--
/*********************************************************************************
  ** 
  *
 ********************************************************************************/
-->*}
{strip}
<div id="massEditContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
		<button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader">{count($RECORDS)} {if count($RECORDS) > 1}{vtranslate($MODULE, $MODULE)}{else}{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}{/if}</h3>
	</div>
	
		<div name='massEditContent'>
			<div class="modal-body">
				{foreach key=RECORD_ID item=RECORD from=$RECORDS name=recordList}
					{if !$smarty.foreach.recordList.first}
						{assign var=isgroupValues value=$RECORD->getPicklistValuesDetails('isgroup')}
					{/if}
					<div class="row-fluid">
						<h4><a href="{$URL_ROOT}{$RECORD->getDetailViewUrl()}" target="_blank">{$RECORD->getHtmlLabel()}</a></h4>
						<span class="span10">
							<textarea class="span8" onfocus="$(this).select();">
								{$RECORD->getName()}
								{if $RECORD->get('mailingstreet2')} - {$RECORD->get('mailingstreet2')}{/if}
								{if $RECORD->get('mailingstreet3')} - {$RECORD->get('mailingstreet3')}{/if}
								{if $RECORD->get('mailingstreet')} - {$RECORD->get('mailingstreet')}{/if}
								{if $RECORD->get('mailingzip')} - {$RECORD->get('mailingzip')} {$RECORD->get('mailingcity')}{/if}
								{if $RECORD->get('mailingcountry') && strcasecmp($RECORD->get('mailingcountry'), 'France') !== 0} - {$RECORD->get('mailingcountry')}{/if}
								{if $RECORD->get('email') && !$RECORD->get('emailoptout')} - {$RECORD->get('email')}{/if}
								{if $RECORD->get('phone')} - {$RECORD->get('phone')}{/if}
								{if $RECORD->get('mobile')} - {$RECORD->get('mobile')}{/if}
								{if $RECORD->get('homephone')} - {$RECORD->get('home')}{/if}
								{"\r\n"}
							</textarea>
						</span>
					</div>
					{if !$smarty.foreach.recordList.last}
						<br/>
						<hr/>
					{/if}
				{/foreach}
			</div>
		</div>
		<div class="modal-footer">
			<div class=" pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</a>
			</div>
		</div>
	</form>
</div>
{/strip}