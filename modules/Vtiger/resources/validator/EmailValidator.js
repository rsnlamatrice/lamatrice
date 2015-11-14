/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/*
 * La procédure utilisée est dans :
 * modules\Vtiger\resources\validator\FieldValidator.js
 *
 */

Vtiger_BaseValidator_Js("Vtiger_EmailValidator_Js",{},{
	error: "",
	validate: function(){
		var fieldValue = this.fieldInfo.val();
		var tfld = fieldValue.replace(/^\s+/,'').replace(/\s+$/,'');
		var emailFilter = /^[^@]+@[^@.]+\.[^@]*\w\w$/ ;
		var illegalChars= /[\(\)\<\>\,\;\:\\\"\[\]]/ ;

		if (fieldValue.value == "") {

			this.getEmptyEmailError();

		} else if (!emailFilter.test(tfld)) {

			this.getInvalidEmailError();

		} else if (fieldValue.match(illegalChars)) {

			this.getIllegalCharacterEmailError();
		
		//ED15113
		} else if (!this.checkEmailDomain(fieldValue)) {

			this.getInvalidEmailDomainError();
		
		}
	},

	getEmptyEmailError: function(){
		this.error = "You didn't enter an email address.\n";
		return this.error;
	},

	getInvalidEmailError: function(){
		this.error = "Please enter a valid email address.\n";
		return this.error;
	},

	getIllegalCharacterEmailError: function(){
		this.error = "The email address contains illegal characters.\n";
		return this.error;
	},

	getInvalidEmailDomainError: function(){
		this.error = "The email domain is known as invalid.\n";
		return this.error;
	},

	checkEmailDomain: function($emailAddress){
		var postData = {
			'action' : 'FieldValidatorAjax',
			'mode' : 'getEmailDomainValidation',
			'domain' : $emailAddress.split('@')[1],
		};
		var params = {
			'async' : false,
			'data' : postData,
		};
		var isError = false
		, validDomain = '';
	    AppConnector.request(params).then(
			function(data) {
				if(data && data.result && data.result.error) {
					isError = true;
					validDomain = data.result.validdomain; //TODO update email ?
					var params = {
						title : app.vtranslate('JS_MESSAGE'),
						text: 'Domaine reconnu comme incorrect'
							+ (validDomain ? '\nUtilisez le domaine "' + validDomain + '".' : ''),
						animation: 'show',
						type: 'error'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			},
			function(error,err){
				isError = true;
			}
	    );
		return !isError;
	}


})