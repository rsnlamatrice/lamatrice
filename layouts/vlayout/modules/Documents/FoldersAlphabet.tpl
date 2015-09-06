{*<!--
/*********************************************************************************
** ED150904
*
 ********************************************************************************/
-->*}
{strip}

<div class="alphabetSorting noprint folderSorting" style="height: auto;" data-searchkey="folderid">
	<table width="100%" class="table-bordered" style="border: 1px solid #ddd;table-layout: fixed; font-size: 11px; border-spacing: 1px;">
		<tbody>
			<tr>
			{foreach item=FOLDER from=$FOLDERS}
				<td class="alphabetSearch textAlignCenter cursorPointer {if $FOLDER_SEARCH_VALUE eq $FOLDER->getName()} fontBold highlightBackgroundColor {/if}"
					style="padding : 0px !important;
					{if $FOLDER->get('uicolor')}
						{if $FOLDER_SEARCH_VALUE eq $FOLDER->getName()}
							border: 4px inset {$FOLDER->get('uicolor')};
						{else}
							border: 3px solid {$FOLDER->get('uicolor')};
						{/if}
					{/if}">
					<a href="#">{vtranslate($FOLDER->getName(), $MODULE)}</a>
				</td>
			{/foreach}
			</tr>
		</tbody>
	</table>
</div>

{/strip}