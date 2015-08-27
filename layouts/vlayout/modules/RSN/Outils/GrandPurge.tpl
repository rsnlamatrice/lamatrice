{*<!--
/*********************************************************************************
** 
*
********************************************************************************/
-->*}

{strip}
<div class="grande-purge relatedContainer margin0px"><h3>ATTENTION : Grande purge des donn&eacute;es</h3>
    
    <form action="index.php?module=RSN&view=GrandePurge" enctype="multipart/form-data" method="POST" name="grandePurge">
        <input type="hidden" name="module" value="{$MODULE}" />
        <input type="hidden" name="view" value="GrandePurge" />
        
        <table>
            {assign var=COLUMN value=0}
            {foreach item=MODULE_MODEL from=$MODULES}
                {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                {if array_key_exists($MODULE_NAME, $PURGEABLES)}
                    {if $COLUMN == 0}<tr>{/if}
                    <td>
                    <label title="{$MODULE_NAME}"><input type="checkbox" name="module-{$MODULE_NAME}" {if $PURGEABLES[$MODULE_NAME]}checked="checked"{/if}/>
                        {vtranslate($MODULE_NAME, $MODULE_NAME)}</label>
                    </td>
                    {assign var=COLUMN value=(($COLUMN+1) % 4)}
                    {if $COLUMN == 0}</tr>{/if}
                {/if}
            {/foreach}
            <tfoot>
                <tr><td colspan="4">
                    <button onclick="$(this).parents('table:first').find('tbody input:checked').removeAttr('checked'); return false;">décocher tout</button>
                    &nbsp;
                    <button onclick="$(this).parents('table:first').find('tbody input:not(:checked)').attr('checked', 'checked'); return false;">tout cocher</button>
                </td></tr>
            </tfoot>
        </table>
            {*foreach item=MODULE_MODEL from=$MODULES}
                {assign var=MODULE_NAME value=$MODULE_MODEL->getName()}
                <br>'{$MODULE_NAME}' => true,
            {/foreach*}
        <div>
            <br/>
            <label><input type="radio" name="doAction" value="Count" checked="checked" />Compter</label>
            <label><input type="radio" name="doAction" value="PurgeModules" />Purger pour de vrai</label>
            <br/>
            <input type="submit" value="Exécution"/>
        </div>
        
    </form>
</div>
<style>
    .grande-purge {
        padding-left: 3em;
    }
    .grande-purge td {
        padding-right: 1em;
    }
    .grande-purge input[type="checkbox"], .grande-purge input[type="radio"] {
        display: inline;
        margin-right: 0.7em;
    }
</style>
{/strip}
