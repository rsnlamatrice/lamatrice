{*<!--
/*********************************************************************************
** ED151110
*
 ********************************************************************************/
-->*}
{strip}
    <div id="statisticsSummaryContainer">
    <div id="statisticsSummary">
    {foreach item=FIELD_MODEL key=FIELD_NAME from=$STATISTICS_FIELDS}
        <div class="stat-field">
            {$FIELD_NAME} : {print_r($FIELD_MODEL, true)}
        </div>
    {/foreach}
    </div>
    </div>
{/strip}