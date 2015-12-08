/*+***********************************************************************************
 * AV1511
 *************************************************************************************/


jQuery.Class("Vtiger_Export_Js",{
	//constructor
	init : function() {
	},

	getForm : function() {
		if(!this.exportViewForm) {
			this.exportViewForm = jQuery('#exportForm');
		}
		return this.exportViewForm;
	},

	getPreviewContainer : function() {
		return $('#preview-container');
	},

	getSourceModuleName : function(form) {
		if (!form) {
		    form = this.getForm();
		}
		return jQuery('[name="source_module"]', form).val();
	},

	showLoader : function() {
		var imagePath = 'layouts/vlayout/skins/images/loading.gif';
		var imageHtml = '<div class="imageHolder" style="text-align:center; width:65.9574%;"><img class="loadinImg" src="'+imagePath+'" /></div>';

		this.getPreviewContainer().html(imageHtml);
	},

	registerEventForPreview : function(){
		var thisInstance = this;
		jQuery('[name="export-preview"]').on('click', function(e){
			e.preventDefault();
			thisInstance.showLoader();			
			var data = thisInstance.getForm().serialize() + '&preview=1';

			$.ajax({
                url: "",
                type: "POST",
                data: data,
                success: function(html) {
                    thisInstance.getPreviewContainer().html(html);
                }
            });

			return false;
		});
	},
	
	registerEvents : function(){
		var thisInstance = this;
		thisInstance.registerEventForPreview();
	},
});
