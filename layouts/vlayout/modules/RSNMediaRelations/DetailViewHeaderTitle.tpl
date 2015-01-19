{*<!--
/*********************************************************************************
** 
*
 ********************************************************************************/
-->*}
{strip}
	<span class="span2">
		<span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-account"></span></span>
	</span>
	<span class="span8 margin0px">
		<span class="row-fluid">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="{$RECORD->getName()}">{$RECORD->getName()}</span>
			<br><span>{$RECORD->getDisplayValue('mediacontactid')}&nbsp;-&nbsp;{$RECORD->getDisplayValue('rsnmediaid')}</span>
			{if $RECORD->get('rsnthematiques')}
			&nbsp;<span title="{$RECORD->getDisplayValue('rsnthematiques')}">{$RECORD->getDisplayValue('rsnthematiques')}</span>
			{/if}
			&nbsp;/&nbsp;<span>{$RECORD->getDisplayValue('byuserid')},&nbsp;le&nbsp;{$RECORD->getDisplayValue('daterelation')}</span>
		</span>
	</span>
{/strip}