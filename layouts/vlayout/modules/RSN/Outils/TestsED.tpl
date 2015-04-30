{*<!--
/*********************************************************************************
** 
*
********************************************************************************/
-->*}

{strip}
<pre class="relatedContainer listViewPageDiv margin4px"><h3>Tests ED</h3>
    <div id="ed-test">{print_r(getAllTaxes(), true)}</div>
    <script src="layouts/vlayout/modules/CustomView/resources/CustomView.js"></script>
    <script>$(document.body).ready(function(){
	var url = "module=CustomView&view=EditAjax&source_module=Contacts&record=100";
	//Vtiger_CustomView_Js.loadFilterView(url);
    });</script>
</pre>
{/strip}
