<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 16:01:12
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/MenuBar.tpl" */ ?>
<?php /*%%SmartyHeaderCode:188125578554afed38c70226-76445073%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '2f0c72eddf760de5a7451cfb4929377b0a643088' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/MenuBar.tpl',
      1 => 1420811147,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '188125578554afed38c70226-76445073',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MENU_STRUCTURE' => 0,
    'moreMenus' => 0,
    'MODULE' => 0,
    'HOME_MODULE_MODEL' => 0,
    'moduleName' => 0,
    'topMenus' => 0,
    'moduleModel' => 0,
    'MENU_TOPITEMS_LIMIT' => 0,
    'topmenuClassName' => 0,
    'translatedModuleLabel' => 0,
    'NUMBER_OF_PARENT_TABS' => 0,
    'SPAN_CLASS' => 0,
    'moduleList' => 0,
    'USER_MODEL' => 0,
    'HEADER_LINKS' => 0,
    'obj' => 0,
    'src' => 0,
    'title' => 0,
    'childLinks' => 0,
    'href' => 0,
    'label' => 0,
    'onclick' => 0,
    'ANNOUNCEMENT' => 0,
    'announcement' => 0,
    'PARENT_MODULE' => 0,
    'VIEW' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54afed38df99b',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54afed38df99b')) {function content_54afed38df99b($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["topMenus"] = new Smarty_variable($_smarty_tpl->tpl_vars['MENU_STRUCTURE']->value->getTop(), null, 0);?><?php $_smarty_tpl->tpl_vars["moreMenus"] = new Smarty_variable($_smarty_tpl->tpl_vars['MENU_STRUCTURE']->value->getMore(), null, 0);?><?php $_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS'] = new Smarty_variable(count(array_keys($_smarty_tpl->tpl_vars['moreMenus']->value)), null, 0);?><div class="navbar" id="topMenus"><div class="navbar-inner" id="nav-inner"><div class="menuBar row-fluid"><div class="span9" style="overflow: hidden;"><ul class="nav modulesList"><li class="tabs"><a class="alignMiddle <?php if ($_smarty_tpl->tpl_vars['MODULE']->value=='Home'){?> selected <?php }?>" href="<?php echo $_smarty_tpl->tpl_vars['HOME_MODULE_MODEL']->value->getDefaultUrl();?>
"><img src="<?php echo vimage_path('home.png');?>
" alt="<?php echo vtranslate('LBL_HOME',$_smarty_tpl->tpl_vars['moduleName']->value);?>
" title="<?php echo vtranslate('LBL_HOME',$_smarty_tpl->tpl_vars['moduleName']->value);?>
" /></a></li><?php  $_smarty_tpl->tpl_vars['moduleModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['moduleModel']->_loop = false;
 $_smarty_tpl->tpl_vars['moduleName'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['topMenus']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['topmenu']['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['moduleModel']->key => $_smarty_tpl->tpl_vars['moduleModel']->value){
$_smarty_tpl->tpl_vars['moduleModel']->_loop = true;
 $_smarty_tpl->tpl_vars['moduleName']->value = $_smarty_tpl->tpl_vars['moduleModel']->key;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['topmenu']['index']++;
?><?php $_smarty_tpl->tpl_vars['translatedModuleLabel'] = new Smarty_variable(vtranslate($_smarty_tpl->tpl_vars['moduleModel']->value->get('label'),$_smarty_tpl->tpl_vars['moduleName']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["topmenuClassName"] = new Smarty_variable("tabs", null, 0);?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['topmenu']['index']>$_smarty_tpl->tpl_vars['MENU_TOPITEMS_LIMIT']->value){?><?php $_smarty_tpl->tpl_vars["topmenuClassName"] = new Smarty_variable("tabs opttabs", null, 0);?><?php }?><li class="<?php echo $_smarty_tpl->tpl_vars['topmenuClassName']->value;?>
"><a id="menubar_item_<?php echo $_smarty_tpl->tpl_vars['moduleName']->value;?>
" href="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getDefaultUrl();?>
" <?php if ($_smarty_tpl->tpl_vars['MODULE']->value==$_smarty_tpl->tpl_vars['moduleName']->value){?> class="selected" <?php }?>><?php echo $_smarty_tpl->tpl_vars['translatedModuleLabel']->value;?>
</a></li><?php } ?><li class="dropdown" id="moreMenu"><a class="dropdown-toggle" data-toggle="dropdown" href="#moreMenu"><?php echo vtranslate('LBL_ALL',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<b class="caret"></b></a><div class="dropdown-menu moreMenus" <?php if (($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value<=2)&&($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value!=0)){?>style="width: 30em;"<?php }elseif($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value==0){?>style="width: 10em;"<?php }?>><?php  $_smarty_tpl->tpl_vars['moduleList'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['moduleList']->_loop = false;
 $_smarty_tpl->tpl_vars['parent'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['moreMenus']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['moduleList']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['moduleList']->iteration=0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['more']['index']=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['moduleList']->key => $_smarty_tpl->tpl_vars['moduleList']->value){
$_smarty_tpl->tpl_vars['moduleList']->_loop = true;
 $_smarty_tpl->tpl_vars['parent']->value = $_smarty_tpl->tpl_vars['moduleList']->key;
 $_smarty_tpl->tpl_vars['moduleList']->iteration++;
 $_smarty_tpl->tpl_vars['moduleList']->last = $_smarty_tpl->tpl_vars['moduleList']->iteration === $_smarty_tpl->tpl_vars['moduleList']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['more']['index']++;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['more']['last'] = $_smarty_tpl->tpl_vars['moduleList']->last;
?><?php if ($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value>=4){?><?php $_smarty_tpl->tpl_vars['SPAN_CLASS'] = new Smarty_variable('span3', null, 0);?><?php }elseif($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value==3){?><?php $_smarty_tpl->tpl_vars['SPAN_CLASS'] = new Smarty_variable('span4', null, 0);?><?php }elseif($_smarty_tpl->tpl_vars['NUMBER_OF_PARENT_TABS']->value<=2){?><?php $_smarty_tpl->tpl_vars['SPAN_CLASS'] = new Smarty_variable('span6', null, 0);?><?php }?><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['more']['index']%4==0){?><div class="row-fluid"><?php }?><span class="<?php echo $_smarty_tpl->tpl_vars['SPAN_CLASS']->value;?>
"><strong><?php echo vtranslate("LBL_".($_smarty_tpl->tpl_vars['parent']->value),$_smarty_tpl->tpl_vars['moduleName']->value);?>
</strong><hr><?php  $_smarty_tpl->tpl_vars['moduleModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['moduleModel']->_loop = false;
 $_smarty_tpl->tpl_vars['moduleName'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['moduleList']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['moduleModel']->key => $_smarty_tpl->tpl_vars['moduleModel']->value){
$_smarty_tpl->tpl_vars['moduleModel']->_loop = true;
 $_smarty_tpl->tpl_vars['moduleName']->value = $_smarty_tpl->tpl_vars['moduleModel']->key;
?><?php $_smarty_tpl->tpl_vars['translatedModuleLabel'] = new Smarty_variable(vtranslate($_smarty_tpl->tpl_vars['moduleModel']->value->get('label'),$_smarty_tpl->tpl_vars['moduleName']->value), null, 0);?><label class="moduleNames"><a id="menubar_item_<?php echo $_smarty_tpl->tpl_vars['moduleName']->value;?>
" href="<?php echo $_smarty_tpl->tpl_vars['moduleModel']->value->getDefaultUrl();?>
"><?php echo $_smarty_tpl->tpl_vars['translatedModuleLabel']->value;?>
</a></label><?php } ?></span><?php if ($_smarty_tpl->getVariable('smarty')->value['foreach']['more']['last']||($_smarty_tpl->getVariable('smarty')->value['foreach']['more']['index']+1)%4==0){?></div><?php }?><?php } ?><?php if ($_smarty_tpl->tpl_vars['USER_MODEL']->value->isAdminUser()){?><div class="row-fluid"><a id="menubar_item_moduleManager" href="index.php?module=MenuEditor&parent=Settings&view=Index" class="pull-right"><?php echo vtranslate('LBL_CUSTOMIZE_MAIN_MENU',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><div class="row-fluid"><a id="menubar_item_moduleManager" href="index.php?module=ModuleManager&parent=Settings&view=List" class="pull-right"><?php echo vtranslate('LBL_ADD_MANAGE_MODULES',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></div><?php }?></div></li></ul></div><div class="span3" id="headerLinks"><span class="pull-right headerLinksContainer"><?php  $_smarty_tpl->tpl_vars['obj'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['obj']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['HEADER_LINKS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['obj']->key => $_smarty_tpl->tpl_vars['obj']->value){
$_smarty_tpl->tpl_vars['obj']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['obj']->key;
?><?php $_smarty_tpl->tpl_vars["src"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getIconPath(), null, 0);?><?php $_smarty_tpl->tpl_vars["icon"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getIcon(), null, 0);?><?php $_smarty_tpl->tpl_vars["title"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getLabel(), null, 0);?><?php $_smarty_tpl->tpl_vars["childLinks"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getChildLinks(), null, 0);?><span class="dropdown span<?php if (!empty($_smarty_tpl->tpl_vars['src']->value)){?> settingIcons <?php }?>"><?php if (!empty($_smarty_tpl->tpl_vars['src']->value)){?><a id="menubar_item_right_<?php echo $_smarty_tpl->tpl_vars['title']->value;?>
" class="dropdown-toggle" data-toggle="dropdown" href="#"><img src="<?php echo $_smarty_tpl->tpl_vars['src']->value;?>
" alt="<?php echo vtranslate($_smarty_tpl->tpl_vars['title']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
" title="<?php echo vtranslate($_smarty_tpl->tpl_vars['title']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
" /></a><?php }else{ ?><?php $_smarty_tpl->tpl_vars['title'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('first_name'), null, 0);?><?php if (empty($_smarty_tpl->tpl_vars['title']->value)){?><?php $_smarty_tpl->tpl_vars['title'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('last_name'), null, 0);?><?php }?><span class="dropdown-toggle" data-toggle="dropdown" href="#"><a id="menubar_item_right_<?php echo $_smarty_tpl->tpl_vars['title']->value;?>
"  class="userName textOverflowEllipsis span" title="<?php echo $_smarty_tpl->tpl_vars['title']->value;?>
"><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
 <i class="caret"></i> </a> </span><?php }?><?php if (!empty($_smarty_tpl->tpl_vars['childLinks']->value)){?><ul class="dropdown-menu pull-right"><?php  $_smarty_tpl->tpl_vars['obj'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['obj']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['childLinks']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['obj']->key => $_smarty_tpl->tpl_vars['obj']->value){
$_smarty_tpl->tpl_vars['obj']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['obj']->key;
?><?php if ($_smarty_tpl->tpl_vars['obj']->value->getLabel()==null){?><li class="divider">&nbsp;</li><?php }elseif($_smarty_tpl->tpl_vars['obj']->value->getLabel()=='LBL_FEEDBACK'){?><li><a href="https://discussions.vtiger.com" target="_blank"><?php echo vtranslate($_smarty_tpl->tpl_vars['obj']->value->getLabel(),$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></li><?php }else{ ?><?php $_smarty_tpl->tpl_vars["id"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getId(), null, 0);?><?php $_smarty_tpl->tpl_vars["href"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getUrl(), null, 0);?><?php $_smarty_tpl->tpl_vars["label"] = new Smarty_variable($_smarty_tpl->tpl_vars['obj']->value->getLabel(), null, 0);?><?php $_smarty_tpl->tpl_vars["onclick"] = new Smarty_variable('', null, 0);?><?php if (stripos($_smarty_tpl->tpl_vars['obj']->value->getUrl(),'javascript:')===0){?><?php $_smarty_tpl->tpl_vars["onclick"] = new Smarty_variable(("onclick=").($_smarty_tpl->tpl_vars['href']->value), null, 0);?><?php $_smarty_tpl->tpl_vars["href"] = new Smarty_variable("javascript:;", null, 0);?><?php }?><li><a target="<?php echo $_smarty_tpl->tpl_vars['obj']->value->target;?>
" id="menubar_item_right_<?php echo Vtiger_Util_Helper::replaceSpaceWithUnderScores($_smarty_tpl->tpl_vars['label']->value);?>
" <?php if ($_smarty_tpl->tpl_vars['label']->value=='Switch to old look'){?>switchLook<?php }?> href="<?php echo $_smarty_tpl->tpl_vars['href']->value;?>
" <?php echo $_smarty_tpl->tpl_vars['onclick']->value;?>
><?php echo vtranslate($_smarty_tpl->tpl_vars['label']->value,$_smarty_tpl->tpl_vars['MODULE']->value);?>
</a></li><?php }?><?php } ?></ul><?php }?></span><?php } ?></span></div></div><div class="clearfix"></div></div></div><?php $_smarty_tpl->tpl_vars["announcement"] = new Smarty_variable($_smarty_tpl->tpl_vars['ANNOUNCEMENT']->value->get('announcement'), null, 0);?><div class="announcement noprint" id="announcement"><marquee direction="left" scrolldelay="10" scrollamount="3" behavior="scroll" class="marStyle" onmouseover="javascript:stop();" onmouseout="javascript:start();"><?php if (!empty($_smarty_tpl->tpl_vars['announcement']->value)){?><?php echo $_smarty_tpl->tpl_vars['announcement']->value;?>
<?php }else{ ?><?php echo vtranslate('LBL_NO_ANNOUNCEMENTS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
<?php }?></marquee></div><input type='hidden' value="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
" id='module' name='module'/><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['PARENT_MODULE']->value;?>
" id="parent" name='parent' /><input type='hidden' value="<?php echo $_smarty_tpl->tpl_vars['VIEW']->value;?>
" id='view' name='view'/>
<?php }} ?>