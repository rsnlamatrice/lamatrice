<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 12:37:14
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ListViewPostProcess.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1668321743544e4feeb986c1-73841219%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1fd744a84bd4f97f636710feebe1e040766dbb84' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/ListViewPostProcess.tpl',
      1 => 1418207702,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1668321743544e4feeb986c1-73841219',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_544e4feec207f',
  'variables' => 
  array (
    'MODULE_MODEL' => 0,
    'PAGING_MODEL' => 0,
    'PAGE_COUNT' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4feec207f')) {function content_544e4feec207f($_smarty_tpl) {?>
</div></div><div class="listViewActionsDiv row-fluid noprint"><div class="listViewTopMenuDiv noprint"><span class="span3 btn-toolbar pull-right listViewActions"><?php if ((method_exists($_smarty_tpl->tpl_vars['MODULE_MODEL']->value,'isPagingSupported')&&($_smarty_tpl->tpl_vars['MODULE_MODEL']->value->isPagingSupported()==true))||!method_exists($_smarty_tpl->tpl_vars['MODULE_MODEL']->value,'isPagingSupported')){?><span class="pageNumbers alignTop" data-placement="bottom" ></span><span class="btn-group alignTop"><span class="btn-group"><button class="btn" id="listViewPreviousPageButton-bottom" <?php if ((!$_smarty_tpl->tpl_vars['PAGING_MODEL']->value->isPrevPageExists())){?> disabled <?php }?> type="button"><span class="icon-chevron-left"></span></button><button class="btn dropdown-toggle" type="button" disabled><i class="vtGlyph vticon-pageJump"></i></button><button class="btn" id="listViewNextPageButton-bottom" <?php if ((!$_smarty_tpl->tpl_vars['PAGING_MODEL']->value->isNextPageExists())||($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1)){?> disabled <?php }?> type="button"><span class="icon-chevron-right"></span></button></span></span><?php }?></span></div></div></div><?php }} ?>