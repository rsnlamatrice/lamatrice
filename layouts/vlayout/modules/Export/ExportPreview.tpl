<table cellpadding="5" cellspacing="0" style="border:solid 2px #565656; border-collapse: collapse;">
	{if $DISPLAY_HEADER eq true}
		<tr>
			{foreach key=ID item=HEADER from=$HEADERS}
					<th style="border:solid 1px #aaa;color:black;">{$HEADER}</th>
			{/foreach}
		</tr>
	{/if}

	{foreach key=ID item=ENTRY from=$ENTRIES}
		<tr>
			{foreach key=KEY item=VALUE from=$ENTRY}
				<td style="border:solid 1px #aaa; margin:0px;" title="{$VALUE|escape:'html'}">{$VALUE|truncate:30}</td>
			{/foreach}
		</tr>
	{/foreach}
</table>