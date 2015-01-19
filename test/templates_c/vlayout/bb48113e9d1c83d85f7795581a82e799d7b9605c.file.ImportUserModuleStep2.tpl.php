<?php /* Smarty version Smarty-3.1.7, created on 2014-11-11 15:29:51
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleManager/ImportUserModuleStep2.tpl" */ ?>
<?php /*%%SmartyHeaderCode:22201856554621d5f8b0438-94108611%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'bb48113e9d1c83d85f7795581a82e799d7b9605c' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Settings/ModuleManager/ImportUserModuleStep2.tpl',
      1 => 1413615928,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '22201856554621d5f8b0438-94108611',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'QUALIFIED_MODULE' => 0,
    'MODULEIMPORT_FAILED' => 0,
    'VERSION_NOT_SUPPORTED' => 0,
    'MODULEIMPORT_FILE_INVALID' => 0,
    'MODULEIMPORT_NAME' => 0,
    'MODULEIMPORT_EXISTS' => 0,
    'MODULEIMPORT_DEP_VTVERSION' => 0,
    'MODULEIMPORT_LICENSE' => 0,
    'MODULEIMPORT_DIR_EXISTS' => 0,
    'MODULE' => 0,
    'MODULEIMPORT_FILE' => 0,
    'MODULEIMPORT_TYPE' => 0,
    'need_license_agreement' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54621d5fa780d',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54621d5fa780d')) {function content_54621d5fa780d($_smarty_tpl) {?>
<div class="container-fluid" id="importModules"><div class="widget_header row-fluid"><h3><?php echo vtranslate('LBL_IMPORT_MODULE_FROM_FILE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</h3></div><hr><div class="contents"><div class="row-fluid"><div id="vtlib_modulemanager_import_div"><form method="POST" action="index.php"><input type="hidden" name="module" value="ModuleManager"><input type="hidden" name="parent" value="Settings"><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_FAILED']->value!=''){?><div class="span10"><b><?php echo vtranslate('LBL_FAILED',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></div><div class="span10"><?php if ($_smarty_tpl->tpl_vars['VERSION_NOT_SUPPORTED']->value=='true'){?><font color=red><b><?php echo vtranslate('LBL_VERSION_NOT_SUPPORTED',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></font><?php }else{ ?><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_FILE_INVALID']->value=="true"){?><font color=red><b><?php echo vtranslate('LBL_INVALID_FILE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></font> <?php echo vtranslate('LBL_INVALID_IMPORT_TRY_AGAIN',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }else{ ?><font color=red><?php echo vtranslate('LBL_UNABLE_TO_UPLOAD',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</font> <?php echo vtranslate('LBL_UNABLE_TO_UPLOAD2',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }?><?php }?></div><input type="hidden" name="view" value="List"><button  class="btn btn-success" type="submit"><strong><?php echo vtranslate('LBL_FINISH',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></button><?php }else{ ?><table class="table table-bordered"><thead><tr class="blockHeader"><th colspan="2"><strong><?php echo vtranslate('LBL_VERIFY_IMPORT_DETAILS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</strong></th></tr></thead><tbody><tr><td><b><?php echo vtranslate('LBL_MODULE_NAME',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></td><td><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULEIMPORT_NAME']->value,$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_EXISTS']->value=='true'){?> <font color=red><b><?php echo vtranslate('LBL_EXISTS',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></font> <?php }?></td></tr><tr><td><b><?php echo vtranslate('LBL_REQ_VTIGER_VERSION',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></td><td><?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_DEP_VTVERSION']->value;?>
</td></tr><?php $_smarty_tpl->tpl_vars["need_license_agreement"] = new Smarty_variable("false", null, 0);?><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_LICENSE']->value){?><?php $_smarty_tpl->tpl_vars["need_license_agreement"] = new Smarty_variable("true", null, 0);?><tr><td width=20%<?php ?>><b><?php echo vtranslate('LBL_LICENSE',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
</b></td><td><textarea readonly class='row-fluid'><?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_LICENSE']->value;?>
</textarea><br><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_EXISTS']->value!='true'){?><input type="checkbox"  onclick="if(this.form.saveButton){if(this.checked){this.form.saveButton.disabled=false;}else{this.form.saveButton.disabled=true;}}">  <?php echo vtranslate('LBL_LICENSE_ACCEPT_AGREEMENT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<?php }?></td></tr><?php }?></tbody></table><div class="modal-footer"><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_EXISTS']->value=='true'||$_smarty_tpl->tpl_vars['MODULEIMPORT_DIR_EXISTS']->value=='true'){?><input type="hidden" name="view" value="List"><button class="btn btn-success" class="crmbutton small delete"onclick="this.form.mode.value='';"><strong><?php echo vtranslate('LBL_CANCEL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></button><?php if ($_smarty_tpl->tpl_vars['MODULEIMPORT_EXISTS']->value=='true'){?><input type="hidden" name="view" value="ModuleImport"><input type="hidden" name="module_import_file" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_FILE']->value;?>
"><input type="hidden" name="module_import_type" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_TYPE']->value;?>
"><input type="hidden" name="module_import_name" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_NAME']->value;?>
"><input type="hidden" name="mode" value="importUserModuleStep3"><input type="checkbox" class="pull-right" onclick="this.form.mode.value='updateUserModuleStep3';this.form.submit();" ><span class="pull-right">I would like to update now.&nbsp;</span><?php }?><?php }else{ ?><input type="hidden" name="view" value="ModuleImport"><input type="hidden" name="module_import_file" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_FILE']->value;?>
"><input type="hidden" name="module_import_type" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_TYPE']->value;?>
"><input type="hidden" name="module_import_name" value="<?php echo $_smarty_tpl->tpl_vars['MODULEIMPORT_NAME']->value;?>
"><input type="hidden" name="mode" value="importUserModuleStep3"><span class="span6 pull-right"><?php echo vtranslate('LBL_PROCEED_WITH_IMPORT',$_smarty_tpl->tpl_vars['QUALIFIED_MODULE']->value);?>
<div class=" pull-right cancelLinkContainer"><a class="cancelLink" type="reset" data-dismiss="modal" onclick="javascript:window.history.back();"><?php echo vtranslate('LBL_NO',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><button  class="btn btn-success" type="submit" name="saveButton"<?php if ($_smarty_tpl->tpl_vars['need_license_agreement']->value=='true'){?> disabled <?php }?>><strong><?php echo vtranslate('LBL_YES');?>
</strong></button></span><?php }?></div><?php }?></form></div></div></div></div><?php }} ?>