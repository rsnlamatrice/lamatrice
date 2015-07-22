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
	<span class="span10 margin0px">
		<span class="row-fluid">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="{$RECORD->getName()}">
				{$RECORD->getName()}
			</span>
			{if $RECORD->get('productcode')}
				<span class="font-x-x-large textOverflowEllipsis span" title="{$RECORD->getName()}">
					{$RECORD->getDisplayValue('productcode')}
				</span>
			{/if}
			{if !$RECORD->get('discontinued')}
				<code>désactivé</code>
			{/if}
		</span>
	</span>
{/strip}