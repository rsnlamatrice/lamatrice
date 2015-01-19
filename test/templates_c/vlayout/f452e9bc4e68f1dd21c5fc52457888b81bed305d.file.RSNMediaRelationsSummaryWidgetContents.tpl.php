<?php /* Smarty version Smarty-3.1.7, created on 2014-12-04 15:16:53
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/RSNMediaRelationsSummaryWidgetContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:59679625475dd37be6f95-55585831%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f452e9bc4e68f1dd21c5fc52457888b81bed305d' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/RSNMediaContacts/RSNMediaRelationsSummaryWidgetContents.tpl',
      1 => 1417702105,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '59679625475dd37be6f95-55585831',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_5475dd37c7929',
  'variables' => 
  array (
    'RELATED_MODULE' => 0,
    'RELATED_RECORD_MODEL' => 0,
    'RELATED_RECORDS' => 0,
    'RELATED_RECORD' => 0,
    'MODULE' => 0,
    'FIELD_VALUE' => 0,
    'PICKLIST_VALUES' => 0,
    'PICKLIST_ITEM' => 0,
    'UID' => 0,
    'PICKLIST_KEY' => 0,
    'PICKLIST_CLASS' => 0,
    'PICKLIST_ICON' => 0,
    'PICKLIST_LABEL' => 0,
    'NUMBER_OF_RECORDS' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5475dd37c7929')) {function content_5475dd37c7929($_smarty_tpl) {?>
<div class="relatedContainer"><input type="hidden" name="relatedModuleName" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
" /></div><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD_MODEL']->value->getListViewPicklistValues('satisfaction'), null, 0);?><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><div class="contactsContainer"><ul class="unstyled"><li><div class="row-fluid"><div class="span2 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('daterelation');?>
</a></div><div class="span5 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getName();?>
<?php if ($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('comment')){?><br/><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('comment');?>
<?php }?></a></div><div class="span3 textOverflowEllipsis"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
" id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value;?>
_Related_Record_<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('id');?>
" title="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('lastname');?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('byuserid');?>
</a></div><div class="span1"><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get('satisfaction'), null, 0);?><?php if (is_array($_smarty_tpl->tpl_vars['FIELD_VALUE']->value)){?><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_VALUE']->value[0], null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['FIELD_VALUE']->value==null){?><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable(0, null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value&&array_key_exists($_smarty_tpl->tpl_vars['FIELD_VALUE']->value,$_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value[$_smarty_tpl->tpl_vars['FIELD_VALUE']->value], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_VALUE']->value, null, 0);?><?php }?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php if (isset($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'])){?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable('', null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['icon'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable(false, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable(false, null, 0);?><?php }?><label for="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_CLASS']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ICON']->value){?><span class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_ICON']->value;?>
"></span><?php }else{ ?>&nbsp;<?php echo $_smarty_tpl->tpl_vars['PICKLIST_LABEL']->value;?>
<?php }?></label></div></div></li></ul></div><?php } ?><?php $_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS'] = new Smarty_variable(count($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['NUMBER_OF_RECORDS']->value==15){?><div class="row-fluid"><div class="pull-right"><a class="moreRecentContacts cursorPointer"><?php echo vtranslate('LBL_MORE',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a></div></div><?php }?>
<?php }} ?>