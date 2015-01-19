<?php /* Smarty version Smarty-3.1.7, created on 2014-11-21 11:12:28
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Import/Import_Default_Values_Widget.tpl" */ ?>
<?php /*%%SmartyHeaderCode:134492538546f100c159966-93415477%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '056ba2cab7a006d2fcedc49be1f0d11f0fbd6399' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Import/Import_Default_Values_Widget.tpl',
      1 => 1413615844,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '134492538546f100c159966-93415477',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'AVAILABLE_FIELDS' => 0,
    '_FIELD_NAME' => 0,
    '_FIELD_INFO' => 0,
    '_FIELD_TYPE' => 0,
    '_PICKLIST_DETAILS' => 0,
    'FOR_MODULE' => 0,
    'USERS_LIST' => 0,
    '_ID' => 0,
    '_NAME' => 0,
    'GROUPS_LIST' => 0,
    'DATE_FORMAT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_546f100c27672',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_546f100c27672')) {function content_546f100c27672($_smarty_tpl) {?>

<div style="visibility: hidden; height: 0px;" id="defaultValuesElementsContainer">
	<?php  $_smarty_tpl->tpl_vars['_FIELD_INFO'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_FIELD_INFO']->_loop = false;
 $_smarty_tpl->tpl_vars['_FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['AVAILABLE_FIELDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_FIELD_INFO']->key => $_smarty_tpl->tpl_vars['_FIELD_INFO']->value){
$_smarty_tpl->tpl_vars['_FIELD_INFO']->_loop = true;
 $_smarty_tpl->tpl_vars['_FIELD_NAME']->value = $_smarty_tpl->tpl_vars['_FIELD_INFO']->key;
?>
	<span id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue_container" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="small span11">
		<?php $_smarty_tpl->tpl_vars["_FIELD_TYPE"] = new Smarty_variable($_smarty_tpl->tpl_vars['_FIELD_INFO']->value->getFieldDataType(), null, 0);?>
		<?php if ($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='picklist'||$_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='multipicklist'){?>
			<select id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="small chzn-select">
            <option value=""><?php echo vtranslate('LBL_SELECT_OPTION','Vtiger');?>
</option>
			<?php  $_smarty_tpl->tpl_vars['_PICKLIST_DETAILS'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['_FIELD_INFO']->value->getPicklistDetails(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->key => $_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->value){
$_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->_loop = true;
?>
				<option value="<?php echo $_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->value['value'];?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['_PICKLIST_DETAILS']->value['label'],$_smarty_tpl->tpl_vars['FOR_MODULE']->value);?>
</option>
			<?php } ?>
			</select>
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='integer'){?>
			<input type="text" id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="small defaultInputTextContainer" value="0" />
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='owner'||$_smarty_tpl->tpl_vars['_FIELD_INFO']->value->getUIType()=='52'){?>
			<select id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="small chzn-select">
				<option value="">--<?php echo vtranslate('LBL_NONE',$_smarty_tpl->tpl_vars['FOR_MODULE']->value);?>
--</option>
			<?php  $_smarty_tpl->tpl_vars['_NAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_NAME']->_loop = false;
 $_smarty_tpl->tpl_vars['_ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['USERS_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_NAME']->key => $_smarty_tpl->tpl_vars['_NAME']->value){
$_smarty_tpl->tpl_vars['_NAME']->_loop = true;
 $_smarty_tpl->tpl_vars['_ID']->value = $_smarty_tpl->tpl_vars['_NAME']->key;
?>
				<option value="<?php echo $_smarty_tpl->tpl_vars['_ID']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['_NAME']->value;?>
</option>
			<?php } ?>
			<?php if ($_smarty_tpl->tpl_vars['_FIELD_INFO']->value->getUIType()=='53'){?>
				<?php  $_smarty_tpl->tpl_vars['_NAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['_NAME']->_loop = false;
 $_smarty_tpl->tpl_vars['_ID'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['GROUPS_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['_NAME']->key => $_smarty_tpl->tpl_vars['_NAME']->value){
$_smarty_tpl->tpl_vars['_NAME']->_loop = true;
 $_smarty_tpl->tpl_vars['_ID']->value = $_smarty_tpl->tpl_vars['_NAME']->key;
?>
				<option value="<?php echo $_smarty_tpl->tpl_vars['_ID']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['_NAME']->value;?>
</option>
				<?php } ?>
			<?php }?>
			</select>
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='date'){?>
			<input type="text" id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue"
					data-date-format="<?php echo $_smarty_tpl->tpl_vars['DATE_FORMAT']->value;?>
" class="defaultInputTextContainer span2 datepicker" value="" />
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='datetime'){?>
				<input type="text" id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue"
					   class="defaultInputTextContainer small span2" value="" data-date-format="<?php echo $_smarty_tpl->tpl_vars['DATE_FORMAT']->value;?>
"/>
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value=='boolean'){?>
			<input type="checkbox" id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="small" />
		<?php }elseif($_smarty_tpl->tpl_vars['_FIELD_TYPE']->value!='reference'){?>
			<input type="input" id="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" name="<?php echo $_smarty_tpl->tpl_vars['_FIELD_NAME']->value;?>
_defaultvalue" class="defaultInputTextContainer small" />
		<?php }?>
		</span>
	<?php } ?>
</div>
<script type="text/javascript">
	jQuery(document).ready(function() {
		$('.small .span2').datepicker();
	});
</script><?php }} ?>