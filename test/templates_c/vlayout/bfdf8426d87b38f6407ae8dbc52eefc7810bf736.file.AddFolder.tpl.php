<?php /* Smarty version Smarty-3.1.7, created on 2014-12-05 11:50:53
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Documents/AddFolder.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1151263814545cd9c41b6cb9-98697059%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bfdf8426d87b38f6407ae8dbc52eefc7810bf736' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Documents/AddFolder.tpl',
      1 => 1413619434,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1151263814545cd9c41b6cb9-98697059',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545cd9c429989',
  'variables' => 
  array (
    'MODULE' => 0,
    'FIELD_NAME' => 0,
    'INPUT_ID' => 0,
    'VALUE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545cd9c429989')) {function content_545cd9c429989($_smarty_tpl) {?>
<div class="modelContainer"><div class="modal-header contentsBackground"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3><?php echo vtranslate('LBL_ADD_NEW_FOLDER',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3></div><form class="form-horizontal" id="addDocumentsFolder" method="post" action="index.php"><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
" /><input type="hidden" name="action" value="Folder" /><input type="hidden" name="mode" value="save" /><div class="modal-body"><div class="row-fluid"><div class="control-group"><label class="control-label"><span class="redColor">*</span><?php echo vtranslate('LBL_FOLDER_NAME',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label><div class="controls"><input class="span3" data-validator='<?php echo Zend_Json::encode(array(array('name'=>'FolderName')));?>
' data-validation-engine="validate[required, funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" id="documentsFolderName" name="foldername" class="span12" type="text" value=""/></div></div><div class="control-group"><label class="control-label"><?php echo vtranslate('LBL_FOLDER_DESCRIPTION',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label><div class="controls"><textarea rows="1" class="input-xxlarge fieldValue span3" name="folderdesc" id="description"></textarea></div></div><div class="control-group"><label class="control-label"><?php echo vtranslate('LBL_FOLDER_UICOLOR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</label><div class="controls"><?php $_smarty_tpl->tpl_vars["INPUT_ID"] = new Smarty_variable(($_smarty_tpl->tpl_vars['MODULE']->value)."_addFolder_fieldName_".($_smarty_tpl->tpl_vars['FIELD_NAME']->value), null, 0);?><input id="<?php echo $_smarty_tpl->tpl_vars['INPUT_ID']->value;?>
" type="hidden"class="colorField"name="uicolor"value=""/><div id="<?php echo $_smarty_tpl->tpl_vars['INPUT_ID']->value;?>
-colorSelector" class="colorpicker-holder"><div style="background-color: <?php echo $_smarty_tpl->tpl_vars['VALUE']->value;?>
"></div></div></div></div></div></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('ModalFooter.tpl',$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</form></div><?php }} ?>