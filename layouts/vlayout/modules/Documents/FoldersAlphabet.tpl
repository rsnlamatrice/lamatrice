{*<!--
/*********************************************************************************
** ED150904
*
 ********************************************************************************/
-->*}
{strip}

<select>
	{foreach item=FOLDER from=$FOLDERS}
		<option class="alphabetSearch textAlignCenter cursorPointer {if $FOLDER_SEARCH_VALUE eq $FOLDER->getName()} fontBold highlightBackgroundColor {/if}"
			style="padding : 0px !important;
			{if $FOLDER->get('uicolor')}
				{if $FOLDER_SEARCH_VALUE eq $FOLDER->getName()}
					border: 4px inset {$FOLDER->get('uicolor')};
				{else}
					border: 3px solid {$FOLDER->get('uicolor')};
				{/if}
			{/if}">
			<a href="#">{vtranslate($FOLDER->getName(), $MODULE)}</a>
		</option>
	{/foreach}
</select>

{/strip}
