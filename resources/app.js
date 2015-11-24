/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

var app = {

	/**
	 * variable stores client side language strings
	 */
	languageString : [],
	
	
	weekDaysArray : {Sunday : 0,Monday : 1, Tuesday : 2, Wednesday : 3,Thursday : 4, Friday : 5, Saturday : 6},

	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getModuleName : function() {
		return jQuery('#module').val();
	},

	/**
	 * Function to get the module name. This function will get the value from element which has id module
	 * @return : string - module name
	 */
	getParentModuleName : function() {
		return jQuery('#parent').val();
	},

	/**
	 * Function returns the current view name
	 */
	getViewName : function() {
		return jQuery('#view').val();
	},
	/**
	 * Function to get the contents container
	 * @returns jQuery object
	 */
	getContentsContainer : function() {
		return jQuery('.bodyContents');
	},

	/**
	 * Function which will convert ui of select boxes.
	 * @params parent - select element
	 * @params view - select2
	 * @params viewParams - select2 params
	 * @returns jquery object list which represents changed select elements
	 */
	changeSelectElementView : function(parent, view, viewParams){

		var selectElement = jQuery();
		if(typeof parent == 'undefined') {
			parent = jQuery('body');
		}

		//If view is select2, This will convert the ui of select boxes to select2 elements.
		if(view == 'select2') {
			app.showSelect2ElementView(parent, viewParams);
			return;
		}
		selectElement = jQuery('.chzn-select', parent);
		//parent itself is the element
		if(parent.is('select.chzn-select')) {
			selectElement = parent;
		}

		//fix for multiselect error prompt hide when validation is success
		selectElement.filter('[multiple]').filter('[data-validation-engine*="validate"]').on('change',function(e){
			jQuery(e.currentTarget).trigger('focusout');
		});

		var chosenElement = selectElement.chosen();
		var chosenSelectConainer = jQuery('.chzn-container');
		//Fix for z-index issue in IE 7
		if (jQuery.browser.msie && jQuery.browser.version === "7.0") {
			var zidx = 1000;
			chosenSelectConainer.each(function(){
				$(this).css('z-index', zidx);
				zidx-=10;
			});
		}
		return chosenSelectConainer;
	},

	/**
	 * Function to destroy the chosen element and get back the basic select Element
	 */
	destroyChosenElement : function(parent) {
		var selectElement = jQuery();
		if(typeof parent == 'undefined') {
			parent = jQuery('body');
		}

		selectElement = jQuery('.chzn-select', parent);
		//parent itself is the element
		if(parent.is('select.chzn-select')) {
			selectElement = parent;
		}

		selectElement.css('display','block').removeClass("chzn-done").data("chosen", null).next().remove();

		return selectElement;

	},
	/**
	 * Function which will show the select2 element for select boxes . This will use select2 library
	 */
	showSelect2ElementView : function(selectElement, params) {
		if(typeof params == 'undefined') {
			params = {};
		}

		var data = selectElement.data();
		if(data != null) {
			params = jQuery.extend(data,params);
		}

		// Sort DOM nodes alphabetically in select box.
		if (typeof params['customSortOptGroup'] != 'undefined' && params['customSortOptGroup']) {
			jQuery('optgroup', selectElement).each(function(){
				var optgroup = jQuery(this);
				var options  = optgroup.children().toArray().sort(function(a, b){
					var aText = jQuery(a).text();
					var bText = jQuery(b).text();
					return aText < bText ? 1 : -1;
				});
				jQuery.each(options, function(i, v){
					optgroup.prepend(v);
				});
			});
			delete params['customSortOptGroup'];
		}

		//formatSelectionTooBig param is not defined even it has the maximumSelectionSize,
		//then we should send our custom function for formatSelectionTooBig
		if(typeof params.maximumSelectionSize != "undefined" && typeof params.formatSelectionTooBig == "undefined") {
			var limit = params.maximumSelectionSize;
			//custom function which will return the maximum selection size exceeds message.
			var formatSelectionExceeds = function(limit) {
					return app.vtranslate('JS_YOU_CAN_SELECT_ONLY')+' '+limit+' '+app.vtranslate('JS_ITEMS');
			}
			params.formatSelectionTooBig = formatSelectionExceeds;
		}
		if(selectElement.attr('multiple') != 'undefined' && typeof params.closeOnSelect == 'undefined') {
			params.closeOnSelect = false;
		}	
		/* ED141011 TODO background-color */
		selectElement.select2(params)
					 .on("open", function(e) {
						 var element = jQuery(e.currentTarget);
						 var instance = element.data('select2');
						 instance.dropdown.css('z-index',1000002);
					 });
		if(typeof params.maximumSelectionSize != "undefined") {
			app.registerChangeEventForMultiSelect(selectElement,params);
		}
		return selectElement;
	},

	/**
	 * Function to check the maximum selection size of multiselect and update the results
	 * @params <object> multiSelectElement
	 * @params <object> select2 params
	 */

	registerChangeEventForMultiSelect :  function(selectElement,params) {
		if(typeof selectElement == 'undefined') {
			return;
		}
		var instance = selectElement.data('select2');
		var limit = params.maximumSelectionSize;
		selectElement.on('change',function(e){
			var data = instance.data()
			if (jQuery.isArray(data) && data.length >= limit ) {
				instance.updateResults();
            }
		});

	},

	/**
	 * Function to get data of the child elements in serialized format
	 * @params <object> parentElement - element in which the data should be serialized. Can be selector , domelement or jquery object
	 * @params <String> returnFormat - optional which will indicate which format return value should be valid values "object" and "string"
	 * @return <object> - encoded string or value map
	 */
	getSerializedData : function(parentElement, returnFormat){
		if(typeof returnFormat == 'undefined') {
			returnFormat = 'string';
		}

		parentElement = jQuery(parentElement);

		var encodedString = parentElement.children().serialize();
		if(returnFormat == 'string'){
			return encodedString;
		}
		var keyValueMap = {};
		var valueList = encodedString.split('&')

		for(var index in valueList){
			var keyValueString = valueList[index];
			var keyValueArr = keyValueString.split('=');
			var nameOfElement = keyValueArr[0];
			var valueOfElement =  keyValueArr[1];
			keyValueMap[nameOfElement] = decodeURIComponent(valueOfElement);
		}
		return keyValueMap;
	},

	showModalWindow: function(data, url, cb, css) {

		var unBlockCb = function(){};
		var overlayCss = {};

		//null is also an object
		if(typeof data == 'object' && data != null && !(data instanceof jQuery)){
			css = data.css;
			cb = data.cb;
			url = data.url;
			unBlockCb = data.unblockcb;
			overlayCss = data.overlayCss;
			data = data.data

		}
		if (typeof url == 'function') {
			if(typeof cb == 'object') {
				css = cb;
			}
			cb = url;
			url = false;
		}
		else if (typeof url == 'object') {
			cb = function() { };
			css = url;
			url = false;
		}

		if (typeof cb != 'function') {
			cb = function() { }
		}

		var id = 'globalmodal';
		var container = jQuery('#'+id);
		if (container.length) {
			container.remove();
		}
		container = jQuery('<div></div>');
		container.attr('id', id);

		var showModalData = function (data) {

			var defaultCss = {
							'top' : '0px',
							'width' : 'auto',
							'cursor' : 'default',
							'left' : '35px',
							'text-align' : 'left',
							'border-radius':'6px'
							};
			var effectiveCss = defaultCss;
			if(typeof css == 'object') {
				effectiveCss = jQuery.extend(defaultCss, css)
			}

			var defaultOverlayCss = {
										'cursor' : 'default'
									};
			var effectiveOverlayCss = defaultOverlayCss;
			if(typeof overlayCss == 'object' ) {
				effectiveOverlayCss = jQuery.extend(defaultOverlayCss,overlayCss);
			}
			container.html(data);

			// Mimic bootstrap modal action body state change
			jQuery('body').addClass('modal-open');

			//container.modal();
			jQuery.blockUI({
					'message' : container,
					'overlayCSS' : effectiveOverlayCss,
					'css' : effectiveCss,

					// disable if you want key and mouse events to be enable for content that is blocked (fix for select2 search box)
					bindEvents: false,

					//Fix for overlay opacity issue in FF/Linux
					applyPlatformOpacityRules : false
				});
			var unblockUi = function() {
				app.hideModalWindow(unBlockCb);
				jQuery(document).unbind("keyup",escapeKeyHandler);
			}
			var escapeKeyHandler = function(e){
				if (e.keyCode == 27) {
						unblockUi();
				}
			}
			jQuery('.blockOverlay').click(unblockUi);
			jQuery(document).on('keyup',escapeKeyHandler);
			jQuery('[data-dismiss="modal"]', container).click(unblockUi);

			container.closest('.blockMsg').position({
				'of' : jQuery(window),
				'my' : 'center top',
				'at' : 'center top',
				'collision' : 'flip none',
				//TODO : By default the position of the container is taking as -ve so we are giving offset
				// Check why it is happening
				'offset' : '0 50'
			});
			//container.css({'height' : container.innerHeight()+15+'px'});

			// TODO Make it better with jQuery.on
			app.changeSelectElementView(container);
			//register all select2 Elements
			app.showSelect2ElementView(container.find('select.select2'));
			
			//register date fields event to show mini calendar on click of element
			app.registerEventForDatePickerFields(container);
			cb(container);
		}

		if (data) {
			showModalData(data)

		} else {
			jQuery.get(url).then(function(response){
				showModalData(response);
			});
		}

		return container;
	},

	/**
	 * Function which you can use to hide the modal
	 * This api assumes that we are using block ui plugin and uses unblock api to unblock it
	 */
	hideModalWindow : function(callback) {
		// Mimic bootstrap modal action body state change - helps to avoid body scroll
		// when modal is shown using css: http://stackoverflow.com/a/11013994
		jQuery('body').removeClass('modal-open');

		var id = 'globalmodal';
		var container = jQuery('#'+id);
		if (container.length <= 0) {
			return;
		}

		if(typeof callback != 'function') {
			callback = function() {};
		}
		jQuery.unblockUI({
			'onUnblock' : callback
		});
	},

	isHidden : function(element) {
		if(element.css('display')== 'none') {
			return true;
		}
		return false;
	},

	/**
	 * Default validation eninge options
	 */
	validationEngineOptions: {
		// Avoid scroll decision and let it scroll up page when form is too big
		// Reference: http://www.position-absolute.com/articles/jquery-form-validator-because-form-validation-is-a-mess/
		scroll: false,
		promptPosition: 'topLeft',
		//to support validation for chosen select box
		prettySelect : true,
		useSuffix: "_chzn",
        usePrefix : "s2id_"
	},

	/**
	 * Function to push down the error message size when validation is invoked
	 * @params : form Element
	 */
	formAlignmentAfterValidation : function(form){
		// to avoid hiding of error message under the fixed nav bar
		var offset = form.find(".formError:not('.greenPopup'):first").offset();
		//ED150427
		if (offset == null)
			return;
		var destination = offset.top;
		var resizedDestnation = destination-105;
		jQuery('html').animate({
			scrollTop:resizedDestnation
		}, 'slow');
	},

	convertToDatePickerFormat: function(dateFormat){
		if(dateFormat == 'yyyy-mm-dd'){
			return 'Y-m-d';
		} else if(dateFormat == 'mm-dd-yyyy') {
			return 'm-d-Y';
		} else if (dateFormat == 'dd-mm-yyyy') {
			return 'd-m-Y';
		}
		return dateFormat;
	},

	convertTojQueryDatePickerFormat: function(dateFormat){
		var i = 0;
		var splitDateFormat = dateFormat.split('-');
		for(var i in splitDateFormat){
			var sectionDate = splitDateFormat[i];
			var sectionCount = sectionDate.length;
			if(sectionCount == 4){
				var strippedString = sectionDate.substring(0,2);
				splitDateFormat[i] = strippedString;
			}
		}
		var joinedDateFormat =  splitDateFormat.join('-');
		return joinedDateFormat;
	},
	getDateInVtigerFormat: function(dateFormat,dateObject){
		var finalFormat = app.convertTojQueryDatePickerFormat(dateFormat);
		var date = jQuery.datepicker.formatDate(finalFormat,dateObject);
		return date;
	},

	registerEventForTextAreaFields : function(parentElement) {
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if(parentElement.is('textarea')){
			var element = parentElement;
		}else{
			var element = jQuery('textarea', parentElement);
		}
		if(element.length == 0){
			return;
		}
		element.autosize();
	},
	
	registerEventForDatePickerFields : function(parentElement,registerForAddon,customParams){
		
		/* ED141010
		 * TODO ailleurs
		 * */
		this.registerEventForColorPickerFields(parentElement,registerForAddon,customParams);
		this.registerEventForButtonSetFields(parentElement,registerForAddon,customParams);
		this.registerEventForAsyncFields(parentElement,registerForAddon,customParams);
		
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}
		if(typeof registerForAddon == 'undefined'){
			registerForAddon = true;
		}

		parentElement = jQuery(parentElement);

		if(parentElement.hasClass('dateField')){
			var element = parentElement;
		}else{
			var element = jQuery('.dateField', parentElement);
		}
		if(element.length == 0){
			return;
		}
		if(registerForAddon == true){
			var parentDateElem = element.closest('.date');
			jQuery('.add-on',parentDateElem).on('click',function(e){
				var elem = jQuery(e.currentTarget);
				//Using focus api of DOM instead of jQuery because show api of datePicker is calling e.preventDefault
				//which is stopping from getting focus to input element
				elem.closest('.date').find('input.dateField').get(0).focus();
			});
		}
		var dateFormat = element.data('dateFormat');
		var vtigerDateFormat = app.convertToDatePickerFormat(dateFormat);
		var language = jQuery('body').data('language');
		var lang = language.split('_');
		
		//Default first day of the week
		var defaultFirstDay = jQuery('#start_day').val();
		if(defaultFirstDay == '' || typeof(defaultFirstDay) == 'undefined'){
			var convertedFirstDay = 1
		} else {
			convertedFirstDay = this.weekDaysArray[defaultFirstDay];
		}
		var params = {
			format : vtigerDateFormat,
			calendars: 1,
			locale: $.fn.datepicker.dates[lang[0]],
			starts: convertedFirstDay,
			eventName : 'focus',
			onChange: function(formated){
                var element = jQuery(this).data('datepicker').el;
                element = jQuery(element);
                var datePicker = jQuery('#'+ jQuery(this).data('datepicker').id);
                var viewDaysElement = datePicker.find('table.datepickerViewDays');
                //If it is in day mode and the prev value is not eqaul to current value
                //Second condition is manily useful in places where user navigates to other month
                if(viewDaysElement.length > 0 && element.val() != formated) {
                    element.DatePickerHide();
                    element.blur();
                }
				element.val(formated).trigger('change');
			}
		}
		if(typeof customParams != 'undefined'){
			var params = jQuery.extend(params,customParams);
		}
		element.each(function(index,domElement){
			var jQelement = jQuery(domElement);
			var dateObj = new Date();
			var selectedDate = app.getDateInVtigerFormat(dateFormat, dateObj);
			//Take the element value as current date or current date
			if(jQelement.val() != '') {
				selectedDate = jQelement.val();
			}
			params.date = selectedDate;
			params.current = selectedDate;
			jQelement.DatePicker(params)
		});

	},
	registerEventForDateFields : function(parentElement) {
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if(parentElement.hasClass('dateField')){
			var element = parentElement;
		}else{
			var element = jQuery('.dateField', parentElement);
		}
		element.datepicker({'autoclose':true}).on('changeDate', function(ev){
			var currentElement = jQuery(ev.currentTarget);
			var dateFormat = currentElement.data('dateFormat');
			var finalFormat = app.getDateInVtigerFormat(dateFormat,ev.date);
			var date = jQuery.datepicker.formatDate(finalFormat,ev.date);
			currentElement.val(date);
		});
	},

	/**
	 * Function which will register time fields
	 *
	 * @params : container - jquery object which contains time fields with class timepicker-default or itself can be time field
	 *			 registerForAddon - boolean value to register the event for Addon or not
	 *			 params  - params for the  plugin
	 *
	 * @return : container to support chaining
	 */
	registerEventForTimeFields : function(container, registerForAddon, params) {

		if(typeof cotainer == 'undefined') {
			container = jQuery('body');
		}
		if(typeof registerForAddon == 'undefined'){
			registerForAddon = true;
		}

		container = jQuery(container);

		if(container.hasClass('timepicker-default')) {
			var element = container;
		}else{
			var element = container.find('.timepicker-default');
		}

		if(registerForAddon == true){
			var parentTimeElem = element.closest('.time');
			jQuery('.add-on',parentTimeElem).on('click',function(e){
				var elem = jQuery(e.currentTarget);
				elem.closest('.time').find('.timepicker-default').focus();
			});
		}

		if(typeof params == 'undefined') {
			params = {};
		}

		var timeFormat = element.data('format');
		if(timeFormat == '24') {
			timeFormat = 'H:i';
		} else {
			timeFormat = 'h:i A';
		}
		var defaultsTimePickerParams = {
			'timeFormat' : timeFormat,
			'className'  : 'timePicker'
		};
		var params = jQuery.extend(defaultsTimePickerParams, params);

		element.timepicker(params);

		return container;
	},

	/**
	 * Function to destroy time fields
	 */
	destroyTimeFields : function(container) {

		if(typeof cotainer == 'undefined') {
			container = jQuery('body');
		}

		if(container.hasClass('timepicker-default')) {
			var element = container;
		}else{
			var element = container.find('.timepicker-default');
		}
		element.data('timepicker-list',null);
		return container;
	},


	/**
	 * Function to register color fields
	 * ED141010
	 */
	registerEventForColorPickerFields : function(parentElement,registerForAddon, customParams) {
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
													  *			apres enregistrement, affiche le #010fE24 de la couleur et le pickcolor ne fonctionne plus
													  * 		*/
						;
					}
					$(colpkr).fadeOut(200);
					return false;
				},
				onChange: function (hsb, hex, rgb) {
					$this.val('#' + hex);
					$(selectorId + ' div').css('backgroundColor', '#' + hex);
				}
			})
		});
	},
	
	/**
	 * Function to register color fields
	 * ED141010
	 */
	registerEventForButtonSetFields : function(parentElement,registerForAddon, customParams) {
		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if(parentElement.is('.buttonset')){
			var element = parentElement;
		}else{
			var element = jQuery('.buttonset', parentElement);
		}
		if(element.length == 0){
			return;
		}
		element.buttonset();
	},

	/**
	 * Function to register async fields
	 * AV150416
	 */
	registerEventForAsyncFields : function(parentElement,registerForAddon, customParams) {
		var self = this;
		var elements;

		if(typeof parentElement == 'undefined') {
			parentElement = jQuery('body');
		}

		parentElement = jQuery(parentElement);

		if(parentElement.is('.ui-async')){
			elements = parentElement;
		}else{
			elements = jQuery('.ui-async', parentElement);
		}
		if(elements.length == 0){
			return;
		}

		elements.each(function( index ) {
			//TODO: generalized auto-complete for Chosen type.
			var tagName = $( this )[0].tagName.toLowerCase();
			switch (tagName) {
			case 'input':
				$( this ).autocomplete({
					source: [],
					change: function() {
				        self.onInputValueChange($( this ));
				    }
				});
				$( this ).on('input', function(e) {
					self.updateListForInput($( this ));
				});
				break;
			case 'select'://tmp on change !!
				self.getChosenElementFromSelect($( this )).find('input').on('input', function(e) {
					self.updateListForChosen($( this ));
				});
				break;
			}
		});
	},

	/**
	 * AV150421
	 */
	parseAutoFillValues: function(valueString) {
		var regExp = /\[([^\]]+)\]/g,
			results = [],
			match;

		while ((match = regExp.exec(valueString))) {
    		results.push(match[1]);
    	}

		return results;
	},

	/**
	 * AV150421
	 */
	getRealValue: function(valueString) {
		var regExp = /\[([^\]]+)\]/g;
		var realValue = valueString.replace(regExp, '').trim();

		return realValue;
	},

	/**
	 * AV150421
	 * @returns jso {fieldName: fieldValue, fieldName: fieldValue, fieldName: fieldValue}
	 */
	onInputValueChange: function(field) {
		var autoFillFields = this.getAutoFillFields(field);
		var fieldValue = field.val()
		, values = this.parseAutoFillValues(fieldValue)
		, fieldName = field.attr('name')
		, fieldsValue = {};

		for (var i=0; i < autoFillFields.length; ++i) {
			if (values[i]){
				$input = $('input[name="' + autoFillFields[i] +  '"]');
				$input.val(values[i]);
				//En DetailView, il faut aussi modifier le span 
				$input.parents('.fieldValue:first').children('span.value').text(values[i]);
				fieldsValue[autoFillFields[i] ] = values[i];
			}
			//tmp chosen !!!
		}
		fieldsValue[fieldName] = this.getRealValue(fieldValue);
		field.val(fieldsValue[fieldName]);
		
		return fieldsValue;
	},

	/**
	 * Function to check if a particular result is filter.
	 * AV150421
	 */
	resultIsFiltered: function(result, filters) {
		for (var filterName in filters) {
			if (filters[filterName] && result[filterName] && result[filterName].toLowerCase().indexOf(filters[filterName].toLowerCase()) != 0) {
				return true;
			}
		}

		return false;
	},

	/**
	 * Function to filter the result using filed filter.
	 * AV150421
	 */
	filterResults: function(fieldData, filters) {
		var filteredFieldData = [];

		for (var i=0; i < fieldData.length; ++i) {
			if (!this.resultIsFiltered(fieldData[i], filters)) {
				filteredFieldData.push(fieldData[i]);
			}
		}

		return filteredFieldData;
	},

	/**
	 * Function to get asynchronous field data using ajax and a cache.
	 * AV150416
	 */
	getFieldData: function() {
		var cache = [];
		return function(searchField, searchValue, searchFieldId, filters, fill_fields, callback) {
			var self = this;
			searchValue = searchValue.toLowerCase();
			var filterString = '';
			for (var filter in filters) {
    			filterString += '%%' + filters[filter];
    		}

			if (cache[searchField] !== undefined) {
				for (var i = 1; i <= searchValue.length; ++i) {
					var cacheValue = searchValue.substr(0, i);
					if (cache[searchField][cacheValue] !== undefined) {
						callback(this.filterResults(cache[searchField][cacheValue], filters));
						return;
					} else if (cache[searchField][cacheValue + filterString] !== undefined) {
						callback(this.filterResults(cache[searchField][cacheValue + filterString], filters));
						return;
					}
				}
			}

			var nedeedFields = '';
			var first = true;
			for (var key in fill_fields) {
				if (!first) {
					nedeedFields += ':';
				}
				nedeedFields += fill_fields[key];
				first = false;
			}
			for (var key in filters) {
				if (nedeedFields.indexOf(key) == -1) {
					if (!first) {
						nedeedFields += ':';
					}
					nedeedFields += key;
					first = false;
				}
			}

			var data = {
				module: app.getModuleName(),
		        action: 'GetFieldData',
		        search_field: searchField,
		        search_value: searchValue,
		        search_field_id: searchFieldId,
		        needed_fields: nedeedFields
			}

			var filterNumber = 0;

			for (var key in filters) {
				if (filters[key]) {
					data[key] = filters[key];
					++filterNumber;
				}
			}

			$.post('index.php', data,
		    function(data, status) {
		    	var cacheString = searchValue;
		    	if (filterNumber > 0) {
		    		cacheString += filterString;
		    	}
		    	cache[searchField] = (cache[searchField] === undefined) ? [] : cache[searchField];
		    	cache[searchField][cacheString] = data.result;
		    	callback(self.filterResults(data.result, filters));
		    });
		};
	}(),

	/**
	 * Function to get the minimum length of a ui-async field before auto-completion
	 * AV150416
	 */
	getMinAutoCompleteLength : function (field) {
		var regExp = /ui-async-(\d+)/;
		var match = regExp.exec(field.attr('class'));

		return (match) ? parseInt(match[1]) : 1;
	},

	/**
	 * Function to get related filter fields names
	 * AV150417
	 */
	getRelatedFilterFields : function (field) {
		var regExp = /auto-filter-by-([a-zA-Z0-9\-_]+)/g,
			str = field.attr('class'),
			results = [],
			match;

		while ((match = regExp.exec(str))) {
    		results.push(match[1]);
    	}

		return results;
	},

	/**
	 * Function to get related filter fields names
	 * AV150420
	 */
	getAutoFillFields : function (field) {
		var regExp = /auto-fill-([a-zA-Z0-9\-_]+)/g,
			str = field.attr('class'),
			results = [],
			match;

		while ((match = regExp.exec(str))) {
    		results.push(match[1]);
    	}

		return results;
	},

	/**
	 * Function to get field id
	 * @param : the field
	 * @return : the field id
	 * AV150420
	 */
	getFieldId: function(field) {
		var regExp = /ui-fieldid-(\d+)/;
		var match = regExp.exec(field.attr('class'));

		return (match) ? parseInt(match[1]) : 0;
	},

	/**
	 * Function to cast the fieldData returned by the serveur to a arrays of string.
	 * AV150421
	 */
	getFieldDataArray: function(fieldData) {
		var fieldDataArray = [];
		for(var i=0; i<fieldData.length; ++i) {
			var value = '',
				first = true;

			for (var fieldname in fieldData[i]) {
				if (!first) {
					value += ' [';
				}

				value += fieldData[i][fieldname];

				if (!first) {
					value += ']';
				} else {
					first = false;
				}
			}
			fieldDataArray.push(value);
		}

		return fieldDataArray;
	},

	/**
	 * Function to update the auto-completion list a ui-async field.
	 * This function is call when a modification event is triggered.
	 * AV150416
	 */
	updateListForInput: function(field) {
		var self = this;
		this.getFilters(field);
		if (field.val().length >= this.getMinAutoCompleteLength(field)) {
			this.getFieldData(field.attr('name'), field.val(), this.getFieldId(field), this.getFilters(field), this.getAutoFillFields(field),function(fieldData) {
				field.autocomplete({
	                source: self.getFieldDataArray(fieldData)
	            });
			});
		}
	},

	/**
	 * Function to update the auto-completion list a ui-async field.
	 * This function is call when a modification event is triggered.
	 * AV150416
	 */
	updateListForChosen: function(field) {
		var self = this;
		var value = field.val();
		if (value.length >= this.getMinAutoCompleteLength(field)) {
			var chosenContainer = $(field).parents('div.chzn-container:first');
			var selectElement = this.getSelectElementFromChosen(chosenContainer);
			this.getFieldData(selectElement.attr('name'), value, this.getFieldId(selectElement), this.getFilters(selectElement), this.getAutoFillFields(selectElement), function(fieldData) {
				$(selectElement).empty();
				var fieldDataArray = self.getFieldDataArray(fieldData);
				for (var i=0; i < fieldData.length; ++i) {
					var newOption = $('<option value="' + fieldData[i][selectElement.attr('name')] + '">' + fieldDataArray[i] + '</option>');
		        	$(selectElement).append(newOption);
		        }
		        $(selectElement).trigger("liszt:updated");
		        field.val(value);
			});
		}
	},

	/**
	 * Function to get filters for a specific field
	 * @param : the field
	 * @return : an array containing filters
	 * AV150417
	 */
	getFilters: function(field) {
		var relatedFilterFields = this.getRelatedFilterFields(field);
		var filters = {};

		for (var i=0; i < relatedFilterFields.length; ++i) {
			//tmp it may be many field name like that !!??
			var relatedField = $('input[name="' + relatedFilterFields[i] + '"]')[0];

			if (relatedField !== undefined) {
				filters[relatedFilterFields[i]] = $( relatedField ).val();
			}
		}

		return filters;
	},

	/**
	 * Function to get the chosen element from the raw select element
	 * @params: select element
	 * @return : chosenElement - corresponding chosen element
	 */
	getChosenElementFromSelect : function(selectElement) {
		var selectId = selectElement.attr('id');
		var chosenEleId = selectId+"_chzn";
		return jQuery('#'+chosenEleId);
	},

	/**
	 * Function to get the select2 element from the raw select element
	 * @params: select element
	 * @return : select2Element - corresponding select2 element
	 */
	getSelect2ElementFromSelect : function(selectElement) {
		var selectId = selectElement.attr('id');
		//since select2 will add s2id_ to the id of select element
		var select2EleId = "s2id_"+selectId;
		return jQuery('#'+select2EleId);
	},

	/**
	 * Function to get the select element from the chosen element
	 * @params: chosen element
	 * @return : selectElement - corresponding select element
	 */
	getSelectElementFromChosen : function(chosenElement) {
		var chosenId = chosenElement.attr('id');
		var selectEleIdArr = chosenId.split('_chzn');
		var selectEleId = selectEleIdArr['0'];
		return jQuery('#'+selectEleId);
	},

	/**
	 * Function to set with of the element to parent width
	 * @params : jQuery element for which the action to take place
	 */
	setInheritWidth : function(elements) {
		jQuery(elements).each(function(index,element){
			var parentWidth = jQuery(element).parent().width();
			jQuery(element).width(parentWidth);
		});
	},


	initGuiders: function (list) {
		if (list) {
			for (var index=0, len=list.length; index < len; ++index) {
				var guiderData = list[index];
				guiderData['id'] = ""+index;
				guiderData['overlay'] = true;
				guiderData['highlight'] = true;
				guiderData['xButton'] = true;
				if (index < len-1) {
					guiderData['buttons'] = [{name: 'Next'}];
					guiderData['next'] = ""+(index+1);

				}
				guiders.createGuider(guiderData);
			}
			// TODO auto-trigger the guider.
			guiders.show('0');
		}
	},

	showScrollBar : function(element, options) {
		if(typeof options == 'undefined') {
			options = {};
		}
		if(typeof options.height == 'undefined') {
			options.height = element.css('height');
		}

		return element.slimScroll(options);
	},

	showHorizontalScrollBar : function(element, options) {
		if(typeof options == 'undefined') {
			options = {};
		}
		var params = {
			horizontalScroll: true,
			theme: "dark-thick",
			advanced: {
				autoExpandHorizontalScroll:true
			}
		}
		if(typeof options != 'undefined'){
			var params = jQuery.extend(params,options);
		}
		return element.mCustomScrollbar(params);
	},

	/**
	 * Function returns translated string
	 */
	vtranslate : function(key) {
		if(app.languageString[key] != undefined) {
			return app.languageString[key];
		} else {
			var strings = jQuery('#js_strings').text();
			if(strings != '') {
				app.languageString = JSON.parse(strings);
				if(key in app.languageString){
					return app.languageString[key];
				}
			}
		}
		return key;
	},

	/**
	 * Function which will set the contents height to window height
	 */
	setContentsHeight : function() {
		var bodyContentsElement = app.getContentsContainer();
		var borderTopWidth = parseInt(bodyContentsElement.css('borderTopWidth'));
		var borderBottomWidth = parseInt(bodyContentsElement.css('borderBottomWidth'));
		//Height should not include padding, margins and borders width. So reducing those values
		bodyContentsElement.css('min-height',(jQuery(window).height()- (borderTopWidth + borderBottomWidth)));
	},

	/**
	 * Function will return the current users layout + skin path
	 * @param <string> img - image name
	 * @return <string>
	 */
	vimage_path : function(img) {
		return jQuery('body').data('skinpath')+ '/images/' + img ;
	},

	/*
	 * Cache API on client-side
	 */
	cacheNSKey: function(key) { // Namespace in client-storage
		return 'vtiger6.' + key;
	},
	cacheGet: function(key, defvalue) {
		key = this.cacheNSKey(key);
		return jQuery.jStorage.get(key, defvalue);
	},
	cacheSet: function(key, value) {
		key = this.cacheNSKey(key);
		jQuery.jStorage.set(key, value);
	},
	cacheClear : function(key) {
		key = this.cacheNSKey(key);
		return jQuery.jStorage.deleteKey(key);
	},

	htmlEncode : function(value){
		if (value) {
			return jQuery('<div />').text(value).html();
		} else {
			return '';
		}
	},

	htmlDecode : function(value) {
		//ED151124 Array
		if (typeof value === 'object') {
			for(var k in value)
				value[k] = this.htmlDecode(value[k]);
			return value;
		}
		else if (value) {
			return $('<div />').html(value).text();
		} else {
			return '';
		}
	},

	/**
	 * Function places an element at the center of the page
	 * @param <jQuery Element> element
	 */
	placeAtCenter : function(element) {
		element.css("position","absolute");
		element.css("top", ((jQuery(window).height() - element.outerHeight()) / 2) + jQuery(window).scrollTop() + "px");
		element.css("left", ((jQuery(window).width() - element.outerWidth()) / 2) + jQuery(window).scrollLeft() + "px");
	},

	getvalidationEngineOptions : function(select2Status){
		return app.validationEngineOptions;
	},

	/**
	 * Function to notify UI page ready after AJAX changes.
	 * This can help in re-registering the event handlers (which was done during ready event).
	 */
	notifyPostAjaxReady: function() {
		jQuery(document).trigger('postajaxready');
	},

	/**
	 * Listen to xready notiications.
	 */
	listenPostAjaxReady: function(callback) {
		jQuery(document).on('postajaxready', callback);
	},

	/**
	 * Form function handlers
	 */
	setFormValues: function(kv) {
		for (var k in kv) {
			jQuery(k).val(kv[k]);
		}
	},

	setRTEValues: function(kv) {
		for (var k in kv) {
			var rte = CKEDITOR.instances[k];
			if (rte) rte.setData(kv[k]);
		}
	},

	/**
	 * Function returns the javascript controller based on the current view
	 */
	getPageController : function() {
		var moduleName = app.getModuleName();
		var view = app.getViewName()
		var parentModule = app.getParentModuleName();

		var moduleClassName = parentModule+"_"+moduleName+"_"+view+"_Js";
		if(typeof window[moduleClassName] == 'undefined'){
			moduleClassName = parentModule+"_Vtiger_"+view+"_Js";
		}
		if(typeof window[moduleClassName] == 'undefined') {
			moduleClassName = moduleName+"_"+view+"_Js";
		}
		if(typeof window[moduleClassName] == 'undefined') {
			moduleClassName = "Vtiger_"+view+"_Js";
		}
        if(typeof window[moduleClassName] == 'function') {
			return new window[moduleClassName]();
		}
	},

	/**
	 * Function to decode the encoded htmlentities values
	 */
	getDecodedValue : function(value) {
		return jQuery('<div></div>').html(value).text();
	},

	/**
	 * Function to check whether the color is dark or light
	 */
	getColorContrast: function(hexcolor){
		var r = parseInt(hexcolor.substr(0,2),16);
		var g = parseInt(hexcolor.substr(2,2),16);
		var b = parseInt(hexcolor.substr(4,2),16);
		var yiq = ((r*299)+(g*587)+(b*114))/1000;
		return (yiq >= 128) ? 'light' : 'dark';
	},
    
    updateRowHeight : function() {
        var rowType = jQuery('#row_type').val();
        if(rowType.length <=0 ){
            //Need to update the row height
            var widthType = app.cacheGet('widthType', 'mediumWidthType');
            var serverWidth = widthType;
            switch(serverWidth) {
                case 'narrowWidthType' : serverWidth = 'narrow'; break;
                case 'wideWidthType' : serverWidth = 'wide'; break;
                default : serverWidth = 'medium';
            }
			var userid = jQuery('#current_user_id').val();
            var params = {
                'module' : 'Users',
                'action' : 'SaveAjax',
                'record' : userid,
                'value' : serverWidth,
                'field' : 'rowheight'
            };
            AppConnector.request(params).then(function(){
                jQuery(rowType).val(serverWidth);
            });
        }
    }
}

jQuery(document).ready(function(){
	app.changeSelectElementView();

	//register all select2 Elements
	app.showSelect2ElementView(jQuery('body').find('select.select2'));

	app.setContentsHeight();
	
	//Updating row height
	app.updateRowHeight();
	
	jQuery(window).resize(function(){
		app.setContentsHeight();
	})

	String.prototype.toCamelCase = function(){
		var value = this.valueOf();
		return  value.charAt(0).toUpperCase() + value.slice(1).toLowerCase()
	}

	// Instantiate Page Controller
	var pageController = app.getPageController();
	if(pageController) pageController.registerEvents();
});

/* Global function for UI5 embed page to callback */
function resizeUI5IframeReset() {
	jQuery('#ui5frame').height(650);
}
function resizeUI5Iframe(newHeight) {
	jQuery('#ui5frame').height(parseInt(newHeight,10)+15); // +15px - resize on IE without scrollbars
}
