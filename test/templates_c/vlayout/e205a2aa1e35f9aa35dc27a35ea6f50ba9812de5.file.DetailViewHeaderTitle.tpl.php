<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 16:13:12
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/DetailViewHeaderTitle.tpl" */ ?>
<?php /*%%SmartyHeaderCode:61051893454622588107349-31553127%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e205a2aa1e35f9aa35dc27a35ea6f50ba9812de5' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/DetailViewHeaderTitle.tpl',
      1 => 1415543408,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '61051893454622588107349-31553127',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5462258814de5',
  'variables' => 
  array (
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5462258814de5')) {function content_5462258814de5($_smarty_tpl) {?>
<span class="span2"><span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-contact"></span></span></span><span class="span8 margin0px"><span class="row-fluid"><span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('nom');?>
<br><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rsnmediaid');?>
&nbsp;<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rubrique');?>
</span></span><?php }} ?>