<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 16:01:12
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/CommonActions.tpl" */ ?>
<?php /*%%SmartyHeaderCode:197782846154afed38e0e1c9-42670652%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'fc1f880809f5f3fcd7569f375bbf67416356e5d4' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/CommonActions.tpl',
      1 => 1420811147,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '197782846154afed38e0e1c9-42670652',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'ANNOUNCEMENT' => 0,
    'USER_MODEL' => 0,
    'COMPANY_LOGO' => 0,
    'MODULE_NAME' => 0,
    'SEARCHABLE_MODULES' => 0,
    'announcement' => 0,
    'MODULE' => 0,
    'MAIN_PRODUCT_WHITELABEL' => 0,
    'MENUS' => 0,
    'moduleModel' => 0,
    'singularLabel' => 0,
    'quickCreateModule' => 0,
    'quickCreateVisible' => 0,
    'count' => 0,
    'moduleName' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54afed38ecb77',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54afed38ecb77')) {function content_54afed38ecb77($_smarty_tpl) {?>

<?php $_smarty_tpl->tpl_vars["announcement"] = new Smarty_variable($_smarty_tpl->tpl_vars['ANNOUNCEMENT']->value->get('announcement'), null, 0);?><?php $_smarty_tpl->tpl_vars['count'] = new Smarty_variable(0, null, 0);?><?php $_smarty_tpl->tpl_vars["dateFormat"] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('date_format'), null, 0);?><div class="navbar commonActionsContainer noprint"><div class="actionsContainer row-fluid"><div class="span2"><span class="companyLogo"><img src="<?php echo $_smarty_tpl->tpl_vars['COMPANY_LOGO']->value->get('imagepath');?>
" title="<?php echo $_smarty_tpl->tpl_vars['COMPANY_LOGO']->value->get('title');?>
" alt="<?php echo $_smarty_tpl->tpl_vars['COMPANY_LOGO']->value->get('alt');?>
"/>&nbsp;</span></div><div class="span10 marginLeftZero"><div class="row-fluid"><div class="searchElement span7"><div class="select-search"><select class="chzn-select" id="basicSearchModulesList" style="width:150px;"><option value="" class="globalSearch_module_All"><?php echo vtranslate('LBL_ALL_RECORDS',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</option><?php  $_smarty_tpl->tpl_vars['fieldObject'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['fieldObject']->_loop = false;
 $_smarty_tpl->tpl_vars['MODULE_NAME'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SEARCHABLE_MODULES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['fieldObject']->key => $_smarty_tpl->tpl_vars['fieldObject']->value){
$_smarty_tpl->tpl_vars['fieldObject']->_loop = true;
 $_smarty_tpl->tpl_vars['MODULE_NAME']->value = $_smarty_tpl->tpl_vars['fieldObject']->key;
?><option value="<?php echo $_smarty_tpl->tpl_vars['MODULE_NAME']->value;?>
" class="globalSearch_module_<?php echo $_smarty_tpl->tpl_vars['MODULE_NAME']->value;?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['MODULE_NAME']->value,$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</option><?php } ?></select></div><div class="input-append searchBar"><input type="text" class="" id="globalSearchValue" placeholder="<?php echo vtranslate('LBL_GLOBAL_SEARCH');?>
" results="10" /><span id="searchIcon" class="add-on search-icon"><i class="icon-white icon-search "></i></span><span class="adv-search"><a class="alignMiddle" id="globalSearch"><?php echo vtranslate('LBL_ADVANCE_SEARCH');?>
</a></span></div></div><div class="notificationMessageHolder span3"></div><div class="nav quickActions btn-toolbar span2 pull-right"><div class="pull-right commonActionsButtonContainer"><?php if (!empty($_smarty_tpl->tpl_vars['announcement']->value)){?><div class="btn-group cursorPointer"><img class='alignMiddle' src="<?php echo vimage_path('btnAnnounceOff.png');?>
" alt="<?php echo vtranslate('LBL_ANNOUNCEMENT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" title="<?php echo vtranslate('LBL_ANNOUNCEMENT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" id="announcementBtn" /></div>&nbsp;<?php }?><div class="btn-group cursorPointer" id="guiderHandler"><?php if (!$_smarty_tpl->tpl_vars['MAIN_PRODUCT_WHITELABEL']->value){?><img src="<?php echo vimage_path('circle_question_mark.png');?>
" class="alignMiddle" alt="?" title="<?php echo vtranslate('LBL_GUIDER',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" style="display:none;"/><?php }?></div>&nbsp;<div class="btn-group cursorPointer"><img id="menubar_quickCreate" src="<?php echo vimage_path('btnAdd.png');?>
" class="alignMiddle" alt="<?php echo vtranslate('LBL_QUICK_CREATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" title="<?php echo vtranslate('LBL_QUICK_CREATE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" data-toggle="dropdown" /><ul class="dropdown-menu dropdownStyles commonActionsButtonDropDown"><li class="title"><strong><?php echo vtranslate('Quick Create',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</strong></li><hr/><li id="quickCreateModules"><div class="row-fluid"><div class="span12"><?php  $_smarty_tpl->tpl_vars['moduleModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['moduleModel']->_loop = false;
 $_smarty_tpl->tpl_vars['moduleName'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['MENUS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['moduleModel']->key => $_smarty_tpl->tpl_vars['moduleModel']->value){
$_smarty_tpl->tpl_vars['moduleModel']->_loop = true;
 $_smarty_tpl->tpl_vars['moduleName']->value = $_smarty_tpl->tpl_vars['moduleModel']->key;
?><?php if ($_smarty_tpl->tpl_vars['moduleModel']->value->isPermitted('EditView')){?><?php $_smarty_tpl->tpl_vars['quickCreateModule'] = new Smarty_variable($_smarty_tpl->tpl_vars['moduleModel']->value->isQuickCreateSupported(), null, 0);?><?php $_smarty_tpl->tpl_vars['quickCreateVisible'] = new Smarty_variable($_smarty_tpl->tpl_vars['moduleModel']->value->isQuickCreateMenuVisible(), null, 0);?><?php $_smarty_tpl->tpl_vars['singularLabel'] = new Smarty_variable($_smarty_tpl->tpl_vars['moduleModel']->value->getSingularLabelKey(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['singularLabel']->value=='SINGLE_Calendar'){?><?php $_smarty_tpl->tpl_vars['singularLabel'] = new Smarty_variable('LBL_EVENT_OR_TASK', null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['quickCreateModule']->value=='1'){?><?php if ($_smarty_tpl->tpl_vars['quickCreateVisible']->value){?><?php if ($_smarty_tpl->tpl_vars['count']->value%3==0){?><div class="row-fluid"><?php }?><div class="span4"><a id="menubar_quickCreate_<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getName();?>
" class="quickCreateModule" data-name="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getName();?>
"data-url="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getQuickCreateUrl();?>
" href="javascript:void(0)"><?php echo vtranslate($_smarty_tpl->tpl_vars['singularLabel']->value,$_smarty_tpl->tpl_vars['moduleName']->value);?>
</a></div><?php if ($_smarty_tpl->tpl_vars['count']->value%3==2){?></div><?php }?><?php $_smarty_tpl->tpl_vars['count'] = new Smarty_variable($_smarty_tpl->tpl_vars['count']->value+1, null, 0);?><?php }else{ ?><div class="span0 hide"><a id="menubar_quickCreate_<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getName();?>
" class="quickCreateModule" data-name="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getName();?>
"data-url="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getQuickCreateUrl();?>
" href="javascript:void(0)"><?php echo vtranslate($_smarty_tpl->tpl_vars['singularLabel']->value,$_smarty_tpl->tpl_vars['moduleName']->value);?>
</a></div><?php }?><?php }?><?php }?><?php } ?></div></div></li></ul></div>&nbsp;</div></div></div></div></div></div>
<?php }} ?>