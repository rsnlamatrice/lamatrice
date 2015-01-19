<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 18:08:02
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Roles/EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:187146826254624272d00420-13371237%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '5a16742b6ecee79f52fc3111099a44b9d6053e3c' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Roles/EditView.tpl',
      1 => 1413615934,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '187146826254624272d00420-13371237',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'QUALIFIED_MODULE' => 0,
    'RECORD_MODEL' => 0,
    'RECORD_ID' => 0,
    'MODE' => 0,
    'PROFILE_ID' => 0,
    'HAS_PARENT' => 0,
    'PROFILE_DIRECTLY_RELATED_TO_ROLE' => 0,
    'ALL_PROFILES' => 0,
    'PROFILE' => 0,
    'ROLE_PROFILES' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54624272eec56',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54624272eec56')) {function content_54624272eec56($_smarty_tpl) {?>
<div class="container-fluid"><label class="themeTextColor font-x-x-large"><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULE']->value,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</label><hr><form name="EditRole" action="index.php" method="post" id="EditView" class="form-horizontal"><input type="hidden" name="module" value="Roles"><input type="hidden" name="action" value="Save"><input type="hidden" name="parent" value="Settings"><?php $_smarty_tpl->tpl_vars['RECORD_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getId(), null, 0);?><input type="hidden" name="record" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_ID']->value;?>
" /><input type="hidden" name="mode" value="<?php echo $_smarty_tpl->tpl_vars['MODE']->value;?>
"><input type="hidden" name="profile_directly_related_to_role_id" value="<?php echo $_smarty_tpl->tpl_vars['PROFILE_ID']->value;?>
" /><?php ob_start();?><?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getParent()){?><?php echo "true";?><?php }?><?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars['HAS_PARENT'] = new Smarty_variable($_tmp1, null, 0);?><?php if ($_smarty_tpl->tpl_vars['HAS_PARENT']->value){?><input type="hidden" name="parent_roleid" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getParent()->getId();?>
"><?php }?><div class="row-fluid"><div class="row-fluid"><label class="fieldLabel span3"><strong><?php echo vtranslate('LBL_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<span class="redColor">*</span>: </strong></label><input type="text" class="fieldValue span6" name="rolename" id="profilename" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
" data-validation-engine='validate[required]'  /></div><br><div class="row-fluid"><label class="fieldLabel span3"><strong><?php echo vtranslate('LBL_REPORTS_TO',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: </strong></label><div class="span8 fieldValue"><input type="hidden" name="parent_roleid" <?php if ($_smarty_tpl->tpl_vars['HAS_PARENT']->value){?>value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getParent()->getId();?>
"<?php }?>><input type="text" class="input-large" name="parent_roleid_display" <?php if ($_smarty_tpl->tpl_vars['HAS_PARENT']->value){?>value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getParent()->getName();?>
"<?php }?> readonly></div></div><br><div class="row-fluid"><label class="fieldLabel span3"><strong><?php echo vtranslate('LBL_CAN_ASSIGN_RECORDS_TO',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
: </strong></label><div class="row-fluid span8 fieldValue"><div class="span"><input type="radio" value="1"<?php if (!$_smarty_tpl->tpl_vars['RECORD_MODEL']->value->get('allowassignedrecordsto')){?> checked=""<?php }?> <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->get('allowassignedrecordsto')=='1'){?> checked="" <?php }?> name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;<span><?php echo vtranslate('LBL_ALL_USERS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><div class="span1">&nbsp;</div><div class="span"><input type="radio" value="2" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->get('allowassignedrecordsto')=='2'){?> checked="" <?php }?> name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;<span><?php echo vtranslate('LBL_USERS_WITH_SAME_OR_LOWER_LEVEL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><div class="span1">&nbsp;</div><div class="span"><input type="radio" value="3" <?php if ($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->get('allowassignedrecordsto')=='3'){?> checked="" <?php }?> name="allowassignedrecordsto" data-handler="new" class="alignTop"/>&nbsp;<span><?php echo vtranslate('LBL_USERS_WITH_LOWER_LEVEL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div></div></div><br><div class="row-fluid"><label class="fieldLabel span3"><strong><?php echo vtranslate('LBL_PRIVILEGES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
:</strong></label><div class="row-fluid span8 fieldValue"><div class="span"><input type="radio" value="1" <?php if ($_smarty_tpl->tpl_vars['PROFILE_DIRECTLY_RELATED_TO_ROLE']->value){?> checked="" <?php }?> name="profile_directly_related_to_role" data-handler="new" class="alignTop"/>&nbsp;<span><?php echo vtranslate('LBL_ASSIGN_NEW_PRIVILEGES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><div class="span1">&nbsp;</div><div class="span"><input type="radio" value="0" <?php if ($_smarty_tpl->tpl_vars['PROFILE_DIRECTLY_RELATED_TO_ROLE']->value==false){?> checked="" <?php }?> name="profile_directly_related_to_role" data-handler="existing" class="alignTop"/>&nbsp;<span><?php echo vtranslate('LBL_ASSIGN_EXISTING_PRIVILEGES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div></div></div><div class="row-fluid hide" data-content="new"><div class="fieldValue span12 contentsBackground padding1per"></div></div><br><div class="row-fluid hide" data-content="existing"><div class="fieldValue row-fluid"><?php $_smarty_tpl->tpl_vars["ROLE_PROFILES"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getProfiles(), null, 0);?><select class="select2" multiple="true" id="profilesList" name="profiles[]" data-placeholder="<?php echo vtranslate('LBL_CHOOSE_PROFILES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
" style="width: 800px"><?php  $_smarty_tpl->tpl_vars['PROFILE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PROFILE']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ALL_PROFILES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PROFILE']->key => $_smarty_tpl->tpl_vars['PROFILE']->value){
$_smarty_tpl->tpl_vars['PROFILE']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['PROFILE']->value->isDirectlyRelated()==false){?><option value="<?php echo $_smarty_tpl->tpl_vars['PROFILE']->value->getId();?>
" <?php if (isset($_smarty_tpl->tpl_vars['ROLE_PROFILES']->value[$_smarty_tpl->tpl_vars['PROFILE']->value->getId()])){?>selected="true"<?php }?>><?php echo $_smarty_tpl->tpl_vars['PROFILE']->value->getName();?>
</option><?php }?><?php } ?></select></div></div><br></div><div class="pull-right"><button class="btn btn-success" type="submit"><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button><a class="cancelLink" onclick="javascript:window.history.back();" type="reset">Cancel</a></div></form></div><?php }} ?>