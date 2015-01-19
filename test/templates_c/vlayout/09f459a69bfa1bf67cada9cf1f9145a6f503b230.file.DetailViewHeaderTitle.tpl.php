<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 17:00:58
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/DetailViewHeaderTitle.tpl" */ ?>
<?php /*%%SmartyHeaderCode:210474092754affb3aad4d89-18862473%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '09f459a69bfa1bf67cada9cf1f9145a6f503b230' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/DetailViewHeaderTitle.tpl',
      1 => 1420811146,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '210474092754affb3aad4d89-18862473',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54affb3aaed31',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54affb3aaed31')) {function content_54affb3aaed31($_smarty_tpl) {?>
<span class="span2"><span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-contact"></span></span></span><span class="span8 margin0px"><span class="row-fluid"><span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('nom');?>
<br><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rsnmediaid');?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rubrique');?>
</span></span><?php }} ?>