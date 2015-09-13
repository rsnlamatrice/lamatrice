if (typeof(RSNImportSourcesJs) == 'undefined') {
    /*
	 * Namespaced javascript class for RSNImport
	 */
    RSNImportSourcesJs = {

    	registerEvent: function() {
    		this.registerSelectSourceEvent();
    		this.registerBasicNextButtonEvent();
			this.registerGetMorePreviewDataEvent();
    	},

		/** ED150906
		 * Get More Preview Data
		 */
    	registerGetMorePreviewDataEvent: function() {
			jQuery('body').on('click', '.getMorePreviewData', function(e){
				e.preventDefault();
				
				var url = this.href
				, $thisElement = $(this)
				, $destTablePage = $thisElement.parents('table:first')
				, $destTableData = $destTablePage.find('table.importContents:first');
				AppConnector.request(url).then(
					function(data){
						var $data = $(data)
						, $trs = $data.find('table.importContents > tbody > tr')
						, $tfootRows = $data.find('table.searchUIBasic > tfoot > tr')
						;
						$destTableData.children('tbody').append($trs);//append rows
						$destTablePage.children('tfoot').html($tfootRows);//replace table foot for next link
						
					},
					function(error){
					}
				);
				
				return false;
			});
		},

    	registerSelectSourceEvent: function() {
			jQuery('#SelectSourceDropdown').on('change', function(e){
				$('button[name="next"]').removeAttr('disabled');
				RSNImportSourcesJs.loadSourceConfiguration(this.value);
			});
		},

		registerBasicNextButtonEvent: function() {
			var sourceDropdown = jQuery('#SelectSourceDropdown');
			if (typeof sourceDropdown !== "undefined") { // tmp valide only for first view !!
				if (!sourceDropdown.val()) {
					$('button[name="next"]').attr("disabled", "disabled");
				} else {
					RSNImportSourcesJs.loadSourceConfiguration(jQuery('#SelectSourceDropdown').val());
				}
			}
			$('button[name="next"]').on('click', function(e) {
				RSNImportSourcesJs.preImport(e);
			});
		},

		registerFileConfigurationEvent: function() {
			RSNImportSourcesJs.registerAdvancedFileConfigurationEvent();
			RSNImportSourcesJs.registerFileConfigurationChangeEvent();
		},

		registerAdvancedFileConfigurationEvent: function() {
			var button = jQuery('#show_advanced_file_configuration');
			if (typeof button !== 'undefined') {
				advancedFileConfiguration = jQuery("#advanced_file_configuration");
				advancedFileConfiguration.hide();

				button.on('click', function(e) {
					advancedFileConfiguration.slideToggle();
				});
			}
		},

		registerFileConfigurationChangeEvent: function() {
			jQuery("#import_file").on('change', function(e) {
				RSNImportSourcesJs.checkFileType(e);
			});


			RSNImportSourcesJs.handleNeededFileTypeChange();
			jQuery("#file_type").on('change', function(e) {
				RSNImportSourcesJs.handleNeededFileTypeChange(e);
			});

			jQuery("#file_encoding").on('change', function(e) {
				RSNImportSourcesJs.handleNeededFileEncodingChange(e);
			});

			jQuery("#file_delimiter").on('change', function(e) {
				RSNImportSourcesJs.handleNeededFileDelimiterChange(e);
			});
		},
		
		//Les sources disposent d'une description dans leur balise option
		showSelectedSourceDescription: function(view){
			var descr = $('#SelectSourceDropdown option=[value="' + view + '"][title]').attr('title')
			, $descr = $('#data-import-selected-description').html(descr)
			;
			if (!descr || descr == '<br>')
				$descr.hide();
			else
				$descr
					.show()
					.append($('<span class="pull-right"/>')
						.append($('<a href="#">?</a>')
							.click(function(e){
							})
						)
					)
				;
		},

		loadSourceConfiguration: function(view) {
			this.showSelectedSourceDescription(view);
			var data = {
				module: app.getModuleName(),
				for_module: RSNImportSourcesJs.getForModuleName(),
		        view: view,
		        mode: 'showConfiguration'
			}

			$.post('index.php', data,
		    function(data, status) {
		    	if (status == "success") {
		    		var sourceConfiguration = $('#sourceconfiguration');
		    		sourceConfiguration.empty();
		    		sourceConfiguration.append(data);

		    		sourceConfiguration.find('input[class="onLoad"]').each(function(){
						var onLoadFunction = this.value;
						if (typeof RSNImportSourcesJs[onLoadFunction] === "function") {
							return RSNImportSourcesJs[onLoadFunction]();
						}
					});
		    	}
		    });
		},

		getForModuleName: function() {
			return jQuery('#for_module').val();
		},

		preImport: function(e) {
			var cancel = false;
			$('#sourceconfiguration input[class="validateconfiguration"]').each(function(){
				var validateFunctionName = this.value;
				if (typeof RSNImportSourcesJs[validateFunctionName] === "function") {
					if( ! RSNImportSourcesJs[validateFunctionName](e)){
						cancel = true;
						return false;
					}
				}
			});
			if (cancel) {
				e.preventDefault();
				return false;
			}
			return true;
		},

		validateFile: function(e) {
			return RSNImportSourcesJs.validateFilePath();
		},
		
		/* ED150829
		 * Mode de définition du fichier à traiter : upload ou localpath (chemin sur le serveur)
		 */
		getFileSrcMode : function(){
			return $('input[type="radio"][name="import_file_src_mode"]:checked').val();
		},

		validateFilePath: function() { // tmp !!!!
			var fileSrcMode = this.getFileSrcMode()
			, importFile = fileSrcMode == 'localpath' ? jQuery('#import_file_localpath') : jQuery('#import_file')
			, filePath = importFile.val();
			if(jQuery.trim(filePath) == '') {
				var errorMessage = app.vtranslate('JS_IMPORT_FILE_CAN_NOT_BE_EMPTY');
				var params = {
					text: errorMessage,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				importFile.focus();
				return false;
			}
			if(!RSNImportSourcesJs.validateFileType("import_file", "curent_file_type", "file_type")) {
				return false;
			}
			if(fileSrcMode == 'upload'
			&& !RSNImportSourcesJs.validateUploadFileSize("import_file")) {
				return false;
			}
			return true;
		},

		validateFileType: function(elementId, curentFileTypeId, fileTypeId) {
			var obj = jQuery('#'+elementId);
			if(obj) {
				var fileType = jQuery('#'+ curentFileTypeId).val();
				var neededFileType = jQuery('#' + fileTypeId).val();
				if(fileType != neededFileType) {
					var errorMessage = app.vtranslate('JS_SELECT_FILE_EXTENSION')+'\n' + neededFileType;
					var params = {
						text: errorMessage,
						type: 'error'};
					Vtiger_Helper_Js.showMessage(params);
					obj.focus();
					return false;
				}
			}
			return true;
		},

		validateUploadFileSize : function(elementId) {
			var element = jQuery('#'+elementId);
			var importMaxUploadSize = element.closest('td').data('importUploadSize');
			var uploadedFileSize = element.get(0).files[0].size;
			if(uploadedFileSize > importMaxUploadSize){
				var unit = 'B';
				while(importMaxUploadSize > 1024 && unit != 'GB') {
					importMaxUploadSize /= 1024;
					switch(unit) {
					case 'B':
						unit = 'KB';
						break;
					case 'KB':
						unit = 'MB';
						break;
					case 'MB':
						unit = 'GB';
						break;	
					}
				}

				var errorMessage = app.vtranslate('JS_UPLOADED_FILE_SIZE_EXCEEDS')+" " + (Math.round(importMaxUploadSize*100)/100) + " " + unit + ' max. ' + app.vtranslate('JS_PLEASE_SPLIT_FILE_AND_IMPORT_AGAIN');
				var params = {
					text: errorMessage,
					type: 'error'
				};
				Vtiger_Helper_Js.showMessage(params);
				return false;
			}
			return true;
		},

		checkFileType: function() {
			var filePath = jQuery('#import_file').val();
			if(filePath != '') {
				var fileExtension = filePath.split('.').pop();
				jQuery('#curent_file_type').val(fileExtension);
				//RSNImportSourcesJs.handleFileTypeChange();
			}
		},

		handleNeededFileTypeChange: function(e) {
			var fileType = jQuery('#file_type').val();
			jQuery('#needed_file_type').html(fileType);
			var file_delimiters_elements = jQuery('.file_delimiter');
			if(fileType != 'csv') {
				file_delimiters_elements.each(function(i) {
					jQuery(file_delimiters_elements[i]).hide();
				})
			} else {
				file_delimiters_elements.each(function(i) {
					jQuery(file_delimiters_elements[i]).show();
				})
			}
		},

		handleNeededFileEncodingChange: function(e) {
			var fileEncoding = jQuery('#file_encoding').val();
			jQuery('#needed_file_encoding').html(fileEncoding);
		},

		handleNeededFileDelimiterChange: function(e) {
			var fileDelimiterElement = jQuery('#file_delimiter');
			var fileDelimiter = fileDelimiterElement.val();
			var fileDelimiterName = jQuery('.delimiter_plural[value="' + fileDelimiter + '"]').html();
			jQuery('#needed_file_delimiter').html(fileDelimiterName);
		},
    
		/* ED150913
		 * Validation de la bonne sélection d'un élément lié
		*/
		validateRelatedRecordSelected: function(e) {
			var fieldName = $('#related_record_fieldname').val()
			, $input = $('input[name="' + fieldName + '"]')
			;
			if(! $input.val()){
				$input.parents(':visible:first').find('input:visible:first')
					.validationEngine('showPrompt', 'Veuillez sélectionner un élément', 'error', 'bottomLeft',true);
				return false;
			}
		return true;
		},
		
		/* ED150913
		 * Initialisation de l'étape de sélection d'une entité liée (Document pétition, Critere4D, ...)
		*/
		registerRelatedRecordSelectionEvent: function() {
			
			this.referenceModulePopupRegisterEvent($('#sourceconfiguration'));
		},
		
		/* Copie depuis Edit.js */
		/* Initialise le sélecteur d'entité */
		referenceModulePopupRegisterEvent : function(container){
			var thisInstance = this;
			container.find('.relatedPopup').on("click",function(e){
				thisInstance.openPopUp(e);
			});
			container.find('.referenceModulesList').chosen().change(function(e){
				var element = jQuery(e.currentTarget);
				var closestTD = element.closest('td').next();
				var popupReferenceModule = element.val();
				var referenceModuleElement = jQuery('input[name="popupReferenceModule"]', closestTD);
				var prevSelectedReferenceModule = referenceModuleElement.val();
				referenceModuleElement.val(popupReferenceModule);
	
				//If Reference module is changed then we should clear the previous value
				if(prevSelectedReferenceModule != popupReferenceModule) {
					closestTD.find('.clearReferenceSelection').trigger('click');
				}
			});
		},
		
		getPopUpParams : function(container) {
			var params = {};
			var sourceModule = app.getModuleName();
			var popupReferenceModule = jQuery('input[name="popupReferenceModule"]',container).val();
			var sourceFieldElement = jQuery('input[class="sourceField"]',container);
			var sourceField = sourceFieldElement.attr('name');
			var sourceRecordElement = jQuery('input[name="record"]');
			var sourceRecordId = '';
			if(sourceRecordElement.length > 0) {
			sourceRecordId = sourceRecordElement.val();
			}
		
			var isMultiple = false;
			if(sourceFieldElement.data('multiple') == true){
			isMultiple = true;
			}
		
			var params = {
				'module' : popupReferenceModule,
				'src_module' : sourceModule,
				'src_field' : sourceField,
				/*ED150913 'src_record' : sourceRecordId*/
			}
			
			/*ED150913
			//ED150625
			var fieldInfo = sourceFieldElement.data('fieldinfo');
			if (fieldInfo.search_key) {
				params.search_key = fieldInfo.search_key;
				params.search_value = fieldInfo.search_value;
			}*/
			//ED150913
			params.search_key = jQuery('input[name="related_search_key"]',container).val();
			params.search_value = jQuery('input[name="related_search_value"]',container).val();
			
			if(isMultiple) {
				params.multi_select = true ;
			}
			return params;
		},


		openPopUp : function(e){
			var thisInstance = this;
			var parentElem = jQuery(e.target).closest('td');
	
			var params = this.getPopUpParams(parentElem);
		
			var isMultiple = false;
			if(params.multi_select) {
				isMultiple = true;
			}
		
			var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);
		
			var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
			sourceFieldElement.trigger(prePopupOpenEvent);
		
			if(prePopupOpenEvent.isDefaultPrevented()) {
				return ;
			}
		
			var popupInstance =Vtiger_Popup_Js.getInstance();
			popupInstance.show(params,function(data){
				var responseData = JSON.parse(data);
				var dataList = new Array();
				for(var id in responseData){
					var data = {
						'name' : responseData[id].name,
						'id' : id
					}
					dataList.push(data);
					if(!isMultiple) {
						thisInstance.setReferenceFieldValue(parentElem, data);
					}
				}
			
				if(isMultiple) { //ED150913 TODO non testé et je doute que ça fonctionne
					sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
				}
			});
		},
	
		setReferenceFieldValue : function(container, params) {
			var sourceField = container.find('input[class="sourceField"]').attr('name');
			var fieldElement = container.find('input[name="'+sourceField+'"]');
			var sourceFieldDisplay = sourceField+"_display";
			var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
			var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();
	
			var selectedName = params.name;
			var id = params.id;
	
			fieldElement.val(id)
			fieldDisplayElement.val(selectedName).attr('readonly',true);
			fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});
	
			fieldDisplayElement.validationEngine('closePrompt',fieldDisplayElement);
		},
		/* fin de la copie depuis Edit.js */
	}

	jQuery(document).ready(function() {
		RSNImportSourcesJs.registerEvent();
	});
}