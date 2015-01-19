<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 15:35:16
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleDesigner/CustomLinks.tpl" */ ?>
<?php /*%%SmartyHeaderCode:98666756554621ea4a7edb2-57782990%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '87e31f793bb47de3524aef094b97d1bf73a74553' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleDesigner/CustomLinks.tpl',
      1 => 1413716784,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '98666756554621ea4a7edb2-57782990',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54621ea4ae5e4',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54621ea4ae5e4')) {function content_54621ea4ae5e4($_smarty_tpl) {?><table id="md-custom-links-table">
<tr>
<td>
<div id="md-custom-links-toolbar">
	<h2><?php echo vtranslate('LBL_CUSTOM_LINKS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h2>
	
	<ul id="md-custom-links-list">
	<li><?php echo vtranslate('HEADERSCRIPT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('HEADERCSS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('HEADERLINK',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('LISTVIEW',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('LISTVIEWBASIC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('DETAILVIEW',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('DETAILVIEWBASIC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('DETAILVIEWWIDGET',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('SIDEBARLINK',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('SIDEBARWIDGET',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	</ul>
</div>
</td>
<td>
<div>
<ul id="md-custom-links-ul" class="md-custom-links-ul">
<!-- Custom links added with JS -->
</ul>
</div>
</td>
</table><?php }} ?>