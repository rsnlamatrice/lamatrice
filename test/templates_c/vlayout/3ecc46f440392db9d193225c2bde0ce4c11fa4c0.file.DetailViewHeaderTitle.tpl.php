<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 15:42:36
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMedias/DetailViewHeaderTitle.tpl" */ ?>
<?php /*%%SmartyHeaderCode:10147971655462205c977481-13081409%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '3ecc46f440392db9d193225c2bde0ce4c11fa4c0' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMedias/DetailViewHeaderTitle.tpl',
      1 => 1415543530,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '10147971655462205c977481-13081409',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5462205c9c465',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5462205c9c465')) {function content_5462205c9c465($_smarty_tpl) {?>
<span class="span2"><span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-collectif"></span></span></span><span class="span8 margin0px"><span class="row-fluid"><span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('nom');?>
<br><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rubriquepreferee');?>
</span></span><?php }} ?>