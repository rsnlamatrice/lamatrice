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

{if $IFRAME_SRC}
	<iframe width="800" height="400" src="{$IFRAME_SRC}"></iframe>
{else}
<div>
	<table>
		<tr>
			<td style="border: 1px solid gray">
				<table>
				{foreach key=FIELD_NAME item=VALUE from=$ORIGINAL_ADDRESS}
				{if is_string($VALUE)}
					<tr>
						<td>{vtranslate($FIELD_NAME)}</td>
						<td>{htmlentities($VALUE)}</td>
					</tr>
				{/if}
				{/foreach}
				</table>
			</td>
			<td>&nbsp;</td>
			<td style="border: 1px solid gray">
				<table>
				{foreach key=FIELD_NAME item=VALUE from=$NEW_ADDRESS}
					<tr>
						<td>{htmlentities($VALUE)}</td>
					</tr>
				{/foreach}
				</table>
			</td>
		</tr>
	</table>
</div>
{/if}
{/strip}