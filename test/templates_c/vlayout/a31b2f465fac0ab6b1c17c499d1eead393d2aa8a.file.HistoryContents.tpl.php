<?php /* Smarty version Smarty-3.1.7, created on 2014-12-01 16:25:41
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/dashboards/HistoryContents.tpl" */ ?>
<?php /*%%SmartyHeaderCode:487223497547c88756bb570-32360055%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'a31b2f465fac0ab6b1c17c499d1eead393d2aa8a' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/dashboards/HistoryContents.tpl',
      1 => 1413619590,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '487223497547c88756bb570-32360055',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'HISTORIES' => 0,
    'index' => 0,
    'HISTORY' => 0,
    'MODELNAME' => 0,
    'MOD_NAME' => 0,
    'SINGLE_MODULE_NAME' => 0,
    'TRANSLATED_MODULE_NAME' => 0,
    'PARENT' => 0,
    'USER' => 0,
    'DETAILVIEW_URL' => 0,
    'FIELDS' => 0,
    'INDEX' => 0,
    'FIELD' => 0,
    'RELATION' => 0,
    'LINKED_RECORD_DETAIL_URL' => 0,
    'PARENT_DETAIL_URL' => 0,
    'NEXTPAGE' => 0,
    'PAGE' => 0,
    'MODULE_NAME' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_547c8875a7320',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_547c8875a7320')) {function content_547c8875a7320($_smarty_tpl) {?>

<div style='padding:5px;'>
<?php if ($_smarty_tpl->tpl_vars['HISTORIES']->value!=false){?>
	<?php  $_smarty_tpl->tpl_vars['HISTORY'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['HISTORY']->_loop = false;
 $_smarty_tpl->tpl_vars[$_smarty_tpl->tpl_vars['index']->value] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['HISTORIES']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['HISTORY']->key => $_smarty_tpl->tpl_vars['HISTORY']->value){
$_smarty_tpl->tpl_vars['HISTORY']->_loop = true;
 $_smarty_tpl->tpl_vars[$_smarty_tpl->tpl_vars['index']->value]->value = $_smarty_tpl->tpl_vars['HISTORY']->key;
?>
		<?php $_smarty_tpl->tpl_vars['MODELNAME'] = new Smarty_variable(get_class($_smarty_tpl->tpl_vars['HISTORY']->value), null, 0);?>
		<?php if ($_smarty_tpl->tpl_vars['MODELNAME']->value=='ModTracker_Record_Model'){?>
			<?php $_smarty_tpl->tpl_vars['USER'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getModifiedBy(), null, 0);?>
			<?php $_smarty_tpl->tpl_vars['TIME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getActivityTime(), null, 0);?>
			<?php $_smarty_tpl->tpl_vars['PARENT'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getParent(), null, 0);?>
			<?php $_smarty_tpl->tpl_vars['MOD_NAME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getParent()->getModule()->getName(), null, 0);?>
			<?php $_smarty_tpl->tpl_vars['SINGLE_MODULE_NAME'] = new Smarty_variable(('SINGLE_').($_smarty_tpl->tpl_vars['MOD_NAME']->value), null, 0);?>
			<?php $_smarty_tpl->tpl_vars['TRANSLATED_MODULE_NAME'] = new Smarty_variable(vtranslate($_smarty_tpl->tpl_vars['SINGLE_MODULE_NAME']->value,$_smarty_tpl->tpl_vars['MOD_NAME']->value), null, 0);?>
			<div class="row-fluid">
				<div class='span1'>
					<img width='24px' src="<?php echo vimage_path(($_smarty_tpl->tpl_vars['MOD_NAME']->value).('.png'));?>
" alt="<?php echo $_smarty_tpl->tpl_vars['TRANSLATED_MODULE_NAME']->value;?>
" title="<?php echo $_smarty_tpl->tpl_vars['TRANSLATED_MODULE_NAME']->value;?>
" />&nbsp;&nbsp;
				</div>
				<div class="span11">
				<p class="pull-right muted" style="padding-right:5px;"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString(($_smarty_tpl->tpl_vars['TIME']->value));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings(($_smarty_tpl->tpl_vars['TIME']->value));?>
</small></p>
				<?php $_smarty_tpl->tpl_vars['DETAILVIEW_URL'] = new Smarty_variable($_smarty_tpl->tpl_vars['PARENT']->value->getDetailViewUrl(), null, 0);?>
				<?php if ($_smarty_tpl->tpl_vars['HISTORY']->value->isUpdate()){?>
					<?php $_smarty_tpl->tpl_vars['FIELDS'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getFieldInstances(), null, 0);?>
					<div class="">
						<div><b><?php echo $_smarty_tpl->tpl_vars['USER']->value->getName();?>
</b> <?php echo vtranslate('LBL_UPDATED');?>
 <a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value,'javascript:')===0){?>
							onclick='<?php echo substr($_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value,strlen("javascript:"));?>
' <?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value;?>
"' <?php }?>>
							<?php echo $_smarty_tpl->tpl_vars['PARENT']->value->getName();?>
</a>
						</div>
						<?php  $_smarty_tpl->tpl_vars['FIELD'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['FIELD']->_loop = false;
 $_smarty_tpl->tpl_vars['INDEX'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['FIELDS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['FIELD']->key => $_smarty_tpl->tpl_vars['FIELD']->value){
$_smarty_tpl->tpl_vars['FIELD']->_loop = true;
 $_smarty_tpl->tpl_vars['INDEX']->value = $_smarty_tpl->tpl_vars['FIELD']->key;
?>
						<?php if ($_smarty_tpl->tpl_vars['INDEX']->value<2){?>
							<?php if ($_smarty_tpl->tpl_vars['FIELD']->value&&$_smarty_tpl->tpl_vars['FIELD']->value->getFieldInstance()&&$_smarty_tpl->tpl_vars['FIELD']->value->getFieldInstance()->isViewableInDetailView()){?>
							<div class='font-x-small'>
								<i><?php echo vtranslate($_smarty_tpl->tpl_vars['FIELD']->value->getName(),$_smarty_tpl->tpl_vars['FIELD']->value->getModuleName());?>
</i>
								<?php if ($_smarty_tpl->tpl_vars['FIELD']->value->get('prevalue')!=''){?>
									<?php echo vtranslate('LBL_FROM');?>
 <b><?php echo Vtiger_Util_Helper::toVtiger6SafeHTML($_smarty_tpl->tpl_vars['FIELD']->value->getDisplayValue(decode_html($_smarty_tpl->tpl_vars['FIELD']->value->get('prevalue'))));?>
</b>
								<?php }else{ ?>
									<?php echo vtranslate('LBL_CHANGED');?>

								<?php }?>
									<?php echo vtranslate('LBL_TO');?>
 <b><?php echo Vtiger_Util_Helper::toVtiger6SafeHTML($_smarty_tpl->tpl_vars['FIELD']->value->getDisplayValue(decode_html($_smarty_tpl->tpl_vars['FIELD']->value->get('postvalue'))));?>
</b>
							</div>
							<?php }?>
						<?php }else{ ?>
							<a href="<?php echo $_smarty_tpl->tpl_vars['PARENT']->value->getUpdatesUrl();?>
"><?php echo vtranslate('LBL_MORE');?>
</a>
							<?php break 1?>
						<?php }?>
						<?php } ?>
					</div>
				<?php }elseif($_smarty_tpl->tpl_vars['HISTORY']->value->isCreate()){?>
					<div class=''  style='margin-top:5px'>
						<b><?php echo $_smarty_tpl->tpl_vars['USER']->value->getName();?>
</b> <?php echo vtranslate('LBL_ADDED');?>
 <a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value,'javascript:')===0){?>
							onclick='<?php echo substr($_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value,strlen("javascript:"));?>
' <?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['DETAILVIEW_URL']->value;?>
"' <?php }?>>
							<?php echo $_smarty_tpl->tpl_vars['PARENT']->value->getName();?>
</a>
					</div>
				<?php }elseif($_smarty_tpl->tpl_vars['HISTORY']->value->isRelationLink()){?>
					<?php $_smarty_tpl->tpl_vars['RELATION'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getRelationInstance(), null, 0);?>
					<?php $_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATION']->value->getLinkedRecord()->getDetailViewUrl(), null, 0);?>
					<?php $_smarty_tpl->tpl_vars['PARENT_DETAIL_URL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATION']->value->getParent()->getParent()->getDetailViewUrl(), null, 0);?>
					<div class='' style='margin-top:5px'>
						<b><?php echo $_smarty_tpl->tpl_vars['USER']->value->getName();?>
</b> <?php echo vtranslate('LBL_ADDED');?>
 <a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value,'javascript:')===0){?>
							onclick='<?php echo substr($_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value,strlen("javascript:"));?>
' <?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value;?>
"' <?php }?>>
							<?php echo $_smarty_tpl->tpl_vars['RELATION']->value->getLinkedRecord()->getName();?>
</a>
						<?php echo vtranslate('LBL_FOR');?>
 <a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value,'javascript:')===0){?>
							onclick='<?php echo substr($_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value,strlen("javascript:"));?>
' <?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value;?>
"' <?php }?>>
							<?php echo $_smarty_tpl->tpl_vars['RELATION']->value->getParent()->getParent()->getName();?>
</a>
					</div>
				<?php }elseif($_smarty_tpl->tpl_vars['HISTORY']->value->isRelationUnLink()){?>
					<?php $_smarty_tpl->tpl_vars['RELATION'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getRelationInstance(), null, 0);?>
					<?php $_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATION']->value->getLinkedRecord()->getDetailViewUrl(), null, 0);?>
					<?php $_smarty_tpl->tpl_vars['PARENT_DETAIL_URL'] = new Smarty_variable($_smarty_tpl->tpl_vars['RELATION']->value->getParent()->getParent()->getDetailViewUrl(), null, 0);?>
					<div class='' style='margin-top:5px'>
						<b><?php echo $_smarty_tpl->tpl_vars['USER']->value->getName();?>
</b> <?php echo vtranslate('LBL_REMOVED');?>

							<a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value,'javascript:')===0){?> onclick='<?php echo substr($_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value,strlen("javascript:"));?>
'
								<?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['LINKED_RECORD_DETAIL_URL']->value;?>
"' <?php }?>><?php echo $_smarty_tpl->tpl_vars['RELATION']->value->getLinkedRecord()->getName();?>
</a>
						<?php echo vtranslate('LBL_FOR');?>
 <a class="cursorPointer" <?php if (stripos($_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value,'javascript:')===0){?>
							onclick='<?php echo substr($_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value,strlen("javascript:"));?>
' <?php }else{ ?> onclick='window.location.href="<?php echo $_smarty_tpl->tpl_vars['PARENT_DETAIL_URL']->value;?>
"' <?php }?>>
							<?php echo $_smarty_tpl->tpl_vars['RELATION']->value->getParent()->getParent()->getName();?>
</a>
					</div>
				<?php }?>
				</div>
			</div>
			<?php }elseif($_smarty_tpl->tpl_vars['MODELNAME']->value=='ModComments_Record_Model'){?>
			<div class="row-fluid">
				<div class="span1">
					<image width='24px' src="<?php echo vimage_path('Comments.png');?>
"/>&nbsp;&nbsp;
				</div>
				<div class="span11">
					<?php $_smarty_tpl->tpl_vars['COMMENT_TIME'] = new Smarty_variable($_smarty_tpl->tpl_vars['HISTORY']->value->getCommentedTime(), null, 0);?>
					<p class="pull-right muted" style="padding-right:5px;"><small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString(($_smarty_tpl->tpl_vars['COMMENT_TIME']->value));?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings(($_smarty_tpl->tpl_vars['COMMENT_TIME']->value));?>
</small></p>
					<div>
						<b><?php echo $_smarty_tpl->tpl_vars['HISTORY']->value->getCommentedByModel()->getName();?>
</b> <?php echo vtranslate('LBL_COMMENTED');?>
 <?php echo vtranslate('LBL_ON');?>
 <a class="textOverflowEllipsis" href="<?php echo $_smarty_tpl->tpl_vars['HISTORY']->value->getParentRecordModel()->getDetailViewUrl();?>
"><?php echo $_smarty_tpl->tpl_vars['HISTORY']->value->getParentRecordModel()->getName();?>
</a>
					</div>
					<div class='font-x-small'><i>"<?php echo $_smarty_tpl->tpl_vars['HISTORY']->value->get('commentcontent');?>
"</i></div>
				</div>
			</div>
		<?php }?>
	<?php } ?>

	<?php if ($_smarty_tpl->tpl_vars['NEXTPAGE']->value){?>
	<div class="row-fluid">
		<div class="span12">
			<a href="javascript:;" class="load-more" data-page="<?php echo $_smarty_tpl->tpl_vars['PAGE']->value;?>
" data-nextpage="<?php echo $_smarty_tpl->tpl_vars['NEXTPAGE']->value;?>
"><?php echo vtranslate('LBL_MORE');?>
...</a>
		</div>
	</div>
	<?php }?>

<?php }else{ ?>
	<span class="noDataMsg">
		<?php echo vtranslate('LBL_NO_UPDATES_OR_COMMENTS',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>

	</span>
<?php }?>
</div>
<?php }} ?>