<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 18:08:03
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Profiles/EditViewContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:26978119854624273dbdf84-27877546%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ebd74c31df12b4b80372ca96aa2d4c82dbfa773a' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Profiles/EditViewContents.tpl',
      1 => 1413615932,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '26978119854624273dbdf84-27877546',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SCRIPTS' => 0,
    'jsModel' => 0,
    'QUALIFIED_MODULE' => 0,
    'ALL_PROFILES' => 0,
    'PROFILE' => 0,
    'RECORD_ID' => 0,
    'RECORD_MODEL' => 0,
    'PROFILE_MODULES' => 0,
    'PROFILE_MODULE' => 0,
    'MODULE_NAME' => 0,
    'TABID' => 0,
    'BASIC_ACTION_ORDER' => 0,
    'ORDERID' => 0,
    'ALL_BASIC_ACTIONS' => 0,
    'ACTION_MODEL' => 0,
    'IS_RESTRICTED_MODULE' => 0,
    'ACTION_ID' => 0,
    'FIELD_MODEL' => 0,
    'FIELD_ID' => 0,
    'FIELD_LOCKED' => 0,
    'EVENT_MODULE' => 0,
    'ALL_UTILITY_ACTIONS' => 0,
    'ALL_UTILITY_ACTIONS_ARRAY' => 0,
    'index' => 0,
    'colspan' => 0,
    'ACTIONID' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5462427439f68',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5462427439f68')) {function content_5462427439f68($_smarty_tpl) {?>
<?php  $_smarty_tpl->tpl_vars['jsModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['jsModel']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SCRIPTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['jsModel']->key => $_smarty_tpl->tpl_vars['jsModel']->value){
$_smarty_tpl->tpl_vars['jsModel']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['jsModel']->key;
?><script type="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getType();?>
" src="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getSrc();?>
"></script><?php } ?><div class="row-fluid padding1per"><span class="span3"><h4><?php echo vtranslate('LBL_COPY_PRIVILEGES_FROM',"Settings:Roles");?>
</h4></span><span class="span5"><select class="select2" id="directProfilePriviligesSelect" style="min-width : 200px" data-placeholder="<?php echo vtranslate('LBL_CHOOSE_PROFILES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
"><option></option><?php  $_smarty_tpl->tpl_vars['PROFILE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PROFILE']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ALL_PROFILES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PROFILE']->key => $_smarty_tpl->tpl_vars['PROFILE']->value){
$_smarty_tpl->tpl_vars['PROFILE']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['PROFILE']->value->isDirectlyRelated()==false){?><option value="<?php echo $_smarty_tpl->tpl_vars['PROFILE']->value->getId();?>
" <?php if ($_smarty_tpl->tpl_vars['RECORD_ID']->value==$_smarty_tpl->tpl_vars['PROFILE']->value->getId()){?> selected="" <?php }?> ><?php echo $_smarty_tpl->tpl_vars['PROFILE']->value->getName();?>
</option><?php }?><?php } ?></select></span></div><div class="summaryWidgetContainer"><label class="checkbox"><input type="hidden" name="viewall" value="0" /><input type="checkbox" name="viewall" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasGlobalReadPermission()){?>checked="true"<?php }?> /><?php echo vtranslate('LBL_VIEW_ALL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<span style="margin-left:25px"><i class="icon-info-sign"></i><span style="margin-left:2px"><?php echo vtranslate('LBL_VIEW_ALL_DESC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></span></label><label class="checkbox"><input type="hidden" name="editall" value="0" /><input type="checkbox" name="editall" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasGlobalWritePermission()){?>checked="true"<?php }?> /><?php echo vtranslate('LBL_EDIT_ALL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<span style="margin-left:30px"><i class="icon-info-sign"></i><span style="margin-left:2px"><?php echo vtranslate('LBL_EDIT_ALL_DESC',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></span></label></div><table class="table table-striped table-bordered profilesEditView"><thead><tr class="blockHeader"><th width="30%" style="border-left: 1px solid #DDD !important;"><input checked="true" class="alignTop" type="checkbox" id="mainModulesCheckBox" />&nbsp;<?php echo vtranslate('LBL_MODULES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th><th width="14%" style="border-left: 1px solid #DDD !important;"><input type="checkbox" <?php if (!$_smarty_tpl->tpl_vars['RECORD_ID']->value){?> class="alignTop" checked="true" <?php }?> id="mainAction4CheckBox" />&nbsp;<?php echo vtranslate('LBL_VIEW_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th><th width="14%" style="border-left: 1px solid #DDD !important;"><input <?php if (!$_smarty_tpl->tpl_vars['RECORD_ID']->value){?> class="alignTop"  checked="true" <?php }?> type="checkbox" id="mainAction1CheckBox" />&nbsp;<?php echo vtranslate('LBL_EDIT_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th><th width="14%" style="border-left: 1px solid #DDD !important;"><input checked="true" class="alignTop" type="checkbox" id="mainAction2CheckBox" />&nbsp;<?php echo vtranslate('LBL_DELETE_PRVILIGE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th><th width="28%" style="border-left: 1px solid #DDD !important;" nowrap="nowrap"><?php echo vtranslate('LBL_FIELD_AND_TOOL_PRVILIGES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th></tr></thead><tbody><?php $_smarty_tpl->tpl_vars['PROFILE_MODULES'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModulePermissions(), null, 0);?><?php  $_smarty_tpl->tpl_vars['PROFILE_MODULE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PROFILE_MODULE']->_loop = false;
 $_smarty_tpl->tpl_vars['TABID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PROFILE_MODULES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PROFILE_MODULE']->key => $_smarty_tpl->tpl_vars['PROFILE_MODULE']->value){
$_smarty_tpl->tpl_vars['PROFILE_MODULE']->_loop = true;
 $_smarty_tpl->tpl_vars['TABID']->value = $_smarty_tpl->tpl_vars['PROFILE_MODULE']->key;
?><?php $_smarty_tpl->tpl_vars['MODULE_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value!='Events'){?><?php $_smarty_tpl->tpl_vars['IS_RESTRICTED_MODULE'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->isRestrictedModule($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName()), null, 0);?><tr><td><input class="modulesCheckBox alignTop" type="checkbox" name="permissions[<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
][is_permitted]" data-value="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
" data-module-state="" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModulePermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value)){?>checked="true"<?php }else{ ?> data-module-unchecked="true" <?php }?>> <?php echo vtranslate($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->get('label'),$_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getName());?>
</td><?php $_smarty_tpl->tpl_vars["BASIC_ACTION_ORDER"] = new Smarty_variable(array(2,0,1), null, 0);?><?php  $_smarty_tpl->tpl_vars['ORDERID'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ORDERID']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['BASIC_ACTION_ORDER']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ORDERID']->key => $_smarty_tpl->tpl_vars['ORDERID']->value){
$_smarty_tpl->tpl_vars['ORDERID']->_loop = true;
?><td style="border-left: 1px solid #DDD !important;"><?php $_smarty_tpl->tpl_vars["ACTION_MODEL"] = new Smarty_variable($_smarty_tpl->tpl_vars['ALL_BASIC_ACTIONS']->value[$_smarty_tpl->tpl_vars['ORDERID']->value], null, 0);?><?php $_smarty_tpl->tpl_vars['ACTION_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['ACTION_MODEL']->value->get('actionid'), null, 0);?><?php if (!$_smarty_tpl->tpl_vars['IS_RESTRICTED_MODULE']->value&&$_smarty_tpl->tpl_vars['ACTION_MODEL']->value->isModuleEnabled($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value)){?><input style="margin-left: 45% !important" class="action<?php echo $_smarty_tpl->tpl_vars['ACTION_ID']->value;?>
CheckBox" type="checkbox" name="permissions[<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
][actions][<?php echo $_smarty_tpl->tpl_vars['ACTION_ID']->value;?>
]" data-action-state="<?php echo $_smarty_tpl->tpl_vars['ACTION_MODEL']->value->getName();?>
" <?php if (empty($_smarty_tpl->tpl_vars['RECORD_ID']->value)){?> checked="true" <?php }?> <?php if ($_smarty_tpl->tpl_vars['RECORD_ID']->value){?> <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModuleActionPermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['ACTION_MODEL']->value)){?>checked="true"<?php }else{ ?> data-action<?php echo $_smarty_tpl->tpl_vars['ACTION_ID']->value;?>
-unchecked="true"<?php }?> <?php }?>></td><?php }?></td><?php } ?><td style="border-left: 1px solid #DDD !important;"><?php if ($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields()){?><div class="row-fluid"><span class="span4">&nbsp;</span><span class="span4"><button type="button" data-handlerfor="fields" data-togglehandler="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
-fields" class="btn btn-mini" style="padding-right: 20px; padding-left: 20px;"><i class="icon-chevron-down"></i></button></span></div><?php }?></td></tr><tr class="hide"><td colspan="6" class="row-fluid" style="padding-left: 5%;padding-right: 5%"><div class="row-fluid hide" data-togglecontent="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
-fields"><?php if ($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields()){?><div class="span12"><label class="themeTextColor font-x-large pull-left"><strong><?php echo vtranslate('LBL_FIELDS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value=='Calendar'){?> <?php echo vtranslate('LBL_OF',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 <?php echo vtranslate('LBL_TASKS',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }?></strong></label><div class="pull-right"><span class="mini-slider-control ui-slider" data-value="0"><a style="margin-top: 5px" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_INIVISIBLE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span>&nbsp;&nbsp;<span class="mini-slider-control ui-slider" data-value="1"><a style="margin-top: 5px" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_READ_ONLY',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span>&nbsp;&nbsp;<span class="mini-slider-control ui-slider" data-value="2"><a style="margin-top: 5px" class="ui-slider-handle"></a></span><span style="margin-left:15px;"><?php echo vtranslate('LBL_WRITE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><div class="clearfix"></div></div><table class="table table-bordered table-striped"><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PROFILE_MODULE']->value->getFields(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration=0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->last = $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration === $_smarty_tpl->tpl_vars['FIELD_MODEL']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['index']++;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['last'] = $_smarty_tpl->tpl_vars['FIELD_MODEL']->last;
?><?php $_smarty_tpl->tpl_vars["FIELD_ID"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getId(), null, 0);?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['index']%3==0){?><tr><?php }?><td style="border-left: 1px solid #DDD !important;"><?php $_smarty_tpl->tpl_vars["FIELD_LOCKED"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->isModuleFieldLocked($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value), null, 0);?><input type="hidden" name="permissions[<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
][fields][<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
]" data-range-input="<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModuleFieldPermissionValue($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value);?>
" readonly="true"><div class="mini-slider-control editViewMiniSlider pull-left" data-locked="<?php echo $_smarty_tpl->tpl_vars['FIELD_LOCKED']->value;?>
" data-range="<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
" data-value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModuleFieldPermissionValue($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value);?>
"></div><div class="pull-left"><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()){?><span class="redColor">*</span><?php }?> <?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</div></td><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['last']||($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['index']+1)%3==0){?></tr><?php }?><?php } ?></table><?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value=='Calendar'){?><?php $_smarty_tpl->tpl_vars['EVENT_MODULE'] = new Smarty_variable($_smarty_tpl->tpl_vars['PROFILE_MODULES']->value[16], null, 0);?><label class="themeTextColor font-x-large pull-left"><strong><?php echo vtranslate('LBL_FIELDS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <?php echo vtranslate('LBL_OF',$_smarty_tpl->tpl_vars['EVENT_MODULE']->value->getName());?>
 <?php echo vtranslate('LBL_EVENTS',$_smarty_tpl->tpl_vars['EVENT_MODULE']->value->getName());?>
</strong></label><table class="table table-bordered table-striped"><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['EVENT_MODULE']->value->getFields(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration=0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->last = $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration === $_smarty_tpl->tpl_vars['FIELD_MODEL']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['index']++;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["fields"]['last'] = $_smarty_tpl->tpl_vars['FIELD_MODEL']->last;
?><?php $_smarty_tpl->tpl_vars["FIELD_ID"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getId(), null, 0);?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['index']%3==0){?><tr><?php }?><td style="border-left: 1px solid #DDD !important;"><?php $_smarty_tpl->tpl_vars["FIELD_LOCKED"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->isModuleFieldLocked($_smarty_tpl->tpl_vars['EVENT_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value), null, 0);?><input type="hidden" name="permissions[16][fields][<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
]" data-range-input="<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModuleFieldPermissionValue($_smarty_tpl->tpl_vars['EVENT_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value);?>
" readonly="true"><div class="mini-slider-control editViewMiniSlider pull-left" data-locked="<?php echo $_smarty_tpl->tpl_vars['FIELD_LOCKED']->value;?>
" data-range="<?php echo $_smarty_tpl->tpl_vars['FIELD_ID']->value;?>
" data-value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getModuleFieldPermissionValue($_smarty_tpl->tpl_vars['EVENT_MODULE']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value);?>
"></div><div class="pull-left"><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()){?><span class="redColor">*</span><?php }?> <?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</div></td><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['last']||($_smarty_tpl->getVariable('smarty')->value['foreach']['fields']['index']+1)%3==0){?></tr><?php }?><?php } ?></table><?php }?></div></ul><?php }?></div></td></tr><tr class="hide"><td colspan="6" class="row-fluid" style="padding-left: 5%;padding-right: 5%;background-image: none !important;"><div class="row-fluid hide" data-togglecontent="<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
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
?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']%3==0){?><tr><?php }?><?php $_smarty_tpl->tpl_vars['ACTIONID'] = new Smarty_variable($_smarty_tpl->tpl_vars['ACTION_MODEL']->value->get('actionid'), null, 0);?><td <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['last']&&(($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3!=0)){?><?php $_smarty_tpl->tpl_vars["index"] = new Smarty_variable(($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3, null, 0);?><?php $_smarty_tpl->tpl_vars["colspan"] = new Smarty_variable(4-$_smarty_tpl->tpl_vars['index']->value, null, 0);?>colspan="<?php echo $_smarty_tpl->tpl_vars['colspan']->value;?>
"<?php }else{ ?>style="border-right: 1px solid #DDD !important;"<?php }?>><input type="checkbox" class="alignTop" <?php if (empty($_smarty_tpl->tpl_vars['RECORD_ID']->value)){?> checked="true" <?php }?> name="permissions[<?php echo $_smarty_tpl->tpl_vars['TABID']->value;?>
][actions][<?php echo $_smarty_tpl->tpl_vars['ACTIONID']->value;?>
]" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->hasModuleActionPermission($_smarty_tpl->tpl_vars['PROFILE_MODULE']->value,$_smarty_tpl->tpl_vars['ACTIONID']->value)){?>checked="true"<?php }?>> <?php echo $_smarty_tpl->tpl_vars['ACTION_MODEL']->value->getName();?>
</td><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['last']||($_smarty_tpl->getVariable('smarty')->value['foreach']['actions']['index']+1)%3==0){?></div><?php }?><?php } ?></table></div></td></tr><?php }?><?php } ?></tbody></table>
<?php }} ?>