<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 15:52:39
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/Color.tpl" */ ?>
<?php /*%%SmartyHeaderCode:9106000965453940e4c9179-52572109%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'edb5538b645c28555fb6d2efcbe8cdd901b1f27b' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/uitypes/Color.tpl',
      1 => 1413623200,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '9106000965453940e4c9179-52572109',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5453940e5d3b2',
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'MODULE' => 0,
    'FIELD_NAME' => 0,
    'INPUT_ID' => 0,
    'VALUE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5453940e5d3b2')) {function content_5453940e5d3b2($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["FIELD_INFO"] = new Smarty_variable(Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldInfo())), null, 0);?><?php $_smarty_tpl->tpl_vars["FIELD_NAME"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('name'), null, 0);?><?php $_smarty_tpl->tpl_vars["VALUE"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'), null, 0);?><?php $_smarty_tpl->tpl_vars["INPUT_ID"] = new Smarty_variable(($_smarty_tpl->tpl_vars['MODULE']->value)."_editView_fieldName_".($_smarty_tpl->tpl_vars['FIELD_NAME']->value), null, 0);?><div id="<?php echo $_smarty_tpl->tpl_vars['INPUT_ID']->value;?>
-colorSelector" class="colorpicker-holder" <?php if (!$_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isReadOnly()){?>title="2 clics pour editer"<?php }?>><div style="background-color: <?php echo $_smarty_tpl->tpl_vars['VALUE']->value;?>
"></div></div><?php }} ?>