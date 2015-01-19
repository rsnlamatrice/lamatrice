<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 11:58:41
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Picklist/PickListValueDetail.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2131163102545ca4b4160e94-07750754%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bed0449aab7c29d5eb863ff5696cd47f60878067' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/Picklist/PickListValueDetail.tpl',
      1 => 1417370718,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2131163102545ca4b4160e94-07750754',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545ca4b4273aa',
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
    'SELECTED_PICKLIST_FIELDMODEL' => 0,
    'SELECTED_MODULE_NAME' => 0,
    'SELECTED_PICKLISTFIELD_ALL_VALUES' => 0,
    'SELECTED_PICKLISTFIELD_ALL_DATA' => 0,
    'PICKLIST_VALUES' => 0,
    'PICKLIST_INDEX' => 0,
    'PICKLIST_VALUE' => 0,
    'PICKLIST_DATA' => 0,
    'INPUT_ID' => 0,
    'UICOLOR' => 0,
    'PROPERTIES_UICOLOR' => 0,
    'PROPERTIES_UIICON' => 0,
    'ROLES_LIST' => 0,
    'ROLE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545ca4b4273aa')) {function content_545ca4b4273aa($_smarty_tpl) {?>
<ul class="nav nav-tabs massEditTabs" style="margin-bottom: 0;border-bottom: 0"><li class="active"><a href="#allValuesLayout" data-toggle="tab"><strong><?php echo vtranslate('LBL_ALL_VALUES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></a></li><?php if ($_smarty_tpl->tpl_vars['SELECTED_PICKLIST_FIELDMODEL']->value->isRoleBased()){?><li id="assignedToRoleTab"><a href="#AssignedToRoleLayout" data-toggle="tab"><strong><?php echo vtranslate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></a></li><?php }?></ul><div class="tab-content layoutContent padding20 themeTableColor overflowVisible"><div class="tab-pane active" id="allValuesLayout"><div class="row-fluid"><div class="span5 marginLeftZero textOverflowEllipsis"><table id="pickListValuesTable" class="table table-bordered table-condensed table-striped" style="table-layout: fixed"><thead><tr class="listViewHeaders"><th><?php echo vtranslate($_smarty_tpl->tpl_vars['SELECTED_PICKLIST_FIELDMODEL']->value->get('label'),$_smarty_tpl->tpl_vars['SELECTED_MODULE_NAME']->value);?>
&nbsp;<?php echo vtranslate('LBL_ITEMS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</th></tr></thead><tbody><input type="hidden" id="dragImagePath" value="<?php echo vimage_path('drag.png');?>
" /><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['SELECTED_PICKLISTFIELD_ALL_VALUES']->value, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_DATA'] = new Smarty_variable($_smarty_tpl->tpl_vars['SELECTED_PICKLISTFIELD_ALL_DATA']->value, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_INDEX'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['PICKLIST_VALUE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->_loop = false;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->key => $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value){
$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->_loop = true;
 $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value = $_smarty_tpl->tpl_vars['PICKLIST_VALUE']->key;
?><?php $_smarty_tpl->tpl_vars['INPUT_ID'] = new Smarty_variable((('picklistvalue-').($_smarty_tpl->tpl_vars['PICKLIST_INDEX']->value)).('-uicolor'), null, 0);?><tr class="pickListValue cursorPointer" data-key="<?php echo Vtiger_Util_Helper::toSafeHTML($_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value);?>
"><td class="textOverflowEllipsis"><img class="alignMiddle" src="<?php echo vimage_path('drag.png');?>
"/>&nbsp;&nbsp;<div class="picklist-color"><?php if ($_smarty_tpl->tpl_vars['PICKLIST_DATA']->value[$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value]){?><?php $_smarty_tpl->tpl_vars['UICOLOR'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_DATA']->value[$_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value]['uicolor'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['UICOLOR'] = new Smarty_variable('', null, 0);?><?php }?><input type="hidden" class="colorField"id="<?php echo $_smarty_tpl->tpl_vars['INPUT_ID']->value;?>
"name="picklistvalue-uicolor" value="<?php echo $_smarty_tpl->tpl_vars['UICOLOR']->value;?>
"/><div id="<?php echo $_smarty_tpl->tpl_vars['INPUT_ID']->value;?>
-colorSelector" class="colorpicker-holder"><div style="background-color: <?php echo $_smarty_tpl->tpl_vars['UICOLOR']->value;?>
"></div></div></div>&nbsp;&nbsp;<?php echo vtranslate($_smarty_tpl->tpl_vars['PICKLIST_VALUE']->value,$_smarty_tpl->tpl_vars['SELECTED_MODULE_NAME']->value);?>
</td></tr><?php $_smarty_tpl->tpl_vars['PICKLIST_INDEX'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_INDEX']->value+1, null, 0);?><?php } ?></tbody></table></div><div class="span2 row-fluid"><?php if ($_smarty_tpl->tpl_vars['SELECTED_PICKLIST_FIELDMODEL']->value->isEditable()){?><?php if ($_smarty_tpl->tpl_vars['SELECTED_PICKLIST_FIELDMODEL']->value->isRoleBased()){?><button class="btn span10 marginLeftZero" id="assignValue"><?php echo vtranslate('LBL_ASSIGN_VALUE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><br><br><?php }?><button class="btn span10 marginLeftZero" id="addItem"><?php echo vtranslate('LBL_ADD_VALUE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><br><br><button class="btn span10 marginLeftZero" id="renameItem"><?php echo vtranslate('LBL_RENAME_VALUE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><br><br><button class="btn btn-danger span10 marginLeftZero"  id="deleteItem"><?php echo vtranslate('LBL_DELETE_VALUE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><br><br><?php }?><button class="btn btn-success span10 marginLeftZero" disabled=""  id="saveSequence"><?php echo vtranslate('LBL_SAVE_ORDER',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</button><br><br></div><div class="span4"><br><br><br><div><i class="icon-info-sign"></i>&nbsp;<span><?php echo vtranslate('LBL_DRAG_ITEMS_TO_RESPOSITION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</span></div><br><div>&nbsp;&nbsp;<?php echo vtranslate('LBL_SELECT_AN_ITEM_TO_RENAME_OR_DELETE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><br><div>&nbsp;&nbsp;<?php echo vtranslate('LBL_TO_DELETE_MULTIPLE_HOLD_CONTROL_KEY',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><br><br><form id="picklistproperties-ui"><fieldset><h3>Affichage</h3><table border=0><tr><td class="span2"><label for="picklistproperties-uicolor">Couleur</label><td><input type="checkbox" id="picklistproperties-uicolor" name="uicolor"<?php if ($_smarty_tpl->tpl_vars['PROPERTIES_UICOLOR']->value){?> checked="checked"<?php }?>></td><tr style="opacity: 0.5"><td class="span2"><label for="picklistproperties-uiicon">Ic&ocirc;ne</label><td><input type="checkbox" id="picklistproperties-uiicon" name="uiicon"<?php if ($_smarty_tpl->tpl_vars['PROPERTIES_UIICON']->value){?> checked="checked"<?php }?>></td></table></fieldset></form></div></div><div id="createViewContents" class="hide"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("CreateView.tpl",$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div></div><?php if ($_smarty_tpl->tpl_vars['SELECTED_PICKLIST_FIELDMODEL']->value->isRoleBased()){?><div class="tab-pane" id="AssignedToRoleLayout"><div class="row-fluid"><div class="span2" style="margin-top: 5px"><?php echo vtranslate('LBL_ROLE_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</div><div class="span7"><select id="rolesList" class="select2" name="rolesSelected" style="min-width: 220px" data-placeholder="<?php echo vtranslate('LBL_CHOOSE_ROLES',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
"><?php  $_smarty_tpl->tpl_vars['ROLE'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ROLE']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ROLES_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ROLE']->key => $_smarty_tpl->tpl_vars['ROLE']->value){
$_smarty_tpl->tpl_vars['ROLE']->_loop = true;
?><option value="<?php echo $_smarty_tpl->tpl_vars['ROLE']->value->get('roleid');?>
"><?php echo $_smarty_tpl->tpl_vars['ROLE']->value->get('rolename');?>
</option><?php } ?></select></div></div><div id="pickListValeByRoleContainer"></div></div><?php }?><div id="pickListValeByRoleContainer"></div></div><?php }} ?>