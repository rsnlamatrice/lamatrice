{strip}
<div class="contentsDiv span10 marginLeftZero">
	<table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
		<tr>
			<td class="font-x-large" align="left" colspan="2">
				<span class="redColor">{sizeof($NEW_PRODUCTS)} {if sizeof($NEW_PRODUCTS) gt 1}{'LBL_NEW_PRODUCT_FOUND_DURING_IMPORT_PLURAL'|@vtranslate:$MODULE}{else}{'LBL_NEW_PRODUCT_FOUND_DURING_IMPORT'|@vtranslate:$MODULE}{/if}:</span>
			</td>
		</tr>
		{if $ERROR_MESSAGE neq ''}
		<tr>
			<td class="style1" align="left" colspan="2">
				{$ERROR_MESSAGE}
			</td>
		</tr>
		{/if}
		<tr>
			<td valign="top">
				<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents">
					<tr>
						<td class="redColor">{'LBL_PRODUCTCODE'|@vtranslate:$MODULE}</td>
						<td></td>
						<td class="redColor">{'LBL_PRODUCTNAME'|@vtranslate:$MODULE}</td>
					</tr>
					{foreach item=_PRODUCT from=$NEW_PRODUCTS}
						<tr>
							<td>{$_PRODUCT['productcode']}</td>
							<td>:</td>
							<td>{$_PRODUCT['productname']}</td>
						</tr>
					{/foreach}
				</table>
			</td>
		</tr>
		<tr>
			<td align="right" colspan="2">
				<a name="add_new_product" class="btn btn-primary"
					href="index.php?module=Products&view=Edit"><strong>{'LBL_ADD_NEW_PRODUCT'|@vtranslate:$MODULE}</strong></a>
				&nbsp;&nbsp;
				<a name="add_new_service" class="btn btn-primary"
					href="index.php?module=Services&view=Edit"><strong>{'LBL_ADD_NEW_SERVICE'|@vtranslate:$MODULE}</strong></a>
				&nbsp;&nbsp;
				<button name="reload" class="btn btn-success"
					onclick="location.reload();"><strong>{'LBL_TRY_AGAIN'|@vtranslate:$MODULE}</strong></button>
				&nbsp;&nbsp;
				<button name="cancel" class="delete btn btn-danger"
					onclick="location.href='index.php?module=RSNImport&view=Index&for_module={$FOR_MODULE}'"><strong>{'LBL_RETURN'|@vtranslate:$MODULE}</strong></button>
			</td>
		</tr>
	</table>
</div>
{/strip}