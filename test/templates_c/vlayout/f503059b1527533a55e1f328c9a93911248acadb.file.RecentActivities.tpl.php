<?php /* Smarty version Smarty-3.1.7, created on 2014-12-10 17:28:49
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/RecentActivities.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1237742938544e5005ee4264-10510968%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f503059b1527533a55e1f328c9a93911248acadb' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/RecentActivities.tpl',
      1 => 1418228748,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1237742938544e5005ee4264-10510968',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_544e5006314d5',
  'variables' => 
  array (
    'RECENT_ACTIVITIES' => 0,
    'RECENT_ACTIVITY' => 0,
    'MODULE_NAME' => 0,
    'FIELDMODEL' => 0,
    'URELATION' => 0,
    'RELATED_RECORD' => 0,
    'PAGING_MODEL' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e5006314d5')) {function content_544e5006314d5($_smarty_tpl) {?>
<div class="recentActivitiesContainer"><div><?php if (!empty($_smarty_tpl->tpl_vars['RECENT_ACTIVITIES']->value)){?><ul class="unstyled"><?php  $_smarty_tpl->tpl_vars['RECENT_ACTIVITY'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECENT_ACTIVITIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->key => $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value){
$_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->isCreate()){?><li><div><span><strong><?php echo $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getModifiedBy()->getName();?>
</strong> <?php echo vtranslate('LBL_CREATED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getParent()->get('createdtime'));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getParent()->get('createdtime'));?>
</small></p></span></div></li><?php }elseif($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->isUpdate()){?><li><div><span><strong><?php echo $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getModifiedBy()->getDisplayName();?>
</strong> <?php echo vtranslate('LBL_UPDATED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getActivityTime());?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getActivityTime());?>
</small></p></span></div><?php  $_smarty_tpl->tpl_vars['FIELDMODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELDMODEL']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getFieldInstances(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FIELDMODEL']->key => $_smarty_tpl->tpl_vars['FIELDMODEL']->value){
$_smarty_tpl->tpl_vars['FIELDMODEL']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['FIELDMODEL']->value&&$_smarty_tpl->tpl_vars['FIELDMODEL']->value->getFieldInstance()&&$_smarty_tpl->tpl_vars['FIELDMODEL']->value->getFieldInstance()->isViewableInDetailView()){?><div class='font-x-small updateInfoContainer'><i><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELDMODEL']->value->getName(),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</i> :&nbsp;<?php if ($_smarty_tpl->tpl_vars['FIELDMODEL']->value->get('prevalue')!=''){?><?php echo $_smarty_tpl->tpl_vars['FIELDMODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELDMODEL']->value->get('prevalue'));?>
&nbsp;<?php echo vtranslate('LBL_TO',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
&nbsp;<?php }else{ ?><?php }?><b><?php echo $_smarty_tpl->tpl_vars['FIELDMODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELDMODEL']->value->get('postvalue'));?>
</b></div><?php }?><?php } ?></li><?php }elseif($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->isRelationLink()){?><li><div class="row-fluid"><?php $_smarty_tpl->tpl_vars['URELATION'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getRelationInstance(), null, 0);?><?php $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_variable($_smarty_tpl->tpl_vars['URELATION']->value->getUnLinkedRecord(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['RELATED_RECORD']->value){?><span><?php echo vtranslate($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getModuleName(),$_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getModuleName());?>
 <?php echo vtranslate('LBL_ADDED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<strong><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getName();?>
</strong></span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('createdtime'));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('createdtime'));?>
</small></p></span><?php }else{ ?><span><?php echo vtranslate($_smarty_tpl->tpl_vars['URELATION']->value->get('targetmodule'),$_smarty_tpl->tpl_vars['URELATION']->value->get('targetmodule'));?>
 <?php echo vtranslate('LBL_DELETED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 <strong></strong></span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['URELATION']->value->get('changedon'));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['URELATION']->value->get('changedon'));?>
</small></p></span><?php }?></div></li><?php }elseif($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->isRelationUnLink()){?><li><div class="row-fluid"><?php $_smarty_tpl->tpl_vars['URELATION'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->getRelationInstance(), null, 0);?><?php $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_variable($_smarty_tpl->tpl_vars['URELATION']->value->getUnLinkedRecord(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['RELATED_RECORD']->value){?><span><?php echo vtranslate($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getModuleName(),$_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getModuleName());?>
 <?php echo vtranslate('LBL_DELETED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 <strong><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getName();?>
</strong></span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('modifiedtime'));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('modifiedtime'));?>
</small></p></span><?php }else{ ?><span><?php echo vtranslate($_smarty_tpl->tpl_vars['URELATION']->value->get('targetmodule'),$_smarty_tpl->tpl_vars['URELATION']->value->get('targetmodule'));?>
 <?php echo vtranslate('LBL_DELETED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 <strong></strong></span><span class="pull-right"><p class="muted"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['URELATION']->value->get('changedon'));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['URELATION']->value->get('changedon'));?>
</small></p></span><?php }?></div></li><?php }elseif($_smarty_tpl->tpl_vars['RECENT_ACTIVITY']->value->isRestore()){?><li></li><?php }?><?php } ?></ul><?php }else{ ?><div class="summaryWidgetContainer"><p class="textAlignCenter"><?php echo vtranslate('LBL_NO_RECENT_UPDATES');?>
</p></div><?php }?></div><?php if ($_smarty_tpl->tpl_vars['PAGING_MODEL']->value->isNextPageExists()){?><div class="row-fluid"><div class="pull-right"><a href="javascript:void(0)" class="moreRecentUpdates"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
..</a></div></div><?php }?><span class="clearfix"></span></div><?php }} ?>