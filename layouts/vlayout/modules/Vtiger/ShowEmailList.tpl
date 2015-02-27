{*<!--
/*********************************************************************************
** ED150227
*
 ********************************************************************************/
-->*}

{assign var="TEXTAREA_DEFAULT_ROWS" value="20"}

<div class="commentContainer">
	<div class="modal-header contentsBackground">
        <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3>{vtranslate('LBL_EMAIL_LIST', $MODULE)}</h3>
	</div>
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
	
	<div class="modal-footer">
		<a class="cursorPointer marginRight10px"
		   onclick="var $input=$('#txtemaillist'); $input.val($input.val().replace(/(^|;)\s+/g, '$1 '));"
		   >{vtranslate('LBL_IN_1_ROW', $MODULE_NAME)}</a>
		<div class="pull-right cancelLinkContainer">
			<a class="cancelLink" type="reset" data-dismiss="modal" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</div>
	</div>
</div>