<?php /* Smarty version Smarty-3.1.7, created on 2014-12-05 11:49:00
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Groups/EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:211224991654624455532565-90403309%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '15d63d0bc95b2250101d3be9f3a1e46f5d10c584' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Groups/EditView.tpl',
      1 => 1413619506,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '211224991654624455532565-90403309',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5462445570224',
  'variables' => 
  array (
    'RECORD_MODEL' => 0,
    'MODE' => 0,
    'QUALIFIED_MODULE' => 0,
    'MODULE' => 0,
    'MEMBER_GROUPS' => 0,
    'GROUP_LABEL' => 0,
    'ALL_GROUP_MEMBERS' => 0,
    'MEMBER' => 0,
    'GROUP_MEMBERS' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5462445570224')) {function content_5462445570224($_smarty_tpl) {?>
<div class="editViewContainer container-fluid"><form name="EditGroup" action="index.php" method="post" id="EditView" class="form-horizontal"><input type="hidden" name="module" value="Groups"><input type="hidden" name="action" value="Save"><input type="hidden" name="parent" value="Settings"><input type="hidden" name="record" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getId();?>
"><input type="hidden" name="mode" value="<?php echo $_smarty_tpl->tpl_vars['MODE']->value;?>
"><div class="contentHeader row-fluid"><h3><?php if (!empty($_smarty_tpl->tpl_vars['MODE']->value)){?><?php echo vtranslate('LBL_EDITING',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <?php echo vtranslate(('SINGLE_').($_smarty_tpl->tpl_vars['MODULE']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 - <?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
<?php }else{ ?><?php echo vtranslate('LBL_CREATING_NEW',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
 <?php echo vtranslate(('SINGLE_').($_smarty_tpl->tpl_vars['MODULE']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }?></h3><hr></div><div class="control-group"><span class="control-label"><span class="redColor">*</span> <?php echo vtranslate('LBL_GROUP_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span><div class="controls"><input class="input-large" name="groupname" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName();?>
" data-validation-engine="validate[required]"></div></div><div class="control-group"><span class="control-label"><?php echo vtranslate('LBL_DESCRIPTION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span><div class="controls"><input class="input-large" name="description" id="description" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getDescription();?>
" /></div></div><div class="control-group"><span class="control-label"><?php echo vtranslate('LBL_GROUP_MEMBERS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span><div class="controls"><div class="row-fluid"><span class="span6"><?php $_smarty_tpl->tpl_vars["GROUP_MEMBERS"] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getMembers(), null, 0);?><select id="memberList" class="row-fluid members" multiple="true" name="members[]" data-placeholder="<?php echo vtranslate('LBL_ADD_USERS_ROLES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
" data-validation-engine="validate[required]"><?php  $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->_loop = false;
 $_smarty_tpl->tpl_vars['GROUP_LABEL'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['MEMBER_GROUPS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->key => $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->value){
$_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->_loop = true;
 $_smarty_tpl->tpl_vars['GROUP_LABEL']->value = $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->key;
?><optgroup label="<?php echo $_smarty_tpl->tpl_vars['GROUP_LABEL']->value;?>
"><?php  $_smarty_tpl->tpl_vars['MEMBER'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['MEMBER']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ALL_GROUP_MEMBERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['MEMBER']->key => $_smarty_tpl->tpl_vars['MEMBER']->value){
$_smarty_tpl->tpl_vars['MEMBER']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['MEMBER']->value->getName()!=$_smarty_tpl->tpl_vars['RECORD_MODEL']->value->getName()){?><option value="<?php echo $_smarty_tpl->tpl_vars['MEMBER']->value->getId();?>
"  data-member-type="<?php echo $_smarty_tpl->tpl_vars['GROUP_LABEL']->value;?>
" <?php if (isset($_smarty_tpl->tpl_vars['GROUP_MEMBERS']->value[$_smarty_tpl->tpl_vars['GROUP_LABEL']->value][$_smarty_tpl->tpl_vars['MEMBER']->value->getId()])){?>selected="true"<?php }?>><?php echo $_smarty_tpl->tpl_vars['MEMBER']->value->getName();?>
</option><?php }?><?php } ?></optgroup><?php } ?></select></span><span class="span3"><span class="pull-right groupMembersColors"><ul class="liStyleNone"><li class="Users padding5per textAlignCenter"><strong><?php echo vtranslate('LBL_USERS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></li><li class="Groups padding5per textAlignCenter"><strong><?php echo vtranslate('LBL_GROUPS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></li><li class="Roles padding5per textAlignCenter"><strong><?php echo vtranslate('LBL_ROLES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></li><li class="RoleAndSubordinates padding5per textAlignCenter"><strong><?php echo vtranslate('LBL_ROLEANDSUBORDINATE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></li></ul></span></span></div></div></div><div class="row-fluid"><div class="span5"><span class="pull-right"><button class="btn btn-success" type="submit"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></button><a class="cancelLink" type="reset" onclick="javascript:window.history.back();"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a></span></div></div></form></div><?php }} ?>