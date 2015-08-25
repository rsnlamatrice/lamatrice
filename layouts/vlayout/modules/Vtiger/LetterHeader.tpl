{*<!--
/*********************************************************************************
  ** ED150825
  *
 ********************************************************************************/
-->*}
{strip}
<div class="letterHeader">
	<table style="width: 95%;">
		<tr>
			<td style="width: 60%; vertical-align: top;">
				{include file='LetterHeaderSender.tpl'|vtemplate_path:$MODULE}
			</td>
			<td style="width: 40%; vertical-align: top;">
				{include file='LetterHeaderRecipient.tpl'|vtemplate_path:$MODULE}
			</td>
		</tr>
	</table>
</div>
{/strip}