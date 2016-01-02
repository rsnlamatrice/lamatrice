{*<!--
/*********************************************************************************
  ** ED150910
  *
 ********************************************************************************/
-->*}
{strip}
<div id="popupPageContainer" style='background: white;'>
	<div>
		<br>
		<div style='margin-left:10px'><h3>{vtranslate('LBL_MERGE_ACCOUNTS_TITLE', $MODULE)}</h3></div><br>
		<pre class='alert-info'>
			{if $NO_ACCOUNT}
				{vtranslate('LBL_MERGE_ACCOUNTS_NONE', $MODULE)}
				&nbsp;{vtranslate('LBL_MERGE_ACCOUNTS_CHOOSE_REF', $MODULE)}
			{elseif $ALREADY_COMPTE_COMMUN}
				{vtranslate('LBL_MERGE_ACCOUNTS_ALREADY', $MODULE)}
				&nbsp;&nbsp;{$RECORDMODELS[0]->getDisplayValue('account_id')}
			{else}
				{vtranslate('LBL_MERGE_ACCOUNTS_DESCRIPTION', $MODULE)}
			{/if}</pre>
	</div>

	{if ! $ALREADY_COMPTE_COMMUN}
	<input id="popUpClassName" type="hidden" value="Contacts_MergeAccounts_Js"/>
	<form class="form-horizontal contentsBackground" name="massMerge" method="post" action="index.php">
		<input type="hidden" name=module value="{$MODULE}" />
		<input type="hidden" name="action" value="ProcessDuplicatesAccounts" />
		<input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />
		<input type="hidden" name="isAjax" value="1" />
		<input type="hidden" class="triggerEventName" name="triggerEventName" value="{$smarty.request.triggerEventName}"/>

	<div>
		<table class='table table-bordered table-condensed'>
			<thead class='listViewHeaders'>
			<th>
				{vtranslate('LBL_FIELDS', $MODULE)}
			</th>
			{foreach item=RECORD from=$RECORDMODELS name=recordList}
				<th>
					{$RECORD->getName()}{*vtranslate('LBL_RECORD')*} #{$smarty.foreach.recordList.index+1} &nbsp;
					<input {if $smarty.foreach.recordList.index eq 0}
							checked="checked"
						{*elseif !$RECORD->get('account_id')*}
							{*disabled="disabled"*}
						{/if}
						type=radio value="{$RECORD->getId()}" name=primaryRecord style='bottom:1px;position:relative;'/>
				</th>
			{/foreach}
			</thead>
			{foreach item=FIELD from=$FIELDS}
				{if $FIELD->isEditable()}
				<tr>
					<td>
						{vtranslate($FIELD->get('label'), $MODULE)}
					</td>
					{foreach item=RECORD from=$RECORDMODELS name=recordList}
						<td>
							{if $FIELD->getName() === 'reference' && (! $RECORD->get('reference') || ! $RECORD->get('account_id'))}
								{if $NO_ACCOUNT}
									<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio name="referent_contactid"
									data-id="{$RECORD->getId()}" value="{$RECORD->getId()}" style='bottom:1px;position:relative;'/>
									&nbsp;&nbsp;Référent du compte
								{/if}
							{else}
								{*ED150910*}
								{if $FIELD->getName() === 'account_id' && ! $RECORD->get($FIELD->getName())}
								
								{elseif $FIELD->getName() === 'reference'}
									<input {if $RECORD->get('reference') && $RECORD->get('account_id') && ($smarty.foreach.recordList.index eq 0)}checked="checked"{/if}
									type="radio" name="referent_contactid"
									data-id="{$RECORD->getId()}" value="{$RECORD->getId()}" style='bottom:1px;position:relative;'/>
								{elseif $FIELD->get('uitype') == 33 }
									<input checked type=checkbox name="{$FIELD->getName()}[]"
									data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}" style='bottom:1px;position:relative;'/>
								{else}
									<input {if $smarty.foreach.recordList.index eq 0}checked="checked"{/if} type=radio name="{$FIELD->getName()}"
									data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}" style='bottom:1px;position:relative;'/>
								{/if}
								&nbsp;&nbsp;
								{if $FIELD->getName() === 'reference'}
									{if $RECORD->get($FIELD->getName()) && $RECORD->get('account_id')}Référent du compte{/if}
								{else}
									{$RECORD->getDisplayValue($FIELD->getName())}
								{/if}
							{/if}
						</td>
					{/foreach}
				</tr>
				{/if}
			{/foreach}
		</table>
	</div>
	<div class='row-fluid'>
		<div class="offset4">
			<button type=submit class='btn btn-success'>{vtranslate('LBL_MERGE_ACCOUNTS', $MODULE)}</button>
		</div>
	</div>
	</form>
	{/if}
	<br>
</div>
{/strip}