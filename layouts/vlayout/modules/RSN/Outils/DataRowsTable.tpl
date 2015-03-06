{*<!--
/*********************************************************************************
** associative array render
* ED150306
* {include file='DataRowsTable.tpl'|vtemplate_path:$MODULE_NAME}
********************************************************************************/
-->*}

{strip}
{if isset($HTMLDATA)}<pre class="">{$HTMLDATA}</pre>{/if}
{if isset($DATAROWS) && count($DATAROWS)}
<table class="datarows">
    <thead><tr>
        {foreach item=DATACELL key=COLUMN_NAME from=$DATAROWS[0]}
            <th>{$COLUMN_NAME}</th>
        {/foreach}
    </tr></thead>
    
    <tbody>
        {foreach item=DATAROW from=$DATAROWS}
            <tr>
            {foreach item=DATACELL key=COLUMN_NAME from=$DATAROW}
                <td>{$DATACELL}</td>
            {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>
{/if}
<style>
    .relatedContainer {
        padding-left: 1em;
    }
    table.datarows th {
        background-color: #552222;
    }
    table.datarows tr > * {
        border: 1px solid #552222;
        padding: 1px 4px;
    }
</style>
{/strip}
