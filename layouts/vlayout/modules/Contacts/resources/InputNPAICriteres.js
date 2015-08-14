/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Vtiger_Edit_Js("Contacts_InputNPAICriteres_Js",{},{
		
	NPAI_values : {
		'0' : { 'label' : 'Ok', 'icon' : 'ui-icon ui-icon-check green' },
		'1' : { 'label' : 'Supposée', 'icon' : 'ui-icon ui-icon-check darkgreen' },
		'2' : { 'label' : 'Confirmée', 'icon' : 'ui-icon ui-icon-close orange' },
		'3' : { 'label' : 'Définitive', 'icon' : 'ui-icon ui-icon-close darkred' },
	},

	/** ED150813
	 * Function to register events
	 */
	registerRemoveColumnEvent : function(container){
		$('table > thead .remove-column').on('click', this.triggerRemoveColumnEvent);
	},
	
	/** ED150813
	 * Function to remove a column
	 */
	triggerRemoveColumnEvent : function(e){
		e.preventDefault();
		var $this = $(e.currentTarget)
		, $th = $this.parents('th:first')
		, $table = $this.parents('table:first')
		, critere = $th.attr('data-critere');
		if (!confirm("Êtes vous sûr de vouloir supprimer cette colonne '" + critere + "' ?"))
			return;
		$table.find('th[data-critere="' + critere + '"], td[data-critere="' + critere + '"]')
			.remove();
	},

	/** ED150813
	 * Function to register events
	 */
	registerAddColumnEvent : function(container){
		var thisInstance = this;
		$('table > thead .add-critere').on('click', function(e){
			e.preventDefault();
			var $table = $(e.currentTarget).parents('table:first');
			var relatedModuleName = 'Critere4D';
			thisInstance.showSelectRelationPopup(relatedModuleName).then(function(data){
			
				var $models = $table.find('th.critere-model, td.critere-model');
				
				var ids = [];
				for (var critereId in data) {
					var uniqClass = 'critere-id' + critereId
					, critere = data[critereId].name;
					
					if ($table.find('input[name="critereid"][value="' + critereId + '"]:first').length) {
						var params = {
							text: "Vous avez déjà ajouté ce critère " + critere,
							type: 'error'
						};
						Vtiger_Helper_Js.showMessage(params);
						continue;
					}
					
					$models
						.each(function(){
							var $model = $(this)
							, recordId = this.tagName == 'TD' ? $model.parents('tr:first').find('input[name="contactid"]').val() : true
							, $clone = $model.clone()
								.addClass(uniqClass)
								.attr('data-critere', critere)
								.removeClass('hide')
								.removeClass('critere-model')
								.insertBefore($model)
								.find('input[name="critereid"]:first')
									.val(critereId)
									.end()
							;
							if(!recordId)
								$clone.empty();
						})
					;
					$table.find('th.' + uniqClass)
						.attr('data-critere', critere)
						.removeClass('critere-model')
						.html(critere)
						.append($('<a href="" class="remove-column" style="margin-left: 1em;" title="Supprimer la colonne"><span class="icon-trash"></span></a>')
							.click(thisInstance.triggerRemoveColumnEvent)
						)
						.find('input[name="critereid"]:first')
							.val(critereId)
							.end();
				}
			});
			return false;
		});
	},

	//lorsqu'on ajoute une ligne ou une colonne
	initCritereInputCells : function(){
		
	},
	
	/* ED150628 : sélection d'enregistrements
	 * Copiée depuis List.js
	 * @param relatedModulename
	 * @param addSourceParameters : used for unassignement, to show only related records
	 */ 
	showSelectRelationPopup : function(relatedModulename, addSourceParameters){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var popupInstance = Vtiger_Popup_Js.getInstance();
		var parameters = {
			'module' : relatedModulename,
			'src_module' : app.getModuleName(),
			'src_record' : 0,//this.parentRecordId,
			'multi_select' : !!addSourceParameters
		}
		if (addSourceParameters){
			var listInstance = Vtiger_List_Js.getInstance()
			, selectedIds = listInstance.readSelectedIds(true)
			, excludedIds = listInstance.readExcludedIds(true)
			, cvId = listInstance.getCurrentCvId();
			jQuery.extend(parameters, {
				"src_viewname" : cvId,
				"src_selected_ids":selectedIds,
				"src_excluded_ids" : excludedIds
			});
			var searchValue = listInstance.getAlphabetSearchValue();
			if((typeof searchValue != "undefined") && (searchValue.length > 0)) {
				jQuery.extend(parameters, {
					'src_search_key' : listInstance.getRequestSearchField('string'),
					'src_search_value' : (typeof searchValue == 'object' ? JSON.stringify(searchValue) : searchValue),
					'src_operator' : listInstance.getSearchOperator('string')
				});
			}
		}
		popupInstance.show(parameters, function(responseString){
				//jQuery('<pre/>').html(responseString).dialog({ title: "showSelectRelationPopup"});
				var responseData = JSON.parse(responseString);
				aDeferred.resolve(responseData);
			}
		);
		return aDeferred.promise();
	},
	
	/** ED150813
	 * Function to register events
	 */
	registerContactInputEvent : function(container){
		var thisInstance = this;
		$('table > tfoot input.contact_no').on('keypress', function(e){
			if (e.keyCode != 13) 
				return;
			var $input = $(e.currentTarget)
			, $table = $input.parents('table:first');
			var value = this.value.trim();
			if (value) 
				thisInstance.getContactDetails(value, thisInstance.addRowEvent, $table);
			$input.select().focus();
			e.preventDefault();
		});
	},

	/** ED150813
	 * Function to request contact record details
	 */
	getContactDetails : function(contact_no, callback, $table){
		var thisInstance = this;
		//contact_no ne doit pas être confondu avec l'id
		if (/^\d/.test(contact_no))
			contact_no = 'C' + contact_no;
		var params = {
			'record' : contact_no
			, 'source_module' : 'Contacts'
		};
		var progressIndicatorElement = jQuery.progressIndicator({
			'message' : 'Recherche en cours...',
			'position' : 'html',
			'blockInfo' : {
				'enabled' : true
			}
		});
		this.getRecordDetails(params).then(
			function(data){
				progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
		
				var response = data['result']
				, contact = response.data;
				if (!contact) 
					return;
				
				if ($table && $table.find('input[name="contactid"][value="' + contact.record_id + '"]').length) {
					contact.record_id = "";
				}
				callback.call(thisInstance, contact);
			},
			function(error, err){
				progressIndicatorElement.progressIndicator({ 'mode' : 'hide' });
				var contact = {
					contact_no : contact_no
				};
				callback.call(thisInstance, contact);
			}
		);
	},
	
	/** ED150813
	 * Function to add a new row
	 */
	addRowEvent : function(contact_details){
		var $table = $('#EditView table:first')
		, $model = $table.find('tr.row-model')
		, $newRow = $model.clone().insertBefore($model)
		, details = this.getContactHtmlDetails(contact_details);
		
		$newRow
			.removeClass('hide')
			.removeClass('row-model')
			.find('input.contact_no:first')
				.val(contact_details.contact_no)
				.after('<span>'+contact_details.contact_no+'</span>')
				.end()
			.find('input[name="contactid"]:first')
				.val(contact_details.record_id)
				.end()
			.find('td.contact-details:first')
				.html('<pre>' + details + '</pre>')
				.end()
		;
		this.initNPAICell($newRow.find('td.critere[data-critere="NPAI"]:first'), contact_details)
	},
	
	initNPAICell : function($cell, contact_details){
		if (!contact_details.record_id)
			return;
		var uniqKey = contact_details.record_id;
		
		var npai_original = parseInt(contact_details.rsnnpai)
		, new_npai = npai_original > 2 ? npai_original : npai_original + 1
		, counter = 0
		, radios = '<div class="buttonset ui-buttonset">';
		for(npai = 1; npai <= 3; npai++){
			var NPAI_value = this.NPAI_values[''+npai];
			radios += '<label><input type="radio" name="rsnnpai_' + uniqKey + '" value="' + npai + '"';
			if (npai == new_npai) {
				radios += ' checked="checked"';
				first = false;
			}
			radios += '/>'
				+ '<span class="' + NPAI_value.icon + '"></span>'
				+ NPAI_value.label + '</label>'
			;
		}
		radios += '</div>';
		$cell.html(radios);
		
		$cell.append($('<a href="" title="ajouter un commentaire">+ commentaire</a>')
			.attr('data-rsnnpaicomment', contact_details.rsnnpaicomment)
			.click(this.triggerShowNPAIComment)
		);
	},
	//click sur le lien "+ commentaire"
	triggerShowNPAIComment : function(e){
		var $this = $(e.currentTarget)
		, recordId = $this.parents('tr:first').find('input[name="contactid"]').val()
		, comment = $this.attr('data-rsnnpaicomment')
		, $input = $('<textarea/>')
			.attr('name', 'rsnnpaicomment_' + recordId)
			.val(comment);
		$this.replaceWith($input);
		$input.focus();
		e.preventDefault();
	},
	
	/** ED150813
	 * Function to add a new row
	 */
	getContactHtmlDetails : function(contact_details){
		var details = '';
		if (contact_details.record_id) {
			details = '<span class="icon-rsn-small-' +
					+ (contact_details.isgroup == '1' ? 'collectif' : 'contact')
					+ '"></span>&nbsp;'
				+ contact_details.contact_no
				+ " - <b><u>" + (contact_details.firstname + " " + contact_details.lastname).trim() + "</u></b>";
			details = '<a href="?module=Contacts&record=' + contact_details.record_id + '&view=Detail" target="_blank">' + details + '</a>';
			details += '<a href="?module=Contacts&record=' + contact_details.record_id + '&view=Edit" target="_blank" style="float: right; margin-left: 4px;">'
				+ '<span class="icon-pencil"></span></a>';
			details += '<a href="?module=Contacts&record=' + contact_details.record_id + '&view=Detail&mode=showDetailViewByMode&requestMode=full" target="_blank" style="float: right">'
				+ '<span class="icon-th-list"></span></a>';
			if (contact_details.mailingstreet2)
				details += "\r\n" + contact_details.mailingstreet2;
			if (contact_details.mailingstreet3)
				details += "\r\n" + contact_details.mailingstreet3;
			if (contact_details.mailingstreet)
				details += "\r\n" + contact_details.mailingstreet;
			if (contact_details.mailingpobox)
				details += "\r\n" + contact_details.mailingpobox;
			details += "\r\n" + contact_details.mailingzip + " " + contact_details.mailingcity;
			if (contact_details.mailingcountry && contact_details.mailingcountry != 'France')
				details += "\r\n" + contact_details.mailingcountry;
			details += "\r\n" + this.getNPAILabel(contact_details.rsnnpai);
			if (contact_details.rsnnpaicomment)
				details += "\r\n<code>" + this.safe_tags(contact_details.rsnnpaicomment) + '</code>';
			//Autre adresse à afficher si coupon revue ou reçu fiscal
			if ((contact_details.use_address2_for_recu_fiscal || contact_details.use_address2_for_revue) && contact_details.otherstreet){
				details += "\r\nSpécifiquement pour ";
				if(contact_details.use_address2_for_recu_fiscal)
					details += 'Reçu fiscal';
				if(contact_details.use_address2_for_revue)
					details += (contact_details.use_address2_for_recu_fiscal ? ' et ' : '') + 'Revue';
				if (contact_details.otherstreet2)
					details += "\r\n" + contact_details.otherstreet2;
				if (contact_details.otherstreet3)
					details += "\r\n" + contact_details.otherstreet3;
				if (contact_details.otherstreet)
					details += "\r\n" + contact_details.otherstreet;
				if (contact_details.otherpobox)
					details += "\r\n" + contact_details.otherpobox;
				details += "\r\n" + contact_details.otherzip + " " + contact_details.othercity;
				if (contact_details.othercountry && contact_details.othercountry != 'France')
					details += "\r\n" + contact_details.othercountry;
			}
		}
		else if (contact_details.lastname)
			details = "<b><u>"
				+ '<span class="icon-exclamation-sign"></span>&nbsp;'
				+ contact_details.contact_no + " déjà saisi plus haut</u></b>";
		
		else
			details = "<b><u>"
				+ '<span class="icon-exclamation-sign"></span>&nbsp;'
				+ contact_details.contact_no + " inconnu</u></b>";
		return details;
	},
	
	safe_tags : function(str) {
		return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') ;
	},
	
	getNPAILabel : function(npai){
		var NPAI_value = this.NPAI_values[npai];
		return '<span class="' + NPAI_value.icon + '"></span>' + NPAI_value.label;
	},
	
	/**
	 * Function to register recordpresave event
	 */
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var result = thisInstance.checkForPortalUser(form);
			if(!result){
				e.preventDefault();
			}
		})
	},
	
	registerEvents : function(container){
		this._super(container);
		this.registerRecordPreSaveEvent(container);
		this.registerRemoveColumnEvent(container);
		this.registerAddColumnEvent(container);
		this.registerContactInputEvent(container);
	}
})