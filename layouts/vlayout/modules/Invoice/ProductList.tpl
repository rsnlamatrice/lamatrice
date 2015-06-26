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
<div class="detailViewInfo">
	<table class="table table-bordered equalSplit detailview-table">
		{foreach item=LISTVIEW_ENTRY key=ENTRY_KEY from=$LISTVIEW_ENTRIES}
			<tr>
				<td class="fieldLabel narrowWidthType" nowrap>
					<label class="muted marginRight10px">
						{$LISTVIEW_ENTRY['hdnProductcode'|cat:$ENTRY_KEY]} - {$LISTVIEW_ENTRY['productName'|cat:$ENTRY_KEY]}
					</label>
				</td>
				<td class="fieldValue narrowWidthType">
					<label class="muted marginRight10px">
						{$LISTVIEW_ENTRY['qty'|cat:$ENTRY_KEY]}
					</label>
				</td>
			</tr>
		{/foreach}
	</table>
</div>
{/strip}
