<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 16:01:26
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/SummaryViewWidgets.tpl" */ ?>
<?php /*%%SmartyHeaderCode:157016764254afed46b35c85-34671251%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2b8aad632e3470cf58e84f1a7fe88e02de562861' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/SummaryViewWidgets.tpl',
      1 => 1420811148,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '157016764254afed46b35c85-34671251',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE_SUMMARY' => 0,
    'NO_ACTIVITIES_WIDGET' => 0,
    'DETAILVIEW_LINKS' => 0,
    'MODULO_LEFT' => 0,
    'DETAIL_VIEW_WIDGET' => 0,
    'RECORD_ACTIONS' => 0,
    'MODULE_NAME' => 0,
    'RECORD_ACTION' => 0,
    'IS_SELECT_BUTTON' => 0,
    'RECORD_ACTION_LABELS' => 0,
    'RECORD_ACTION_IDX' => 0,
    'RECORD_ACTION_LABEL' => 0,
    'RELATED_ACTIVITIES' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54afed46c67fd',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54afed46c67fd')) {function content_54afed46c67fd($_smarty_tpl) {?>
<?php if (!empty($_smarty_tpl->tpl_vars['MODULE_SUMMARY']->value)){?><div class="row-fluid"><div class="span7"><div class="summaryView row-fluid"><?php echo $_smarty_tpl->tpl_vars['MODULE_SUMMARY']->value;?>
</div><?php }?><?php if ($_smarty_tpl->tpl_vars['NO_ACTIVITIES_WIDGET']->value){?><?php $_smarty_tpl->tpl_vars['MODULO_LEFT'] = new Smarty_variable(1, null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['MODULO_LEFT'] = new Smarty_variable(0, null, 0);?><?php }?><?php  $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['DETAILVIEW_LINKS']->value['DETAILVIEWWIDGET']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['count']['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->key => $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value){
$_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['count']['index']++;
?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['count']['index']%2==$_smarty_tpl->tpl_vars['MODULO_LEFT']->value){?><div class="summaryWidgetContainer"><div class="widgetContainer_<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['count']['index'];?>
" data-url="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getUrl();?>
" data-name="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getLabel();?>
"><div class="widget_header row-fluid"><?php $_smarty_tpl->tpl_vars['RECORD_ACTIONS'] = new Smarty_variable($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('action'), null, 0);?><span class="span<?php if (is_array($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)&&(count($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)>1)){?>6<?php }else{ ?>8<?php }?> margin0px"><h4><?php echo vtranslate($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getLabel(),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</h4></span><?php if (is_array($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)){?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS'] = new Smarty_variable($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('actionlabel'), null, 0);?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_IDX'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['RECORD_ACTION'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RECORD_ACTION']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RECORD_ACTION']->key => $_smarty_tpl->tpl_vars['RECORD_ACTION']->value){
$_smarty_tpl->tpl_vars['RECORD_ACTION']->_loop = true;
?><?php ob_start();?><?php echo $_smarty_tpl->tpl_vars['RECORD_ACTION']->value=="Select";?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars['IS_SELECT_BUTTON'] = new Smarty_variable($_tmp1, null, 0);?><button type="button" class="btn addButton  pull-right <?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> selectRelation <?php }?> "<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> data-moduleName=<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkName');?>
 <?php }?>data-url="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('actionURL');?>
"<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value!=true){?>name="addButton"<?php }?>data-name="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkField');?>
"><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==false){?><i class="icon-plus icon-white"></i><?php }?><?php if ($_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS']->value){?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS']->value[$_smarty_tpl->tpl_vars['RECORD_ACTION_IDX']->value], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL'] = new Smarty_variable(vtranslate(('LBL_').(strtoupper($_smarty_tpl->tpl_vars['RECORD_ACTION']->value)),$_smarty_tpl->tpl_vars['MODULE_NAME']->value), null, 0);?><?php }?>&nbsp;<strong><?php echo $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL']->value;?>
</strong></button><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_IDX'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_ACTION_IDX']->value+1, null, 0);?><?php } ?><input type="hidden" name="relatedModule" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkName');?>
" /><?php }?></div><div class="widget_contents"></div></div></div><?php }?><?php } ?></div><div class="span5" style="overflow: hidden"><?php if (!($_smarty_tpl->tpl_vars['NO_ACTIVITIES_WIDGET']->value)){?><div id="relatedActivities"><?php echo $_smarty_tpl->tpl_vars['RELATED_ACTIVITIES']->value;?>
</div><?php }?><?php  $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['DETAILVIEW_LINKS']->value['DETAILVIEWWIDGET']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['count']['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->key => $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value){
$_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->_loop = true;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['count']['index']++;
?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['count']['index']%2!=$_smarty_tpl->tpl_vars['MODULO_LEFT']->value){?><div class="summaryWidgetContainer"><div class="widgetContainer_<?php echo $_smarty_tpl->getVariable('smarty')->value['foreach']['count']['index'];?>
" data-url="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getUrl();?>
" data-name="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getLabel();?>
"><div class="widget_header row-fluid"><?php $_smarty_tpl->tpl_vars['RECORD_ACTIONS'] = new Smarty_variable($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('action'), null, 0);?><span class="span<?php if (is_array($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)&&(count($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)>1)){?>5<?php }else{ ?>7<?php }?> margin0px"><h4><?php echo vtranslate($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->getLabel(),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</h4></span><?php if (is_array($_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value)){?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS'] = new Smarty_variable($_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('actionlabel'), null, 0);?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_IDX'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['RECORD_ACTION'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RECORD_ACTION']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECORD_ACTIONS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RECORD_ACTION']->key => $_smarty_tpl->tpl_vars['RECORD_ACTION']->value){
$_smarty_tpl->tpl_vars['RECORD_ACTION']->_loop = true;
?><?php ob_start();?><?php echo $_smarty_tpl->tpl_vars['RECORD_ACTION']->value=="Select";?>
<?php $_tmp2=ob_get_clean();?><?php $_smarty_tpl->tpl_vars['IS_SELECT_BUTTON'] = new Smarty_variable($_tmp2, null, 0);?><button type="button" class="btn addButton  pull-right <?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> selectRelation <?php }?> "<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> data-moduleName=<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkName');?>
 <?php }?>data-url="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('actionURL');?>
"<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value!=true){?>name="addButton"<?php }?>data-name="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkField');?>
"><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==false){?><i class="icon-plus icon-white"></i><?php }?><?php if ($_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS']->value){?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_ACTION_LABELS']->value[$_smarty_tpl->tpl_vars['RECORD_ACTION_IDX']->value], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL'] = new Smarty_variable(vtranslate(('LBL_').(strtoupper($_smarty_tpl->tpl_vars['RECORD_ACTION']->value)),$_smarty_tpl->tpl_vars['MODULE_NAME']->value), null, 0);?><?php }?>&nbsp;<strong><?php echo $_smarty_tpl->tpl_vars['RECORD_ACTION_LABEL']->value;?>
</strong></button><?php $_smarty_tpl->tpl_vars['RECORD_ACTION_IDX'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_ACTION_IDX']->value+1, null, 0);?><?php } ?><input type="hidden" name="relatedModule" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['DETAIL_VIEW_WIDGET']->value->get('linkName');?>
" /><?php }?></div><div class="widget_contents"></div></div></div><?php }?><?php } ?></div></div><?php }} ?>