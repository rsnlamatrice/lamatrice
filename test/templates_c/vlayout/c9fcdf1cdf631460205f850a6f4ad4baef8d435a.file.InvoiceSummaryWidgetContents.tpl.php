<?php /* Smarty version Smarty-3.1.7, created on 2014-12-22 10:14:42
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/InvoiceSummaryWidgetContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:16756680885488469e573272-97777188%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'c9fcdf1cdf631460205f850a6f4ad4baef8d435a' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/InvoiceSummaryWidgetContents.tpl',
      1 => 1419239678,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '16756680885488469e573272-97777188',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5488469e6301c',
  'variables' => 
  array (
    'RELATED_RECORDS' => 0,
    'RELATED_RECORD' => 0,
    'SUBJECT2' => 0,
    'SUBJECT1' => 0,
    'MODULE' => 0,
    'RELATED_MODULE' => 0,
    'NUMBER_OF_RECORDS' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5488469e6301c')) {function content_5488469e6301c($_smarty_tpl) {?>
<?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><div class="recentInvoicesContainer row-fluid"><ul class="unstyled"><li class="row-fluid"><div class="row-fluid"><span class="textOverflowEllipsis span6"><?php $_smarty_tpl->tpl_vars['SUBJECT1'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('typedossier'), null, 0);?><?php $_smarty_tpl->tpl_vars['SUBJECT2'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('subject'), null, 0);?><?php if (!$_smarty_tpl->tpl_vars['SUBJECT2']->value){?><?php $_smarty_tpl->tpl_vars['SUBJECT2'] = new Smarty_variable($_smarty_tpl->tpl_vars['SUBJECT1']->value, null, 0);?><?php }elseif($_smarty_tpl->tpl_vars['SUBJECT1']->value&&($_smarty_tpl->tpl_vars['SUBJECT1']->value!=$_smarty_tpl->tpl_vars['SUBJECT2']->value)){?><?php $_smarty_tpl->tpl_vars['SUBJECT2'] = new Smarty_variable((($_smarty_tpl->tpl_vars['SUBJECT1']->value).(' - ')).($_smarty_tpl->tpl_vars['SUBJECT2']->value), null, 0);?><?php }?><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" title="<?php echo $_smarty_tpl->tpl_vars['SUBJECT2']->value;?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
"><?php echo $_smarty_tpl->tpl_vars['SUBJECT2']->value;?>
</a></span><span class="textOverflowEllipsis span3"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('subject');?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('invoicedate');?>
</a></span><span class="textOverflowEllipsis span2"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('subject');?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('hdnGrandTotal');?>
&nbsp;&euro;</a></span></div></li></ul></div><?php } ?><?php $_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS'] = new Smarty_variable(count($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS']->value==15){?><div class="row-fluid"><div class="pull-right"><a class="moreRecentInvoices cursorPointer"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></div></div><?php }?><?php }} ?>