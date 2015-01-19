<?php /* Smarty version Smarty-3.1.7, created on 2014-11-21 10:40:37
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/MailConverter/ListViewContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:128278147546f08959c7dd3-53448655%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'e7e23946859588389d9e84d79e2812fa92f8a153' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/MailConverter/ListViewContents.tpl',
      1 => 1413615910,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '128278147546f08959c7dd3-53448655',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'RECORD_MODELS' => 0,
    'RECORD' => 0,
    'LINK' => 0,
    'QUALIFIED_MODULE' => 0,
    'FIELDS' => 0,
    'COUNTER' => 0,
    'FIELD_MODEL' => 0,
    'FIELDNAME' => 0,
    'DISPLAY_VALUE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_546f0895b1bf2',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_546f0895b1bf2')) {function content_546f0895b1bf2($_smarty_tpl) {?>
<?php  $_smarty_tpl->tpl_vars['RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECORD_MODELS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RECORD']->key => $_smarty_tpl->tpl_vars['RECORD']->value){
$_smarty_tpl->tpl_vars['RECORD']->_loop = true;
?><table class="table table-bordered" id="SCANNER_<?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getId();?>
"><thead><tr><th class="blockHeader" colspan="4"><span class="font-x-large"><?php echo $_smarty_tpl->tpl_vars['RECORD']->value->getName();?>
</span><div class="pull-right btn-group"><button class="btn dropdown-toggle" data-toggle="dropdown">Actions<span class="caret"></span></button><ul class="dropdown-menu pull-right"><?php  $_smarty_tpl->tpl_vars['LINK'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['LINK']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RECORD']->value->getRecordLinks(); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['LINK']->key => $_smarty_tpl->tpl_vars['LINK']->value){
$_smarty_tpl->tpl_vars['LINK']->_loop = true;
?><li> <a style="text-shadow: none" <?php if (strpos($_smarty_tpl->tpl_vars['LINK']->value->getUrl(),'javascript:')===0){?> href='javascript:void(0);' onclick='<?php echo substr($_smarty_tpl->tpl_vars['LINK']->value->getUrl(),strlen("javascript:"));?>
;'<?php }else{ ?> href=<?php echo $_smarty_tpl->tpl_vars['LINK']->value->getUrl();?>
 <?php }?>><?php echo vtranslate($_smarty_tpl->tpl_vars['LINK']->value->getLabel(),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</a></li><?php } ?></ul></div></th></tr></thead><tbody><?php $_smarty_tpl->tpl_vars['FIELDS'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD']->value->getDetailViewFields(), null, 0);?><tr><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['FIELD_MODEL'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = false;
 $_smarty_tpl->tpl_vars['FIELDNAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['FIELDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD_MODEL']->key => $_smarty_tpl->tpl_vars['FIELD_MODEL']->value){
$_smarty_tpl->tpl_vars['FIELD_MODEL']->_loop = true;
 $_smarty_tpl->tpl_vars['FIELDNAME']->value = $_smarty_tpl->tpl_vars['FIELD_MODEL']->key;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration++;
 $_smarty_tpl->tpl_vars['FIELD_MODEL']->last = $_smarty_tpl->tpl_vars['FIELD_MODEL']->iteration === $_smarty_tpl->tpl_vars['FIELD_MODEL']->total;
?><?php if ($_smarty_tpl->tpl_vars['COUNTER']->value%2==0&&$_smarty_tpl->tpl_vars['COUNTER']->value!=0){?></tr><tr><?php }?><td class="fieldLabel"><strong><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('label'),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></td><td class="fieldValue"><?php $_smarty_tpl->tpl_vars['DISPLAY_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELDNAME']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['FIELDNAME']->value=='password'){?>******<?php }elseif($_smarty_tpl->tpl_vars['FIELDNAME']->value=='markas'&&!empty($_smarty_tpl->tpl_vars['DISPLAY_VALUE']->value)){?><?php echo vtranslate('LBL_MARK_MESSAGE_AS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
&nbsp;<?php echo vtranslate($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELDNAME']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }elseif($_smarty_tpl->tpl_vars['FIELDNAME']->value=='searchfor'||$_smarty_tpl->tpl_vars['FIELDNAME']->value=='timezone'){?><?php echo vtranslate($_smarty_tpl->tpl_vars['RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELDNAME']->value),$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['DISPLAY_VALUE']->value;?>
<?php }?></td><?php $_smarty_tpl->tpl_vars['COUNTER'] = new Smarty_variable($_smarty_tpl->tpl_vars['COUNTER']->value+1, null, 0);?><?php if ($_smarty_tpl->tpl_vars['FIELD_MODEL']->last){?><td></td><td></td><?php }?><?php } ?></tr></tbody></table><?php } ?></div>
<?php }} ?>