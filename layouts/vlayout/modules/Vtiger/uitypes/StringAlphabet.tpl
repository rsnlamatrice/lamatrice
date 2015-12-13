{*<!--
/*********************************************************************************
   * ED150903
   * Default "alphabet" search
   * Required : ALPHABET_FIELD
   * Optional : ALPHABETS
 ********************************************************************************/
*}
{strip}
{if !$ALPHABET_FIELD}
	<code>ALPHABET_FIELD is required</code>
{/if}
{assign var=ALPHABET_VALUE value=$ALPHABET_FIELD->get('fieldvalue')}
{if !$ALPHABETS}
	{assign var=ALPHABETS value=vtranslate('LBL_ALPHABETS', 'Vtiger')}
{/if}
{if is_string($ALPHABETS)}
	{assign var=ALPHABETS value=','|explode:$ALPHABETS}
{/if}

<div class="alphabetSorting noprint" data-searchkey="{$ALPHABET_FIELD->getName()}" data-searchoperator="s">
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