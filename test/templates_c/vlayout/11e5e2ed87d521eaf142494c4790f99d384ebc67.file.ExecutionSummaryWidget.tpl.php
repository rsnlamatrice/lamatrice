<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 16:02:02
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNContactsPanels/ExecutionSummaryWidget.tpl" */ ?>
<?php /*%%SmartyHeaderCode:212586226854aaa76a0037e3-14002420%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '11e5e2ed87d521eaf142494c4790f99d384ebc67' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNContactsPanels/ExecutionSummaryWidget.tpl',
      1 => 1420382522,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '212586226854aaa76a0037e3-14002420',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ERROR' => 0,
    'RESULT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54aaa76a08113',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54aaa76a08113')) {function content_54aaa76a08113($_smarty_tpl) {?>
<div class="recordDetails"><?php if ($_smarty_tpl->tpl_vars['ERROR']->value){?><code><?php echo $_smarty_tpl->tpl_vars['ERROR']->value;?>
</code><?php }else{ ?>Résultat : <?php echo $_smarty_tpl->tpl_vars['RESULT']->value;?>
<?php }?></div><div class="pull-right"><a href="">voir les contacts</a></div><?php }} ?>