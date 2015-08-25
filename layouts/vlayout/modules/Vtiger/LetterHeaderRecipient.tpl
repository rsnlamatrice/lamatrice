{*<!--
/*********************************************************************************
  ** ED150825
  *
 ********************************************************************************/
-->*}
{strip}
<div class="letterHeaderRecipient">
	{if $RECIPIENT_RECORD->getModuleName() eq 'Contacts'}
		<div style="font-size: 14px; margin-top: 30mm; line-height: 1.25em;">
			{assign var=addressformat value=$RECIPIENT_RECORD->get('mailingaddressformat')}
			{if !$addressformat}{assign var=addressformat value='NC1'}{/if}
			{if $addressformat[0] == 'N'}
				{trim($RECIPIENT_RECORD->get('firstname')|cat:' '|cat:$RECIPIENT_RECORD->get('lastname'))}<br/>
			{elseif $addressformat[0] == 'C' && $RECIPIENT_RECORD->get('mailingstreet2')}
				{trim($RECIPIENT_RECORD->get('mailingstreet2'))}<br/>
			{/if}
			{if $addressformat[1] == 'N'}
				{trim($RECIPIENT_RECORD->get('firstname')|cat:' '|cat:$RECIPIENT_RECORD->get('lastname'))}<br/>
			{elseif $addressformat[1] == 'C' && $RECIPIENT_RECORD->get('mailingstreet2')}
				{trim($RECIPIENT_RECORD->get('mailingstreet2'))}<br/>
			{/if}
			{if $RECIPIENT_RECORD->get('mailingstreet3')}
				{trim($RECIPIENT_RECORD->get('mailingstreet3'))}<br/>
			{/if}
			{if $RECIPIENT_RECORD->get('mailingstreet')}
				{trim($RECIPIENT_RECORD->get('mailingstreet'))}<br/>
			{/if}
			{if $RECIPIENT_RECORD->get('mailingpobox')}
				{trim($RECIPIENT_RECORD->get('mailingpobox'))}<br/>
			{/if}
			{$RECIPIENT_RECORD->get('mailingzip')}&nbsp;{$RECIPIENT_RECORD->get('mailingcity')}
			{if $RECIPIENT_RECORD->get('mailingcountry') && $RECIPIENT_RECORD->get('mailingcountry') neq 'France'}
				{trim($RECIPIENT_RECORD->get('mailingcountry'))}
			{/if}
		</div>
	{else}
		{var_dump($RECIPIENT_RECORD)}
	{/if}
</div>
{/strip}