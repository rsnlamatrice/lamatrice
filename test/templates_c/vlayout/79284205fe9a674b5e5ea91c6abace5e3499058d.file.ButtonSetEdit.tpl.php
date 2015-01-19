<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 16:32:17
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/ButtonSetEdit.tpl" */ ?>
<?php /*%%SmartyHeaderCode:147271691545bc78a1214f9-73895371%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '79284205fe9a674b5e5ea91c6abace5e3499058d' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/ButtonSetEdit.tpl',
      1 => 1415547964,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '147271691545bc78a1214f9-73895371',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545bc78a2b4fc',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'RECORD' => 0,
    'RECORD_MODEL' => 0,
    'FIELD_NAME' => 0,
    'UID' => 0,
    'OCCUPY_COMPLETE_WIDTH' => 0,
    'PICKLIST_LABELS' => 0,
    'PICKLIST_ITEM' => 0,
    'PICKLIST_KEY' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
    'SELECTED_VALUE' => 0,
    'PICKLIST_CLASS' => 0,
    'PICKLIST_ICON' => 0,
    'PICKLIST_LABEL' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545bc78a2b4fc')) {function content_545bc78a2b4fc($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo()), null, 0);?><?php $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['RECORD']->value){?><?php $_smarty_tpl->tpl_vars['RECORD_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD']->value, null, 0);?><?php }?><?php if (!$_smarty_tpl->tpl_vars['RECORD_MODEL']->value){?>RECORD_MODEL manquant<?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABELS'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getPicklistValuesDetails($_smarty_tpl->tpl_vars['FIELD_NAME']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><?php $_smarty_tpl->tpl_vars['SELECTED_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'), null, 0);?><?php $_smarty_tpl->tpl_vars['UID'] = new Smarty_variable(uniqid('btnset'), null, 0);?><div id="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
" class="buttonset <?php if ($_smarty_tpl->tpl_vars['OCCUPY_COMPLETE_WIDTH']->value){?> row-fluid <?php }?>"><?php  $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = false;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PICKLIST_LABELS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key => $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value){
$_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = true;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value = $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key;
?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php if (isset($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'])){?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable('', null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['icon'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value, null, 0);?><?php }?><input type="radio"name="<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
"id="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
"data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"data-fieldinfo='<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['FIELD_INFO']->value, ENT_QUOTES, 'UTF-8', true);?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator='<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
'<?php }?>data-selected-value='<?php echo $_smarty_tpl->tpl_vars['SELECTED_VALUE']->value;?>
'value="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
"<?php if (trim(decode_html($_smarty_tpl->tpl_vars['SELECTED_VALUE']->value))==trim($_smarty_tpl->tpl_vars['PICKLIST_KEY']->value)){?>checked="checked"<?php }?>/><label for="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_CLASS']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ICON']->value){?><span class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_ICON']->value;?>
"></span>&nbsp;<?php }?><?php echo $_smarty_tpl->tpl_vars['PICKLIST_LABEL']->value;?>
</label><?php } ?><!--script>$(document.body).ready(function(){ $('#<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
').buttonset(); });</script--></div><?php }} ?>