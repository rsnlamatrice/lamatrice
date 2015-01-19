<?php /* Smarty version Smarty-3.1.7, created on 2014-12-22 16:07:01
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ComposeEmailForm.tpl" */ ?>
<?php /*%%SmartyHeaderCode:211936060054983395f23971-06593903%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1f61ce2a8726e3e8cc99f4634a83155154c36cc2' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ComposeEmailForm.tpl',
      1 => 1413619586,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '211936060054983395f23971-06593903',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'SELECTED_IDS' => 0,
    'EXCLUDED_IDS' => 0,
    'VIEWNAME' => 0,
    'TOMAIL_INFO' => 0,
    'TO' => 0,
    'MAX_UPLOAD_SIZE' => 0,
    'EMAIL_MODE' => 0,
    'PARENT_EMAIL_ID' => 0,
    'PARENT_RECORD' => 0,
    'RECORDID' => 0,
    'SEARCH_KEY' => 0,
    'OPERATOR' => 0,
    'ALPHABET_VALUE' => 0,
    'TO_EMAILS' => 0,
    'RELATED_MODULES' => 0,
    'MODULE_NAME' => 0,
    'CC' => 0,
    'BCC' => 0,
    'SUBJECT' => 0,
    'DOCUMENTS_URL' => 0,
    'ATTACHMENTS' => 0,
    'ATTACHMENT' => 0,
    'FILE_TYPE' => 0,
    'DOCUMENT_ID' => 0,
    'MODULE_IS_ACTIVE' => 0,
    'EMAIL_TEMPLATE_URL' => 0,
    'RELATED_LOAD' => 0,
    'DESCRIPTION' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_549833967495c',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_549833967495c')) {function content_549833967495c($_smarty_tpl) {?>
<div class="SendEmailFormStep2" id="composeEmailContainer"><div style='padding:10px 0;'><h3><?php echo vtranslate('LBL_COMPOSE_EMAIL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</h3><hr style='margin:5px 0;width:100%'></div><form class="form-horizontal" id="massEmailForm" method="post" action="index.php" enctype="multipart/form-data" name="massEmailForm"><input type="hidden" name="selected_ids" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['SELECTED_IDS']->value);?>
' /><input type="hidden" name="excluded_ids" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['EXCLUDED_IDS']->value);?>
' /><input type="hidden" name="viewname" value="<?php echo $_smarty_tpl->tpl_vars['VIEWNAME']->value;?>
" /><input type="hidden" name="module" value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
"/><input type="hidden" name="mode" value="massSave" /><input type="hidden" name="toemailinfo" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['TOMAIL_INFO']->value);?>
' /><input type="hidden" name="view" value="MassSaveAjax" /><input type="hidden"  name="to" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['TO']->value);?>
' /><input type="hidden" id="flag" name="flag" value="" /><input type="hidden" id="maxUploadSize" value="<?php echo $_smarty_tpl->tpl_vars['MAX_UPLOAD_SIZE']->value;?>
" /><input type="hidden" id="documentIds" name="documentids" value="" /><input type="hidden" name="emailMode" value="<?php echo $_smarty_tpl->tpl_vars['EMAIL_MODE']->value;?>
" /><?php if (!empty($_smarty_tpl->tpl_vars['PARENT_EMAIL_ID']->value)){?><input type="hidden" name="parent_id" value="<?php echo $_smarty_tpl->tpl_vars['PARENT_EMAIL_ID']->value;?>
" /><input type="hidden" name="parent_record_id" value="<?php echo $_smarty_tpl->tpl_vars['PARENT_RECORD']->value;?>
" /><?php }?><?php if (!empty($_smarty_tpl->tpl_vars['RECORDID']->value)){?><input type="hidden" name="record" value="<?php echo $_smarty_tpl->tpl_vars['RECORDID']->value;?>
" /><?php }?><input type="hidden" name="search_key" value= "<?php echo $_smarty_tpl->tpl_vars['SEARCH_KEY']->value;?>
" /><input type="hidden" name="operator" value="<?php echo $_smarty_tpl->tpl_vars['OPERATOR']->value;?>
" /><input type="hidden" name="search_value" value="<?php echo $_smarty_tpl->tpl_vars['ALPHABET_VALUE']->value;?>
" /><div class="row-fluid padding-bottom1per toEmailField"><span class="span8 row-fluid"><span class="span2"><?php echo vtranslate('LBL_TO',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="redColor">*</span></span><?php if (!empty($_smarty_tpl->tpl_vars['TO']->value)){?><?php $_smarty_tpl->tpl_vars['TO_EMAILS'] = new Smarty_variable(implode(",",$_smarty_tpl->tpl_vars['TO']->value), null, 0);?><?php }?><input data-validation-engine='validate[required]' class="span9 sourceField" type="text" value="<?php echo $_smarty_tpl->tpl_vars['TO_EMAILS']->value;?>
" readonly /></span><span class="span4"><span class="row-fluid"><span class="span10"><div class="input-prepend"><span class="pull-right"><span class="add-on cursorPointer" name="clearToEmailField"><i class="icon-remove-sign" title="<?php echo vtranslate('LBL_CLEAR',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"></i></span><select class="chzn-select emailModulesList" style="width:150px;"><optgroup><?php  $_smarty_tpl->tpl_vars['MODULE_NAME'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['MODULE_NAME']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_MODULES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['MODULE_NAME']->key => $_smarty_tpl->tpl_vars['MODULE_NAME']->value){
$_smarty_tpl->tpl_vars['MODULE_NAME']->_loop = true;
?><option value="<?php echo $_smarty_tpl->tpl_vars['MODULE_NAME']->value;?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULE_NAME']->value,$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</option><?php } ?></optgroup></select></span></div></span><span class="input-append span2 margin0px"><span class="add-on selectEmail cursorPointer"><i class="icon-search" title="<?php echo vtranslate('LBL_SELECT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"></i></span></span></span></span></div><div class="row-fluid padding-bottom1per <?php if (empty($_smarty_tpl->tpl_vars['CC']->value)){?>hide <?php }?>" id="ccContainer"><span class="span8 row-fluid"><span class="span2"><?php echo vtranslate('LBL_CC',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span><input class="span9"  data-validation-engine="validate[funcCall[Vtiger_MultiEmails_Validator_Js.invokeValidation]]" type="text" name="cc" value="<?php if (!empty($_smarty_tpl->tpl_vars['CC']->value)){?><?php echo $_smarty_tpl->tpl_vars['CC']->value;?>
<?php }?>"/></span><span class="span4"></span></div><div class="row-fluid padding-bottom1per <?php if (empty($_smarty_tpl->tpl_vars['BCC']->value)){?>hide <?php }?>" id="bccContainer"><span class="span8 row-fluid"><span class="span2"><?php echo vtranslate('LBL_BCC',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span><input class="span9" data-validation-engine="validate[funcCall[Vtiger_MultiEmails_Validator_Js.invokeValidation]]" type="text" name="bcc" value="<?php if (!empty($_smarty_tpl->tpl_vars['BCC']->value)){?><?php echo $_smarty_tpl->tpl_vars['BCC']->value;?>
<?php }?>"/></span><span class="span4"></span></div><div class="row-fluid <?php if ((!empty($_smarty_tpl->tpl_vars['CC']->value))&&(!empty($_smarty_tpl->tpl_vars['BCC']->value))){?> hide <?php }?>"><span class="span8 row-fluid"><span class="span2">&nbsp;</span><span class="span9"><a class="cursorPointer <?php if ((!empty($_smarty_tpl->tpl_vars['CC']->value))){?>hide<?php }?>" id="ccLink"><?php echo vtranslate('LBL_ADD_CC',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a>&nbsp;&nbsp;<a class="cursorPointer <?php if ((!empty($_smarty_tpl->tpl_vars['BCC']->value))){?>hide<?php }?>" id="bccLink"><?php echo vtranslate('LBL_ADD_BCC',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></span></span><span class="span4"></span></div><div class="row-fluid padding-bottom1per"><span class="span8 row-fluid"><span class="span2"><?php echo vtranslate('LBL_SUBJECT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<span class="redColor">*</span></span><input data-validation-engine='validate[required]' class="span9" type="text" name="subject" value="<?php echo $_smarty_tpl->tpl_vars['SUBJECT']->value;?>
" id="subject"/></span><span class="span4"></span></div><div class="row-fluid padding-bottom1per"><span class="span8 row-fluid"><span class="span2"><?php echo vtranslate('LBL_ATTACHMENT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</span><span class="span9"><div class="row-fluid"><input type="file" id="multiFile" name="file[]"/>&nbsp;<button type="button" class="btn btn-small" id="browseCrm" data-url="<?php echo $_smarty_tpl->tpl_vars['DOCUMENTS_URL']->value;?>
" title="<?php echo vtranslate('LBL_BROWSE_CRM',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><?php echo vtranslate('LBL_BROWSE_CRM',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button></div><div id="attachments" class="row-fluid"><?php  $_smarty_tpl->tpl_vars['ATTACHMENT'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['ATTACHMENT']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['ATTACHMENTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['ATTACHMENT']->key => $_smarty_tpl->tpl_vars['ATTACHMENT']->value){
$_smarty_tpl->tpl_vars['ATTACHMENT']->_loop = true;
?><?php if ((array_key_exists('docid',$_smarty_tpl->tpl_vars['ATTACHMENT']->value))){?><?php $_smarty_tpl->tpl_vars['DOCUMENT_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['ATTACHMENT']->value['docid'], null, 0);?><?php $_smarty_tpl->tpl_vars['FILE_TYPE'] = new Smarty_variable("document", null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['FILE_TYPE'] = new Smarty_variable("file", null, 0);?><?php }?><div class="MultiFile-label customAttachment" data-file-id="<?php echo $_smarty_tpl->tpl_vars['ATTACHMENT']->value['fileid'];?>
" data-file-type="<?php echo $_smarty_tpl->tpl_vars['FILE_TYPE']->value;?>
"  data-file-size="<?php echo $_smarty_tpl->tpl_vars['ATTACHMENT']->value['size'];?>
" <?php if ($_smarty_tpl->tpl_vars['FILE_TYPE']->value=="document"){?> data-document-id="<?php echo $_smarty_tpl->tpl_vars['DOCUMENT_ID']->value;?>
"<?php }?>><?php if ($_smarty_tpl->tpl_vars['ATTACHMENT']->value['nondeletable']!=true){?><a name="removeAttachment" class="cursorPointer">x </a><?php }?><span><?php echo $_smarty_tpl->tpl_vars['ATTACHMENT']->value['attachment'];?>
</span></div><?php } ?></div></span></span><span class="span4"></span></div><div class="padding-bottom1per row-fluid"><div class="span8"><div class="btn-toolbar"><span class="btn-group"><button class="floatNone btn btn-success" id="sendEmail" type="submit" title="<?php echo vtranslate('LBL_SEND',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><strong><?php echo vtranslate('LBL_SEND',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button>&nbsp;&nbsp;</span><span class="btn-group"><button type="submit" class="floatNone btn" id="saveDraft" title="<?php echo vtranslate('LBL_SAVE_AS_DRAFT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><strong><?php echo vtranslate('LBL_SAVE_AS_DRAFT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></span><span class="btn-group"><?php if (!empty($_smarty_tpl->tpl_vars['PARENT_EMAIL_ID']->value)){?><button type="button" class="floatNone btn" id="gotoPreview" title="<?php echo vtranslate('LBL_GO_TO_PREVIEW',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><strong><?php echo vtranslate('LBL_GO_TO_PREVIEW',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php }?></span><span  name="progressIndicator" style="height:30px;">&nbsp;</span></div></div><?php if ($_smarty_tpl->tpl_vars['MODULE_IS_ACTIVE']->value){?><div class="span4"><span class="btn-toolbar pull-right"><button type="button" class="btn" id="selectEmailTemplate" data-url="<?php echo $_smarty_tpl->tpl_vars['EMAIL_TEMPLATE_URL']->value;?>
" title="<?php echo vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
"><strong><?php echo vtranslate('LBL_SELECT_EMAIL_TEMPLATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button></span></div><?php }?></div><?php if ($_smarty_tpl->tpl_vars['RELATED_LOAD']->value==true){?><input type="hidden" name="related_load" value=<?php echo $_smarty_tpl->tpl_vars['RELATED_LOAD']->value;?>
 /><?php }?><textarea id="description" name="description"><?php echo $_smarty_tpl->tpl_vars['DESCRIPTION']->value;?>
</textarea><input type="hidden" name="attachments" value='<?php echo ZEND_JSON::encode($_smarty_tpl->tpl_vars['ATTACHMENTS']->value);?>
' /></form></div><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path('JSResources.tpl'), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>

<?php }} ?>