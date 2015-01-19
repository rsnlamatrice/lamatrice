{*<!--
/*********************************************************************************
** 
*
 ********************************************************************************/
-->*}
{strip}
	<span class="span2">
		<span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-collectif"></span></span>
	</span>
	<span class="span8 margin0px">
		<span class="row-fluid">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="{$RECORD->getName()}">{$RECORD->getDisplayValue('nom')}
			<br>{$RECORD->getDisplayValue('rubriquepreferee')}
		</span>
	</span>
{/strip}