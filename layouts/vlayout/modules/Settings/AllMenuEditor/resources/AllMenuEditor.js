/*+***********************************************************************************
 * ED151112
 *************************************************************************************/
jQuery.Class('Settings_AllMenu_Editor_Js', {}, {
	
	//This will store the MenuEditor Container
	menuEditorContainer : false,
	
	//This will store the MenuEditor Form
	menuEditorForm : false,
	
	/**
	 * Function to get the MenuEditor container
	 */
	getContainer : function() {
		if(this.menuEditorContainer == false) {
			this.menuEditorContainer = jQuery('#menuEditorContainer');
		}
		return this.menuEditorContainer;
	},
	
	/**
	 * Function to get the getBlockContainer
	 */
	getBlocksContainer : function() {
		return this.getForm().find('ul.allMenu');
	},
	
	/**
	 * Function to get the MenuList select element
	 */
	getMenuItems : function($parent) {
		if ($parent) 
			return $parent.find('ul');
	},
	
	/**
	 * Function to get the MenuEditor form
	 */
	getForm : function() {
		if(this.menuEditorForm == false) {
			this.menuEditorForm = jQuery('#menuEditorContainer');
		}
		return this.menuEditorForm;
	},
	
	/**
	 * Function to regiser the event to make the menu items list sortable
	 */
	makeMenuItemsSortable : function() {
		var thisInstance = this;
		var blockContainer = this.getBlocksContainer();
		
		thisInstance.getMenuItems(blockContainer)
			.parent() //ul
				.droppable({
					//'accept' : 'menuItem',
					drop: function( event, ui ) {
						var $draggable = $(ui.draggable);
						if ($draggable.is('.menuParent'))
							return;
						var $itemContainer = $(this).children('ul')
						, itemTop = $draggable.position().top;
						//changement de bloc
						if (ui.draggable[0].parentNode.parentNode !== this){
							ui.position.top = -1; //on va chercher depuis en bas, puisqu'on ajoute à la fin
							$draggable
								.appendTo($itemContainer)
								.css({'position' : 'inherit'})
							;
							
						}
						thisInstance.moveMenuItemAtPosition( ui.position.top, itemTop, $draggable, $itemContainer);
						
				}})
				.end()
				
			.children('li.menuItem')
				.draggable({
					//'scope' : 'menuItem',
					'revert' : 'invalid',
					//'cursor': "crosshair"
				})
				.end()
			
			//can not override drag and drop
			//.sortable({})
			;
		blockContainer.sortable();//sort children
		
	},
	
	/** Déplace l'item
	 */
	moveMenuItemAtPosition : function( direction, reachTop, $draggable, $container){
		var $sibling = $draggable
		, moved = false;
		if(direction <= 0 ){ //vers le haut
			while(($sibling = $sibling.prev()).length){
				if ($sibling.position().top + $sibling.height() < reachTop) {
					$draggable.insertAfter($sibling);
					moved = true;
					break;
				}
				else if ($sibling.position().top < reachTop) {
					$draggable.insertAfter($sibling);
					moved = true;
					break;
				}
			}
			if(!moved)
				if ($draggable.get(0) !== $container.find('li:first').get(0))
					$draggable.insertBefore($container.find('li:first'));
		}
		else { //vers le bas
			while(($sibling = $sibling.next()).length){
				if ($sibling.position().top > (reachTop + $draggable.height())) {
					$draggable.insertAfter($sibling);
					moved = true;
					break;
				}
				else if ($sibling.position().top > reachTop) {
					$draggable.insertBefore($sibling);
					moved = true;
					break;
				}
			}
			if(!moved)
				if ($draggable.get(0) !== $container.find('li:last').get(0))
					$draggable.insertAfter($container.find('li:last'));
		}
		$draggable.removeAttr('style').css('position', 'relative');
	},
	
	/** data to submit
	*/
	getItemsPositions : function(){
		var blocks = {};
		var thisInstance = this;
		var blockContainer = this.getBlocksContainer();
		
		blockContainer.children('li.menuParent')
			.each(function(){
					var blockId = this.getAttribute('data-parent')
					, menuItems = [];
					thisInstance.getMenuItems($(this)).children('li.menuItem')
						.each(function(){
							menuItems.push(this.getAttribute('data-menu'));
						})
					;
					blocks[blockId] = menuItems;
				})
		;
		return blocks;
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		
		//To make the menu items list sortable
		thisInstance.makeMenuItemsSortable();
		
		var params = app.getvalidationEngineOptions(true);
		params.onValidationComplete = function(form, valid){
			if(valid) {
				var progressIndicatorElement = jQuery.progressIndicator({
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				//before saving, update the selected modules list
				jQuery('input[name="itemsPositions"]', thisInstance.getForm()).val(JSON.stringify(thisInstance.getItemsPositions()));
			}
			return valid;
		}
		
		this.getForm().validationEngine(params);
	}
});


jQuery(document).ready(function(){
	var settingAllMenuEditorInstance = new Settings_AllMenu_Editor_Js();
	settingAllMenuEditorInstance.registerEvents();
})
