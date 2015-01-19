<?php /* Smarty version Smarty-3.1.7, created on 2014-12-03 11:25:45
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/EmailRelatedList.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1503677199547ee5297f3136-38650599%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '89bc21c6d6cfda8f108eefb548eac2cd6d7dd90a' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/EmailRelatedList.tpl',
      1 => 1413619584,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1503677199547ee5297f3136-38650599',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'PAGING' => 0,
    'RELATED_MODULE' => 0,
    'ORDER_BY' => 0,
    'SORT_ORDER' => 0,
    'RELATED_ENTIRES_COUNT' => 0,
    'TOTAL_ENTRIES' => 0,
    'RELATED_LIST_LINKS' => 0,
    'RELATED_LINK' => 0,
    'IS_SELECT_BUTTON' => 0,
    'RELATION_FIELD' => 0,
    'RELATED_RECORDS' => 0,
    'START_RANGE' => 0,
    'PAGE_COUNT' => 0,
    'moduleName' => 0,
    'USER_MODEL' => 0,
    'RELATED_HEADERS' => 0,
    'WIDTHTYPE' => 0,
    'HEADER_FIELD' => 0,
    'COLUMN_NAME' => 0,
    'NEXT_SORT_ORDER' => 0,
    'SORT_IMAGE' => 0,
    'RELATED_RECORD' => 0,
    'RELATED_HEADERNAME' => 0,
    'PARENT_RECORD' => 0,
    'RECORD_MODULE_MODEL' => 0,
    'RECORD_FIELD_MODEL' => 0,
    'REFERENCE_RECORD' => 0,
    'UI_TYPE_MODEL' => 0,
    'REFERENCE_MODULE_MODEL' => 0,
    'REFERENCE_MODULE_NAME' => 0,
    'REFERENCE_RECORD_ENTIYNAME_LIST' => 0,
    'MODULE' => 0,
    'IS_DELETABLE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_547ee529bd9a0',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_547ee529bd9a0')) {function content_547ee529bd9a0($_smarty_tpl) {?>
<div class="relatedContainer"><input type="hidden" name="currentPageNum" value="<?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getCurrentPage();?>
" /><input type="hidden" name="relatedModuleName" class="relatedModuleName" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name');?>
" /><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['ORDER_BY']->value;?>
" id="orderBy"><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['SORT_ORDER']->value;?>
" id="sortOrder"><input type="hidden" value="<?php echo $_smarty_tpl->tpl_vars['RELATED_ENTIRES_COUNT']->value;?>
" id="noOfEntries"><input type='hidden' value="<?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getPageLimit();?>
" id='pageLimit'><input type='hidden' value="<?php echo $_smarty_tpl->tpl_vars['TOTAL_ENTRIES']->value;?>
" id='totalCount'><div class="relatedHeader "><div class="btn-toolbar row-fluid"><div class="span8"><?php  $_smarty_tpl->tpl_vars['RELATED_LINK'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_LINK']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_LIST_LINKS']->value['LISTVIEWBASIC']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_LINK']->key => $_smarty_tpl->tpl_vars['RELATED_LINK']->value){
$_smarty_tpl->tpl_vars['RELATED_LINK']->_loop = true;
?><div class="btn-group"><?php ob_start();?><?php echo $_smarty_tpl->tpl_vars['RELATED_LINK']->value->get('_selectRelation');?>
<?php $_tmp1=ob_get_clean();?><?php $_smarty_tpl->tpl_vars['IS_SELECT_BUTTON'] = new Smarty_variable($_tmp1, null, 0);?><button type="button" class="btn addButton<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> selectRelation <?php }?> "<?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==true){?> data-moduleName=<?php echo $_smarty_tpl->tpl_vars['RELATED_LINK']->value->get('_module')->get('name');?>
 <?php }?><?php if (($_smarty_tpl->tpl_vars['RELATED_LINK']->value->isPageLoadLink())){?><?php if ($_smarty_tpl->tpl_vars['RELATION_FIELD']->value){?> data-name="<?php echo $_smarty_tpl->tpl_vars['RELATION_FIELD']->value->getName();?>
" <?php }?><?php }?><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value!=true){?>name="composeEmail"<?php }?>><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==false){?><i class="icon-plus icon-white"></i><?php }?>&nbsp;<strong><?php echo $_smarty_tpl->tpl_vars['RELATED_LINK']->value->getLabel();?>
</strong></button></div><?php } ?>&nbsp;</div><div class="span4"><span class="row-fluid"><span class="span7 pushDown"><span class="pull-right pageNumbers alignTop" data-placement="bottom" data-original-title="" style="margin-top: -5px"><?php if (!empty($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value)){?><?php $_smarty_tpl->tpl_vars['START_RANGE'] = new Smarty_variable($_smarty_tpl->tpl_vars['PAGING']->value->getRecordStartRange(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['START_RANGE']->value>1){?><?php echo $_smarty_tpl->tpl_vars['START_RANGE']->value;?>
&nbsp;<?php echo vtranslate('LBL_to',$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
&nbsp;<?php }?><?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getRecordEndRange();?>
<?php }?></span></span><span class="span5 pull-right"><span class="btn-group pull-right"><button class="btn" id="relatedListPreviousPageButton" <?php if (!$_smarty_tpl->tpl_vars['PAGING']->value->isPrevPageExists()){?> disabled <?php }?> type="button"><span class="icon-chevron-left"></span></button><button class="btn dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" <?php if ($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1){?> disabled <?php }?>><i class="vtGlyph vticon-pageJump" title="<?php echo vtranslate('LBL_LISTVIEW_PAGE_JUMP',$_smarty_tpl->tpl_vars['moduleName']->value);?>
"></i></button><ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown"><li><span class="row-fluid"><span class="span3"><span class="pull-right"><?php echo vtranslate('LBL_PAGE',$_smarty_tpl->tpl_vars['moduleName']->value);?>
</span></span><span class="span4"><input type="text" id="pageToJump" class="listViewPagingInput" value="<?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getCurrentPage();?>
"/></span><span class="span2 textAlignCenter"><?php echo vtranslate('LBL_OF',$_smarty_tpl->tpl_vars['moduleName']->value);?>
</span><span class="span2" id="totalPageCount"><?php echo $_smarty_tpl->tpl_vars['PAGE_COUNT']->value;?>
</span></span></li></ul><button class="btn" id="relatedListNextPageButton" <?php if ((!$_smarty_tpl->tpl_vars['PAGING']->value->isNextPageExists())||($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1)){?> disabled <?php }?> type="button"><span class="icon-chevron-right"></span></button></span></span></span></div></div></div><div class="contents-topscroll"><div class="topscroll-div">&nbsp;</div></div><div class="relatedContents contents-bottomscroll"><div class="bottomscroll-div"><?php $_smarty_tpl->tpl_vars['WIDTHTYPE'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('rowheight'), null, 0);?><table class="table table-bordered listViewEntriesTable"><thead><tr class="listViewHeaders"><?php  $_smarty_tpl->tpl_vars['HEADER_FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_HEADERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['HEADER_FIELD']->key => $_smarty_tpl->tpl_vars['HEADER_FIELD']->value){
$_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = true;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration++;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->last = $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration === $_smarty_tpl->tpl_vars['HEADER_FIELD']->total;
?><th class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='access_count'||$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='idlists'){?><a href="javascript:void(0);" class="noSorting"><?php echo vtranslate($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('label'),$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
</a><?php }elseif($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='time_start'){?><?php }else{ ?><a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="<?php if ($_smarty_tpl->tpl_vars['COLUMN_NAME']->value==$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')){?><?php echo $_smarty_tpl->tpl_vars['NEXT_SORT_ORDER']->value;?>
<?php }else{ ?>ASC<?php }?>" data-fieldname="<?php echo $_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column');?>
"><?php echo vtranslate(html_entity_decode($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('label')),$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
&nbsp;&nbsp;<?php if ($_smarty_tpl->tpl_vars['COLUMN_NAME']->value==$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')){?><img class="<?php echo $_smarty_tpl->tpl_vars['SORT_IMAGE']->value;?>
 icon-white"><?php }?></a><?php }?></th><?php } ?></tr></thead><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><tr class="listViewEntries" data-id='<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getId();?>
' name="emailsRelatedRecord"><?php  $_smarty_tpl->tpl_vars['HEADER_FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_HEADERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['HEADER_FIELD']->key => $_smarty_tpl->tpl_vars['HEADER_FIELD']->value){
$_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = true;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration++;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->last = $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration === $_smarty_tpl->tpl_vars['HEADER_FIELD']->total;
?><?php $_smarty_tpl->tpl_vars['RELATED_HEADERNAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('name'), null, 0);?><td class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->isNameField()==true||$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('uitype')=='4'){?><a><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value);?>
</a><?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='access_count'){?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getAccessCountValue($_smarty_tpl->tpl_vars['PARENT_RECORD']->value->getId());?>
<?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='date_start'){?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value);?>
 <?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue('time_start');?>
<?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='time_start'){?><?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='parent_id'){?><?php $_smarty_tpl->tpl_vars['REFERENCE_RECORD'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value), null, 0);?><?php $_smarty_tpl->tpl_vars['RECORD_MODULE_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getModule(), null, 0);?><?php $_smarty_tpl->tpl_vars['RECORD_FIELD_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RECORD_MODULE_MODEL']->value->getField($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value), null, 0);?><?php $_smarty_tpl->tpl_vars['UI_TYPE_MODEL'] = new Smarty_variable(Vtiger_Base_UIType::getInstanceFromField($_smarty_tpl->tpl_vars['RECORD_FIELD_MODEL']->value), null, 0);?><?php $_smarty_tpl->tpl_vars['REFERENCE_MODULE_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['UI_TYPE_MODEL']->value->getReferenceModule($_smarty_tpl->tpl_vars['REFERENCE_RECORD']->value), null, 0);?><?php $_smarty_tpl->tpl_vars['REFERENCE_MODULE_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['REFERENCE_MODULE_MODEL']->value->get('name'), null, 0);?><?php $_smarty_tpl->tpl_vars['REFERENCE_RECORD_ENTIYNAME_LIST'] = new Smarty_variable(getEntityName($_smarty_tpl->tpl_vars['REFERENCE_MODULE_NAME']->value,$_smarty_tpl->tpl_vars['REFERENCE_RECORD']->value), null, 0);?><a href="index.php?module=<?php echo $_smarty_tpl->tpl_vars['REFERENCE_MODULE_NAME']->value;?>
&view=<?php echo $_smarty_tpl->tpl_vars['REFERENCE_MODULE_MODEL']->value->getDetailViewName();?>
&record=<?php echo $_smarty_tpl->tpl_vars['REFERENCE_RECORD']->value;?>
"title="<?php echo vtranslate($_smarty_tpl->tpl_vars['REFERENCE_MODULE_NAME']->value,$_smarty_tpl->tpl_vars['REFERENCE_MODULE_NAME']->value);?>
"onclick="if(event.stopPropagation){event.stopPropagation();}else{event.cancelBubble=true;}"><?php echo $_smarty_tpl->tpl_vars['REFERENCE_RECORD_ENTIYNAME_LIST']->value[$_smarty_tpl->tpl_vars['REFERENCE_RECORD']->value];?>
</a><?php }elseif($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getFieldDataType()=='owner'){?><?php echo getOwnerName($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value));?>
<?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value);?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->last){?><div class="pull-right actions"><span class="actionImages"><a name="emailsDetailView"><i title="<?php echo vtranslate('LBL_SHOW_COMPLETE_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-th-list alignMiddle"></i></a>&nbsp;<?php if (!($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->isSentMail())){?><a name="emailsEditView"><i title="<?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-pencil alignMiddle"></i></a><?php }?><?php if ($_smarty_tpl->tpl_vars['IS_DELETABLE']->value){?><a class="relationDelete"><i title="<?php echo vtranslate('LBL_DELETE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-trash alignMiddle"></i></a><?php }?></span></div><?php }?></td><?php } ?></tr><?php } ?></table></div></div></div>
<?php }} ?>