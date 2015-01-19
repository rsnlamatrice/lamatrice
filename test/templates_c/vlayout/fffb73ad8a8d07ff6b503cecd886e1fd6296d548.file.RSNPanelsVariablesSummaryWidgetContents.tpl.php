<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 16:18:29
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNContactsPanels/RSNPanelsVariablesSummaryWidgetContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:61977923654aaa76a2276f0-29385034%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fffb73ad8a8d07ff6b503cecd886e1fd6296d548' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNContactsPanels/RSNPanelsVariablesSummaryWidgetContents.tpl',
      1 => 1420471107,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '61977923654aaa76a2276f0-29385034',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54aaa76a2db78',
  'variables' => 
  array (
    'RELATED_MODULE' => 0,
    'RELATED_RECORD_MODEL' => 0,
    'RELATED_RECORDS' => 0,
    'RELATED_RECORD' => 0,
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54aaa76a2db78')) {function content_54aaa76a2db78($_smarty_tpl) {?>
<div class="relatedContainer"><input type="hidden" name="relatedModuleName" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
" /></div><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD_MODEL']->value->getListViewPicklistValues('satisfaction'), null, 0);?><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><div class="contactsContainer"><ul class="unstyled"><li title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('label');?>
"><div class="row-fluid"><div class="span4 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><?php if ($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('disabled')){?>/!\&nbsp;<?php }?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('name');?>
</a></div><div class="span8 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><input value="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('defaultvalue');?>
"/></a></div></div></li></ul></div><?php } ?>
<?php }} ?>