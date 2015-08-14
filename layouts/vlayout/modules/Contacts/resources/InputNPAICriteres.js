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
	registerAddCritereColumnEvent : function(container){
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
					
					if ($table.find('input[name="critereid[]"][value="' + critereId + '"]:first').length) {
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
							, contactId = this.tagName == 'TD'
								? $model.parents('tr:first').is('.row-model')
								|| $model.parents('tr:first').find('input[name="contactid[]"]').val()
								: true
							, $clone = $model.clone()
								.addClass(uniqClass)
								.attr('data-critere', critere)
								.attr('data-critereid', critereId)
								.removeClass('hide')
								.removeClass('critere-model')
								.insertBefore($model)
								.find('input[name="critereid[]"]:first')
									.val(critereId)
									.end()
							;
							if(!contactId)
								$clone.empty();
						})
					;
					$table.find('th.' + uniqClass)
						.attr('data-critere', critere)
						.removeClass('critere-model')
						.html($('<label style="display:inline"/>')
							.append($('<input type="checkbox"/>')
								.click(thisInstance.triggerColumnHeaderCheckEvent)
							)
							.append('&nbsp;<span>'+critere+'<span>')
						)
						.append($('<a href="" class="remove-column" style="margin-left: 1em;" title="Supprimer la colonne"><span class="icon-trash"></span></a>')
							.click(thisInstance.triggerRemoveColumnEvent)
						)
						.find('input[name="critereid[]"]:first')
							.val(critereId)
							.end();
				}
			});
			return false;
		});
	},

	//Clic sur en-tête de colonne : coche toutes les lignes
	triggerColumnHeaderCheckEvent : function(e){
		//La touche Control bloque la propagation du checked
		if (e.ctrlKey)
			return;
		
		var $this = $(e.target)
		, $th = $(e.target).parents('th:first')
		, $table = $th.parents('table:first')
		, critereId = $th.data('critereid')
		, checked = e.target.checked;
		
		//each contact
		$table.find('tbody input[name="contactid[]"]').each(function(){
			if (!this.value)
				return;
			var $tr = $(this).parents('tr:first')
			, contactData = {
				contactid : this.value
			};
			if ($tr.is('.save-done, .row-model'))
				return;
			var $critere = $tr.find('input[type="hidden"][name="critereid[]"][value="' + critereId + '"]')
			, $checkbox = $critere.parents('td:first').find('input[type="checkbox"][name="save-critere[]"]')
			;
			if($checkbox.length){
				$checkbox.get(0).checked = checked;
				$checkbox.change();
			}
		});
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
				
				//Teste si existe déjà
				if ($table && $table.find('input[name="contactid[]"][value="' + contact.record_id + '"]').length) {
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
			.find('input[name="contactid[]"]:first')
				.val(contact_details.record_id)
				.end()
			.find('td.contact-details:first')
				.append('<pre>' + details + '</pre>')
				.end()
		;
		this.initNPAICell($newRow.find('td.critere-NPAI:first'), contact_details);
		this.initCritereCells($newRow);
	},
	
	//lorsqu'on ajoute une ligne
	initCritereCells : function($newRow){
		var $table = $newRow.parents('table:first')
		, $critereHeaders = $table.find('thead th.critere:not(.critere-model)')
		, contactId = $newRow.find('input[name="contactid[]"]').val()
		;
		$critereHeaders.each(function(){
			var $th = $(this)
			, critereId = $th.data('critereid')
			, checked = $th.find('input[type="checkbox"]:checked').length > 0;
			//Contact inconnu : effacement de la cellule
			if (!contactId) {
				$newRow.find('input[type="hidden"][name="critereid[]"][value="' + critereId + '"]').parents('td:first').empty();
				return;	
			}
			if (!checked)
				return;
			var $critere = $newRow.find('input[type="hidden"][name="critereid[]"][value="' + critereId + '"]')
			, $checkbox = $critere.parents('td:first').find('input[type="checkbox"][name="save-critere[]"]')
			;
			if($checkbox.length){
				$checkbox.get(0).checked = checked;
				$checkbox.change();
			}
		});
	},
	
	initNPAICell : function($cell, contact_details){
		if (!contact_details.record_id)
			return;
		var uniqKey = contact_details.record_id;
		
		var cur_npai = parseInt(contact_details.rsnnpai)
		, new_npai = cur_npai > 2 ? cur_npai : cur_npai + 1
		, counter = 0
		, radios = '<div class="buttonset ui-buttonset">';
		for(npai = 0; npai <= 3; npai++){
			var NPAI_value = this.NPAI_values[''+npai];
			radios += '<label';
			if (npai == cur_npai) 
				radios += ' style="font-style: italic;"';
			radios += '><input type="radio" name="rsnnpai_' + uniqKey + '" value="' + npai + '"';
			if (npai == new_npai) 
				radios += ' checked="checked"';
			
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
		, recordId = $this.parents('tr:first').find('input[name="contactid[]"]').val()
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
			details = '<span class="icon-exclamation-sign"></span>&nbsp;'
				+ "<b><u>" + contact_details.contact_no + " inconnu</u></b>";
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
	 * Envoie les données par Ajax
	 */
	registerRecordPreSaveEvent : function(form){
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}

		//form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
		form.on('click', 'button[type="submit"]', function(e){
			
			e.preventDefault();
			var element = jQuery(e.currentTarget);
			element.progressIndicator({});
			
			var params = thisInstance.getSaveData(form);
			
			AppConnector.request(params).then(
				function(data) {
					element.progressIndicator({'mode': 'hide'});
					if(data.result){
						Vtiger_Helper_Js.showPnotify('Ok, c\'est enregistré');
						thisInstance.postSaveData(form);
						//$('<pre></pre>').html(JSON.stringify(data.result)).dialog({width: 'auto', height: 'auto'});
					}else{
						Vtiger_Helper_Js.showMessage(data);
					}
				}
			);
			
		})
	},
	
	/** data to submit
	 */
	getSaveData : function(form){
		var data = {
			module : form.find('input[name="module"]').val()
			, mode : form.find('input[name="mode"]').val()
			, action : form.find('input[name="action"]').val()
			, contacts : {}
		}
		, $criteres = form.find('thead th.critere[data-critere]');
		//each contact
		form.find('tbody input[name="contactid[]"]').each(function(){
			if (!this.value)
				return;
			var $tr = $(this).parents('tr:first')
			, contactData = {
				contactid : this.value
			};
			if ($tr.is('.save-done, .row-model'))
				return;
			
			//NPAI
			var $npai = $tr.find('td.critere-NPAI[data-critere="NPAI"]');
			if ($npai.length) {
				contactData['NPAI'] = {
					value : $npai.find('input[type="radio"]:checked:first').val()
					, comment : $npai.find('textarea:first').val()
				}
			}
			//Autres critères
			$criteres.each(function(){
				var $this = $(this)
				, critereId = $this.data('critereid')
				, $cell = $tr.find('input[name="critereid[]"][value="' + critereId + '"]:first').parents('td:first')
				, $checkbox = $cell.find('input[type="checkbox"][name="save-critere[]"]:checked:first');
				if ($checkbox.length) {
					contactData[critereId] = {
						critereid : critereId
						, date : $cell.find('input[name="critere-dateapplication[]"]:first').val()
						, data : $cell.find('input[name="critere-reldata[]"]:first').val()
					}
				}
			});
			
			data.contacts[contactData.contactid] = contactData;
			
		});
		
		return data;
	},
	
	postSaveData : function(form){
		//each contact
		form.find('tbody input[name="contactid[]"]').each(function(){
			var $tr = $(this).parents('tr:first');
			if ($tr.is('.save-done, .row-model'))
				return;
			
			$tr.addClass('save-done');
		});
	},
	
	registerEvents : function(container){
		this._super(container);
		this.registerRecordPreSaveEvent(container);
		this.registerRemoveColumnEvent(container);
		this.registerAddCritereColumnEvent(container);
		this.registerContactInputEvent(container);
	}
})