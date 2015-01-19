<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 12:56:49
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Contacts/RelatedListContacts.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1129387362545bc7f710a7b9-10872473%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8df758ec295bd176ff6b507ecf9781eb2c146e13' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Contacts/RelatedListContacts.tpl',
      1 => 1419420358,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1129387362545bc7f710a7b9-10872473',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545bc7f775ce7',
  'variables' => 
  array (
    'WIDGET_INSIDE' => 0,
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
    'HEADER_FIELD' => 0,
    'HEADER_NAME' => 0,
    'IS_BUTTONSET' => 0,
    'WIDTHTYPE' => 0,
    'IS_GROUP_FIELD' => 0,
    'COLUMN_NAME' => 0,
    'NEXT_SORT_ORDER' => 0,
    'SORT_IMAGE' => 0,
    'DATE_FORMAT' => 0,
    'RELATED_RECORD' => 0,
    'RELATED_HEADERNAME' => 0,
    'PARENT_RECORD' => 0,
    'FIELD_NAME' => 0,
    'DATE_IDS' => 0,
    'DATE_ID' => 0,
    'MODULE' => 0,
    'I' => 0,
    'DATA' => 0,
    'PICKLIST_VALUES' => 0,
    'PICKLIST_ITEM_KNOWN' => 0,
    'PICKLIST_ITEM' => 0,
    'FIELD_VALUE' => 0,
    'UID' => 0,
    'PICKLIST_KEY' => 0,
    'PICKLIST_CLASS' => 0,
    'PICKLIST_ICON' => 0,
    'PICKLIST_LABEL' => 0,
    'IS_DELETABLE' => 0,
    'IS_EDITABLE' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545bc7f775ce7')) {function content_545bc7f775ce7($_smarty_tpl) {?>
<div class="relatedContainer<?php if ($_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value){?> widget-content critere4d<?php }?>"><?php if (!$_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value){?><input type="hidden" name="currentPageNum" value="<?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getCurrentPage();?>
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
" <?php }?>data-url="<?php echo $_smarty_tpl->tpl_vars['RELATED_LINK']->value->getUrl();?>
"<?php }?><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value!=true){?>name="addButton"<?php }?>><?php if ($_smarty_tpl->tpl_vars['IS_SELECT_BUTTON']->value==false){?><i class="icon-plus icon-white"></i><?php }?>&nbsp;<strong><?php echo $_smarty_tpl->tpl_vars['RELATED_LINK']->value->getLabel();?>
</strong></button></div><?php } ?>&nbsp;</div><div class="span4"><span class="row-fluid"><span class="span7 pushDown"><span class="pull-right pageNumbers alignTop" data-placement="bottom" data-original-title="" style="margin-top: -5px"><?php if (!empty($_smarty_tpl->tpl_vars['RELATED_RECORDS']->value)){?><?php $_smarty_tpl->tpl_vars['START_RANGE'] = new Smarty_variable($_smarty_tpl->tpl_vars['PAGING']->value->getRecordStartRange(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['START_RANGE']->value>1){?><?php echo $_smarty_tpl->tpl_vars['START_RANGE']->value;?>
&nbsp;<?php echo vtranslate('LBL_to',$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
&nbsp;<?php }?><?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getRecordEndRange();?>
<?php }?></span></span><span class="span5 pull-right"><span class="btn-group pull-right"><button class="btn" id="relatedListPreviousPageButton" <?php if (!$_smarty_tpl->tpl_vars['PAGING']->value->isPrevPageExists()){?> disabled <?php }?> type="button"><span class="icon-chevron-left"></span></button><button class="btn dropdown-toggle" type="button" id="relatedListPageJump" data-toggle="dropdown" <?php if ($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1){?> disabled <?php }?>><i class="vtGlyph vticon-pageJump" title="<?php echo vtranslate('LBL_LISTVIEW_PAGE_JUMP',$_smarty_tpl->tpl_vars['moduleName']->value);?>
"></i></button><ul class="listViewBasicAction dropdown-menu" id="relatedListPageJumpDropDown"><li><span class="row-fluid"><span class="span3"><span class="pull-right"><?php echo vtranslate('LBL_PAGE',$_smarty_tpl->tpl_vars['moduleName']->value);?>
</span></span><span class="span4"><input type="text" id="pageToJump" class="listViewPagingInput" value="<?php echo $_smarty_tpl->tpl_vars['PAGING']->value->getCurrentPage();?>
"/></span><span class="span2 textAlignCenter"><?php echo vtranslate('LBL_OF',$_smarty_tpl->tpl_vars['moduleName']->value);?>
</span><span class="span2" id="totalPageCount"><?php echo $_smarty_tpl->tpl_vars['PAGE_COUNT']->value;?>
</span></span></li></ul><button class="btn" id="relatedListNextPageButton" <?php if ((!$_smarty_tpl->tpl_vars['PAGING']->value->isNextPageExists())||($_smarty_tpl->tpl_vars['PAGE_COUNT']->value==1)){?> disabled <?php }?> type="button"><span class="icon-chevron-right"></span></button></span></span></span></div></div></div><div class="contents-topscroll"><div class="topscroll-div">&nbsp;</div></div><?php }?><div class="relatedContents contents-bottomscroll"><div class="bottomscroll-div"><?php $_smarty_tpl->tpl_vars['WIDTHTYPE'] = new Smarty_variable($_smarty_tpl->tpl_vars['USER_MODEL']->value->get('rowheight'), null, 0);?><table class="table table-bordered listViewEntriesTable"><?php if (!$_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value){?><thead><tr class="listViewHeaders"><?php  $_smarty_tpl->tpl_vars['HEADER_FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_HEADERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['HEADER_FIELD']->key => $_smarty_tpl->tpl_vars['HEADER_FIELD']->value){
$_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = true;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration++;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->last = $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration === $_smarty_tpl->tpl_vars['HEADER_FIELD']->total;
?><?php $_smarty_tpl->tpl_vars['HEADER_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getFieldName(), null, 0);?><?php $_smarty_tpl->tpl_vars['IS_GROUP_FIELD'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_NAME']->value=="isgroup", null, 0);?><?php $_smarty_tpl->tpl_vars['IS_BUTTONSET'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('uitype')=='402', null, 0);?><?php if ($_smarty_tpl->tpl_vars['IS_BUTTONSET']->value){?><?php $_smarty_tpl->tpl_vars['tmp'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->set('picklist_values',$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->getListViewPicklistValues($_smarty_tpl->tpl_vars['HEADER_NAME']->value)), null, 0);?><?php }?><th <?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->last){?> colspan="2" <?php }?> nowrap class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['IS_GROUP_FIELD']->value){?><?php }elseif($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='access_count'||$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='idlists'){?><a href="javascript:void(0);" class="noSorting"><?php echo vtranslate($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('label'),$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
</a><?php }elseif($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')=='time_start'){?><?php }else{ ?><a href="javascript:void(0);" class="relatedListHeaderValues" data-nextsortorderval="<?php if ($_smarty_tpl->tpl_vars['COLUMN_NAME']->value==$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')){?><?php echo $_smarty_tpl->tpl_vars['NEXT_SORT_ORDER']->value;?>
<?php }else{ ?>ASC<?php }?>" data-fieldname="<?php echo $_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column');?>
"><?php echo vtranslate($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('label'),$_smarty_tpl->tpl_vars['RELATED_MODULE']->value->get('name'));?>
&nbsp;&nbsp;<?php if ($_smarty_tpl->tpl_vars['COLUMN_NAME']->value==$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('column')){?><img class="<?php echo $_smarty_tpl->tpl_vars['SORT_IMAGE']->value;?>
 icon-white"><?php }?></a><?php }?></th><?php } ?><th/></tr></thead><?php }?><?php if ($_smarty_tpl->tpl_vars['DATE_FORMAT']->value==''){?><?php $_smarty_tpl->tpl_vars['DATE_FORMAT'] = new Smarty_variable('dd-mm-yyyy', null, 0);?><?php }?><?php  $_smarty_tpl->tpl_vars['RELATED_RECORD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['RELATED_RECORD']->key => $_smarty_tpl->tpl_vars['RELATED_RECORD']->value){
$_smarty_tpl->tpl_vars['RELATED_RECORD']->_loop = true;
?><tr class="listViewEntries" data-id='<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getId();?>
' data-recordUrl='<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
'><?php  $_smarty_tpl->tpl_vars['HEADER_FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_HEADERS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['HEADER_FIELD']->key => $_smarty_tpl->tpl_vars['HEADER_FIELD']->value){
$_smarty_tpl->tpl_vars['HEADER_FIELD']->_loop = true;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration++;
 $_smarty_tpl->tpl_vars['HEADER_FIELD']->last = $_smarty_tpl->tpl_vars['HEADER_FIELD']->iteration === $_smarty_tpl->tpl_vars['HEADER_FIELD']->total;
?><?php $_smarty_tpl->tpl_vars['RELATED_HEADERNAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('name'), null, 0);?><?php $_smarty_tpl->tpl_vars['IS_GROUP_FIELD'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=="isgroup", null, 0);?><?php $_smarty_tpl->tpl_vars['IS_BUTTONSET'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('uitype')=='402', null, 0);?><td class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
" data-field-type="<?php echo $_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getFieldDataType();?>
" nowrap><?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->isNameField()==true||$_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('uitype')=='4'){?><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDetailViewUrl();?>
"><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value);?>
</a><?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='access_count'){?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getAccessCountValue($_smarty_tpl->tpl_vars['PARENT_RECORD']->value->getId());?>
<?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='time_start'){?><?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='dateapplication'){?><?php $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value, null, 0);?><div class="input-append span3 row-fluid dateapplication"><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable(0, null, 0);?><?php $_smarty_tpl->tpl_vars['DATE_IDS'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['FIELD_NAME']->value), null, 0);?><?php  $_smarty_tpl->tpl_vars['DATE_ID'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['DATE_ID']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['DATE_IDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['DATE_ID']->key => $_smarty_tpl->tpl_vars['DATE_ID']->value){
$_smarty_tpl->tpl_vars['DATE_ID']->_loop = true;
?><div class="row-fluid date"><?php if ($_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value&&$_smarty_tpl->tpl_vars['DATE_ID']->value){?><?php echo $_smarty_tpl->tpl_vars['DATE_ID']->value->format('d/m/Y');?>
<?php }else{ ?><input id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['I']->value;?>
" type="text"class="span5 dateField"name="<?php echo $_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getFieldName();?>
" data-date-format="<?php echo $_smarty_tpl->tpl_vars['DATE_FORMAT']->value;?>
"<?php if ($_smarty_tpl->tpl_vars['DATE_ID']->value){?>value="<?php echo $_smarty_tpl->tpl_vars['DATE_ID']->value->format('d/m/Y');?>
"dateapplication="<?php echo $_smarty_tpl->tpl_vars['DATE_ID']->value->format('Y-m-d H:i:s');?>
"<?php }?>data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/><span class="add-on"><i class="icon-calendar"></i></span><?php }?></div><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable($_smarty_tpl->tpl_vars['I']->value+1, null, 0);?><?php } ?></div><?php }elseif($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value=='contreltype'){?><div class="input-append row-fluid"><?php $_smarty_tpl->tpl_vars['FIELD_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('picklist_values'), null, 0);?><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['DATA'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['DATA']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['FIELD_NAME']->value); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['DATA']->key => $_smarty_tpl->tpl_vars['DATA']->value){
$_smarty_tpl->tpl_vars['DATA']->_loop = true;
?><?php if ($_smarty_tpl->tpl_vars['DATA']->value==null){?><?php $_smarty_tpl->tpl_vars['DATA'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getDefaultFieldValue(), null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM_KNOWN'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value&&array_key_exists($_smarty_tpl->tpl_vars['DATA']->value,$_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value), null, 0);?><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ITEM_KNOWN']->value){?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value[$_smarty_tpl->tpl_vars['DATA']->value], null, 0);?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)&&array_key_exists('label',$_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['DATA'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php }?><?php }?><div><?php if ($_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value){?><?php echo $_smarty_tpl->tpl_vars['DATA']->value;?>
<?php }else{ ?><input id="<?php echo $_smarty_tpl->tpl_vars['MODULE']->value;?>
_editView_fieldName_<?php echo $_smarty_tpl->tpl_vars['FIELD_NAME']->value;?>
_<?php echo $_smarty_tpl->tpl_vars['I']->value;?>
"class="span3 contreltype select2"value="<?php echo $_smarty_tpl->tpl_vars['DATA']->value;?>
"<?php if ($_smarty_tpl->tpl_vars['DATE_IDS']->value[$_smarty_tpl->tpl_vars['I']->value]){?>dateapplication="<?php echo $_smarty_tpl->tpl_vars['DATE_IDS']->value[$_smarty_tpl->tpl_vars['I']->value]->format('Y-m-d H:i:s');?>
"<?php }?>></select><?php }?></div><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable($_smarty_tpl->tpl_vars['I']->value+1, null, 0);?><?php } ?></div><?php }elseif($_smarty_tpl->tpl_vars['IS_BUTTONSET']->value){?><?php $_smarty_tpl->tpl_vars['PICKLIST_VALUES'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->get('picklist_values'), null, 0);?><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value), null, 0);?><?php if (is_array($_smarty_tpl->tpl_vars['FIELD_VALUE']->value)){?><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_VALUE']->value[0], null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['FIELD_VALUE']->value==null){?><?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['HEADER_FIELD']->value->getDefaultFieldValue(), null, 0);?><?php }?><?php if ($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value&&array_key_exists($_smarty_tpl->tpl_vars['FIELD_VALUE']->value,$_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_VALUES']->value[$_smarty_tpl->tpl_vars['FIELD_VALUE']->value], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_ITEM'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_VALUE']->value, null, 0);?><?php }?><?php if (is_array($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value)){?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['label'], null, 0);?><?php if (isset($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'])){?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['class'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable('', null, 0);?><?php }?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value['icon'], null, 0);?><?php }else{ ?><?php $_smarty_tpl->tpl_vars['PICKLIST_LABEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PICKLIST_ITEM']->value, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_ICON'] = new Smarty_variable(false, null, 0);?><?php $_smarty_tpl->tpl_vars['PICKLIST_CLASS'] = new Smarty_variable(false, null, 0);?><?php }?><label for="<?php echo $_smarty_tpl->tpl_vars['UID']->value;?>
<?php echo $_smarty_tpl->tpl_vars['PICKLIST_KEY']->value;?>
" class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_CLASS']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['PICKLIST_ICON']->value){?><span class="<?php echo $_smarty_tpl->tpl_vars['PICKLIST_ICON']->value;?>
"></span><?php }else{ ?>&nbsp;<?php echo $_smarty_tpl->tpl_vars['PICKLIST_LABEL']->value;?>
<?php }?></label><?php }else{ ?><?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getDisplayValue($_smarty_tpl->tpl_vars['RELATED_HEADERNAME']->value,false,true);?>
<?php }?><?php if ($_smarty_tpl->tpl_vars['HEADER_FIELD']->last&&!$_smarty_tpl->tpl_vars['WIDGET_INSIDE']->value){?><td nowrap class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
"><?php if ($_smarty_tpl->tpl_vars['IS_DELETABLE']->value){?><div class="pull-right actions"><div class="sub-field"><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable(0, null, 0);?><?php  $_smarty_tpl->tpl_vars['DATA'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['DATA']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->get($_smarty_tpl->tpl_vars['FIELD_NAME']->value); if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['DATA']->key => $_smarty_tpl->tpl_vars['DATA']->value){
$_smarty_tpl->tpl_vars['DATA']->_loop = true;
?><div><a class="relationDelete"dateapplication="<?php echo $_smarty_tpl->tpl_vars['DATE_IDS']->value[$_smarty_tpl->tpl_vars['I']->value]->format('Y-m-d H:i:s');?>
"><i title="<?php echo vtranslate('LBL_DELETE',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-trash alignMiddle"></i></a></div><?php $_smarty_tpl->tpl_vars['I'] = new Smarty_variable($_smarty_tpl->tpl_vars['I']->value+1, null, 0);?><?php } ?></div></div><?php }?></td></td><td nowrap class="<?php echo $_smarty_tpl->tpl_vars['WIDTHTYPE']->value;?>
"><div class="pull-right actions"><span class="actionImages"><a href="<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getFullDetailViewUrl();?>
"><i title="<?php echo vtranslate('LBL_SHOW_COMPLETE_DETAILS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-th-list alignMiddle"></i></a>&nbsp;<?php if ($_smarty_tpl->tpl_vars['IS_EDITABLE']->value){?><a href='<?php echo $_smarty_tpl->tpl_vars['RELATED_RECORD']->value->getEditViewUrl();?>
'><i title="<?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['MODULE']->value);?>
" class="icon-pencil alignMiddle"></i></a><?php }?></span></div></td><?php }?></td><?php } ?></tr><?php } ?></table></div></div></div>
<?php }} ?>