<?php /* Smarty version Smarty-3.1.7, created on 2014-12-10 16:13:21
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Inventory/LineItemsEdit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1674008317545cb3a1b9a8f3-39241084%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'b4e6b71896370f88a765231053ae2914df9ff59b' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Inventory/LineItemsEdit.tpl',
      1 => 1413985640,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1674008317545cb3a1b9a8f3-39241084',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545cb3a220659',
  'variables' => 
  array (
    'RELATED_PRODUCTS' => 0,
    'FINAL' => 0,
    'MODULE' => 0,
    'APP' => 0,
    'CURRENCINFO' => 0,
    'SELECTED_CURRENCY' => 0,
    'USER_MODEL' => 0,
    'CURRENCIES' => 0,
    'currency_details' => 0,
    'USER_CURRENCY_ID' => 0,
    'RECORD_STRUCTURE_MODEL' => 0,
    'RECORD_CURRENCY_RATE' => 0,
    'IS_INDIVIDUAL_TAX_TYPE' => 0,
    'IS_GROUP_TAX_TYPE' => 0,
    'row_no' => 0,
    'data' => 0,
    'PRODUCT_ACTIVE' => 0,
    'SERVICE_ACTIVE' => 0,
    'DISCOUNT_TYPE_FINAL' => 0,
    'productTotal' => 0,
    'MODULE_NAME' => 0,
    'PRE_TAX_TOTAL' => 0,
    'TAXES' => 0,
    'tax_detail' => 0,
    'SHIPPING_TAXES' => 0,
    'RECORD' => 0,
    'IS_DUPLICATE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545cb3a220659')) {function content_545cb3a220659($_smarty_tpl) {?>

<!--All final details are stored in the first element in the array with the index name as final_detailsso we will get that array, parse that array and fill the details--><?php $_smarty_tpl->tpl_vars["FINAL"] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_PRODUCTS']->value[1]['final_details'], null, 0);?><?php $_smarty_tpl->tpl_vars["IS_INDIVIDUAL_TAX_TYPE"] = new Smarty_variable(true, null, 0);?><?php $_smarty_tpl->tpl_vars["IS_GROUP_TAX_TYPE"] = new Smarty_variable(false, null, 0);?><?php if ($_smarty_tpl->tpl_vars['FINAL']->value['taxtype']=='group'){?><?php $_smarty_tpl->tpl_vars["IS_GROUP_TAX_TYPE"] = new Smarty_variable(true, null, 0);?><?php $_smarty_tpl->tpl_vars["IS_INDIVIDUAL_TAX_TYPE"] = new Smarty_variable(false, null, 0);?><?php }?><table class="table table-bordered blockContainer lineItemTable" id="lineItemTab"><tr><th colspan="2"><span class="inventoryLineItemHeader"><?php echo vtranslate('LBL_ITEM_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></th><td colspan="1" class="chznDropDown"><b><?php echo $_smarty_tpl->tpl_vars['APP']->value['LBL_CURRENCY'];?>
</b>&nbsp;&nbsp;<?php $_smarty_tpl->tpl_vars['SELECTED_CURRENCY'] = new Smarty_variable($_smarty_tpl->tpl_vars['CURRENCINFO']->value, null, 0);?><?php if ($_smarty_tpl->tpl_vars['SELECTED_CURRENCY']->value==''){?><?php $_smarty_tpl->tpl_vars['USER_CURRENCY_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('currency_id'), null, 0);?><?php  $_smarty_tpl->tpl_vars['currency_details'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['currency_details']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['CURRENCIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['currency_details']->key => $_smarty_tpl->tpl_vars['currency_details']->value){
$_smarty_tpl->tpl_vars['currency_details']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['currency_details']->value['curid']==$_smarty_tpl->tpl_vars['USER_CURRENCY_ID']->value){?><?php $_smarty_tpl->tpl_vars['SELECTED_CURRENCY'] = new Smarty_variable($_smarty_tpl->tpl_vars['currency_details']->value, null, 0);?><?php }?><?php } ?><?php }?><select class="chzn-select" id="currency_id" name="currency_id"><?php  $_smarty_tpl->tpl_vars['currency_details'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['currency_details']->_loop = false;
 $_smarty_tpl->tpl_vars['count'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['CURRENCIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['currency_details']->key => $_smarty_tpl->tpl_vars['currency_details']->value){
$_smarty_tpl->tpl_vars['currency_details']->_loop = true;
 $_smarty_tpl->tpl_vars['count']->value = $_smarty_tpl->tpl_vars['currency_details']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['currency_details']->value['curid'];?>
" class="textShadowNone" data-conversion-rate="<?php echo $_smarty_tpl->tpl_vars['currency_details']->value['conversionrate'];?>
" <?php if ($_smarty_tpl->tpl_vars['SELECTED_CURRENCY']->value['currency_id']==$_smarty_tpl->tpl_vars['currency_details']->value['curid']){?> selected <?php }?>><?php echo getTranslatedCurrencyString($_smarty_tpl->tpl_vars['currency_details']->value['currencylabel']);?>
 (<?php echo $_smarty_tpl->tpl_vars['currency_details']->value['currencysymbol'];?>
)</option><?php } ?></select><?php $_smarty_tpl->tpl_vars["RECORD_CURRENCY_RATE"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_STRUCTURE_MODEL']->value->getRecord()->get('conversion_rate'), null, 0);?><?php if ($_smarty_tpl->tpl_vars['RECORD_CURRENCY_RATE']->value==''){?><?php $_smarty_tpl->tpl_vars["RECORD_CURRENCY_RATE"] = new Smarty_variable($_smarty_tpl->tpl_vars['SELECTED_CURRENCY']->value['conversionrate'], null, 0);?><?php }?><input type="hidden" name="conversion_rate" id="conversion_rate" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_CURRENCY_RATE']->value;?>
" /><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['SELECTED_CURRENCY']->value['currency_id'];?>
" id="prev_selected_currency_id" /><!-- TODO : To get default currency in even better way than depending on first element --><input type="hidden" id="default_currency_id" value="<?php echo $_smarty_tpl->tpl_vars['CURRENCIES']->value[0]['curid'];?>
" /></td><td colspan="3" class="chznDropDown"><div class="pull-right"><div class="inventoryLineItemHeader"><span class="alignTop"><?php echo vtranslate('LBL_TAX_MODE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></div><select class="chzn-select lineItemTax" id="taxtype" name="taxtype" ><OPTION value="individual" <?php if ($_smarty_tpl->tpl_vars['IS_INDIVIDUAL_TAX_TYPE']->value){?>selected<?php }?>><?php echo vtranslate('LBL_INDIVIDUAL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</OPTION><OPTION value="group" <?php if ($_smarty_tpl->tpl_vars['IS_GROUP_TAX_TYPE']->value){?>selected<?php }?>><?php echo vtranslate('LBL_GROUP',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</OPTION></select></div></td></tr><tr><td><b><?php echo vtranslate('LBL_TOOLS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><span class="redColor">*</span><b><?php echo vtranslate('LBL_ITEM_NAME',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_QTY',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_LIST_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b class="pull-right"><?php echo vtranslate('LBL_TOTAL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b class="pull-right"><?php echo vtranslate('LBL_NET_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td></tr><tr id="row0" class="hide lineItemCloneCopy"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("LineItemsContent.tpl",'Inventory'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('row_no'=>0,'data'=>array()), 0);?>
</tr><?php  $_smarty_tpl->tpl_vars['data'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['data']->_loop = false;
 $_smarty_tpl->tpl_vars['row_no'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['RELATED_PRODUCTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['data']->key => $_smarty_tpl->tpl_vars['data']->value){
$_smarty_tpl->tpl_vars['data']->_loop = true;
 $_smarty_tpl->tpl_vars['row_no']->value = $_smarty_tpl->tpl_vars['data']->key;
?><tr id="row<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" class="lineItemRow" <?php if ($_smarty_tpl->tpl_vars['data']->value["entityType".($_smarty_tpl->tpl_vars['row_no']->value)]=='Products'){?>data-quantity-in-stock=<?php echo $_smarty_tpl->tpl_vars['data']->value["qtyInStock".($_smarty_tpl->tpl_vars['row_no']->value)];?>
<?php }?>><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("LineItemsContent.tpl",'Inventory'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('row_no'=>$_smarty_tpl->tpl_vars['row_no']->value,'data'=>$_smarty_tpl->tpl_vars['data']->value), 0);?>
</tr><?php } ?><?php if (count($_smarty_tpl->tpl_vars['RELATED_PRODUCTS']->value)==0){?><tr id="row1" class="lineItemRow"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("LineItemsContent.tpl",'Inventory'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('row_no'=>1,'data'=>array()), 0);?>
</tr><?php }?></table><div class="row-fluid verticalBottomSpacing"><div><?php if ($_smarty_tpl->tpl_vars['PRODUCT_ACTIVE']->value=='true'&&$_smarty_tpl->tpl_vars['SERVICE_ACTIVE']->value=='true'){?><div class="btn-toolbar"><span class="btn-group"><button type="button" class="btn addButton" id="addProduct"><i class="icon-plus icon-white"></i><strong><?php echo vtranslate('LBL_ADD_PRODUCT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></span><span class="btn-group"><button type="button" class="btn addButton" id="addService"><i class="icon-plus icon-white"></i><strong><?php echo vtranslate('LBL_ADD_SERVICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></span></div><?php }elseif($_smarty_tpl->tpl_vars['PRODUCT_ACTIVE']->value=='true'){?><div class="btn-group"><button type="button" class="btn addButton" id="addProduct"><i class="icon-plus icon-white"></i><strong> <?php echo vtranslate('LBL_ADD_PRODUCT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div><?php }elseif($_smarty_tpl->tpl_vars['SERVICE_ACTIVE']->value=='true'){?><div class="btn-group"><button type="button" class="btn addButton" id="addService"><i class="icon-plus icon-white"></i><strong> <?php echo vtranslate('LBL_ADD_SERVICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div><?php }?></div></div><table class="table table-bordered blockContainer lineItemTable" id="lineItemResult"><tr><td  width="83%"><div class="pull-right"><strong><?php echo vtranslate('LBL_ITEMS_TOTAL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></div></td><td><div id="netTotal" class="pull-right netTotal"><?php if (!empty($_smarty_tpl->tpl_vars['FINAL']->value['hdnSubTotal'])){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['hdnSubTotal'];?>
<?php }else{ ?>0.00<?php }?></div></td></tr><tr><td width="83%"><span class="pull-right">(-)&nbsp;<b><a href="javascript:void(0)"  id="finalDiscount"><?php echo vtranslate('LBL_DISCOUNT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></b></span></td><td><span id="discountTotal_final" class="pull-right discountTotal_final"><?php if ($_smarty_tpl->tpl_vars['FINAL']->value['discountTotal_final']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['discountTotal_final'];?>
<?php }else{ ?>0.00<?php }?></span><!-- Popup Discount Div --><div id="finalDiscountUI" class="finalDiscountUI hide"><?php $_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL'] = new Smarty_variable("zero", null, 0);?><?php if (!empty($_smarty_tpl->tpl_vars['FINAL']->value['discount_type_final'])){?><?php $_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL'] = new Smarty_variable($_smarty_tpl->tpl_vars['FINAL']->value['discount_type_final'], null, 0);?><?php }?><input type="hidden" id="discount_type_final" name="discount_type_final" value="<?php echo $_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value;?>
" /><table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable"><thead><tr><th id="discount_div_title_final"><b><?php echo vtranslate('LBL_SET_DISCOUNT_FOR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
:<?php echo $_smarty_tpl->tpl_vars['data']->value[$_smarty_tpl->tpl_vars['productTotal']->value];?>
</b></th><th><button type="button" class="close closeDiv">x</button></th></tr></thead><tbody><tr><td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="zero" <?php if ($_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value=='zero'){?>checked<?php }?> />&nbsp; <?php echo vtranslate('LBL_ZERO_DISCOUNT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td class="lineOnTop"><!-- Make the discount value as zero --><input type="hidden" class="discountVal" value="0" /></td></tr><tr><td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="percentage" <?php if ($_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value=='percentage'){?>checked<?php }?> />&nbsp; % <?php echo vtranslate('LBL_OF_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td><span class="pull-right">&nbsp;%</span><input type="text" id="discount_percentage_final" name="discount_percentage_final" value="<?php echo $_smarty_tpl->tpl_vars['FINAL']->value['discount_percentage_final'];?>
" class="discount_percentage_final smallInputBox pull-right discountVal <?php if ($_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value!='percentage'){?>hide<?php }?>" /></td></tr><tr><td><input type="radio" name="discount_final" class="finalDiscounts" data-discount-type="amount" <?php if ($_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value=='amount'){?>checked<?php }?> />&nbsp;<?php echo vtranslate('LBL_DIRECT_PRICE_REDUCTION',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</td><td><input type="text" id="discount_amount_final" name="discount_amount_final" value="<?php echo $_smarty_tpl->tpl_vars['FINAL']->value['discount_amount_final'];?>
" class="smallInputBox pull-right discount_amount_final discountVal <?php if ($_smarty_tpl->tpl_vars['DISCOUNT_TYPE_FINAL']->value!='amount'){?>hide<?php }?>" /></td></tr></tbody></table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding"><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn btn-success finalDiscountSave" type="button" name="lineItemActionSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><!-- End Popup Div --></td></tr><tr><td width="83%"><span class="pull-right">(+)&nbsp;<b><?php echo vtranslate('LBL_SHIPPING_AND_HANDLING_CHARGES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 </b></span></td><td><span class="pull-right"><input id="shipping_handling_charge" name="shipping_handling_charge" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" type="text" class="lineItemInputBox" value="<?php if ($_smarty_tpl->tpl_vars['FINAL']->value['shipping_handling_charge']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['shipping_handling_charge'];?>
<?php }else{ ?>0.00<?php }?>" /></span></td></tr><tr><td width="83%"><span class="pull-right"><b><?php echo vtranslate('LBL_PRE_TAX_TOTAL',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 </b></span></td><td><?php $_smarty_tpl->tpl_vars['PRE_TAX_TOTAL'] = new Smarty_variable($_smarty_tpl->tpl_vars['FINAL']->value['preTaxTotal'], null, 0);?><span class="pull-right" id="preTaxTotal"><?php if ($_smarty_tpl->tpl_vars['PRE_TAX_TOTAL']->value){?><?php echo $_smarty_tpl->tpl_vars['PRE_TAX_TOTAL']->value;?>
<?php }else{ ?>0.00<?php }?></span><input type="hidden" id="pre_tax_total" name="pre_tax_total" value="<?php if ($_smarty_tpl->tpl_vars['PRE_TAX_TOTAL']->value){?><?php echo $_smarty_tpl->tpl_vars['PRE_TAX_TOTAL']->value;?>
<?php }else{ ?>0.00<?php }?>"/></td></tr><!-- Group Tax - starts --><tr id="group_tax_row" valign="top" class="<?php if ($_smarty_tpl->tpl_vars['IS_INDIVIDUAL_TAX_TYPE']->value){?>hide<?php }?>"><td width="83%"><span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="finalTax"><?php echo vtranslate('LBL_TAX',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></b></span><!-- Pop Div For Group TAX --><div class="hide finalTaxUI" id="group_tax_div"><table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-nobordered popupTable"><tr><th id="group_tax_div_title" colspan="2" nowrap align="left" ><?php echo vtranslate('LBL_GROUP_TAX',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</th><th align="right"><button type="button" class="close closeDiv">x</button></th></tr><?php  $_smarty_tpl->tpl_vars['tax_detail'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['tax_detail']->_loop = false;
 $_smarty_tpl->tpl_vars['loop_count'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['TAXES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['group_tax_loop']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['tax_detail']->key => $_smarty_tpl->tpl_vars['tax_detail']->value){
$_smarty_tpl->tpl_vars['tax_detail']->_loop = true;
 $_smarty_tpl->tpl_vars['loop_count']->value = $_smarty_tpl->tpl_vars['tax_detail']->key;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['group_tax_loop']['iteration']++;
?><tr><td align="left" class="lineOnTop"><input type="text" size="5" name="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxname'];?>
_group_percentage" id="group_tax_percentage<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['group_tax_loop']['iteration'];?>
" value="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['percentage'];?>
" class="smallInputBox groupTaxPercentage" />&nbsp;%</td><td align="center" class="lineOnTop"><div class="textOverflowEllipsis"><?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxlabel'];?>
</div></td><td align="right" class="lineOnTop"><input type="text" size="6" name="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxname'];?>
_group_amount" id="group_tax_amount<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['group_tax_loop']['iteration'];?>
" style="cursor:pointer;" value="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['amount'];?>
" readonly class="cursorPointer smallInputBox groupTaxTotal" /></td></tr><?php } ?><input type="hidden" id="group_tax_count" value="<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['group_tax_loop']['iteration'];?>
" /></table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding"><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn btn-success" type="button" name="lineItemActionSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><!-- End Popup Div Group Tax --></td><td><span id="tax_final" class="pull-right tax_final"><?php if ($_smarty_tpl->tpl_vars['FINAL']->value['tax_totalamount']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['tax_totalamount'];?>
<?php }else{ ?>0.00<?php }?></span></td></tr><!-- Group Tax - ends --><tr><td width="83%"><span class="pull-right">(+)&nbsp;<b><a href="javascript:void(0)" id="shippingHandlingTax"><?php echo vtranslate('LBL_TAX_FOR_SHIPPING_AND_HANDLING',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 </a></b></span><!-- Pop Div For Shipping and Handling TAX --><div class="hide" id="shipping_handling_div"><table class="table table-nobordered popupTable"><thead><tr><th id="sh_tax_div_title" colspan="2" nowrap align="left" ><?php echo vtranslate('LBL_SET_SHIPPING_AND_HANDLING_TAXES_FOR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
: <?php if ($_smarty_tpl->tpl_vars['FINAL']->value['shipping_handling_charge']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['shipping_handling_charge'];?>
<?php }else{ ?>0.00<?php }?></th><th align="right"><button type="button" class="close closeDiv">x</button></th></tr></thead><tbody><?php  $_smarty_tpl->tpl_vars['tax_detail'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['tax_detail']->_loop = false;
 $_smarty_tpl->tpl_vars['loop_count'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SHIPPING_TAXES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['sh_loop']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['tax_detail']->key => $_smarty_tpl->tpl_vars['tax_detail']->value){
$_smarty_tpl->tpl_vars['tax_detail']->_loop = true;
 $_smarty_tpl->tpl_vars['loop_count']->value = $_smarty_tpl->tpl_vars['tax_detail']->key;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['sh_loop']['iteration']++;
?><tr><td><div class="textOverflowEllipsis"><?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxlabel'];?>
</div></td><td><input type="text" name="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxname'];?>
_sh_percent" id="sh_tax_percentage<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['sh_loop']['iteration'];?>
" value="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['percentage'];?>
" class="smallInputBox shippingTaxPercentage" />&nbsp;%</td><td><input type="text" name="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['taxname'];?>
_sh_amount" id="sh_tax_amount<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['sh_loop']['iteration'];?>
" class="cursorPointer smallInputBox shippingTaxTotal pull-right" value="<?php echo $_smarty_tpl->tpl_vars['tax_detail']->value['amount'];?>
" readonly /></td></tr><?php } ?><input type="hidden" id="sh_tax_count" value="<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['sh_loop']['iteration'];?>
" /></tbody></table><div class="modal-footer lineItemPopupModalFooter modal-footer-padding"><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn btn-success finalTaxSave" type="button" name="lineItemActionSave"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><!-- End Popup Div for Shipping and Handling TAX --></td><td><span class="pull-right shipping_handling_tax" id="shipping_handling_tax"><?php if ($_smarty_tpl->tpl_vars['FINAL']->value['shtax_totalamount']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['shtax_totalamount'];?>
<?php }else{ ?>0.00<?php }?></span></td></tr><tr valign="top"><td width="83%" ><div class="pull-right"><b><?php echo vtranslate('LBL_ADJUSTMENT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
&nbsp;&nbsp;</b><div class="radio pull-right"><input type="radio" name="adjustmentType" option value="-" <?php if ($_smarty_tpl->tpl_vars['FINAL']->value['adjustment']<0){?>checked<?php }?>><?php echo vtranslate('LBL_DEDUCT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</div><div class="radio pull-right"><input type="radio" name="adjustmentType" option value="+" <?php if ($_smarty_tpl->tpl_vars['FINAL']->value['adjustment']>=0){?>checked<?php }?>><?php echo vtranslate('LBL_ADD',$_smarty_tpl->tpl_vars['MODULE']->value);?>
&nbsp;&nbsp;</div></div></td><td><span class="pull-right"><input id="adjustment" name="adjustment" type="text" data-validation-engine="validate[funcCall[Vtiger_PositiveNumber_Validator_Js.invokeValidation]]" class="lineItemInputBox" value="<?php if ($_smarty_tpl->tpl_vars['FINAL']->value['adjustment']<0){?><?php echo abs($_smarty_tpl->tpl_vars['FINAL']->value['adjustment']);?>
<?php }elseif($_smarty_tpl->tpl_vars['FINAL']->value['adjustment']){?><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['adjustment'];?>
<?php }else{ ?>0.00<?php }?>"></span></td></tr><tr valign="top"><td  width="83%"><span class="pull-right"><b><?php echo vtranslate('LBL_GRAND_TOTAL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></span></td><td><span id="grandTotal" name="grandTotal" class="pull-right grandTotal"><?php echo $_smarty_tpl->tpl_vars['FINAL']->value['grandTotal'];?>
</span></td></tr><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Invoice'||$_smarty_tpl->tpl_vars['MODULE']->value=='PurchaseOrder'){?><tr valign="top"><td width="83%" ><div class="pull-right"><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Invoice'){?><b><?php echo vtranslate('LBL_RECEIVED',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b><?php }else{ ?><b><?php echo vtranslate('LBL_PAID',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b><?php }?></div></td><td><?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Invoice'){?><span class="pull-right"><input id="received" name="received" type="text" class="lineItemInputBox" value="<?php if ($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('received')&&!($_smarty_tpl->tpl_vars['IS_DUPLICATE']->value)){?><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('received');?>
<?php }else{ ?>0.00<?php }?>"></span><?php }else{ ?><span class="pull-right"><input id="paid" name="paid" type="text" class="lineItemInputBox" value="<?php if ($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('paid')&&!($_smarty_tpl->tpl_vars['IS_DUPLICATE']->value)){?><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('paid');?>
<?php }else{ ?>0.00<?php }?>"></span><?php }?></td></tr><tr valign="top"><td width="83%" ><div class="pull-right"><b><?php echo vtranslate('LBL_BALANCE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></div></td><td><span class="pull-right"><input id="balance" name="balance" type="text" class="lineItemInputBox" value="<?php if ($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('balance')&&!($_smarty_tpl->tpl_vars['IS_DUPLICATE']->value)){?><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('balance');?>
<?php }else{ ?>0.00<?php }?>" readonly></span></td></tr><?php }?></table><input type="hidden" name="totalProductCount" id="totalProductCount" value="<?php echo $_smarty_tpl->tpl_vars['row_no']->value;?>
" /><input type="hidden" name="subtotal" id="subtotal" value="" /><input type="hidden" name="total" id="total" value="" /><?php }} ?>