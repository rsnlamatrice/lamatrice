{*<!--
/*********************************************************************************
  ** 
  *
 ********************************************************************************/
-->*}
{strip}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}

<div class="modelContainer">
<div class="modal-header contentsBackground">
	<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
    <h3>{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)} : {$PARENT_RECORD->getName()}</h3>
    <h3>{vtranslate('LBL_DELETE_RELATION_WITH')}&nbsp;{vtranslate($RELATED_MODULE->getName())}</h3>
</div>
<form class="form-horizontal deleteRelationView" name="DialogForm" method="post" action="index.php">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="src_record" value="{$PARENT_RECORD->getId()}">
	<input type="hidden" name="related_module" value="{$RELATED_MODULE->getName()}">
		
	<input type="hidden" name="action" value="RelationAjax">
	<input type="hidden" name="mode" value="deleteRelation">
	<input type="hidden" name="reload_page" value="1">
	<div class="dialogFormContent">
		<div class="modal-body">
			{if $RELATED_ENTIRES_COUNT eq 0}
				<i>aucun élément lié</i>
			{elseif $RELATED_ENTIRES_COUNT eq 1}
				<label><input type="radio" name="related_record_list[]" value="*">&nbsp;Supprimer l'unique élément lié</label>
			{else}
				{if $RELATED_ENTIRES_COUNT eq $PAGING->getPageLimit()}
					<label><input type="radio" name="related_record_list[]" value=":first({$RELATED_ENTIRES_COUNT})">&nbsp;Les {$RELATED_ENTIRES_COUNT} premiers éléments liés</label>
					<label><input type="radio" name="related_record_list[]" value="*">&nbsp;Supprimer tous les éléments liés</label>
				{else}
					<label><input type="radio" name="related_record_list[]" value="*">&nbsp;Supprimer les {$RELATED_ENTIRES_COUNT} éléments liés</label>
					
				{/if}
			{/if}
			<label><input type="radio" name="related_record_list[]" value="" checked="checked">&nbsp;Aucune suppression, rafraichir depuis la requête uniquement.</label>

		</div>
	</div>
	<div class="modal-footer dialogFormActions">{* TODO rename class *}
		<a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		<button class="btn btn-success" type="submit"><strong>Ok, go !</strong></button>
	</div>
</form>
</div>
{/strip}