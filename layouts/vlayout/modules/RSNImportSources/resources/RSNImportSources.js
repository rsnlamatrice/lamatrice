if (typeof(RSNImportSourcesJs) == 'undefined') {
    /*
	 * Namespaced javascript class for RSNImport
	 */
    RSNImportSourcesJs = {

    	registerEvent: function() {
    		RSNImportSourcesJs.registerSelectSourceEvent();
    		RSNImportSourcesJs.registerBasicNextButtonEvent();
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

		loadSourceConfiguration: function(view) {
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

		    		var onLoadFunction = sourceConfiguration.find('input[class="onLoad"]').val();//tmp
			
					if (typeof RSNImportSourcesJs[onLoadFunction] === "function") {
						return RSNImportSourcesJs[onLoadFunction]();
					}
		    	}
		    });
		},

		getForModuleName: function() {
			return jQuery('#for_module').val();
		},

		preImport: function(e) {
			var validateFunctionName = $('#sourceconfiguration input[class="validateconfiguration"]').val();//tmp
			
			if (typeof RSNImportSourcesJs[validateFunctionName] === "function") {
				return RSNImportSourcesJs[validateFunctionName](e);
			}

			return true;
		},

		validateFile: function(e) {
			if (!RSNImportSourcesJs.validateFilePath()) {
				e.preventDefault();
				return false;
			}
			
			return true;
		},

		validateFilePath: function() { // tmp !!!!
			var importFile = jQuery('#import_file');
			var filePath = importFile.val();
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
			if(!RSNImportSourcesJs.validateUploadFileSize("import_file")) {
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
    }

	jQuery(document).ready(function() {
		RSNImportSourcesJs.registerEvent();
	});
}