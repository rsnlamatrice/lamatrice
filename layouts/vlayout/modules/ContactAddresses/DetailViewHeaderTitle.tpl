{*<!--
/*********************************************************************************
** 
*
 ********************************************************************************/
-->*}
{strip}
	<span class="span8 margin0px">
		<span class="row-fluid">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="{$RECORD->getName()}">{$RECORD->getDisplayValue('contactid')}&nbsp;-&nbsp;{vtranslate($RECORD->getDisplayValue('addresstype'))}</span>
			<br><span>{$RECORD->getDisplayValue('modifiedtime')}</span>
		</span>
	</span>
{/strip}