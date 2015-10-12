/*+***********************************************************************************
 * 
 *************************************************************************************/
Vtiger_Popup_Js("Contacts_DuplicatesRelations_Js",{

},{

	registerSelectButton : function(){
		var popupPageContentsContainer = this.getPopupPageContainer()
		, form = popupPageContentsContainer.find('form')
		, thisInstance = this;
		popupPageContentsContainer.on('click','button.btn-success', function(e){
			
			//Si on a un compte commun à faire, on doit laisser s'afficher normalement la page, sinon, on peut obtenir le résultat de la requête
			if(form.find('input[type="radio"][name][value="commonAccount"]:checked').length)//="createRelation*"  =commonAccount
				return true;
			
			e.preventDefault();
			var actionUrl = form.serializeFormData();
			AppConnector.request(actionUrl).then(
				function(data) {
					if(typeof data === 'string'
					&& /^{[\s\S]*}$/.test(data))
						data = JSON.parse(data);
					if(typeof data === 'object') {
						if (data.success) {
							thisInstance.done(data, thisInstance.getEventName());
							return;
						}
						$('<pre></pre>')
							.html(JSON.stringify(data))
							.dialog()
						;
					}
					else {
						popupPageContentsContainer.html(data);
					}
				},
				function(error,err) {
					alert(error);
				}
			);
		});
	},
});