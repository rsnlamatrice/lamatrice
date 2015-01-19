/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
jQuery.Class('Settings_Folders_Js', {}, {
	
	//This will store the Folders Container
	foldersContainer : false,
	
	//This will store the  MenuList selectElement
	folderListSelectElement : false,
	
	//This will store the Folders Form
	foldersForm : false,
	
	/**
	 * Function to get the Folders container
	 */
	getContainer : function() {
		if(this.foldersContainer == false) {
			this.foldersContainer = jQuery('#foldersContainer');
		}
		return this.foldersContainer;
	},
	
	/**
	 * Function to get the Folders form
	 */
	getForm : function() {
		if(this.foldersForm == false) {
			this.foldersForm = jQuery('#foldersContainer');
		}
		return this.foldersForm;
	},
	/**
	 * Function which will show the save button in folders Container
	 */
	showSaveButton : function(target) {
		
		var container = this.getContainer();
		var saveButton = jQuery('[name="saveFoldersList"]', container);
		
		if(app.isHidden(saveButton)) {
			saveButton.removeClass('hide');
		}
		/* ED141226
		 * marque les dossiers modifiés
		 */
		if (!target)
			target = container;
		else
			target = $(target).parents('tr');
		target.find('input[name="changed[]"]').val('1');
		
	},
	
	/* ED141226 */
	registerEventForColorPickerFields : function(parentElement) {
		thisInstance = this;
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if(parentElement.is('.colorField')){
			var element = parentElement;
		}else{
			var element = jQuery('.colorField', parentElement);
		}
		if(element.length == 0){
			return;
		}
		element.each(function(){
			var $this = $(this);
			var id = this.id;
			if (!id) {
				id = this.id = Math.random();
			}
			var selectorId = '#' + id + '-colorSelector';
			$(selectorId).ColorPicker({
				color: $this.val(),
				onShow: function (colpkr) {
					$(colpkr).fadeIn(200);
					return false;
				},
				onHide: function (colpkr) {
					var $input = $this;
					if ($input.parent().hasClass('edit')) { /*Detail view -> Edit on click*/
						$input
							.parent().prev().click() /* ne valide pas mais permet de declencher le submit en 1 seul clic ailleurs. Enfin, c'est bizarre
							* TODO : 	gerer le reset a la couleur d'origine (clic en haut a droite du pickcolor)
							* 		*/
						;
					}
					$(colpkr).fadeOut(200);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					var $colorSelector = $(this.data('colorpicker').el);
					$input = $('#' + $colorSelector.attr('id').replace(/\-colorSelector$/, ''));
					$input.val('#' + hex);
					$colorSelector.children('div').css('backgroundColor', '#' + hex);
					thisInstance.showSaveButton($input);
				}
			})
		});
	},
	
	registerPickListValuesSortableEvent : function(container) {
		thisInstance = this;
		var tbody = jQuery( "tbody",jQuery('#pickListValuesTable'));
		tbody.sortable({
			'helper' : function(e,ui){
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function(index,element){
					element = jQuery(element);
					element.width(element.width());
				})
				return ui;
			},
			'containment' : tbody,
			'revert' : true,
			'update': function(e, ui ) {
				thisInstance.showSaveButton();
			}
		});
	},
	
	/* ED141226 */
	registerEventForEditable : function(container){
		var thisInstance = this;
		$('.folder--description', container).on('click', function() {
			$(this)
				.hide()
				.nextAll('textarea:first')
					.removeClass('hide')
			;
		});
	},
	
	/* ED141226 */
	registerDeleteEvent : function(container){
		var thisInstance = this;
		$('.actionImages > .icon-trash', container).on('click', function() {
			$trSrc = $(this).parents('tr:first');
			if (($trSrc.find('input[name="folderid[]"]').val() == 'new')
			|| confirm('La suppression est d\351finitive !')) {
				$trSrc
					.find('input[name="deleted[]"]')
						.val('1')
						.end()
					.hide()
				;
				thisInstance.showSaveButton(this);
			}
		});
	},
	
	/* ED141226 */
	registerDuplicateEvent : function(container){
		var thisInstance = this;
		$('.actionImages > .icon-plus', container).on('click', function() {
			$trSrc = $(this).parents('tr:first');
			$tr = $trSrc.clone();
			$tr.insertAfter($trSrc);
			
			var id = ('_' + Math.random()).substr(3);
			var colorSelectorId = id + '-colorSelector';
			
			$tr
				.find('.notes-counter').html('nouveau')
					.end()
				.find('input[name="folderid[]"]').val('new')
					.end()
				.find('input[name="changed[]"]').val('1')
					.end()
				.find('textarea[name="description[]"]').val('')
					.end()
				.find('input[name="uicolor[]"]').attr('id', id)
					.end() 	
				.find('.colorpicker-holder').attr('id', colorSelectorId)
					.end() 	
				.find('input[name="foldername[]"]').val('')
					.focus()
			;
			
			//this.registerPickListValuesSortableEvent($tr);
			thisInstance.registerEventForColorPickerFields($tr);
			thisInstance.registerEventForEditable($tr);
			thisInstance.registerInputsEvent($tr);
			thisInstance.registerDeleteEvent($tr);
			thisInstance.registerDuplicateEvent($tr);
			thisInstance.registerUIEvents($tr);
		
			$tr
				.find('.folder--description').click()
					.end()
			;
			thisInstance.showSaveButton(this);
		});
	},
	
	/* ED141226 */
	registerInputsEvent : function(container){
		var thisInstance = this;
		$(':input[name]', container).on('change', function() {
			thisInstance.showSaveButton(this);
		});
	},
	
	/* ED141226 */
	registerUIEvents : function(container){
		if (!container.is('tr'))
			$tr = $('tr', container);
		else
			$tr = container;
		$tr.hover( function() {
			$(this).find('div.actions > .actionImages')
				.removeClass('hide');
		}, function() {
			$(this).find('div.actions > .actionImages')
				.addClass('hide');
		});
	},
	
	validateInputs : function(form){
		var error;
		var names = {};
		$(form.find('input[name="deleted[]"]')).each(function(){
			if (this.value) return;
			var $tr = $(this).parents('tr:first');
			var name = $tr.find('input[name="foldername[]"]').val().trim();
			if (!name) {
				error = "Le nom est vide";
				return false;
			}
			if (names[name]) {
				error = name + " est déjà utilisé";
				return false;
			}
			names[name] = true;
		});
		if (error) {
			$('<div></div>').html(error).dialog({
				title: 'Attention',
				modal: true,
				buttons: [{
					text: "Fermer",
					click: function() { $( this ).dialog( "close" ); }
				}]
			});
			return false;
		}
		return true;
	},
	
	registerEvents : function(e){
		var thisInstance = this;
		var container = thisInstance.getContainer();
		var form = thisInstance.getForm();
		
		/* ED141226 */
		this.registerPickListValuesSortableEvent(container);
		this.registerEventForColorPickerFields(container);
		this.registerEventForEditable(container);
		this.registerInputsEvent(container);
		this.registerDeleteEvent(container);
		this.registerDuplicateEvent(container);
		this.registerUIEvents(container);
		
		var params = app.getvalidationEngineOptions(true);
		params.onValidationComplete = function(form, valid){
			if(valid) {
				if (!thisInstance.validateInputs(form)) {
					return false;
				}
				var progressIndicatorElement = jQuery.progressIndicator({
					'position' : 'html',
					'blockInfo' : {
						'enabled' : true
					}
				});
				//avant l'enregistrement, met à jour des champs
				var pickListValues = jQuery('#pickListValuesTable').find('.pickListValue');
				var index = 0;
				jQuery.each(pickListValues,function(i,element) {
					var $this = $(this);
					if ($this.find('input[name="deleted[]"]').val() == '1') {
						return;
					}
					var $input = $this.find('input[name="sequence[]"]');
					$input.val(index++);
					
					$this.find('input[disabled]')
						.removeAttr('disabled');
				});
			}
			return valid;
		}
		
		form.validationEngine(params);
	}
});


jQuery(document).ready(function(){
	var settingFoldersInstance = new Settings_Folders_Js();
	settingFoldersInstance.registerEvents();
})
