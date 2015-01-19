<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 12:55:32
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/CustomView/EditView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2144100082545caf0631f584-24304934%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ed280ab160d64835aacff8865308991b72d18c1c' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/CustomView/EditView.tpl',
      1 => 1413623032,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2144100082545caf0631f584-24304934',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545caf06592c7',
  'variables' => 
  array (
    'CUSTOMVIEW_MODEL' => 0,
    'RECORD_ID' => 0,
    'MODULE' => 0,
    'SOURCE_MODULE' => 0,
    'CV_PRIVATE_VALUE' => 0,
    'DATE_FILTERS' => 0,
    'CV_PENDING_VALUE' => 0,
    'RECORD_STRUCTURE' => 0,
    'BLOCK_LABEL' => 0,
    'BLOCK_FIELDS' => 0,
    'FIELD_MODEL' => 0,
    'MANDATORY_FIELDS' => 0,
    'FIELD_NAME' => 0,
    'SELECTED_FIELDS' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545caf06592c7')) {function content_545caf06592c7($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars['SELECTED_FIELDS'] = new Smarty_variable($_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->getSelectedFields(), null, 0);?><div class="row-fluid span"><form class="form-horizontal" id="CustomView" name="CustomView" method="post" action="index.php"><input type=hidden name="record" id="record" value="<?php echo $_smarty_tpl->tpl_vars['RECORD_ID']->value;?>
" /><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
" /><input type="hidden" name="action" value="Save" /><input type="hidden" name="source_module" value="<?php echo $_smarty_tpl->tpl_vars['SOURCE_MODULE']->value;?>
"/><input type="hidden" id="stdfilterlist" name="stdfilterlist" value=""/><input type="hidden" id="advfilterlist" name="advfilterlist" value=""/><input type="hidden" id="status" name="status" value="<?php echo $_smarty_tpl->tpl_vars['CV_PRIVATE_VALUE']->value;?>
"/><h3><?php echo vtranslate('LBL_CREATE_VIEW',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3><hr style='margin:5px 5px 5px 0px;'><input type="hidden" id="sourceModule" value="<?php echo $_smarty_tpl->tpl_vars['SOURCE_MODULE']->value;?>
"><input type="hidden" name="date_filters" data-value='<?php echo Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($_smarty_tpl->tpl_vars['DATE_FILTERS']->value));?>
' /><div class="filterBlocksAlignment"><h4 class="filterHeaders"><?php echo vtranslate('LBL_BASIC_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 :</h4><div class="row-fluid"><span class="span"><?php echo vtranslate('LBL_VIEW_NAME',$_smarty_tpl->tpl_vars['MODULE']->value);?>
*</span><input  class="span" type="text" id="viewname" data-validation-engine='validate[required]' name="viewname" value="<?php echo $_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->get('viewname');?>
"><span class="span"><input id="setdefault" type="checkbox" name="setdefault" value="1" <?php if ($_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->isDefault()){?> checked="checked"<?php }?>><span class="alignMiddle"> <?php echo vtranslate('LBL_SET_AS_DEFAULT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></span><span class="span"><input id="setmetrics" name="setmetrics" type="checkbox" value="1" <?php if ($_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->get('setmetrics')=='1'){?> checked="checked"<?php }?>><span class="alignMiddle"> <?php echo vtranslate('LBL_LIST_IN_METRICS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></span><span class="span"><input id="status" name="status" type="checkbox" <?php if ($_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->isSetPublic()){?> value="<?php echo $_smarty_tpl->tpl_vars['CUSTOMVIEW_MODEL']->value->get('status');?>
" checked="checked" <?php }else{ ?> value="<?php echo $_smarty_tpl->tpl_vars['CV_PENDING_VALUE']->value;?>
" <?php }?>><span class="alignMiddle"> <?php echo vtranslate('LBL_SET_AS_PUBLIC',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span></span></div><br><h4 class="filterHeaders"><?php echo vtranslate('LBL_CHOOSE_COLUMNS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
 (<?php echo vtranslate('LBL_MAX_NUMBER_FILTER_COLUMNS');?>
):</h4><div class="columnsSelectDiv"><?php $_smarty_tpl->tpl_vars['MANDATORY_FIELDS'] = new Smarty_variable(array(), null, 0);?><select data-placeholder="<?php echo vtranslate('LBL_ADD_MORE_COLUMNS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" multiple class="select2-container columnsSelect" id="viewColumnsSelect"><?php  $_smarty_tpl->tpl_vars['BLOCK_FIELDS'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['BLOCK_FIELDS']->_loop = false;
 $_smarty_tpl->tpl_vars['BLOCK_LABEL'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['RECORD_STRUCTURE']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['BLOCK_FIELDS']->key => $_smarty_tpl->tpl_vars['BLOCK_FIELDS']->value){
$_smarty_tpl->tpl_vars['BLOCK_FIELDS']->_loop = true;
 $_smarty_tpl->tpl_vars['BLOCK_LABEL']->value = $_smarty_tpl->tpl_vars['BLOCK_FIELDS']->key;
?><optgroup label='<?php echo vtranslate($_smarty_tpl->tpl_vars['BLOCK_LABEL']->value,$_smarty_tpl->tpl_vars['SOURCE_MODULE']->value);?>
'><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['BLOCK_FIELDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELD_NAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
?><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()){?><?php echo array_push($_smarty_tpl->tpl_vars['MANDATORY_FIELDS']->value,$_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getCustomViewColumnName());?>
<?php }?><option value="<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getCustomViewColumnName();?>
" data-field-name="<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
"<?php if (in_array($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getCustomViewColumnName(),$_smarty_tpl->tpl_vars['SELECTED_FIELDS']->value)){?>selected<?php }?>><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['SOURCE_MODULE']->value);?>
<?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->isMandatory()==true){?> <span>*</span> <?php }?></option><?php } ?></optgroup><?php } ?></select><input type="hidden" name="columnslist" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['SELECTED_FIELDS']->value);?>
' /><input id="mandatoryFieldsList" type="hidden" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['MANDATORY_FIELDS']->value);?>
' /></div><br><h4 class="filterHeaders"><?php echo vtranslate('LBL_CHOOSE_FILTER_CONDITIONS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
:</h4><div class="well filterConditionsDiv"><div class="row-fluid"><span class="span10"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('AdvanceFilter.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</span></div></div></div><div class="filterActions"><a class="cancelLink pull-right padding1per" type="reset" onClick="window.location.reload()"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a><button class="btn btn-success pull-right" id="customViewSubmit" type="submit"><strong><?php echo vtranslate('LBL_SAVE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div></form></div>
<?php }} ?>