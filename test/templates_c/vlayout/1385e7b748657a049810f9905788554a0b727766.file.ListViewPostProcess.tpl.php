<?php /* Smarty version Smarty-3.1.7, created on 2015-01-09 16:01:13
         compiled from "/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ListViewPostProcess.tpl" */ ?>
<?php /*%%SmartyHeaderCode:156644511154afed395124f8-08022846%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1385e7b748657a049810f9905788554a0b727766' => 
    array (
      0 => '/var/www/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ListViewPostProcess.tpl',
      1 => 1420811147,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '156644511154afed395124f8-08022846',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE_MODEL' => 0,
    'PAGING_MODEL' => 0,
    'PAGE_COUNT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_54afed3953310',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54afed3953310')) {function content_54afed3953310($_smarty_tpl) {?>
</div></div><div class="listViewActionsDiv row-fluid noprint"><div class="listViewTopMenuDiv noprint"><span class="span3 btn-toolbar pull-right listViewActions"><?php if ((method_exists($_smarty_tpl->tpl_vars['MODULE_MODEL']->value,'isPagingSupported')&&($_smarty_tpl->tpl_vars['MODULE_MODEL']->value->isPagingSupported()==true))||!method_exists($_smarty_tpl->tpl_vars['MODULE_MODEL']->value,'isPagingSupported')){?><span class="pageNumbers alignTop" data-placement="bottom" ></span><span class="btn-group alignTop"><span class="btn-group"><button class="btn" id="listViewPreviousPageButton-bottom" <?php if ((!$_smarty_tpl->tpl_vars['PAGING_MODEL']->value->isPrevPageExists())){?> disabled <?php }?> type="button"><span class="icon-chevron-left"></span></button><button class="btn dropdown-toggle" type="button" disabled><i class="vtGlyph vticon-pageJump"></i></button><button class="btn" id="listViewNextPageButton-bottom" <?php if ((!$_smarty_tpl->tpl_vars['PAGING_MODEL']->value->isNextPageExists())||($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1)){?> disabled <?php }?> type="button"><span class="icon-chevron-right"></span></button></span></span><?php }?></span></div></div></div><?php }} ?>