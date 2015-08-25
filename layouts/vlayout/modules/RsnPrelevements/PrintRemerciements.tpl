{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}

{include file="Header.tpl"|vtemplate_path:$MODULE}
<title>Remerciements</title>
<style>
	body {
		font-family: Arial;
	}
	
	td[data-field-type="numeric"], td[data-field-type="currency"]{
	text-align: right;
    }
    .ui-icon {
	display: inline-block;
    }
</style>
<div class="noprint" style="position: absolute; right: 3em; top: 0.5em; text-align: right;">
    <h1>Remerciements
	<button class="btn noprint"
	style="margin-left: 1em;
	    /*background-color: #a26d1f;
	    background-image: -moz-linear-gradient(center top , #a26d1f, #a26d1f);
	    background-repeat: repeat-x;
	    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	    color: #ffffff;*/
	    text-shadow: none;"
	onclick="window.print(); return false;">imprimer</button>
	<button class="btn noprint"
	style="margin-left: 1em;
	    /*background-color: #a26d1f;
	    background-image: -moz-linear-gradient(center top , #a26d1f, #a26d1f);
	    background-repeat: repeat-x;
	    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	    color: #ffffff;*/
	    text-shadow: none;"
	onclick="window.history.back(); return false;">retour</button>
	</h1>
</div>
{assign var=NUM_PAGE value=0}
{foreach item=RECORD from=$RECORDS}
	{if $NUM_PAGE > 0}
		<p style="page-break-before: always">&nbsp;</p>
	{/if}
	{assign var=PRELEVEMENT_RECORD value=$RECORD->getRsnPrelevement()}
	{assign var=RECIPIENT_RECORD value=$RECORD->getContact()}
	{include file="LetterHeader.tpl"|vtemplate_path:$MODULE}
	<div class="letterBody" style="margin-top: 2em; margin-left: 2em; font-size: 12px; line-height: 1.5em;">
		<p style="margin-left: 60%">Lyon, le {date('d/m/Y')}</p>
		<p>Bonjour,</p>
		<p>Depuis le {$PRELEVEMENT_RECORD->getDateDebut()}, vous nous autorisez à vous soustraire {$PRELEVEMENT_RECORD->get('montant')} € par prélèvement {$PRELEVEMENT_RECORD->getPeriodiciteLabel()}.</p>
		<p>Nous vous remercions très sincèrement de nous filer des thunes.</p>
		
		<p>Portez vous bien.</p>
		<p>Annie</p>
	</div>
	{assign var=NUM_PAGE value=$NUM_PAGE + 1}
{/foreach}
<script>
    $(document.body).ready(function(){
		window.print();
    });
</script>


</body></html>
{/strip}
