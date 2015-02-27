{*<!--
/*********************************************************************************
** ED150227
*
 ********************************************************************************/
-->*}

{assign var="TEXTAREA_DEFAULT_ROWS" value="20"}

<div class="commentContainer">
	<div class="commentTitle row-fluid">
	<div class="addCommentBlock">
	<div>
		<textarea id="txtemaillist" name="emailList" rows="{$TEXTAREA_DEFAULT_ROWS}" class="emaillist"
			style="min-width: 40em;"
			spellcheck="false"
			onfocus="$(this).select();"
			>{foreach item=RECORD from=$RECORDS
				}{foreach item=FIELD key=FIELD_NAME from=$EMAIL_FIELDS
					}{if $RECORD[$FIELD]
						}{$RECORD[$CONTACTNAME_FIELD]} &lt;{$RECORD[$FIELD]}&gt;;
{/if}{/foreach}{/foreach}</textarea>
	</div>
	</div>
	</div>
	<div class="basicEditCommentBlock" style="margin-bottom: 2em;">
		<div class="pull-right marginRight10px">
			<a class="cursorPointer marginRight10px" type="reset"
			   onclick="var $input=$('#txtemaillist'); $input.val($input.val().replace(/(^|;)\s+/g, '$1 '));"
			   >{vtranslate('LBL_IN_1_ROW', $MODULE_NAME)}</a>
			<a class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
		</div>
	</div>
</div>