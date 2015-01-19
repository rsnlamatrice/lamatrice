<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 15:56:12
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaRelations/DetailViewHeaderTitle.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2412441355462270a5a1de9-14098779%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '22e75dd2c15fce18f10a50bb09a903677364669d' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaRelations/DetailViewHeaderTitle.tpl',
      1 => 1415544894,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2412441355462270a5a1de9-14098779',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5462270a61cd6',
  'variables' => 
  array (
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5462270a61cd6')) {function content_5462270a61cd6($_smarty_tpl) {?>
<span class="span2"><span class="summaryImg rsn-summaryImg"><span class="icon-rsn-large-account"></span></span></span><span class="span8 margin0px"><span class="row-fluid"><span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
</span><br><span><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('mediacontactid');?>
&nbsp;-&nbsp;<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rsnmediaid');?>
</span><?php if ($_smarty_tpl->tpl_vars['RECORD']->value->get('rsnthematiques')){?>&nbsp;<span title="<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rsnthematiques');?>
"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('rsnthematiques');?>
</span><?php }?>&nbsp;/&nbsp;<span><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('byuserid');?>
,&nbsp;le&nbsp;<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue('daterelation');?>
</span></span></span><?php }} ?>