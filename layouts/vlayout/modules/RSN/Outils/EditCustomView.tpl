{*<!--
/*********************************************************************************
** 
*
********************************************************************************/
-->*}

{strip}
{if !isset($VIEW_MODULE)}
    {assign var=VIEW_MODULE value="Contacts"}
    {assign var=VIEW_ID value=100}
{/if}
<pre class="relatedContainer listViewPageDiv margin4px"><h3>Edition de la vue personnalisée {$VIEW_MODULE}:{$VIEW_ID}</h3>
    <div id="ed-test"></div>
    <script src="layouts/vlayout/modules/CustomView/resources/CustomView.js"></script>
    <script>$(document.body).ready(function(){
	var url = "module=CustomView&view=EditAjax&source_module={$VIEW_MODULE}&record={$VIEW_ID}";
	Vtiger_CustomView_Js.loadFilterView(url);
    });</script>
</pre>
{/strip}
