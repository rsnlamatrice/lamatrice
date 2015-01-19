<?php /* Smarty version Smarty-3.1.7, created on 2014-12-10 15:33:34
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Calendar/QuickCreate.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1984020020548859be31cd28-59913724%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bb34d1255d7fa0305d7048ec81ef44239cd534a3' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Calendar/QuickCreate.tpl',
      1 => 1413619426,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1984020020548859be31cd28-59913724',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SCRIPTS' => 0,
    'jsModel' => 0,
    'MODULE' => 0,
    'PICKIST_DEPENDENCY_DATASOURCE' => 0,
    'USER_MODEL' => 0,
    'RAND_NUMBER' => 0,
    'QUICK_CREATE_CONTENTS' => 0,
    'MODULE_NAME' => 0,
    'RECORD_STRUCTURE' => 0,
    'FIELD_MODEL' => 0,
    'refrenceList' => 0,
    'COUNTER' => 0,
    'isReferenceField' => 0,
    'refrenceListCount' => 0,
    'value' => 0,
    'CALENDAR_MODULE_MODEL' => 0,
    'EDIT_VIEW_URL' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_548859be61053',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_548859be61053')) {function content_548859be61053($_smarty_tpl) {?>
<?php  $_smarty_tpl->tpl_vars['jsModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['jsModel']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SCRIPTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['jsModel']->key => $_smarty_tpl->tpl_vars['jsModel']->value){
$_smarty_tpl->tpl_vars['jsModel']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['jsModel']->key;
?><script type="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getType();?>
" src="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getSrc();?>
"></script><?php } ?><div class="modelContainer"><div class="modal-header contentsBackground"><button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="<?php echo vtranslate('LBL_CLOSE');?>
">&times;</button><h3><?php echo vtranslate('LBL_QUICK_CREATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 <?php echo vtranslate('LBL_EVENT_OR_TASK',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><form class="form-horizontal recordEditView" id="quickCreate" name="QuickCreate" method="post" action="index.php"><?php if (!empty($_smarty_tpl->tpl_vars['PICKIST_DEPENDENCY_DATASOURCE']->value)){?><input type="hidden" name="picklistDependency" value='<?php echo Vtiger_Util_Helper::toSafeHTML($_smarty_tpl->tpl_vars['PICKIST_DEPENDENCY_DATASOURCE']->value);?>
' /><?php }?><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
"><input type="hidden" name="action" value="SaveAjax"><input type="hidden" name="defaultCallDuration" value="<?php echo $_smarty_tpl->tpl_vars['USER_MODEL']->value->get('callduration');?>
" /><input type="hidden" name="defaultOtherEventDuration" value="<?php echo $_smarty_tpl->tpl_vars['USER_MODEL']->value->get('othereventduration');?>
" /><!-- Random number is used to make specific tab is opened --><?php $_smarty_tpl->tpl_vars["RAND_NUMBER"] = new Smarty_variable(rand(), null, 0);?><div class="modal-body tabbable" style="padding:0px"><ul class="nav nav-pills" style="margin-bottom:0px;padding-left:5px"><li class="active"><a href="javascript:void(0);" data-target=".EventsQuikcCreateContents_<?php echo $_smarty_tpl->tpl_vars['RAND_NUMBER']->value;?>
" data-toggle="tab" data-tab-name="Event"><?php echo vtranslate('LBL_EVENT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></li><li class=""><a href="javascript:void(0);" data-target=".CalendarQuikcCreateContents_<?php echo $_smarty_tpl->tpl_vars['RAND_NUMBER']->value;?>
 " data-toggle="tab" data-tab-name="Task"><?php echo vtranslate('LBL_TASK',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></li></ul><div class="tab-content overflowVisible"><?php $_smarty_tpl->tpl_vars["CALENDAR_MODULE_MODEL"] = new Smarty_variable($_smarty_tpl->tpl_vars['QUICK_CREATE_CONTENTS']->value['Calendar']['moduleModel'], null, 0);?><?php  $_smarty_tpl->tpl_vars['MODULE_DETAILS'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['MODULE_DETAILS']->_loop = false;
 $_smarty_tpl->tpl_vars['MODULE_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['QUICK_CREATE_CONTENTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['MODULE_DETAILS']->key => $_smarty_tpl->tpl_vars['MODULE_DETAILS']->value){
$_smarty_tpl->tpl_vars['MODULE_DETAILS']->_loop = true;
 $_smarty_tpl->tpl_vars['MODULE_NAME']->value = $_smarty_tpl->tpl_vars['MODULE_DETAILS']->key;
?><div class="<?php echo $_smarty_tpl->tpl_vars['MODULE_NAME']->value;?>
QuikcCreateContents_<?php echo $_smarty_tpl->tpl_vars['RAND_NUMBER']->value;?>
 tab-pane <?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value=='Events'){?> active in <?php }?>fade"><?php $_smarty_tpl->tpl_vars["RECORD_STRUCTURE_MODEL"] = new Smarty_variable($_smarty_tpl->tpl_vars['QUICK_CREATE_CONTENTS']->value[$_smarty_tpl->tpl_vars['MODULE_NAME']->value]['recordStructureModel'], null, 0);?><?php $_smarty_tpl->tpl_vars["RECORD_STRUCTURE"] = new Smarty_variable($_smarty_tpl->tpl_vars['QUICK_CREATE_CONTENTS']->value[$_smarty_tpl->tpl_vars['MODULE_NAME']->value]['recordStructure'], null, 0);?><?php $_smarty_tpl->tpl_vars["MODULE_MODEL"] = new Smarty_variable($_smarty_tpl->tpl_vars['QUICK_CREATE_CONTENTS']->value[$_smarty_tpl->tpl_vars['MODULE_NAME']->value]['moduleModel'], null, 0);?><div class="quickCreateContent"><div style='margin:5px'><table class="massEditTable table table-bordered"><tr><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['RECORD_STRUCTURE']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->last = $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration === $_smarty_tpl->tpl_vars['FIELD_MODEL']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['blockfields']['last'] = $_smarty_tpl->tpl_vars['FIELD_MODEL']->last;
?><?php $_smarty_tpl->tpl_vars["isReferenceField"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getFieldDataType(), null, 0);?><?php $_smarty_tpl->tpl_vars["refrenceList"] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getReferenceList(), null, 0);?><?php $_smarty_tpl->tpl_vars["refrenceListCount"] = new Smarty_variable(count($_smarty_tpl->tpl_vars['refrenceList']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['COUNTER']->value==2){?></tr><tr><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable(1, null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable($_smarty_tpl->tpl_vars['COUNTER']->value+1, null, 0);?><?php }?><td class="fieldLabel alignMiddle"><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> <span class="redColor">*</span> <?php }?><?php ob_start();?><?php echo $_smarty_tpl->tpl_vars['isReferenceField']->value;?>
<?php $_tmp1=ob_get_clean();?><?php if ($_tmp1=="reference"){?><?php if ($_smarty_tpl->tpl_vars['refrenceListCount']->value>1){?><select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList"><optgroup><?php  $_smarty_tpl->tpl_vars['value'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['value']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['refrenceList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['value']->key => $_smarty_tpl->tpl_vars['value']->value){
$_smarty_tpl->tpl_vars['value']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['value']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['value']->value;?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['value']->value,$_smarty_tpl->tpl_vars['value']->value);?>
</option><?php } ?></optgroup></select><?php }else{ ?><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }?><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }else{ ?><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }?></td><td class="fieldValue" <?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('uitype')=='19'){?> colspan="3" <?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable($_smarty_tpl->tpl_vars['COUNTER']->value+1, null, 0);?> <?php }?>><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getUITypeModel()->getTemplateName(),$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</td><?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value=='Events'&&$_smarty_tpl->getVariable('smarty')->value['foreach']['blockfields']['last']){?><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('uitypes/FollowUp.tpl',$_smarty_tpl->tpl_vars['MODULE_NAME']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('MODULE'=>$_smarty_tpl->tpl_vars['MODULE_NAME']->value), 0);?>
<?php }?><?php } ?></tr><?php if ($_REQUEST['parent_id']!=''){?><input type="hidden" name="parent_id" value="<?php echo $_REQUEST['parent_id'];?>
" /><?php }elseif($_REQUEST['contact_id']!=''){?><input type="hidden" name="contact_id" value="<?php echo $_REQUEST['contact_id'];?>
" /><?php }?></table></div></div><div class="modal-footer quickCreateActions"><?php if ($_smarty_tpl->tpl_vars['MODULE_NAME']->value=='Calendar'){?><?php $_smarty_tpl->tpl_vars["EDIT_VIEW_URL"] = new Smarty_variable($_smarty_tpl->tpl_vars['CALENDAR_MODULE_MODEL']->value->getCreateTaskRecordUrl(), null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars["EDIT_VIEW_URL"] = new Smarty_variable($_smarty_tpl->tpl_vars['CALENDAR_MODULE_MODEL']->value->getCreateEventRecordUrl(), null, 0);?><?php }?><a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a><button class="btn btn-success" type="submit"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><button class="btn" id="goToFullForm" type="button" data-edit-view-url="<?php echo $_smarty_tpl->tpl_vars['EDIT_VIEW_URL']->value;?>
"><strong><?php echo vtranslate('LBL_GO_TO_FULL_FORM',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></div><?php } ?></div></div></form></div><?php }} ?>