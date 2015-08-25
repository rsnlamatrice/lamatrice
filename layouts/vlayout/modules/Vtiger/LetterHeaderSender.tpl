{*<!--
/*********************************************************************************
  ** ED150825
  *
 ********************************************************************************/
-->*}
{strip}
<div class="letterHeaderSender" style="margin-left: 1em; line-height: 1.5em;">
	{if get_class($SENDER_RECORD) eq 'Vtiger_CompanyDetails_Model'}
		<img src="test/logo/{$SENDER_RECORD->get('logoname')}"/>
		<div style="font-size: 14px; margin-top: 2em;">
			<b>{$SENDER_RECORD->get('organizationname')}</b>
			<br/>
			{$SENDER_RECORD->get('address')}
			<br/>
			{$SENDER_RECORD->get('code')}&nbsp;{$SENDER_RECORD->get('city')}
		</div>
	{else}
		{var_dump($SENDER_RECORD)}
	{/if}
</div>
{/strip}