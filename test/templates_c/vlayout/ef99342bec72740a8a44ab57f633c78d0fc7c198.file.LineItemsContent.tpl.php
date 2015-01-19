<?php /* Smarty version Smarty-3.1.7, created on 2014-12-10 16:13:21
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Inventory/LineItemsContent.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1813032685545cb3a2222e87-28203397%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ef99342bec72740a8a44ab57f633c78d0fc7c198' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Inventory/LineItemsContent.tpl',
      1 => 1413619450,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1813032685545cb3a2222e87-28203397',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545cb3a27f98f',
  'variables' => 
  array (
    'row_no' => 0,
    'entityIdentifier' => 0,
    'data' => 0,
    'RELATED_PRODUCTS' => 0,
    'MODULE' => 0,
    'tax_row_no' => 0,
    'productName' => 0,
    'hdnProductId' => 0,
    'entityType' => 0,
    'productDeleted' => 0,
    'subproduct_ids' => 0,
    'subprod_names' => 0,
    'comment' => 0,
    'qty' => 0,
    'qtyInStock' => 0,
    'listPrice' => 0,
    'PRICEBOOK_MODULE_MODEL' => 0,
    'discount_type' => 0,
    'DISCOUNT_TYPE' => 0,
    'productTotal' => 0,
    'checked_discount_zero' => 0,
    'checked_discount_percent' => 0,
    'discount_percent' => 0,
    'checked_discount_amount' => 0,
    'discount_amount' => 0,
    'IS_GROUP_TAX_TYPE' => 0,
    'totalAfterDiscount' => 0,
    'tax_data' => 0,
    'taxname' => 0,
    'popup_tax_rowname' => 0,
    'discountTotal' => 0,
    'taxTotal' => 0,
    'netPrice' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545cb3a27f98f')) {function content_545cb3a27f98f($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["deleted"] = new Smarty_variable(("deleted").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["hdnProductId"] = new Smarty_variable(("hdnProductId").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["productName"] = new Smarty_variable(("productName").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["comment"] = new Smarty_variable(("comment").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["productDescription"] = new Smarty_variable(("productDescription").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["qtyInStock"] = new Smarty_variable(("qtyInStock").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["qty"] = new Smarty_variable(("qty").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["listPrice"] = new Smarty_variable(("listPrice").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["productTotal"] = new Smarty_variable(("productTotal").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["subproduct_ids"] = new Smarty_variable(("subproduct_ids").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["subprod_names"] = new Smarty_variable(("subprod_names").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["entityIdentifier"] = new Smarty_variable(("entityType").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["entityType"] = new Smarty_variable($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['entityIdentifier']->value], null, 0);?><?php $_smarty_tpl->tpl_vars["discount_type"] = new Smarty_variable(("discount_type").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["discount_percent"] = new Smarty_variable(("discount_percent").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["checked_discount_percent"] = new Smarty_variable(("checked_discount_percent").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["style_discount_percent"] = new Smarty_variable(("style_discount_percent").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["discount_amount"] = new Smarty_variable(("discount_amount").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["checked_discount_amount"] = new Smarty_variable(("checked_discount_amount").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["style_discount_amount"] = new Smarty_variable(("style_discount_amount").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["checked_discount_zero"] = new Smarty_variable(("checked_discount_zero").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["discountTotal"] = new Smarty_variable(("discountTotal").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["totalAfterDiscount"] = new Smarty_variable(("totalAfterDiscount").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["taxTotal"] = new Smarty_variable(("taxTotal").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["netPrice"] = new Smarty_variable(("netPrice").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["FINAL"] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_PRODUCTS']->value[1]['final_details'], null, 0);?><?php $_smarty_tpl->tpl_vars["productDeleted"] = new Smarty_variable(("productDeleted").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><td><i class="icon-trash deleteRow cursorPointer" title="<?php echo vtranslate('LBL_DELETE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"></i>&nbsp;<a><img src="<?php echo vimage_path('drag.png');?>
" border="0" title="<?php echo vtranslate('LBL_DRAG',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"/></a><input type="hidden" class="rowNumber" value="<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" /></td><td><!-- Product Re-Ordering Feature Code Addition Starts --><input type="hidden" name="hidtax_row_no<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" id="hidtax_row_no<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['tax_row_no']->value;?>
"/><!-- Product Re-Ordering Feature Code Addition ends --><div><input type="text" id="<?php echo $_smarty_tpl->tpl_vars['productName']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['productName']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productName']->value];?>
" class="productName <?php if ($_smarty_tpl->tpl_vars['row_no']->value!=0){?> autoComplete <?php }?>" placeholder="<?php echo vtranslate('LBL_TYPE_SEARCH',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-validation-engine="validate[required]" <?php if (!empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productName']->value])){?> disabled="disabled" <?php }?>/><input type="hidden" id="<?php echo $_smarty_tpl->tpl_vars['hdnProductId']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['hdnProductId']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['hdnProductId']->value];?>
" class="selectedModuleId"/><input type="hidden" id="lineItemType<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" name="lineItemType<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['entityType']->value;?>
" class="lineItemType"/><?php if ($_smarty_tpl->tpl_vars['row_no']->value==0){?><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ServicesPopup" title="<?php echo vtranslate('Services',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-module-name="Services" data-field-name="serviceid" src="<?php echo vimage_path('Services.png');?>
"/><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" title="<?php echo vtranslate('Products',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-module-name="Products" data-field-name="productid" src="<?php echo vimage_path('Products.png');?>
"/>&nbsp;<i class="icon-remove-sign clearLineItem cursorPointer" title="<?php echo vtranslate('LBL_CLEAR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" style="vertical-align:middle"></i><?php }else{ ?><?php if (($_smarty_tpl->tpl_vars['entityType']->value=='Services')&&(!$_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productDeleted']->value])){?><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ServicesPopup" data-module-name="Services" title="<?php echo vtranslate('Services',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-field-name="serviceid" src="<?php echo vimage_path('Services.png');?>
"/>&nbsp;<i class="icon-remove-sign clearLineItem cursorPointer" title="<?php echo vtranslate('LBL_CLEAR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" style="vertical-align:middle"></i><?php }elseif((!$_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productDeleted']->value])){?><img class="lineItemPopup cursorPointer alignMiddle" data-popup="ProductsPopup" data-module-name="Products" title="<?php echo vtranslate('Products',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-field-name="productid" src="<?php echo vimage_path('Products.png');?>
"/>&nbsp;<i class="icon-remove-sign clearLineItem cursorPointer" title="<?php echo vtranslate('LBL_CLEAR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" style="vertical-align:middle"></i><?php }?><?php }?></div><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['subproduct_ids']->value];?>
" id="<?php echo $_smarty_tpl->tpl_vars['subproduct_ids']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['subproduct_ids']->value;?>
" class="subProductIds" /><div id="<?php echo $_smarty_tpl->tpl_vars['subprod_names']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['subprod_names']->value;?>
" class="subInformation"><span class="subProductsContainer"><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['subprod_names']->value];?>
</span></div><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productDeleted']->value]){?><div class="row-fluid deletedItem redColor"><?php if (empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productName']->value])){?><?php echo vtranslate('LBL_THIS_LINE_ITEM_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_THIS_LINE_ITEM',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }else{ ?><?php echo vtranslate('LBL_THIS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo $_smarty_tpl->tpl_vars['entityType']->value;?>
 <?php echo vtranslate('LBL_IS_DELETED_FROM_THE_SYSTEM_PLEASE_REMOVE_OR_REPLACE_THIS_ITEM',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }?></div><?php }else{ ?><div><br><textarea id="<?php echo $_smarty_tpl->tpl_vars['comment']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['comment']->value;?>
" class="lineItemCommentBox"><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['comment']->value];?>
</textarea><?php }?></td><td><input id="<?php echo $_smarty_tpl->tpl_vars['qty']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['qty']->value;?>
" type="text" class="qty smallInputBox" data-validation-engine="validate[required,funcCall[Vtiger_GreaterThanZero_Validator_Js.invokeValidation]]" value="<?php if (!empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['qty']->value])){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['qty']->value];?>
<?php }else{ ?>1<?php }?>"/><?php if ($_smarty_tpl->tpl_vars['MODULE']->value!='PurchaseOrder'){?><br><span class="stockAlert redColor <?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['qty']->value]<=$_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['qtyInStock']->value]){?>hide<?php }?>" ><?php echo vtranslate('LBL_STOCK_NOT_ENOUGH',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<br><?php echo vtranslate('LBL_MAX_QTY_SELECT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
&nbsp;<span class="maxQuantity"><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['qtyInStock']->value];?>
</span></span><?php }?></td><td><div><input id="<?php echo $_smarty_tpl->tpl_vars['listPrice']->value;?>
" name="<?php echo $_smarty_tpl->tpl_vars['listPrice']->value;?>
" value="<?php if (!empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['listPrice']->value])){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['listPrice']->value];?>
<?php }else{ ?>0<?php }?>" type="text" data-validation-engine="validate[required,funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="listPrice smallInputBox" />&nbsp;<?php $_smarty_tpl->tpl_vars['PRICEBOOK_MODULE_MODEL'] = new Smarty_variable(Vtiger_Module_Model::getInstance('PriceBooks'), null, 0);?><?php if ($_smarty_tpl->tpl_vars['PRICEBOOK_MODULE_MODEL']->value->isPermitted('DetailView')){?><img src="<?php echo vimage_path('PriceBooks.png');?>
" class="cursorPointer alignMiddle priceBookPopup" data-popup="Popup" data-module-name="PriceBooks" title="<?php echo vtranslate('PriceBooks',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"/><?php }?></div><div><span>(-)&nbsp; <b><a href="javascript:void(0)" class="individualDiscount"><?php echo vtranslate('LBL_DISCOUNT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a> : </b></span></div><div class="discountUI hide" id="discount_div<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
"><?php $_smarty_tpl->tpl_vars["DISCOUNT_TYPE"] = new Smarty_variable("zero", null, 0);?><?php if (!empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discount_type']->value])){?><?php $_smarty_tpl->tpl_vars["DISCOUNT_TYPE"] = new Smarty_variable($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discount_type']->value], null, 0);?><?php }?><input type="hidden" id="discount_type<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" name="discount_type<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['DISCOUNT_TYPE']->value;?>
" class="discount_type" /><table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable"><tr><!-- TODO : CLEAN : should not append product total it should added in the js because product total can change at any point of time --><th id="discount_div_title<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" nowrap><b><?php echo vtranslate('LBL_SET_DISCOUNT_FOR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 : <?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productTotal']->value];?>
</b></th><th><button type="button" class="close closeDiv">x</button></th></tr><!-- TODO : discount price and amount are hide by default we need to check id they are already selected if so we should not hide them  --><tr><td><input type="radio" name="discount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['checked_discount_zero']->value];?>
 <?php if (empty($_smarty_tpl->tpl_vars['data']->value)){?>checked<?php }?> class="discounts" data-discount-type="zero" />&nbsp;<?php echo vtranslate('LBL_ZERO_DISCOUNT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td><!-- Make the discount value as zero --><input type="hidden" class="discountVal" value="0" /></td></tr><tr><td><input type="radio" name="discount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['checked_discount_percent']->value];?>
 class="discounts" data-discount-type="percentage" />&nbsp; %<?php echo vtranslate('LBL_OF_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td><span class="pull-right">&nbsp;%</span><input type="text" id="discount_percentage<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" name="discount_percentage<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discount_percent']->value];?>
" class="discount_percentage smallInputBox pull-right discountVal <?php if (empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['checked_discount_percent']->value])){?>hide<?php }?>" /></td></tr><tr><td class="LineItemDirectPriceReduction"><input type="radio" name="discount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['checked_discount_amount']->value];?>
 class="discounts" data-discount-type="amount" />&nbsp;<?php echo vtranslate('LBL_DIRECT_PRICE_REDUCTION',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td><input type="text" id="discount_amount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" name="discount_amount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discount_amount']->value];?>
" class="smallInputBox pull-right discount_amount discountVal <?php if (empty($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['checked_discount_amount']->value])){?>hide<?php }?>"/></td></tr></table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding"><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn btn-success discountSave" type="button" name="lineItemActionSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><div><b><?php echo vtranslate('LBL_TOTAL_AFTER_DISCOUNT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 :</b></div><div class="individualTaxContainer <?php if ($_smarty_tpl->tpl_vars['IS_GROUP_TAX_TYPE']->value){?>hide<?php }?>">(+)&nbsp;<b><a href="javascript:void(0)" class="individualTax"><?php echo vtranslate('LBL_TAX',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 </a> : </b></div><span class="taxDivContainer"><div class="taxUI hide" id="tax_div<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
"><!-- we will form the table with all taxes --><table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable" id="tax_table<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
"><tr><th id="tax_div_title<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" nowrap align="left" ><b><?php echo vtranslate('LBL_SET_TAX_FOR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 : <?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['totalAfterDiscount']->value];?>
</b></th><th colspan="2"><button type="button" class="close closeDiv">x</button></th></tr><?php  $_smarty_tpl->tpl_vars['tax_data'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['tax_data']->_loop = false;
 $_smarty_tpl->tpl_vars['tax_row_no'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['data']->value['taxes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['tax_data']->key => $_smarty_tpl->tpl_vars['tax_data']->value){
$_smarty_tpl->tpl_vars['tax_data']->_loop = true;
 $_smarty_tpl->tpl_vars['tax_row_no']->value = $_smarty_tpl->tpl_vars['tax_data']->key;
?><?php $_smarty_tpl->tpl_vars["taxname"] = new Smarty_variable((($_smarty_tpl->tpl_vars['tax_data']->value['taxname']).("_percentage")).($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["tax_id_name"] = new Smarty_variable(("hidden_tax").($_smarty_tpl->tpl_vars['tax_row_no']->value)+((1).("_percentage")).($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["taxlabel"] = new Smarty_variable((($_smarty_tpl->tpl_vars['tax_data']->value['taxlabel']).("_percentage")).($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["popup_tax_rowname"] = new Smarty_variable(("popup_tax_row").($_smarty_tpl->tpl_vars['row_no']->value), null, 0);?><tr><td><input type="text" name="<?php echo $_smarty_tpl->tpl_vars['taxname']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['taxname']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['tax_data']->value['percentage'];?>
" class="smallInputBox taxPercentage" />&nbsp;%</td><td><div class="textOverflowEllipsis"><?php echo $_smarty_tpl->tpl_vars['tax_data']->value['taxlabel'];?>
</div></td><td><input type="text" name="<?php echo $_smarty_tpl->tpl_vars['popup_tax_rowname']->value;?>
" class="cursorPointer smallInputBox taxTotal" value="<?php echo $_smarty_tpl->tpl_vars['tax_data']->value['amount'];?>
" readonly /></td></tr><?php } ?></table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding"><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn btn-success taxSave" type="button" name="lineItemActionSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div></span></td><td><div id="productTotal<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" align="right" class="productTotal"><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productTotal']->value]){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productTotal']->value];?>
<?php }else{ ?>0.00<?php }?></div><div id="discountTotal<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" align="right" class="discountTotal"><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discountTotal']->value]){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['discountTotal']->value];?>
<?php }else{ ?>0.00<?php }?></div><div id="totalAfterDiscount<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" align="right" class="totalAfterDiscount"><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['totalAfterDiscount']->value]){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['totalAfterDiscount']->value];?>
<?php }else{ ?>0.00<?php }?></div><div id="taxTotal<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" align="right" class="productTaxTotal <?php if ($_smarty_tpl->tpl_vars['IS_GROUP_TAX_TYPE']->value){?>hide<?php }?>"><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['taxTotal']->value]){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['taxTotal']->value];?>
<?php }else{ ?>0.00<?php }?></div></td><td><span id="netPrice<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" class="pull-right netPrice"><?php if ($_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['netPrice']->value]){?><?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['netPrice']->value];?>
<?php }else{ ?>0.00<?php }?></span></td><?php }} ?>