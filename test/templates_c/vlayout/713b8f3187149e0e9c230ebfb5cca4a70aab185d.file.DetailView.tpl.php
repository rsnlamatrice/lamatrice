<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 15:53:38
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Profiles/DetailView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:183136996546243c2bf4ca7-50891176%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '713b8f3187149e0e9c230ebfb5cca4a70aab185d' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Profiles/DetailView.tpl',
      1 => 1413619532,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '183136996546243c2bf4ca7-50891176',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_546243c306ac0',
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
    'RECORD_MODEL' => 0,
    'ENABLE_IMAGE_PATH' => 0,
    'DISABLE_IMAGE_PATH' => 0,
    'PROFILE_MODULE' => 0,
    'BASIC_ACTION_ORDER' => 0,
    'ACTION_ID' => 0,
    'ALL_BASIC_ACTIONS' => 0,
    'IS_RESTRICTED_MODULE' => 0,
    'ACTION_MODEL' => 0,
    'TABID' => 0,
    'FIELD_MODEL' => 0,
    'COUNTER' => 0,
    'DATA_VALUE' => 0,
    'ALL_UTILITY_ACTIONS' => 0,
    'ALL_UTILITY_ACTIONS_ARRAY' => 0,
    'index' => 0,
    'colspan' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_546243c306ac0')) {function content_546243c306ac0($_smarty_tpl) {?>
<div class="container-fluid"><br><h3><?php echo vtranslate('LBL_PROFILE_VIEW',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h3><button class="btn pull-right" type="button" onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getEditViewUrl();?>
"'><?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><div class="clearfix"></div><hr><div class="profileDetailView"><div class="row-fluid"><div class="row-fluid"><label class="fieldLabel span2 muted"><span class="redColor">*</span><?php echo vtranslate('LBL_PROFILE_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: </label><span class="fieldValue span6" name="profilename" id="profilename" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
"><strong><?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
</strong></span></div><br><div class="row-fluid"><label class="fieldLabel span2 muted"><?php echo vtranslate('LBL_DESCRIPTION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
:</strong></label><span class="fieldValue span8" name="description" id="description"><strong><?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getDescription();?>
</strong></span></div><br><?php ob_start();?><?php echo vimage_path('Enable.png');?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["ENABLE_IMAGE_PATH"] = new Smarty_variable($_tmp1, null, 0);?><?php ob_start();?><?php echo vimage_path('Disable.png');?>
<?php $_tmp2=ob_get_clean();?><?php $_smarty_tpl->tpl_vars["DISABLE_IMAGE_PATH"] = new Smarty_variable($_tmp2, null, 0);?><div class="summaryWidgetContainer"><div><img class="alignMiddle" src="<?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasGlobalReadPermission()){?><?php echo $_smarty_tpl->tpl_vars['ENABLE_IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISABLE_IMAGE_PATH']->value;?>
<?php }?>" />&nbsp;<?php echo vtranslate('LBL_VIEW_ALL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<span style="margin-left:25px"><i class="icon-info-sign"></i><span style="margin-left:2px"><?php echo vtranslate('LBL_VIEW_ALL_DESC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></span></div><div  style="margin-top: 5px;"><img class="alignMiddle" src="<?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasGlobalWritePermission()){?><?php echo $_smarty_tpl->tpl_vars['ENABLE_IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISABLE_IMAGE_PATH']->value;?>
<?php }?>" />&nbsp;<?php echo vtranslate('LBL_EDIT_ALL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<span style="margin-left:30px"><i class="icon-info-sign"></i><span style="margin-left:2px"><?php echo vtranslate('LBL_EDIT_ALL_DESC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></span></div></div><div class="row-fluid"><table class="table table-striped table-bordered"><thead><tr><th width="27%" style="border-left: 1px solid #DDD !important;"><?php echo vtranslate('LBL_MODULES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th><th width="11%" style="border-left: 1px solid #DDD !important;"><span class="horizontalAlignCenter">&nbsp;<?php echo vtranslate('LBL_VIEW_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></th><th width="12%" style="border-left: 1px solid #DDD !important;"><span class="horizontalAlignCenter" >&nbsp;<?php echo vtranslate('LBL_EDIT_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></th><th width="11%" style="border-left: 1px solid #DDD !important;"><span class="horizontalAlignCenter" ><?php echo vtranslate('LBL_DELETE_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></th><th width="39%" style="border-left: 1px solid #DDD !important;" nowrap="nowrap"><?php echo vtranslate('LBL_FIELD_AND_TOOL_PRVILIGES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th></tr></thead><tbody><?php  $_smarty_tpl->tpl_vars['PROFILE_MODULE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PROFILE_MODULE']->_loop = false;
 $_smarty_tpl->tpl_vars['TABID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModulePermissions(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PROFILE_MODULE']->key => $_smarty_tpl->tpl_vars['PROFILE_MODULE']->value){
$_smarty_tpl->tpl_vars['PROFILE_MODULE']->_loop = true;
 $_smarty_tpl->tpl_vars['TABID']->value = $_smarty_tpl->tpl_vars['PROFILE_MODULE']->key;
?><?php $_smarty_tpl->tpl_vars['IS_RESTRICTED_MODULE'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->isRestrictedModule($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName()), null, 0);?><tr><td><img src="<?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModulePermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value)){?><?php echo $_smarty_tpl->tpl_vars['ENABLE_IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISABLE_IMAGE_PATH']->value;?>
<?php }?>" class="alignMiddle" />&nbsp;<?php echo vtranslate($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->get('label'),$_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName());?>
</td><?php $_smarty_tpl->tpl_vars["BASIC_ACTION_ORDER"] = new Smarty_variable(array(2,0,1), null, 0);?><?php  $_smarty_tpl->tpl_vars['ACTION_ID'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ACTION_ID']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['BASIC_ACTION_ORDER']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ACTION_ID']->key => $_smarty_tpl->tpl_vars['ACTION_ID']->value){
$_smarty_tpl->tpl_vars['ACTION_ID']->_loop = true;
?><td style="border-left: 1px solid #DDD !important;"><?php $_smarty_tpl->tpl_vars["ACTION_MODEL"] = new Smarty_variable($_smarty_tpl->tpl_vars['ALL_BASIC_ACTIONS']->value[$_smarty_tpl->tpl_vars['ACTION_ID']->value], null, 0);?><?php if (!$_smarty_tpl->tpl_vars['IS_RESTRICTED_MODULE']->value&&$_smarty_tpl->tpl_vars['ACTION_MODEL']->value->isModuleEnabled($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value)){?><img style="margin-left: 40%" class="alignMiddle" src="<?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModuleActionPermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['ACTION_MODEL']->value)){?><?php echo $_smarty_tpl->tpl_vars['ENABLE_IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISABLE_IMAGE_PATH']->value;?>
<?php }?>" /><?php }?></td><?php } ?><td style="border-left: 1px solid #DDD !important;"><?php if ($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields()){?><div class="row-fluid"><span class="span4">&nbsp;</span><span class="span4"><button type="button" data-handlerfor="fields" data-togglehandler="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
-fields" class="btn btn-mini" style="padding-right: 20px; padding-left: 20px;"><i class="icon-chevron-down"></i></button></span></div><?php }?></td></tr><tr class="hide"><td colspan="6" class="row-fluid" style="padding-left: 5%;padding-right: 5%"><div class="row-fluid hide" data-togglecontent="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
-fields"><?php if ($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields()){?><div class="span12"><label class="themeTextColor font-x-large pull-left"><strong><?php echo vtranslate('LBL_FIELDS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></label><div class="pull-right"><span class="mini-slider-control ui-slider" data-value="0"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_INIVISIBLE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span>&nbsp;<span class="mini-slider-control ui-slider" data-value="1"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_READ_ONLY',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span>&nbsp;<span class="mini-slider-control ui-slider" data-value="2"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_WRITE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span>&nbsp;</div><div class="clearfix"></div></div><table class="table table-bordered table-striped"><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->last = $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration === $_smarty_tpl->tpl_vars['FIELD_MODEL']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['last'] = $_smarty_tpl->tpl_vars['FIELD_MODEL']->last;
?><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isActiveField()){?><?php $_smarty_tpl->tpl_vars["FIELD_ID"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getId(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['COUNTER']->value%3==0){?><tr><?php }?><td><?php $_smarty_tpl->tpl_vars["DATA_VALUE"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModuleFieldPermissionValue($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['DATA_VALUE']->value==0){?><span class="mini-slider-control ui-slider" data-value="0"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><?php }elseif($_smarty_tpl->tpl_vars['DATA_VALUE']->value==1){?><span class="mini-slider-control ui-slider" data-value="1"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><?php }else{ ?><span class="mini-slider-control ui-slider" data-value="2"><a style="margin-top: 4px;" class="ui-slider-handle"></a></span><?php }?><span style="margin-left: 15px"><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()){?><span class="redColor">*</span><?php }?> <?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName());?>
</span></td><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['last']||($_smarty_tpl->tpl_vars['COUNTER']->value+1)%3==0){?></tr><?php }?><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable($_smarty_tpl->tpl_vars['COUNTER']->value+1, null, 0);?><?php }?><?php } ?></table></div></ul><?php }?></div></td></tr><tr class="hide"><td colspan="6" class="row-fluid" style="padding-left: 5%;padding-right: 5%"><div class="row-fluid hide" data-togglecontent="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
-fields"><div class="span12"><label class="themeTextColor font-x-large pull-left"><strong><?php echo vtranslate('LBL_TOOLS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></label></div><table class="table table-bordered table-striped"><?php $_smarty_tpl->tpl_vars['UTILITY_ACTION_COUNT'] = new Smarty_variable(0, null, 0);?><?php $_smarty_tpl->tpl_vars["ALL_UTILITY_ACTIONS_ARRAY"] = new Smarty_variable(array(), null, 0);?><?php  $_smarty_tpl->tpl_vars['ACTION_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ACTION_MODEL']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ALL_UTILITY_ACTIONS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ACTION_MODEL']->key => $_smarty_tpl->tpl_vars['ACTION_MODEL']->value){
$_smarty_tpl->tpl_vars['ACTION_MODEL']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['ACTION_MODEL']->value->isModuleEnabled($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value)){?><?php $_smarty_tpl->tpl_vars["testArray"] = new Smarty_variable(array_push($_smarty_tpl->tpl_vars['ALL_UTILITY_ACTIONS_ARRAY']->value,$_smarty_tpl->tpl_vars['ACTION_MODEL']->value), null, 0);?><?php }?><?php } ?><?php  $_smarty_tpl->tpl_vars['ACTION_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ACTION_MODEL']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ALL_UTILITY_ACTIONS_ARRAY']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['ACTION_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['ACTION_MODEL']->iteration=0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["actions"]['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['ACTION_MODEL']->key => $_smarty_tpl->tpl_vars['ACTION_MODEL']->value){
$_smarty_tpl->tpl_vars['ACTION_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['ACTION_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['ACTION_MODEL']->last = $_smarty_tpl->tpl_vars['ACTION_MODEL']->iteration === $_smarty_tpl->tpl_vars['ACTION_MODEL']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["actions"]['index']++;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["actions"]['last'] = $_smarty_tpl->tpl_vars['ACTION_MODEL']->last;
?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']%3==0){?><tr><?php }?><?php $_smarty_tpl->tpl_vars['ACTION_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['ACTION_MODEL']->value->get('actionid'), null, 0);?><td <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['last']&&(($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3!=0)){?><?php $_smarty_tpl->tpl_vars["index"] = new Smarty_variable(($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3, null, 0);?><?php $_smarty_tpl->tpl_vars["colspan"] = new Smarty_variable(4-$_smarty_tpl->tpl_vars['index']->value, null, 0);?>colspan="<?php echo $_smarty_tpl->tpl_vars['colspan']->value;?>
"<?php }?>><img class="alignMiddle" src="<?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModuleActionPermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['ACTION_ID']->value)){?><?php echo $_smarty_tpl->tpl_vars['ENABLE_IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISABLE_IMAGE_PATH']->value;?>
<?php }?>" />&nbsp;&nbsp;<?php echo $_smarty_tpl->tpl_vars['ACTION_MODEL']->value->getName();?>
</td><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['last']||($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3==0){?></div><?php }?><?php } ?></table></div></td></tr><?php } ?></tbody></table></div></div></div></div><?php }} ?>