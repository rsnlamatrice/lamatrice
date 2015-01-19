<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 12:48:27
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/ButtonSetDetail.tpl" */ ?>
<?php /*%%SmartyHeaderCode:433820323545bc789f22f85-84836501%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd7431f3a53b19768f3be38c5e8fba807a1847e4a' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/ButtonSetDetail.tpl',
      1 => 1413623200,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '433820323545bc789f22f85-84836501',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545bc78a10c48',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'LABELS' => 0,
    'RECORD' => 0,
    'RECORD_MODEL' => 0,
    'FIELD_NAME' => 0,
    'UID' => 0,
    'OCCUPY_COMPLETE_WIDTH' => 0,
    'PICKLIST_LABELS' => 0,
    'SELECTED_VALUE' => 0,
    'PICKLIST_KEY' => 0,
    'PICKLIST_ITEM' => 0,
    'PICKLIST_CLASS' => 0,
    'PICKLIST_ICON' => 0,
    'PICKLIST_LABEL' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545bc78a10c48')) {function content_545bc78a10c48($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo()), null, 0);?><?php $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['LABELS']->value){?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABELS'] = new Smarty_variable($_smarty_tpl->tpl_vars['LABELS']->value, null, 0);?><?php }else{ ?><?php if ($_smarty_tpl->tpl_vars['RECORD']->value){?><?php $_smarty_tpl->tpl_vars['RECORD_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD']->value, null, 0);?><?php }?><?php if (!$_smarty_tpl->tpl_vars['RECORD_MODEL']->value){?>Erreur : RECORD_MODEL manquant<?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABELS'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getPicklistValuesDetails($_smarty_tpl->tpl_vars['FIELD_NAME']->value), null, 0);?><?php }?><?php }?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><?php $_smarty_tpl->tpl_vars['SELECTED_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'), null, 0);?><?php $_smarty_tpl->tpl_vars['UID'] = new Smarty_variable(uniqid('btnset'), null, 0);?><div id="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
" class="<?php if ($_smarty_tpl->tpl_vars['OCCUPY_COMPLETE_WIDTH']->value){?> row-fluid <?php }?>"><?php  $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = false;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PICKLIST_LABELS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key => $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value){
$_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = true;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value = $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key;
?><?php if (trim(decode_html($_smarty_tpl->tpl_vars['SELECTED_VALUE']->value))==trim($_smarty_tpl->tpl_vars['PICKLIST_KEY']->value)){?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php if (isset($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'])){?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable('', null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['icon'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value, null, 0);?><?php }?><label for="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_CLASS']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ICON']->value){?><span class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_ICON']->value;?>
"></span>&nbsp;<?php }?><?php echo $_smarty_tpl->tpl_vars['PICKLIST_LABEL']->value;?>
</label><?php break 1?><?php }?><?php } ?></div><?php }} ?>