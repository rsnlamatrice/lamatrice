{*<!--
/*********************************************************************************
** Copie de la gestion des champs depuis SummaryViewContents.tpl
*	en remplaçant $SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'] par $RECORD_STRUCTURE['TOOLTIP_FIELDS']
*
 ********************************************************************************/
-->*}
{strip}
{* champs Ne pas... *}
{assign var=DONOT_FIELDS value=array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb', 'donototherdocuments')}
{assign var=MODULE_NAME value=$MODULE}
<div class="detailViewInfo">
	<table class="table table-bordered equalSplit detailview-table">
		{foreach item=FIELD_MODEL key=FIELD_NAME from=$RECORD_STRUCTURE['TOOLTIP_FIELDS']}
		{if $FIELD_NAME neq 'modifiedtime'
		&& $FIELD_NAME neq 'createdtime'
		&& $FIELD_NAME neq 'mailingcountry'
		&& $FIELD_NAME neq 'mailingzip'
		&& $FIELD_NAME neq 'mailingstreet2'
		
		&& $FIELD_NAME neq 'donotcall'
		&& $FIELD_NAME neq 'donotprospect'
		&& $FIELD_NAME neq 'donotrelanceadh'
		&& $FIELD_NAME neq 'donotappeldoncourrier'
		&& $FIELD_NAME neq 'donotrelanceabo'
		&& $FIELD_NAME neq 'donotappeldonweb'
		&& $FIELD_NAME neq 'donototherdocuments'
		
		&& $FIELD_NAME neq 'reference'
		
		&& $FIELD_NAME neq 'phone'
		&& $FIELD_NAME neq 'rsnnpai'
		
		&& $FIELD_NAME neq 'contact_no'
		&& $FIELD_NAME neq 'firstname'
		&& $FIELD_NAME neq 'isgroup'
		
		&& ($FIELD_NAME neq 'description' || $RECORD->get($FIELD_NAME))
		
		&& $FIELD_NAME neq 'leadsource'
		}
			<tr>
				<td class="fieldLabel narrowWidthType" nowrap>
				{if $FIELD_NAME == 'emailoptout'}
					Ne pas...
				{elseif $FIELD_NAME == 'email'}
					Email, téléphone
				{elseif $FIELD_NAME == 'lastname'}
					Contact
					<span class="pull-right" style="padding-right:4px;">
						{$RECORD->get('contact_no')}
					</span>
				{elseif $FIELD_NAME == 'contacttype'}
					Type de contact / origine
				{elseif $FIELD_NAME == 'mailingcity'}
					Adresse
					{* status NPAI *}
					{if $RECORD->get('rsnnpai') !== ''}
						{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
						{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
						{assign var=FIELD_NAME value='rsnnpai'}
						{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['TOOLTIP_FIELDS'][$FIELD_NAME]}
						{if !$FIELD_MODEL}{$FIELD_NAME} n'existe pas !{/if}
						<span class="pull-right" style="padding-right:4px;">
							{if $RECORD->get('rsnnpai') neq '0'}
								<span style="margin-left:19px;">NPAI</span>
							{/if}
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						</span>
						{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
						{assign var=FIELD_NAME value=$FIELD_NAME_TMP}	
					{/if}
				{else}
					{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
				{/if}
				</td>
				<td class="fieldValue narrowWidthType">
					<span class="value">
						{if $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
							{$RECORD->getDisplayValue($FIELD_NAME)}
						{* concaténation de prénom + nom + mailingstreet2 *}
						{elseif $FIELD_NAME eq 'lastname'}
							{$RECORD->getHtmlLabel('isgroup,firstname,lastname,mailingstreet2')}
						{* DO NOT *}
						{elseif $FIELD_NAME eq 'emailoptout'}
							{assign var=DONOT_COUNTER value=0}
							{assign var=DO_COUNTER value=0}
							{* compte le nombre de cochés et de pas cochés *}
							{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
								{if $RECORD->get($DONOT_FIELD)}
									{assign var=DONOT_COUNTER value=$DONOT_COUNTER+1}
								{else}
									{assign var=DO_COUNTER value=$DO_COUNTER+1}
								{/if}
							{/foreach}
							<div class="inline-children">
							{if (($DO_COUNTER > 0) && ($DONOT_COUNTER eq 0)) }
								{* affiche "si, on peut tout faire" *}
								{assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
								{* bug si vide *}
								{if $FIELD_MODEL->get('fieldvalue') === ''}
									{* cherche le 1er item *}
									{foreach item=DONNOT_ALLTHESAME_ITEM key=DONNOT_ALLTHESAME_KEY from=$DONNOT_ALLTHESAME}
										{assign var=TMP value=$FIELD_MODEL->set('fieldvalue', $DONNOT_ALLTHESAME_KEY)}
										{break}
									{/foreach}
								{/if}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME}
							{elseif (($DONOT_COUNTER > 0) && ($DO_COUNTER eq 0)) }
								{* affiche "on ne peut rien faire" *}
								{*assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME*}
								{* affiche "reçu fiscal seul" *}
								{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
								{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
								{assign var=FIELD_NAME value='donototherdocuments'}
								{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['TOOLTIP_FIELDS'][$FIELD_NAME]}
								{if !$FIELD_MODEL}{$FIELD_NAME} n'existe pas !{/if}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
								{assign var=FIELD_NAME value=$FIELD_NAME_TMP}	
							{else}
								{* affiche les labels "ne pas" *}
								{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
								{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
									{if $RECORD->get($DONOT_FIELD)}
										{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['TOOLTIP_FIELDS'][$DONOT_FIELD]}
										{if FIELD_MODEL === null}
											{$DONOT_FIELD} inconnu (need UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `vtiger_field`.`fieldid` = ??{$DONOT_FIELD};)
										{else}
											{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										{/if}
									{/if}
								{/foreach}
								{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
							{/if}
							</div>
						{* concaténation de cp + ville + pays *}
						{elseif $FIELD_NAME eq 'mailingcity'}
							{if $RECORD->get('mailingstreet')}
								{$RECORD->getDisplayValue('mailingstreet')}&nbsp;-&nbsp;
							{/if}
							{$RECORD->getDisplayValue('mailingzip')}&nbsp;
							
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							
							{if $RECORD->get('mailingcountry')}
								&nbsp;-&nbsp;{$RECORD->getDisplayValue('mailingcountry')}
							{/if}
						{* concaténation de nom du compte + reference *}
						{elseif $FIELD_NAME eq 'account_id'}
							{* origin *}
							{if $RECORD->get($FIELD_NAME)}
								<span style="float: left;">
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								</span>
								<span style="float: left; margin-left: 1em;">
								{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
								{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
								{assign var=FIELD_NAME value='reference'}
								{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['TOOLTIP_FIELDS'][$FIELD_NAME]}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
								{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
								</span>
								{if !$RECORD->get($FIELD_NAME)}
									<span style="float: left;">
									référent du compte
									</span>
								{/if}
							{/if}
							
						{* concaténation de email + phone *}
						{elseif $FIELD_NAME eq 'email'}
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							{if $RECORD->get('phone')}
								&nbsp;-&nbsp;{$RECORD->getDisplayValue('phone')}
							{/if}
							
						{* type de contact + origin *}
						{elseif $FIELD_NAME == 'contacttype'}
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							{* origin *}
							{if $RECORD->get('leadsource')}
								{if $RECORD->get($FIELD_NAME)}<br>{/if}
								{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
								{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
								{assign var=FIELD_NAME value='leadsource'}
								{assign var=FIELD_MODEL value=$RECORD_STRUCTURE['TOOLTIP_FIELDS'][$FIELD_NAME]}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
								{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
							{/if}
						{else}
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							
						{/if}
                        {*include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD*}
					</span>
				</td>
			</tr>
		{/if}
		{/foreach}
	</table>
</div>
{/strip}
