//TODO add new var in the varList

function RSNSQLKeywordList() {//tmp name
	//tmp hardcode ...
	Autocompletor.KeywordList.apply(this, [['ACCESSIBLE','ADD','ALL','ALTER','ANALYZE','AND','AS','ASC','ASENSITIVE','AUTO_INCREMENT','BDB','BEFORE','BERKELEYDB','BETWEEN','BIGINT','BINARY','BLOB','BOTH','BY','CALL','CASCADE','CASE','CHANGE','CHAR','CHARACTER','CHECK','COLLATE','COLUMN','COLUMNS','CONCAT', 'CONDITION','CONNECTION','CONSTRAINT','CONTINUE','CONVERT','CREATE','CROSS','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR','DATABASE','DATABASES','DAY_HOUR','DAY_MICROSECOND','DAY_MINUTE','DAY_SECOND','DEC','DECIMAL','DECLARE','DEFAULT','DELAYED','DELETE','DESC','DESCRIBE','DETERMINISTIC','DISTINCT','DISTINCTROW','DIV','DOUBLE','DROP','DUAL','EACH','ELSE','ELSEIF','ENCLOSED','ESCAPED','EXISTS','EXIT','EXPLAIN','FALSE','FETCH','FIELDS','FLOAT','FLOAT4','FLOAT8','FOR','FORCE','FOREIGN','FOUND','FRAC_SECOND','FROM','FULLTEXT','GENERAL','GRANT','GROUP','HAVING','HIGH_PRIORITY','HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IF','IGNORE','IGNORE_SERVER_IDS','IN','INDEX','INFILE','INNER','INNODB','INOUT','INSENSITIVE','INSERT','INT','INT1','INT2','INT3','INT4','INT8','INTEGER','INTERVAL','INTO','IO_THREAD','IS','ITERATE','JOIN','KEY','KEYS','KILL','LEADING','LEAVE','LEFT','LIKE','LIMIT','LINEAR','LINES','LOAD','LOCALTIME','LOCALTIMESTAMP','LOCK','LONG','LONGBLOB','LONGTEXT','LOOP','LOW_PRIORITY','MASTER_HEARTBEAT_PERIOD','MASTER_SERVER_ID','MASTER_SSL_VERIFY_SERVER_CERT','MATCH','MAXVALUE','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MIDDLEINT','MINUTE_MICROSECOND','MINUTE_SECOND','MOD','MODIFIES','MySQL','NATURAL','NOT','NO_WRITE_TO_BINLOG','NULL','NUMERIC','ON','OPTIMIZE','OPTION','OPTIONALLY','OR','ORDER','OUT','OUTER','OUTFILE','PRECISION','PRIMARY','PRIVILEGES','PROCEDURE','PURGE','RANGE','READ','READS','READ_WRITE','REAL','REFERENCES','REGEXP','RELEASE','RENAME','REPEAT','REPLACE','REQUIRE','RESIGNAL','RESTRICT','RETURN','REVOKE','RIGHT','RLIKE','SCHEMA','SCHEMAS','SECOND_MICROSECOND','SELECT','SENSITIVE','SEPARATOR','SET','SHOW','SIGNAL','SLOW','SMALLINT','SOME','SONAME','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_BIG_RESULT','SQL_CALC_FOUND_ROWS','SQL_SMALL_RESULT','SQL_TSI_DAY','SQL_TSI_FRAC_SECOND','SQL_TSI_HOUR','SQL_TSI_MINUTE','SQL_TSI_MONTH','SQL_TSI_QUARTER','SQL_TSI_SECOND','SQL_TSI_WEEK','SQL_TSI_YEAR','SSL','STARTING','STRAIGHT_JOIN','STRIPED','TABLE','TABLES','TERMINATED','THEN','TIMESTAMPADD','TIMESTAMPDIFF','TINYBLOB','TINYINT','TINYTEXT','TO','TRAILING','TRIGGER','TRUE','The','UNDO','UNION','UNIQUE','UNLOCK','UNSIGNED','UPDATE','USAGE','USE','USER_RESOURCES','USING','UTC_DATE','UTC_TIME','UTC_TIMESTAMP','VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARYING','WHEN','WHERE','WHILE','WITH','WRITE','XOR','YEAR_MONTH','ZEROFILL']]);
	this.tables = [];
	this.updateTablesList();//tmp here
	this.columns = {};
	this.variables = [];
	this.SQLQueries = [];
	this.updateVariablesList();//tmp here
	this.updateSQLQueriesList();
	//this.variables = ['foo', 'bar', 'toto'];//tmp
}

RSNSQLKeywordList.prototype = Object.create(Autocompletor.KeywordList.prototype);

RSNSQLKeywordList.prototype.getRecordId = function() {
	if (document.getElementById('recordId')) {
		return document.getElementById('recordId').value;
	} else {
		return -1;
	}
}

RSNSQLKeywordList.prototype.retrieveKeywords = function (callback) {
	if (this.ac.currentWordData.value[0] == ']') {
		callback([]);
		return;
	}
	var previousChar = this.ac.getCharPrecedingWord(this.ac.currentWordData);
	if (previousChar == '.') {
		var table = this.ac.getWordDataAt(this.ac.currentWordData.begin - 1);
		if (this.columns[table.value]) {
			callback(this.columns[table.value]);
		} else {
			this.updateTableColumns(table.value, function(columns) {
				callback(columns);
			});
		}
	} else {
		callback(this.keywords.concat(this.tables).concat(this.variables).concat(this.SQLQueries));
	}
}

RSNSQLKeywordList.prototype.updateTablesList = function (callback) {
	var self = this,
		data = {
		module: 'RSNSQLQueries',//tmp module name ??
		action: 'GetFieldData',
		mode: 	'tables'
	};

	$.post('index.php', data,
	function(data, status) {
		self.tables = data.result;

		if (typeof callback === 'function') {
			callback(self.tables);
		}
	});
}

RSNSQLKeywordList.prototype.updateTableColumns = function (table, callback) {
	var self = this,
		data = {
		module: 		'RSNSQLQueries',//tmp module name ??
		action: 		'GetFieldData',
		mode: 			'columns',
		relatedTable: 	table
	};

	$.post('index.php', data,
	function(data, status) {
		self.columns[table] = data.result;

		if (typeof callback === 'function') {
			callback(self.columns[table]);
		}
	});
}

RSNSQLKeywordList.prototype.updateVariablesList = function (callback) {
	var self = this,
		data = {
		module: 'RSNSQLQueries',//tmp module name ??
		action: 'GetFieldData',
		mode: 	'variables',
		record: this.getRecordId()
	};

	$.post('index.php', data,
	function(data, status) {
		self.variables = self.transformListArray(data.result, '$', '', '[[? ', ' ]]');

		if (typeof callback === 'function') {
			callback(self.variables);
		}
	});
}

RSNSQLKeywordList.prototype.updateSQLQueriesList = function (callback) {
	var self = this,
		data = {
		module: 'RSNSQLQueries',//tmp module name ??
		action: 'GetFieldData',
		mode: 	'SQLQueries'
	};

	$.post('index.php', data,
	function(data, status) {
		self.SQLQueries = self.transformListArray(data.result, '$query->', '()', '[[QUERY ', ' ]]');

		if (typeof callback === 'function') {
			callback(self.SQLQueries);
		}
	});
}

function initRSNSQLAutocomplete() {
	var textzone = document.getElementById('RSNSQLQueries_editView_fieldName_query');// TMP !!!!!!!!!
	if (textzone) {
		ac = new Autocompletor.AutoComplete;
		ac.init(textzone, new RSNSQLKeywordList());
	}
}

/************************************** Initialising autocomplete ***************************************************/

window.onload = function(e) {
	initRSNSQLAutocomplete();

	document.addEventListener('contentsLoad', function() {
		initRSNSQLAutocomplete();
	})
};