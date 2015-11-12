{*<!--
/*********************************************************************************
** ED151110
*
 ********************************************************************************/
-->*}
{strip}
    <div id="statisticsSummaryContainer">
    <div id="statisticsSummary">
    <table>
    {foreach item=FIELD_VALUES key=FIELD_NAME from=$STATISTICS_FIELDS}
        <tr class="stat-field">
            <td>{$FIELD_NAME} :</td>
            {foreach item=FIELD_INFOS key=FIELD_VALUE_KEY from=$FIELD_VALUES }
                <td>{$FIELD_INFOS['value']}</td><td class="unit">{$FIELD_INFOS['unit']}</td>
            {/foreach}
        </tr>
    {/foreach}
    </table>
    </div>
    </div>
{/strip}