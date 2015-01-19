<?php /* Smarty version Smarty-3.1.7, created on 2014-11-21 10:40:37
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Vtiger/ListView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1387981507546f0895636ed2-62746375%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9f31d7af5c2c7cbf03da3bb7440f866f4874c19d' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Vtiger/ListView.tpl',
      1 => 1413615938,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1387981507546f0895636ed2-62746375',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_546f089578bc1',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_546f089578bc1')) {function content_546f089578bc1($_smarty_tpl) {?>
<div><div class="listViewTopMenuDiv"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ListViewHeader.tpl',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div><div class="listViewContentDiv" id="listViewContents"><br><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ListViewContents.tpl',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div></div><?php }} ?>