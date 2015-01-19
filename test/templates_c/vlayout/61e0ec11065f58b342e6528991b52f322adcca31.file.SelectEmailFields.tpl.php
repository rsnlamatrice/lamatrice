<?php /* Smarty version Smarty-3.1.7, created on 2014-12-22 16:06:53
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/SelectEmailFields.tpl" */ ?>
<?php /*%%SmartyHeaderCode:19015973345498338d737569-66041258%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '61e0ec11065f58b342e6528991b52f322adcca31' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/SelectEmailFields.tpl',
      1 => 1413619578,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '19015973345498338d737569-66041258',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'SELECTED_IDS' => 0,
    'EXCLUDED_IDS' => 0,
    'VIEWNAME' => 0,
    'SEARCH_KEY' => 0,
    'OPERATOR' => 0,
    'ALPHABET_VALUE' => 0,
    'PARENT_MODULE' => 0,
    'PARENT_RECORD' => 0,
    'RELATED_MODULE' => 0,
    'SOURCE_MODULE' => 0,
    'EMAIL_FIELDS' => 0,
    'EMAIL_FIELD' => 0,
    'EMAIL_FIELD_LIST' => 0,
    'EMAIL_FIELD_NAME' => 0,
    'EMAIL_FIELD_LABEL' => 0,
    'RELATED_LOAD' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5498338da2643',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5498338da2643')) {function content_5498338da2643($_smarty_tpl) {?>
<div id="sendEmailContainer" class="modelContainer"><div class="modal-header contentsBackground"><button data-dismiss="modal" class="close" title="<?php echo vtranslate('LBL_CLOSE');?>
">&times;</button><h3><?php echo vtranslate('LBL_SELECT_EMAIL_IDS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><form class="form-horizontal" id="SendEmailFormStep1" method="post" action="index.php"><input type="hidden" name="selected_ids" value=<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['SELECTED_IDS']->value);?>
 /><input type="hidden" name="excluded_ids" value=<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['EXCLUDED_IDS']->value);?>
 /><input type="hidden" name="viewname" value="<?php echo $_smarty_tpl->tpl_vars['VIEWNAME']->value;?>
" /><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
"/><input type="hidden" name="view" value="ComposeEmail"/><input type="hidden" name="search_key" value= "<?php echo $_smarty_tpl->tpl_vars['SEARCH_KEY']->value;?>
" /><input type="hidden" name="operator" value="<?php echo $_smarty_tpl->tpl_vars['OPERATOR']->value;?>
" /><input type="hidden" name="search_value" value="<?php echo $_smarty_tpl->tpl_vars['ALPHABET_VALUE']->value;?>
" /><?php if (!empty($_smarty_tpl->tpl_vars['PARENT_MODULE']->value)){?><input type="hidden" name="sourceModule" value="<?php echo $_smarty_tpl->tpl_vars['PARENT_MODULE']->value;?>
" /><input type="hidden" name="sourceRecord" value="<?php echo $_smarty_tpl->tpl_vars['PARENT_RECORD']->value;?>
" /><input type="hidden" name="parentModule" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
" /><?php }?><div class='padding20'><h4><?php echo vtranslate('LBL_MUTIPLE_EMAIL_SELECT_ONE',$_smarty_tpl->tpl_vars['SOURCE_MODULE']->value);?>
</h4></div><div id="multiEmailContainer"><div class='padding20'><?php $_smarty_tpl->tpl_vars['EMAIL_FIELD_LIST'] = new Smarty_variable(array(), null, 0);?><?php  $_smarty_tpl->tpl_vars['EMAIL_FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['EMAIL_FIELD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['EMAIL_FIELDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['EMAIL_FIELD']->key => $_smarty_tpl->tpl_vars['EMAIL_FIELD']->value){
$_smarty_tpl->tpl_vars['EMAIL_FIELD']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['EMAIL_FIELD']->value->isViewEnabled()){?><?php $_smarty_tpl->createLocalArrayVariable('EMAIL_FIELD_LIST', null, 0);
$_smarty_tpl->tpl_vars['EMAIL_FIELD_LIST']->value[$_smarty_tpl->tpl_vars['EMAIL_FIELD']->value->get('name')] = vtranslate($_smarty_tpl->tpl_vars['EMAIL_FIELD']->value->get('label'),$_smarty_tpl->tpl_vars['SOURCE_MODULE']->value);?><?php }?><?php } ?><div class="control-group"><label class="radio"><input id="selectAllEmails" type="radio" name="selectedFields" value='<?php echo ZEND_JSON::encode(array_keys($_smarty_tpl->tpl_vars['EMAIL_FIELD_LIST']->value));?>
' />&nbsp; <?php echo vtranslate('LBL_ALL_EMAILS',$_smarty_tpl->tpl_vars['SOURCE_MODULE']->value);?>
</label></div><?php  $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->_loop = false;
 $_smarty_tpl->tpl_vars['EMAIL_FIELD_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['EMAIL_FIELD_LIST']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['emailFieldIterator']['iteration']=0;
foreach ($_from as $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->key => $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->value){
$_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->_loop = true;
 $_smarty_tpl->tpl_vars['EMAIL_FIELD_NAME']->value = $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->key;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['emailFieldIterator']['iteration']++;
?><div class="control-group"><label class="radio"><input type="radio" class="emailField" name="selectedFields" value='<?php echo ZEND_JSON::encode(array($_smarty_tpl->tpl_vars['EMAIL_FIELD_NAME']->value));?>
' <?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['emailFieldIterator']['iteration']==1){?> checked="checked" <?php }?>/>&nbsp; <?php echo $_smarty_tpl->tpl_vars['EMAIL_FIELD_LABEL']->value;?>
</label></div><?php } ?></div></div><div class='modal-footer'><div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal"><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button class="btn addButton" type="submit" name="selectfield"><strong><?php echo vtranslate('LBL_SELECT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></div><?php if ($_smarty_tpl->tpl_vars['RELATED_LOAD']->value==true){?><input type="hidden" name="relatedLoad" value=<?php echo $_smarty_tpl->tpl_vars['RELATED_LOAD']->value;?>
 /><?php }?></form></div>

<?php }} ?>