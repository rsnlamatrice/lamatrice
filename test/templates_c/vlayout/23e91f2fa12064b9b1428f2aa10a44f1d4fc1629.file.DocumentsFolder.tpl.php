<?php /* Smarty version Smarty-3.1.7, created on 2014-12-03 10:15:07
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/DocumentsFolder.tpl" */ ?>
<?php /*%%SmartyHeaderCode:59120627545cd9efd1abb8-73317637%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '23e91f2fa12064b9b1428f2aa10a44f1d4fc1629' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/DocumentsFolder.tpl',
      1 => 1413619600,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '59120627545cd9efd1abb8-73317637',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545cd9efe445b',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'FOLDER_VALUES' => 0,
    'FOLDER_VALUE' => 0,
    'FOLDER_INFO' => 0,
    'FIELD_INFO' => 0,
    'SPECIAL_VALIDATOR' => 0,
    'UICOLOR' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545cd9efe445b')) {function content_545cd9efe445b($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo())), null, 0);?><?php $_smarty_tpl->tpl_vars['FOLDER_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDocumentFolders(), null, 0);?><?php $_smarty_tpl->tpl_vars["SPECIAL_VALIDATOR"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getValidator(), null, 0);?><?php  $_smarty_tpl->tpl_vars['FOLDER_INFO'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FOLDER_INFO']->_loop = false;
 $_smarty_tpl->tpl_vars['FOLDER_VALUE'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['FOLDER_VALUES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FOLDER_INFO']->key => $_smarty_tpl->tpl_vars['FOLDER_INFO']->value){
$_smarty_tpl->tpl_vars['FOLDER_INFO']->_loop = true;
 $_smarty_tpl->tpl_vars['FOLDER_VALUE']->value = $_smarty_tpl->tpl_vars['FOLDER_INFO']->key;
?><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue')==$_smarty_tpl->tpl_vars['FOLDER_VALUE']->value){?><?php $_smarty_tpl->tpl_vars['UICOLOR'] = new Smarty_variable($_smarty_tpl->tpl_vars['FOLDER_INFO']->value['uicolor'], null, 0);?><?php break 1?><?php }?><?php } ?><select class="chzn-select" name="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldName();?>
"data-validation-engine="validate[<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> required,<?php }?>funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"data-fieldinfo='<?php echo $_smarty_tpl->tpl_vars['FIELD_INFO']->value;?>
' <?php if (!empty($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value)){?>data-validator=<?php echo Zend_Json::encode($_smarty_tpl->tpl_vars['SPECIAL_VALIDATOR']->value);?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['UICOLOR']->value){?> style="background-color: <?php echo $_smarty_tpl->tpl_vars['UICOLOR']->value;?>
"<?php }?>><?php  $_smarty_tpl->tpl_vars['FOLDER_INFO'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FOLDER_INFO']->_loop = false;
 $_smarty_tpl->tpl_vars['FOLDER_VALUE'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['FOLDER_VALUES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FOLDER_INFO']->key => $_smarty_tpl->tpl_vars['FOLDER_INFO']->value){
$_smarty_tpl->tpl_vars['FOLDER_INFO']->_loop = true;
 $_smarty_tpl->tpl_vars['FOLDER_VALUE']->value = $_smarty_tpl->tpl_vars['FOLDER_INFO']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['FOLDER_VALUE']->value;?>
" <?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue')==$_smarty_tpl->tpl_vars['FOLDER_VALUE']->value){?> selected <?php }?>style="background-color: <?php echo $_smarty_tpl->tpl_vars['FOLDER_INFO']->value['uicolor'];?>
"><?php echo $_smarty_tpl->tpl_vars['FOLDER_INFO']->value['name'];?>
<span style="background-color: <?php echo $_smarty_tpl->tpl_vars['FOLDER_INFO']->value['uicolor'];?>
; display:inline-block; width: 16px; height: 16px; margin-left: 4px;">&nbsp;</span></option><?php } ?></select><?php }} ?>