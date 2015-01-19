<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 16:11:55
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/Picklist.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1177279647544e4ffa265073-67555832%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3d57fd75d147085e81a9c43c711a20c18b8464b3' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/Picklist.tpl',
      1 => 1417167048,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1177279647544e4ffa265073-67555832',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_544e4ffa45b96',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'PICKLIST_VALUES' => 0,
    'PICKLIST_DATA' => 0,
    'OCCUPY_COMPLETE_WIDTH' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
    'PICKLIST_ITEM' => 0,
    'PICKLIST_NAME' => 0,
    'PICKLIST_ADD_ATTR' => 0,
    'PICKLIST_VALUE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4ffa45b96')) {function content_544e4ffa45b96($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo()), null, 0);?><?php if (!isset($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_DATA'] = new Smarty_variable(array(), null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getPicklistValues($_smarty_tpl->tpl_vars['PICKLIST_DATA']->value), null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><select class="chzn-select <?php if ($_smarty_tpl->tpl_vars['OCCUPY_COMPLETE_WIDTH']->value){?> row-fluid <?php }?>" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName();?>
" data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"data-fieldinfo='<?php echo htmlspecialchars($_smarty_tpl->tpl_vars['FIELD_INFO']->value, ENT_QUOTES, 'UTF-8', true);?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator='<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
'<?php }?> data-selected-value='<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue');?>
'><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isEmptyPicklistOptionAllowed()){?><option value=""><?php echo vtranslate('LBL_SELECT_OPTION','Vtiger');?>
</option><?php }?><?php  $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = false;
 $_smarty_tpl->tpl_vars['PICKLIST_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key => $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value){
$_smarty_tpl->tpl_vars['PICKLIST_ITEM']->_loop = true;
 $_smarty_tpl->tpl_vars['PICKLIST_NAME']->value = $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->key;
?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value, null, 0);?><?php }?><option value="<?php echo Vtiger_Util_Helper::toSafeHTML($_smarty_tpl->tpl_vars['PICKLIST_NAME']->value);?>
" <?php if (trim(decode_html($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue')))==trim($_smarty_tpl->tpl_vars['PICKLIST_NAME']->value)){?> selected <?php }?><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ADD_ATTR']->value){?> <?php echo $_smarty_tpl->tpl_vars['PICKLIST_ADD_ATTR']->value;?>
="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value[$_smarty_tpl->tpl_vars['PICKLIST_ADD_ATTR']->value];?>
"<?php }?>><?php echo $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value;?>
</option><?php } ?></select><?php }} ?>