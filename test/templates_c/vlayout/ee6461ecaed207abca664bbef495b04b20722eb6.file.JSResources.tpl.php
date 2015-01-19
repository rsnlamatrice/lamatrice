<?php /* Smarty version Smarty-3.1.7, created on 2015-01-05 12:37:14
         compiled from "/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/JSResources.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1380889676544e4feedbeac3-65741695%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ee6461ecaed207abca664bbef495b04b20722eb6' => 
    array (
      0 => '/Users/cogi4d/Sites/lamatrice/includes/runtime/../../layouts/vlayout/modules/Vtiger/JSResources.tpl',
      1 => 1413623182,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1380889676544e4feedbeac3-65741695',
  'function' => 
  array (
  ),
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_544e4feee259e',
  'variables' => 
  array (
    'SCRIPTS' => 0,
    'jsModel' => 0,
    'VTIGER_VERSION' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_544e4feee259e')) {function content_544e4feee259e($_smarty_tpl) {?>



	<script type="text/javascript" src="libraries/html5shim/html5.js"></script>

	<script type="text/javascript" src="libraries/jquery/jquery.blockUI.js"></script>
	<script type="text/javascript" src="libraries/jquery/chosen/chosen.jquery.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/select2/select2.min.js"></script>
        <script>/* ED141011 - configuration du jquery/select2
                 * transpose la couleur des options de base dans le plugin
                 */
        $.fn.select2.defaults.__formatResult_original = $.fn.select2.defaults.formatResult;
        $.fn.select2.defaults.formatResult = function(result, container, query) {
            if (result && result.element.length && result.element[0].style.backgroundColor) {
                container.css('background-color', result.element[0].style.backgroundColor);
            }
            return this.__formatResult_original(result, container, query);
        }
        
        /** ED141011
         * when select2 is created, manage colors
         * Called on first load and when filter is selected
         * Pblm : $.select2 does not manage selected attribute
         */
        $('body').on('DOMNodeInserted', 'a.select2-choice', function () {
                var $select = $(this).parents(".select2-container:first").nextAll("select:first");
                var $this = $(this)
                , text = $this.text().trim()
                , $option;
                //search option with same text
                $select.find('option').each(function(){
                    if (text == $(this).text().trim()) {
                        $option = $(this);
                        return false;//break
                    }
                });
                if ($option){
                    var uicolor = $option[0].style.backgroundColor;
                    if (uicolor)
                        //$this.children('span:first').css('background-color', uicolor);
                        $this.css({ 'background-image': 'none', 'background-color': uicolor });
                    else
                        //$this.children('span:first').css('background-color', 'inherit');
                        $this.css({ 'background-image': 'inherit', 'background-color': 'inherit' });
                }
          });
        </script>
	<script type="text/javascript" src="libraries/jquery/jquery-ui/js/jquery-ui-1.8.16.custom.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/jquery.class.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/defunkt-jquery-pjax/jquery.pjax.js"></script>
	<script type="text/javascript" src="libraries/jquery/jstorage.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/autosize/jquery.autosize-min.js"></script>

	<script type="text/javascript" src="libraries/jquery/rochal-jQuery-slimScroll/slimScroll.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/pnotify/jquery.pnotify.min.js"></script>
	<script type="text/javascript" src="libraries/jquery/jquery.hoverIntent.minified.js"></script>

	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-alert.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tooltip.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-collapse.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-modal.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-dropdown.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-popover.js"></script>
	<script type="text/javascript" src="libraries/bootstrap/js/bootbox.min.js"></script>
	<script type="text/javascript" src="resources/jquery.additions.js"></script>
	<script type="text/javascript" src="resources/app.js"></script>
	<script type="text/javascript" src="resources/helper.js"></script>
	<script type="text/javascript" src="resources/Connector.js"></script>
	<script type="text/javascript" src="resources/ProgressIndicator.js" ></script>
	<script type="text/javascript" src="libraries/jquery/posabsolute-jQuery-Validation-Engine/js/jquery.validationEngine.js" ></script>
	<script type="text/javascript" src="libraries/guidersjs/guiders-1.2.6.js"></script>
	<script type="text/javascript" src="libraries/jquery/datepicker/js/datepicker.js"></script>
	<script type="text/javascript" src="libraries/jquery/dangrossman-bootstrap-daterangepicker/date.js"></script>
	<script type="text/javascript" src="libraries/jquery/jquery.ba-outside-events.min.js"></script>
        
	<?php  $_smarty_tpl->tpl_vars['jsModel'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['jsModel']->_loop = false;
 $_smarty_tpl->tpl_vars['index'] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['SCRIPTS']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['jsModel']->key => $_smarty_tpl->tpl_vars['jsModel']->value){
$_smarty_tpl->tpl_vars['jsModel']->_loop = true;
 $_smarty_tpl->tpl_vars['index']->value = $_smarty_tpl->tpl_vars['jsModel']->key;
?>
		<script type="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getType();?>
" src="<?php echo $_smarty_tpl->tpl_vars['jsModel']->value->getSrc();?>
?&v=<?php echo $_smarty_tpl->tpl_vars['VTIGER_VERSION']->value;?>
"></script>
	<?php } ?>

	
	<script type="text/javascript" src="libraries/jquery/colorpicker/js/colorpicker.js"></script>

	<!-- Added in the end since it should be after less file loaded -->
	<script type="text/javascript" src="libraries/bootstrap/js/less.min.js"></script><?php }} ?>