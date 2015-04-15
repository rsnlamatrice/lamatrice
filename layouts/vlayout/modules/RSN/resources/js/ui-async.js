/*+***********************************************************************************
 AV150415
 *************************************************************************************/

var Rsn_ui_async = {
	getAsyncInputs : function() {
		return $('.ui-async');
	},

	getFieldData: function() {
		var cache = [];
		return function(searchField, searchValue, callback) {
			searchValue = searchValue.toLowerCase();
			if (cache[searchField] !== undefined) {
				for (var i = 1; i <= searchValue.length; ++i) {
					var cacheValue = searchValue.substr(0, i);
					if (cache[searchField][cacheValue] !== undefined) {
						callback(cache[searchField][cacheValue]);
						return;
					}
				}
			}

			$.post('index.php',
		    {
		        module: app.getModuleName(),
		        action: 'GetFieldData',
		        search_field: searchField,
		        search_value: searchValue
		    },
		    function(data, status){
		    	cache[searchField] = (cache[searchField] === undefined) ? [] : cache[searchField];
		    	cache[searchField][searchValue] = data.result;
		    	callback(data.result);
		    });
		};
	}(),

	getMinAutoCompleteLength : function (field) {
		var regExp = /ui-async-(\d+)/;
		var matches = regExp.exec(field.attr('class'));

		return (matches) ? parseInt(matches[1]) : 1;
	},

	initAsyncFields : function() {
		var asyncFields = this.getAsyncInputs();
		var self = this;
		asyncFields.each(function( index ) {
			//TOTO: generalized auto-complete for all field tag, not only for <input type="text">
			$( this ).autocomplete({
				source: []
			});
			$( this ).on('input', function(e) {
				self.updateList($( this) );
			});
		});
	},

	updateList: function(field, fieldTag) {
		if (field.val().length >= this.getMinAutoCompleteLength(field)) {
			fieldTag = (fieldTag === undefined) ? 'input' : fieldTag;
			switch (fieldTag) {
			case 'input':
				this.getFieldData(field.attr('name'), field.val(), function(fieldData) {
					field.autocomplete({
		                source: fieldData
		            });
				});
				break;
			default:
			}
		}
	}
}

//On Page Load
jQuery(document).ready(function() {
	Rsn_ui_async.initAsyncFields();
});