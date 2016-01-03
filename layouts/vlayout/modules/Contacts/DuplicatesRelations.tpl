{*<!--
/*********************************************************************************
  ** ED151009
  *  Generate relations between contacts found as duplicated
  *
 ********************************************************************************/
-->*}
{strip}
<div id="popupPageContainer" class="popupBackgroundColor">

	<input id="popUpClassName" type="hidden" value="Contacts_DuplicatesRelations_Js"/>
	<div>
		<br>
		<div style='margin-left:10px'><h3>{vtranslate('LBL_DUPLICATES_RELATIONS_IN', $MODULE)} > {$MODULE}</h3></div><br>
		<div class='alert-info'>{vtranslate('LBL_DUPLICATES_RELATIONS_DESCRIPTION', $MODULE)}</div>
	</div>
	
	<form class="form-horizontal contentsBackground" name="massMerge" method="post" action="index.php">
		<input type="hidden" name=module value="{$MODULE}" />
		<input type="hidden" name="view" value="DuplicatesRelations" />
		<input type="hidden" name="mode" value="saveRelations" />
		<input type="hidden" class="triggerEventName" name="triggerEventName" value="{$smarty.request.triggerEventName}"/>

	<div>
		<table class='table table-bordered table-condensed'>
			<tbody>
			{foreach item=RECORD from=$RECORDMODELS name=recordList}
			{if $smarty.foreach.recordList.last}{break}{/if}
			<tr>
				{* Affecte les valeurs du record courant à la propriété fiedlvalue de chaque field *}
				{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELDS}
					{assign var=FIELD_MODEL value=$FIELDS[$FIELD_NAME]}
					{assign var=tmp value=$FIELD_MODEL->set('fieldvalue', $RECORD->get($FIELD_MODEL->get('column')))}
				{/foreach}
				
				<tr>
					<td class="span1" style="vertical-align: top;">
						<span style="position: top;">#{$smarty.foreach.recordList.index + 1}</span>
						<br><br>
						<a href="#{$RECORD->getId()}" onclick="$(this).parents('tr:first').remove(); return false;"><span class="ui-icon ui-icon-trash"></span></a>
					</td>
					<td>
					<div class="summaryWidgetContainer">
						<div class="widget_header row-fluid">
							<table>
								<tr>
									<th><h4><a href="#{$RECORD->getId()}" onclick="$(this).parents('td:first').find('.recordDetails').toggle(); return false;">
										{assign var=FIELD_NAME value='isgroup'}
										{assign var=ISGROUP value=$RECORD->get($FIELD_NAME)}
										{assign var=FIELD_MODEL value=$FIELDS[$FIELD_NAME]}
										{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									
										{$RECORD->getName()}
										<br/><span style="font-weight: normal;">{$RECORD->get('contact_no')}</span>
									</a></h4></th>
									
									{foreach item=HEADER_FIELD key=FIELD_NAME from=$HEADER_FIELDS}
										{if $FIELD_NAME eq 'modifiedtime'}{continue}{/if}
										{assign var=FIELD_MODEL value=$FIELDS[$FIELD_NAME]}
										<td class="{$HEADER_FIELD['class']}">
										{if $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
											{$RECORD->getDisplayValue($FIELD_NAME)}
										{else}
											{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										{/if}
										{if $FIELD_NAME eq 'createdtime'}
											{assign var=FIELD_NAME value='modifiedtime'}
											{assign var=FIELD_MODEL value=$FIELDS[$FIELD_NAME]}
											{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										{/if}
										</td>
									{/foreach}
								</tr>
							</table>
						</div>
						
						<div class="recordDetails span8 hide">
							{include file='SummaryViewContents.tpl'|@vtemplate_path:$MODULE RECORD_STRUCTURE=$SUMMARY_RECORD_STRUCTURE MODULE_NAME=$MODULE RECORD=$RECORD}
						</div>
						
						<div class="widget_header row-fluid">
							<table>
								
								{foreach item=ALTER_RECORD from=$RECORDMODELS name=alterRecords}
									{if $smarty.foreach.alterRecords.index <= $smarty.foreach.recordList.index}{continue}{/if}
									<tr>
										<td rowspan="2">#{$smarty.foreach.alterRecords.index + 1}
										</td>
										<td rowspan="2">
											{assign var=FIELD_NAME value='isgroup'}
											{assign var=ISGROUP value=$ALTER_RECORD->get($FIELD_NAME)}
											{assign var=FIELD_MODEL value=$FIELDS[$FIELD_NAME]}
											{assign var=tmp value=$FIELD_MODEL->set('fieldvalue', $ISGROUP)}
											{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										
											<b>{$ALTER_RECORD->getName()}</b>
											<br/>{$ALTER_RECORD->get('contact_no')}
											
											{if $ISGROUP}
												{assign var=FIELD_NAME value='mailingstreet2'}
												{if $ALTER_RECORD->get($FIELD_NAME)}
													<div style="max-width: 15em;">{$ALTER_RECORD->get($FIELD_NAME)}
													</div>
												{/if}
											{/if}
										</td>
										<td >
											<label><input type="radio" name="createRelation-{$RECORD->getId()}-{$ALTER_RECORD->getId()}"
												value="relation"/> en relation
											</label>
										</td>
										<td>
											<input name="reldata-{$RECORD->getId()}-{$ALTER_RECORD->getId()}" onchange="
													{* TODO to .js *}
													{* check the radio button *}
													var $this = $(this)
													, $tr = $this.parents('tr:first')
													, $radio = $tr.find('input[type=&quot;radio&quot;][value=&quot;relation&quot;]')
													;
													if (this.value){
														$radio.attr('checked', 'checked');
													} else {
														$radio.removeAttr('checked');
													}
													return false;"/>
											<div style="display: inline-block; font-size: small; font-style: italic;">&nbsp;(&nbsp;
											{foreach item=RELDATA from=$RELDATA_VALUES}
												<a href="#" class="set-reldata" onclick="
													{* TODO to .js *}
													{* set text to input *}
													var $this = $(this)
													, $td = $this.parents('td:first')
													, $input = $td.find('input:first')
													, text = $this.text()
													;
													$input.val(text).change();
													return false;">{$RELDATA}</a>,&nbsp;
											{/foreach}
											...
											&nbsp;)</span>
										</td>
									</tr>
									<tr>
										<td colspan="2">
											<label><input type="radio" name="createRelation-{$RECORD->getId()}-{$ALTER_RECORD->getId()}"
												value="commonAccount"/> en compte commun
											</label>
										</td>
									</tr>
								{/foreach}
							</table>
						</div>
					</div>
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	<div class='row-fluid'>
		<div class="offset4" style="margin-top: 2em;">
			<button type=submit class='btn btn-success'>{vtranslate('LBL_SAVE', $MODULE)}</button>
		</div>
	</div>
	</form>
	<br>
</div>
{/strip}