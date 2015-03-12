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
	</div>
	</div>
        {* ED141210 : duplication du navigateur. Reprise des id avec ajout du suffixe '-bottom'
        modification de /layouts/vlayout/modules/Vtiger/resources/List.js
	   ED150311 : this template is called when page is loading but not when selecting an other module view.
	    TODO : remove this .tpl part and replace with a jquery clone of top navigator on each load
	*}
	{if $PAGE_NUMBER > 1 || ($LISTVIEW_ENTIRES_COUNT >= $PAGING_MODEL->getPageLimit())}
	    <div class="listViewActionsDiv row-fluid noprint">
	    
		<div class="listViewTopMenuDiv noprint">
		    <span class="span3 btn-toolbar pull-right listViewActions">
		    {if (method_exists($MODULE_MODEL,'isPagingSupported') && ($MODULE_MODEL->isPagingSupported()  eq true)) || !method_exists($MODULE_MODEL,'isPagingSupported')}
			    <span class="pageNumbers alignTop" data-placement="bottom" ></span>
			    <span class="btn-group alignTop">
				    <span class="btn-group">
					    <button class="btn" id="listViewPreviousPageButton-bottom" {if (!$PAGING_MODEL->isPrevPageExists())} disabled {/if} type="button">
						<span class="icon-chevron-left"></span></button>
					    <button class="btn dropdown-toggle" type="button" disabled>
						    <i class="vtGlyph vticon-pageJump"></i>
					    </button>
					    <button class="btn" id="listViewNextPageButton-bottom" {if (!$PAGING_MODEL->isNextPageExists()) or ($PAGE_COUNT eq 1)} disabled {/if} type="button">
						<span class="icon-chevron-right"></span></button>
				    </span>
			    </span>
		    {/if}
		    </span>
		</div>
	    </div>
        {/if}
</div>
{/strip}