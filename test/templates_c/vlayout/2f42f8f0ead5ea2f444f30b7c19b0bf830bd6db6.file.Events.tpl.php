<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 15:35:16
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleDesigner/Events.tpl" */ ?>
<?php /*%%SmartyHeaderCode:27817664254621ea4b2cf82-61491059%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2f42f8f0ead5ea2f444f30b7c19b0bf830bd6db6' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleDesigner/Events.tpl',
      1 => 1413716784,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '27817664254621ea4b2cf82-61491059',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54621ea4b8241',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54621ea4b8241')) {function content_54621ea4b8241($_smarty_tpl) {?><table id="md-events-table">
<tr>
<td>
<div id="md-events-toolbar">
	<h2><?php echo vtranslate('LBL_EVENTS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h2>
	
	<ul id="md-events-list">
	<li><?php echo vtranslate('vtiger.entity.beforesave.modifiable',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.beforesave',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.beforesave.final',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.aftersave',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.aftersave.final',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.beforedelete',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.afterdelete',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	<li><?php echo vtranslate('vtiger.entity.afterrestore',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</li>
	</ul>
</div>
</td>
<td>
<div>
<ul id="md-events-ul" class="md-events-ul">
<!-- Events added with JS -->
</ul>
</div>
</td>
</table><?php }} ?>