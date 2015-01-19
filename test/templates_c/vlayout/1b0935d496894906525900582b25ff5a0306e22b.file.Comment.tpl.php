<?php /* Smarty version Smarty-3.1.7, created on 2014-12-10 12:13:58
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/Comment.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1193819430545c98766ee5c9-64860288%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '1b0935d496894906525900582b25ff5a0306e22b' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/Comment.tpl',
      1 => 1413619588,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1193819430545c98766ee5c9-64860288',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_545c987690595',
  'variables' => 
  array (
    'COMMENT' => 0,
    'IMAGE_PATH' => 0,
    'COMMENTOR' => 0,
    'MODULE_NAME' => 0,
    'CHILDS_ROOT_PARENT_MODEL' => 0,
    'CURRENTUSER' => 0,
    'CHILD_COMMENTS_MODEL' => 0,
    'CHILDS_ROOT_PARENT_ID' => 0,
    'PARENT_COMMENT_ID' => 0,
    'CHILD_COMMENTS_COUNT' => 0,
    'REASON_TO_EDIT' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545c987690595')) {function content_545c987690595($_smarty_tpl) {?>
<hr><div class="commentDiv"><div class="singleComment"><div class="commentInfoHeader row-fluid" data-commentid="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getId();?>
" data-parentcommentid="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->get('parent_comments');?>
"><div class="commentTitle" id="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getId();?>
"><?php $_smarty_tpl->tpl_vars['PARENT_COMMENT_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->getParentCommentModel(), null, 0);?><?php $_smarty_tpl->tpl_vars['CHILD_COMMENTS_MODEL'] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->getChildComments(), null, 0);?><div class="row-fluid"><div class="span1"><?php $_smarty_tpl->tpl_vars['IMAGE_PATH'] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->getImagePath(), null, 0);?><img class="alignMiddle pull-left" src="<?php if (!empty($_smarty_tpl->tpl_vars['IMAGE_PATH']->value)){?><?php echo $_smarty_tpl->tpl_vars['IMAGE_PATH']->value;?>
<?php }else{ ?><?php echo vimage_path('DefaultUserIcon.png');?>
<?php }?>"></div><div class="span11 commentorInfo"><?php $_smarty_tpl->tpl_vars['COMMENTOR'] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->getCommentedByModel(), null, 0);?><div class="inner"><span class="commentorName pull-left"><strong><?php echo $_smarty_tpl->tpl_vars['COMMENTOR']->value->getName();?>
</strong></span><span class="pull-right"><p class="muted"><em><?php echo vtranslate('LBL_COMMENTED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</em>&nbsp;<small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['COMMENT']->value->getCommentedTime());?>
"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['COMMENT']->value->getCommentedTime());?>
</small></p></span><div class="clearfix"></div></div><div class="commentInfoContent"><?php echo nl2br($_smarty_tpl->tpl_vars['COMMENT']->value->get('commentcontent'));?>
</div></div></div></div></div><div class="row-fluid commentActionsContainer"><div class="row-fluid commentActionsDiv"><div class="pull-right commentActions"><?php if ($_smarty_tpl->tpl_vars['CHILDS_ROOT_PARENT_MODEL']->value){?><?php $_smarty_tpl->tpl_vars['CHILDS_ROOT_PARENT_ID'] = new Smarty_variable($_smarty_tpl->tpl_vars['CHILDS_ROOT_PARENT_MODEL']->value->getId(), null, 0);?><?php }?><span><a class="cursorPointer replyComment"><i class="icon-share-alt"></i><?php echo vtranslate('LBL_REPLY',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a><?php if ($_smarty_tpl->tpl_vars['CURRENTUSER']->value->getId()==$_smarty_tpl->tpl_vars['COMMENT']->value->get('userid')){?>&nbsp;<span style="color:black">|</span>&nbsp;<a class="cursorPointer editComment feedback"><?php echo vtranslate('LBL_EDIT',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</a><?php }?></span><?php $_smarty_tpl->tpl_vars['CHILD_COMMENTS_COUNT'] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount(), null, 0);?><?php if ($_smarty_tpl->tpl_vars['CHILD_COMMENTS_MODEL']->value!=null&&($_smarty_tpl->tpl_vars['CHILDS_ROOT_PARENT_ID']->value!=$_smarty_tpl->tpl_vars['PARENT_COMMENT_ID']->value)){?>&nbsp;<span style="color:black">|</span>&nbsp;<span class="viewThreadBlock" data-child-comments-count="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
"><a class="cursorPointer viewThread"><span class="childCommentsCount"><?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
</span>&nbsp;<?php if ($_smarty_tpl->tpl_vars['CHILD_COMMENTS_COUNT']->value==1){?><?php echo vtranslate('LBL_REPLY',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }else{ ?><?php echo vtranslate('LBL_REPLIES',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }?><img class="alignMiddle" src="<?php echo vimage_path('rightArrowSmall.png');?>
" /></a></span><span class="hide hideThreadBlock" data-child-comments-count="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
"><a class="cursorPointer hideThread"><span class="childCommentsCount"><?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
</span>&nbsp;<?php if ($_smarty_tpl->tpl_vars['CHILD_COMMENTS_COUNT']->value==1){?><?php echo vtranslate('LBL_REPLY',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }else{ ?><?php echo vtranslate('LBL_REPLIES',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }?><img class="alignMiddle" src="<?php echo vimage_path('downArrowSmall.png');?>
" /></a></span><?php }elseif($_smarty_tpl->tpl_vars['CHILD_COMMENTS_MODEL']->value!=null&&($_smarty_tpl->tpl_vars['CHILDS_ROOT_PARENT_ID']->value==$_smarty_tpl->tpl_vars['PARENT_COMMENT_ID']->value)){?>&nbsp;<span style="color:black">|</span>&nbsp;<span class="hide viewThreadBlock" data-child-comments-count="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
"><a class="cursorPointer viewThread"><span class="childCommentsCount"><?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
</span>&nbsp;<?php if ($_smarty_tpl->tpl_vars['CHILD_COMMENTS_COUNT']->value==1){?><?php echo vtranslate('LBL_REPLY',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }else{ ?><?php echo vtranslate('LBL_REPLIES',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }?><img class="alignMiddle" src="<?php echo vimage_path('rightArrowSmall.png');?>
" /></a></span><span class="hideThreadBlock" data-child-comments-count="<?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
"><a class="cursorPointer hideThread"><span class="childCommentsCount"><?php echo $_smarty_tpl->tpl_vars['COMMENT']->value->getChildCommentsCount();?>
</span>&nbsp;<?php if ($_smarty_tpl->tpl_vars['CHILD_COMMENTS_COUNT']->value==1){?><?php echo vtranslate('LBL_REPLY',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }else{ ?><?php echo vtranslate('LBL_REPLIES',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
<?php }?><img class="alignMiddle" src="<?php echo vimage_path('downArrowSmall.png');?>
" /></a></span><?php }?></div></div><?php $_smarty_tpl->tpl_vars["REASON_TO_EDIT"] = new Smarty_variable($_smarty_tpl->tpl_vars['COMMENT']->value->get('reasontoedit'), null, 0);?><div class="row-fluid"  name="editStatus"><hr style="border-color: gray;border-style: dashed;"><div class="row-fluid pushUpandDown2per"><span class="<?php if (empty($_smarty_tpl->tpl_vars['REASON_TO_EDIT']->value)){?>hide<?php }?> span6">[ <?php echo vtranslate('LBL_EDIT_REASON',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
 ] : <span  name="editReason" class="textOverflowEllipsis"><?php echo nl2br($_smarty_tpl->tpl_vars['REASON_TO_EDIT']->value);?>
</span></span><?php if ($_smarty_tpl->tpl_vars['COMMENT']->value->getCommentedTime()!=$_smarty_tpl->tpl_vars['COMMENT']->value->getModifiedTime()){?><span class="<?php if (empty($_smarty_tpl->tpl_vars['REASON_TO_EDIT']->value)){?>row-fluid<?php }else{ ?> span6<?php }?>"><span class="pull-right"><p class="muted"><em><?php echo vtranslate('LBL_MODIFIED',$_smarty_tpl->tpl_vars['MODULE_NAME']->value);?>
</em>&nbsp;<small title="<?php echo Vtiger_Util_Helper::formatDateTimeIntoDayString($_smarty_tpl->tpl_vars['COMMENT']->value->getModifiedTime());?>
" class="commentModifiedTime"><?php echo Vtiger_Util_Helper::formatDateDiffInStrings($_smarty_tpl->tpl_vars['COMMENT']->value->getModifiedTime());?>
</small></p></span></span><?php }?></div></div></div></div><div>

<?php }} ?>