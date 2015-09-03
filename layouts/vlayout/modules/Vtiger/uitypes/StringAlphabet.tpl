{*<!--
/*********************************************************************************
   * ED150903
   * Default "alphabet" search
 ********************************************************************************/
*}
{strip}

{assign var=ALPHABET_VALUE value=$LISTVIEW_HEADER->get('fieldvalue')}
{assign var=ALPHABETS_LABEL value=vtranslate('LBL_ALPHABETS', 'Vtiger')}
{assign var=ALPHABETS value=','|explode:$ALPHABETS_LABEL}

<div class="alphabetSorting noprint" data-searchkey="{$LISTVIEW_HEADER->getName()}">
	<table width="100%" class="table-bordered" style="border: 1px solid #ddd;table-layout: fixed">
		<tbody>
			<tr>
			{foreach item=ALPHABET from=$ALPHABETS}
				<td class="alphabetSearch textAlignCenter cursorPointer {if $ALPHABET_VALUE eq $ALPHABET} highlightBackgroundColor {/if}" style="padding : 0px !important"><a id="{$ALPHABET}" href="#">{$ALPHABET}</a></td>
			{/foreach}
			</tr>
		</tbody>
	</table>
</div>
{/strip}