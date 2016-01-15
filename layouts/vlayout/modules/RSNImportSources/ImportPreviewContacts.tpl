{strip}
{* Import_Data_Action::
$IMPORT_RECORD_NONE = 0;
$IMPORT_RECORD_CREATED = 1;
$IMPORT_RECORD_SKIPPED = 2;
$IMPORT_RECORD_UPDATED = 3;
$IMPORT_RECORD_MERGED = 4;
$IMPORT_RECORD_FAILED = 5;*}
{assign var=SKIP_ROW_FIELDS value=array('id', 'status', 'isgroup', 'date')}
<div class="marginLeftZero" style="overflow: scroll;width:95%;">
	{if sizeof($PREVIEW_DATA) gt 0}
		<param id="rsnnpai-picklistvalues" value="{htmlspecialchars (json_encode($RSNNPAI_VALUES))}"/>
		<table style="margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="importPreview searchUIBasic well">
			{foreach key=MODULE_NAME item=MODULE_DATA from=$PREVIEW_DATA}
			{if $MODULE_NAME neq 'Contacts'}
				{include file='ImportPreviewModuleContents.tpl'|@vtemplate_path:'RSNImportSources'}
				{if $PREVIEW_DATA@last}{assign var=ROW_OFFSET value=count($MODULE_DATA)}{/if}
			{else}
				<tr>
					<td class="font-x-large" align="left" colspan="2">
						<span class="big">{'LBL_IMPORT_PREVIEW_FOR_MODULE'|@vtranslate:$MODULE} <b>{$MODULE_NAME|@vtranslate:$MODULE_NAME}</b> :</span>
					</td>
				</tr>
				<tr>
					<td valign="top" colspan="2">
						{if sizeof($MODULE_DATA) gt 0}
							<table cellpadding="10" cellspacing="0" class="dvtSelectedCell thickBorder importContents" data-module="{$MODULE_NAME}">
								{if $ROW_OFFSET === 0}
									<thead><tr class="header-filters">
										<th colspan="3">
											{* filters *}
											<param name="PREVIEW_DATA_URL" value="{$PREVIEW_DATA_URL}"/>
											<div>
												<input type="hidden" name="search_key[]" value="_contactid_status"/>
												<input type="hidden" name="operator[]" value="e"/>
												<select name="search_value[]" title="Contacts à vérifier ou prêts à être importés" style="font-size: 16px;">
													{foreach item=CONTACTID_STATUS key=GROUP_LABEL from=$CONTACTID_STATUS_GROUPS}
														{if $GROUP_LABEL}<optgroup label="{$GROUP_LABEL}">{/if}
														{foreach item=LABEL key=STATUS_ID from=$CONTACTID_STATUS}
															{assign var=ROW_CLASS value='RECORDID_STATUS_COLORS_'|cat:$STATUS_ID}
															<option class="{$ROW_CLASS}" value="{$STATUS_ID}"
															{if $HEADER_FILTERS && $HEADER_FILTERS['_contactid_status'] !== null
															&& $HEADER_FILTERS['_contactid_status'] == $STATUS_ID}selected="selected"{/if}>
																{$LABEL}</option>
														{/foreach}
														{if $GROUP_LABEL}</optgroup>{/if}
													{/foreach}
												</select>
											</div>
											<div>
											<input type="hidden" name="search_key[]" value="_contactid_source"/>
											<input type="hidden" name="operator[]" value="e"/>
											<select name="search_value[]" title="Méthodes de reconnaissance des contacts" style="font-size: 16px;">
												{foreach item=LABEL key=STATUS_ID from=$CONTACTID_SOURCES}
													<option value="{$STATUS_ID}"
														{if $HEADER_FILTERS && $HEADER_FILTERS['_contactid_source'] == $STATUS_ID}selected="selected"{/if}>
														{$LABEL}</option>
												{/foreach}
											</select>
											</div>
											
										</th>
										{*foreach from=$MODULE_DATA[0] key=FIELD_NAME item=VALUE*}
										{foreach key=FIELD_NAME item=CONTACT_FIELD from=$CONTACTS_FIELDS_MAPPING}
											{if $FIELD_NAME[0] === '_' || in_array($FIELD_NAME, $SKIP_ROW_FIELDS)}
												{continue}
											{/if}
											<th>{$FIELD_NAME|@vtranslate:$MODULE_NAME}</th>
										{/foreach}
									</tr></thead>
								{/if}
								<tbody>
								{foreach item=ROW key=ROW_INDEX from=$MODULE_DATA}
									{assign var=MODULE_ROW_OFFSET value=$MODULE_ROW_OFFSET + 1}
									{assign var=ROW_OFFSET value=$ROW_OFFSET + 1}
									{if $ROW['status']}
										{assign var=ROW_CLASS value='ROW_STATUS ROW_STATUS_COLORS_'|cat:$ROW['status']}
									{else}
										{assign var=ROW_CLASS value='RECORDID_STATUS RECORDID_STATUS_COLORS_'|cat:$ROW['_contactid_status']}
									{/if}
									<tr class="preimport-row {$ROW_CLASS}" data-rowid="{$ROW['id']}">
										<th colspan="3">
											{* sélection de la ligne pour validation *}
											<label><input type="checkbox" class="row-selection"
												{if $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE}
													checked="checked"
												{/if}
												/>&nbsp;{$ROW['id']}</label>
											
											{* SNA *}
											{if $ROW['_contactid_status'] !== null
											&& (!$ROW['mailingcountry'] || $ROW['mailingcountry'] === 'France')}
												<a href="#" class="address-sna-check">SNA</a>
												<br><a href="#Pages blanches" class="address-pagesblanches">Pages bl.</a>
											{/if}
										</th>
										{*foreach key=FIELD_NAME item=VALUE from=$ROW*}
										{foreach key=FIELD_NAME item=CONTACT_FIELD from=$CONTACTS_FIELDS_MAPPING}
											{if $FIELD_NAME[0] === '_' ||  in_array($FIELD_NAME, $SKIP_ROW_FIELDS)}
												{continue}
											{/if}
											<td data-fieldname="{$FIELD_NAME}"
												{if $FIELD_NAME eq 'rsnnpai'
													|| $FIELD_NAME eq 'listesn'
													|| $FIELD_NAME eq 'listesd'
													|| $FIELD_NAME eq 'ip'
													|| $FIELD_NAME eq 'date'}
													class="no-swap-value not-trashable not-checkable"
												{elseif $FIELD_NAME eq 'email'
													|| $FIELD_NAME eq 'mailingcountry'}
													class="no-swap-value"
												{elseif $FIELD_NAME eq 'lastname'
													|| $FIELD_NAME eq 'mailingcity'}
													class="no-swap-value-right"
												{elseif $FIELD_NAME eq 'firstname'
													|| $FIELD_NAME eq 'mailingstreet2'}
													class="no-swap-value-left"
												{/if}
											>
											{if $FIELD_NAME eq 'rsnnpai' && ($ROW[$FIELD_NAME] || $ROW[$FIELD_NAME] === '0')}
												{* pas sûr que ça serve, et en plus ça ne serait plus clickable avec la classe not-checkable définie ci-dessus *}
												<span class="value">
													<input type="hidden" value="{$ROW[$FIELD_NAME]}"/>
													<span class="{$RSNNPAI_VALUES[$ROW[$FIELD_NAME]]['icon']}"></span>
													{$RSNNPAI_VALUES[$ROW[$FIELD_NAME]]['label']}
												</span>
											{elseif $FIELD_NAME eq 'rsnnpai' && $ROW['date']}
												{DateTimeField::convertToUserFormat($ROW['date'])}
											{else}
												<span class="value">{$ROW[$FIELD_NAME]}</span>
											{/if}
											</td>
										{/foreach}
									</tr>
									{assign var=CONTACT_ROWS value=$ROW['_contact_rows']}
									{if $CONTACT_ROWS}
										{assign var=CONTACT_ROW_INDEX value=0}
										{foreach item=CONTACT_ROW key=CONTACT_ID from=$CONTACT_ROWS}
											<tr class="contact-row {if $CONTACT_ROW_INDEX === 0}selected-contact{/if}"
												data-rowid="{$ROW['id']}" data-contactid="{$CONTACT_ID}">
												{if $CONTACT_ROW_INDEX === 0}
													<th class="contact-source" rowspan="{count($CONTACT_ROWS) + 1}">
														{$ROW['_contactid_source']}
													</th>
												{/if}
												<th class="enlarge-click-container"><label>
													<input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}"
															{if $CONTACT_ROW_INDEX === 0}checked="checked"{/if}
													/></label>
												</th>
												<th class="enlarge-click-container">
													<a href="{$CONTACTS_MODULE_MODEL->getDetailViewUrl($CONTACT_ID)}" target="_blank">
														<span class="icon-rsn-small-isgroup{$CONTACT_ROW['isgroup']}"></span>
														<br/><label class="contact-no">{$CONTACT_ROW['contact_no']}</label>
													</a>
															
												</th>
												{*foreach key=FIELD_NAME item=VALUE from=$ROW*}
												{foreach key=FIELD_NAME item=CONTACT_FIELD from=$CONTACTS_FIELDS_MAPPING}
													{if $FIELD_NAME[0] === '_'}
														{continue}
													{/if}
													{if $CONTACT_FIELD && array_key_exists($CONTACT_FIELD, $CONTACT_ROW)}
														<td data-fieldname="{$FIELD_NAME}"
														class="
															{if $FIELD_NAME eq 'mailingstreet2'
															|| $FIELD_NAME eq 'rsnnpai'
															|| $FIELD_NAME eq 'mailingpobox'
															|| straddressfieldcmp($ROW[$FIELD_NAME], $CONTACT_ROW[$CONTACT_FIELD]) === 0}
																values-eq
															{else}
																values-neq
															{/if}
															{if $FIELD_NAME eq 'rsnnpai'}
																{' '}not-checkable
															{/if}
														">
															<span class="value">
															{if $FIELD_NAME eq 'rsnnpai'}
																<input type="hidden" value="{$CONTACT_ROW[$CONTACT_FIELD]}"/>
																<span class="{$RSNNPAI_VALUES[$CONTACT_ROW[$CONTACT_FIELD]]['icon']}"/></span>
																{$RSNNPAI_VALUES[$CONTACT_ROW[$CONTACT_FIELD]]['label']}
																{if $CONTACT_ROW['rsnnpaidate']} <span class="pull-right">{DateTimeField::convertToUserFormat($CONTACT_ROW['rsnnpaidate'])}</span>{/if}
																{if $CONTACTS_MODULE_MODEL->getRNVPLabel($CONTACT_ROW)}
																	<div>
																		RNVP : {$CONTACTS_MODULE_MODEL->getRNVPLabel($CONTACT_ROW)}
																	</div>
																{/if}
																{if $CONTACT_ROW['mailingmodifiedtime']}
																	<div class="addressmodifiedtime {if $ROW['date'] && $ROW['date'] < $CONTACT_ROW['mailingmodifiedtime']} values-neq{/if}">
																		Adr : {DateTimeField::convertToUserFormat($CONTACT_ROW['mailingmodifiedtime'])}
																	</div>
																{/if}
																
															{elseif $FIELD_NAME eq 'email' && !$CONTACT_ROW[$CONTACT_FIELD] && $ROW[$FIELD_NAME]}
																(pas d'email)
															{else}
																{$CONTACT_ROW[$CONTACT_FIELD]}
															{/if}
															</span>
														</td>
													{/if}
												{/foreach}
											</tr>
											{assign var=CONTACT_ROW_INDEX value=$CONTACT_ROW_INDEX+1}
										{/foreach}
									{/if}
									{if $ROW['status'] == 0 && $ROW['_contactid_status'] !== null}
										<tr class="contact-row tools-row" data-contactid="">
											{if ! $CONTACT_ROWS}
												<th class="contact-source" colspan="2">&nbsp;</th>
											{else}
												<th class="contact-source" >&nbsp;</th>
											{/if}
											<td colspan="2" class="select-contact">
												<input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}" disabled="disabled"/>
												<a href="#"><i>&nbsp;sélectionner...</i></a></td>
											<td colspan="1" class="create-contact">
												<label><input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}" data-status="{RSNImportSources_Import_View::$RECORDID_STATUS_CREATE}"
													{if ! $CONTACT_ROWS}checked="checked"{/if}/>
													<i>&nbsp;créer</i></label></td>
											<td colspan="1" class="create-contact">
												<label><input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}" data-status="{RSNImportSources_Import_View::$RECORDID_STATUS_LATER}"
													{if $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_LATER}checked="checked"{/if}/>
													<i>&nbsp;plus tard</i></label></td>
											<td colspan="1" class="skip-row">
												<label><input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}" data-status="{RSNImportSources_Import_View::$RECORDID_STATUS_SKIP}"
													{if $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_SKIP}checked="checked"{/if}/>
													<i>&nbsp;annuler</i></label></td>
											{if $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_SKIP
											|| $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_UPDATE
											|| $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_CREATE
											|| $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_SELECT
											|| $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_LATER}
											<td colspan="1" class="restore-check-row">
												<label><input type="radio" class="contact-mode-selection" name="contact_related_to_{$ROW['id']}" data-status="{RSNImportSources_Import_View::$RECORDID_STATUS_CHECK}"
													title="remettre cette ligne en attente de validation"/>
													<i>&nbsp;vérifier</i></label></td>
											{/if}
										</tr>
									{/if}
								{/foreach}
								</tbody>
							</table>
						{else}
							<span class="big font-x-large">{'LBL_NO_DATA'|@vtranslate:$MODULE}:</span>
						{/if}
					</td>
				</tr>
				
				{if $IMPORTABLE_ROWS_COUNT}
					<tr class="importContents-toolbox" data-module="{$MODULE_NAME}">
						<td class="style1" align="left" colspan="2"
								{if ! $PREVIEW_DATA@last}style="padding-bottom: 4em;"{/if}>
							{if $VALIDABLE_CONTACTS_COUNT}
								<span class="span8">
									<label><input class="all-rows-selection" type="checkbox"
										{if $ROW['_contactid_status'] == RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE}
											checked="checked"
										{/if}>sélectionner toutes les lignes</label>
								{if $VALIDATE_PREIMPORT_URL}
									<param name="VALIDATE_PREIMPORT_URL" value="{$VALIDATE_PREIMPORT_URL}"/>
									<button type="submit" name="validate-preimport-rows" class="btn btn-success">
										<strong>{'LBL_VALIDATE_SELECTED_CONTACT_ROWS'|@vtranslate:$MODULE}</strong>
									</button>
								{/if}
								</span>
							{/if}
							<span class="span6" style="padding-left: 2em;">
								Nombre de lignes à importer : {$IMPORTABLE_ROWS_COUNT}{if true || $SOURCE_ROWS_COUNT neq $IMPORTABLE_ROWS_COUNT}&nbsp;/&nbsp;{$SOURCE_ROWS_COUNT}{/if}
								&nbsp;-&nbsp;Affichée{if $MODULE_ROW_OFFSET > 1}s{/if} : {$MODULE_ROW_OFFSET} 
								
								{if $MODULE_ROW_OFFSET < $SOURCE_ROWS_COUNT}
									<a class="getMorePreviewData" href="{$MORE_DATA_URL}" style="margin-left: 2em">voir plus de lignes</a>
								{/if}
							</span>
						</td>
					</tr>
				{/if}
				{if $PREVIEW_DATA@last}{assign var=ROW_OFFSET value=$MODULE_ROW_OFFSET}{/if}
			{/if}
			{/foreach}
		</table>
		<div style="padding-left: 4em;">
			<form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="selectImportSource">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" id="for_module" name="for_module" value="{$FOR_MODULE}" />
				<input type="hidden" name="view" value="Index" />
				<input type="hidden" name="mode" value="import" /><!--TMP Import Module ???? -->
				<input type="hidden" name="ImportSource" value="{$IMPORT_SOURCE}" />
				{include file='PreviewButtons.tpl'|@vtemplate_path:'RSNImportSources'}<!-- TMP -->
			</form>
		</div>
	{else}
		<table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<span class="big">{'LBL_NO_DATA'|@vtranslate:$MODULE}:</span>
				</td>
			</tr>
		</table>
	{/if}
</div>
{/strip}