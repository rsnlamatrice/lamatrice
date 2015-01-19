<?php /* Smarty version Smarty-3.1.7, created on 2014-12-11 14:54:47
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Products/MoreCurrenciesList.tpl" */ ?>
<?php /*%%SmartyHeaderCode:228384365489a227e5dbb5-61210534%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '43364beffa00c57b8383b8374f782ea941016277' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Products/MoreCurrenciesList.tpl',
      1 => 1413619464,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '228384365489a227e5dbb5-61210534',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'PRICE_DETAILS' => 0,
    'price' => 0,
    'check_value' => 0,
    'disable_value' => 0,
    'USER_MODEL' => 0,
    'base_cur_check' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5489a2280f719',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5489a2280f719')) {function content_5489a2280f719($_smarty_tpl) {?>
<div id="currency_class" class="multiCurrencyEditUI modelContainer"><div class="modal-header"><button data-dismiss="modal" class="floatRight close" type="button" title="<?php echo vtranslate('LBL_CLOSE');?>
">x</button><h3 id="massEditHeader"><?php echo vtranslate('LBL_PRICES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><div class="multiCurrencyContainer"><div class="currencyContent"><div class="modal-body"><table width="100%" border="0" cellpadding="5" cellspacing="0" class="table table-bordered"><tr class="detailedViewHeader"><td><b><?php echo vtranslate('LBL_CURRENCY',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_CONVERSION_RATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_RESET_PRICE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td><td><b><?php echo vtranslate('LBL_BASE_CURRENCY',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</b></td></tr><?php  $_smarty_tpl->tpl_vars['price'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['price']->_loop = false;
 $_smarty_tpl->tpl_vars['count'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PRICE_DETAILS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['price']->key => $_smarty_tpl->tpl_vars['price']->value){
$_smarty_tpl->tpl_vars['price']->_loop = true;
 $_smarty_tpl->tpl_vars['count']->value = $_smarty_tpl->tpl_vars['price']->key;
?><tr data-currency-id=<?php echo $_smarty_tpl->tpl_vars['price']->value['curname'];?>
><?php if ($_smarty_tpl->tpl_vars['price']->value['check_value']==1||$_smarty_tpl->tpl_vars['price']->value['is_basecurrency']==1){?><?php $_smarty_tpl->tpl_vars['check_value'] = new Smarty_variable("checked", null, 0);?><?php $_smarty_tpl->tpl_vars['disable_value'] = new Smarty_variable('', null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['check_value'] = new Smarty_variable('', null, 0);?><?php $_smarty_tpl->tpl_vars['disable_value'] = new Smarty_variable("disabled=true", null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['price']->value['is_basecurrency']==1){?><?php $_smarty_tpl->tpl_vars['base_cur_check'] = new Smarty_variable("checked", null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['base_cur_check'] = new Smarty_variable('', null, 0);?><?php }?><td><span class="row-fluid"><span class="span8 alignBottom"><span class="pull-left"><?php echo getTranslatedCurrencyString($_smarty_tpl->tpl_vars['price']->value['currencylabel']);?>
 (<?php echo $_smarty_tpl->tpl_vars['price']->value['currencysymbol'];?>
)</span></span><span class="span2"><input type="checkbox" name="cur_<?php echo $_smarty_tpl->tpl_vars['price']->value['curid'];?>
_check" id="cur_<?php echo $_smarty_tpl->tpl_vars['price']->value['curid'];?>
_check" class="small pull-right enableCurrency" <?php echo $_smarty_tpl->tpl_vars['check_value']->value;?>
></span></span></td><td><div class="row-fluid"><input <?php echo $_smarty_tpl->tpl_vars['disable_value']->value;?>
 type="text" size="10" class="span9 convertedPrice" name="<?php echo $_smarty_tpl->tpl_vars['price']->value['curname'];?>
" id="<?php echo $_smarty_tpl->tpl_vars['price']->value['curname'];?>
" value="<?php echo $_smarty_tpl->tpl_vars['price']->value['curvalue'];?>
" data-validation-engine="validate[funcCall[Vtiger_Currency_Validator_Js.invokeValidation]]" data-decimal-seperator='<?php echo $_smarty_tpl->tpl_vars['USER_MODEL']->value->get('currency_decimal_separator');?>
' data-group-seperator='<?php echo $_smarty_tpl->tpl_vars['USER_MODEL']->value->get('currency_grouping_separator');?>
' /></div></td><td><div class="row-fluid"><input readonly="" type="text" size="10" class="span9 conversionRate" name="cur_conv_rate<?php echo $_smarty_tpl->tpl_vars['price']->value['curid'];?>
" value="<?php echo $_smarty_tpl->tpl_vars['price']->value['conversionrate'];?>
"></div></td><td><div class="row-fluid"><button <?php echo $_smarty_tpl->tpl_vars['disable_value']->value;?>
 type="button" class="btn currencyReset resetButton" id="cur_reset<?php echo $_smarty_tpl->tpl_vars['price']->value['curid'];?>
" value="<?php echo vtranslate('LBL_RESET',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><?php echo vtranslate('LBL_RESET',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button></div></td><td><div class="row-fluid textAlignCenter"><input <?php echo $_smarty_tpl->tpl_vars['disable_value']->value;?>
 type="radio" class="baseCurrency" id="base_currency<?php echo $_smarty_tpl->tpl_vars['price']->value['curid'];?>
" name="base_currency_input" value="<?php echo $_smarty_tpl->tpl_vars['price']->value['curname'];?>
" <?php echo $_smarty_tpl->tpl_vars['base_cur_check']->value;?>
 /></div></td></tr><?php } ?></table></div></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ModalFooter.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div></div><?php }} ?>