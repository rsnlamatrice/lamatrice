{*<!--
/*********************************************************************************
** ED1502
*
 ********************************************************************************/
-->*}

{assign var="TEXTAREA_DEFAULT_ROWS" value="12"}

<div class="commentContainer">
	<div class="commentTitle row-fluid">
		<div class="addCommentBlock">
			<div>
				{print_r($CONTACTNAME_FIELD, true)}
				<br>{print_r($EMAIL_FIELDS, true)}
				<textarea name="emailList" rows="{$TEXTAREA_DEFAULT_ROWS}" class="emaillist" style="width: 100%;">
					{for $nRecord=0 to count($RECORDS)}
					{*foreach item=$RECORD from=$RECORDS*}
						{assign var=RECORD value=$RECORDS[$nRecord]}
				<br>{print_r($RECORD, true)}
						{for $nField=0 to count($EMAIL_FIELDS)}
							{assign var=FIELD value=$EMAIL_FIELDS[$nField]}
						{*foreach item=$FIELD from=$EMAIL_FIELDS*}
							{if $RECORD[$FIELD]}
								{$RECORD[$CONTACTNAME_FIELD]} &lt;{$RECORD[$FIELD]}&gt;;
							{/if}
						{/for}
					{/for}
					{print_r($RECORDS, true)}
				</textarea>
			</div>
		</div>
	</div>
		<div class="hide basicEditCommentBlock" style="min-height: 150px;">
		<div class="pull-right">
			<a class="cursorPointer closeCommentBlock cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
		</div>
	</div>
</div>