<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 16:01:27
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Contacts/ContactsSummaryWidgetContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:141815381454afed47e0ef98-32471286%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '94f37f4f8585e125e18aefa8187a7a8334ccc31e' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Contacts/ContactsSummaryWidgetContents.tpl',
      1 => 1420811144,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '141815381454afed47e0ef98-32471286',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RELATED_MODULE' => 0,
    'RELATED_RECORDS' => 0,
    'RELATED_RECORD' => 0,
    'MODULE' => 0,
    'RELTYPES' => 0,
    'NUMBER_OF_RECORDS' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54afed47e4fb0',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54afed47e4fb0')) {function content_54afed47e4fb0($_smarty_tpl) {?>
<div class="relatedContainer"><input type="hidden" name="relatedModuleName" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
" /></div><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><div class="contactsContainer"><ul class="unstyled"><li><div class="row-fluid"><div class="span6 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><?php if ($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('isgroup')!='1'){?><span class="icon-rsn-small-contact"></span><?php }else{ ?><span class="icon-rsn-small-collectif"></span><?php }?>&nbsp;<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getName();?>
</a></div><div class="span4 textOverflowEllipsis"><span><?php $_smarty_tpl->tpl_vars['RELTYPES'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('contreltype'), null, 0);?><?php if (is_array($_smarty_tpl->tpl_vars['RELTYPES']->value)){?><?php echo $_smarty_tpl->tpl_vars['RELTYPES']->value[0];?>
<?php }?></span></div></div></li></ul></div><?php } ?><?php $_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS'] = new Smarty_variable(count($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS']->value==5){?><div class="row-fluid"><div class="pull-right"><a class="moreRecentContacts cursorPointer"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></div></div><?php }?>
<?php }} ?>