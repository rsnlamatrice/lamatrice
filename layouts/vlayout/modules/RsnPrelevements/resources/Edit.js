/*+***********************************************************************************
 * http://marlot.org/util/calcul-de-la-cle-rib.php
 *************************************************************************************/

Vtiger_Edit_Js("RsnPrelevements_Edit_Js",{},{
	
	
	
	/* RIB */
	
	/**
	 * Function which will register event for RIB Fields changes
	 */
	registerRIBChangeEvent : function(container) {
		var thisInstance = this;
		
		container.find('.block-LBL_RIB').on('change', 'input:visible', function(e, data){
			this.value = this.value.trim();
			thisInstance.checkCleRIB(container);
		});
	},

	/**
	 * Function to check la clé du RIB
	 */
	checkCleRIB : function(container) {
		//Chaque lettre est remplacée par son équivalent numérique :
		//
		//A,J = 1 ; B,K,S = 2 ; C,L,T = 3 ; D,M,U = 4 ; E,N,V = 5
		//F,O,W = 6 ; G,P,X = 7 ; H,Q,Y = 8 ; I,R,Z = 9
		//
		//La clé peut alors être calculée avec la formule suivante :
		//
		//Clé RIB = 97 - ( (
		//   89 x Code banque +
		//   15 x Code guichet +
		//   3 x Numéro de compte ) modulo 97 )
		
		var $cle = this.getCleRIBInput(container)
		, cle = $cle.val();
		if (!cle) return;
		
		var banque = this.parseRIBChars(this.getBanqueRIBInput(container).val());
		if (!banque) return;
		
		var guichet = this.parseRIBChars(this.getGuichetRIBInput(container).val());
		if (!guichet) return;
		
		var compte = this.parseRIBChars(this.getCompteRIBInput(container).val());
		if (!compte) return;
		
		var controlCle = 97 - ( (
			89 * banque +
			15 * guichet +
			3 * compte ) % 97 );
		if(cle != controlCle){
			$cle.validationEngine('showPrompt', 'La clé calculée diffère. Elle devrait être ' + controlCle);
		}
		else
			$cle.validationEngine('closePrompt', $cle);
    },
	
	parseRIBChars : function(value){
		if (!value)
			return value;
		var result = '';
		value = value.trim().toUpperCase().split('');
		for (var i=0; i < value.length; i++) {
			switch (value[i]) {
			case 'A':
			case 'J':
				value[i] = 1; break;
			case 'B':
			case 'K':
			case 'S':
				value[i] = 2; break;
			case 'C':
			case 'L':
			case 'T':
				value[i] = 3; break;
			case 'D':
			case 'M':
			case 'U':
				value[i] = 4; break;
			case 'E':
			case 'N':
			case 'V':
				value[i] = 5; break;
			case 'F':
			case 'O':
			case 'W':
				value[i] = 6; break;
			case 'G':
			case 'P':
			case 'X':
				value[i] = 7; break;
			case 'H':
			case 'Q':
			case 'Y':
				value[i] = 8; break;
			case 'I':
			case 'R':
			case 'Z':
				value[i] = 9; break;
			default:
				break;
			}
		}
		return parseInt(value.join(''));
		//A,J = 1 ; B,K,S = 2 ; C,L,T = 3 ; D,M,U = 4 ; E,N,V = 5
		//F,O,W = 6 ; G,P,X = 7 ; H,Q,Y = 8 ; I,R,Z = 9
		
	},
	
	getCleRIBInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_clerib');
	},
	
	getBanqueRIBInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_codebanque');
	},
	
	getGuichetRIBInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_codeguichet');
	},
	
	getCompteRIBInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_numcompte');
	},
	
	
	/* SEPA/IBAN */
	
	/**
	 * Function which will register event for SEPA Fields changes
	 */
	registerIBANChangeEvent : function(container) {
		var thisInstance = this;
		
		container.find('.block-LBL_SEPA').on('change', 'input:visible', function(e, data){
			this.value = this.value.trim();
			thisInstance.checkCleIBAN(container);
		});
	},

	/**
	 * Function to check la clé du IBAN
	 */
	checkCleIBAN : function(container) {
		//La valeur numérique sur laquelle porte le calcul de la clé du numéro IBAN est la concaténation du numéro de compte BBAN et du code pays dont les lettres ont été converties en leur équivalent numérique avec les valeurs suivantes :
		//
		//A = 10 ; B = 11 ; C = 12 ; ... etc ... ; Y = 34 ; Z = 35
		//
		//La clé peut alors être calculée avec la formule suivante :
		//
		//Clé IBAN = 98 - ( ( Valeur numérique ) modulo 97 )
		
		var $cle = this.getCleIBANInput(container)
		, cle = $cle.val();
		if (!cle) return;
		
		var pays = this.getPaysIBANInput(container).val();
		if (!pays) return;
		
		var bban = this.getBBANIBANInput(container).val();
		if (!bban) return;
		
		var numero=this.parseIBANChars(bban.toString()+pays.toString())+"00";	
		var controlCle=0;
		var pos=0;
		while (pos<numero.length) {
			controlCle=parseInt(controlCle.toString()+numero.substr(pos,9),10) % 97;
			pos+=9;
		}
		controlCle=98-(controlCle % 97);
		controlCle=(controlCle<10 ? "0" : "")+controlCle.toString();
		
		if(cle != controlCle){
			$cle.validationEngine('showPrompt', 'La clé calculée diffère. Elle devrait être ' + controlCle);
		}
		else
			$cle.validationEngine('closePrompt', $cle);
		
    },
	
	parseIBANChars : function(texte){
		//A = 10 ; B = 11 ; C = 12 ; ... etc ... ; Y = 34 ; Z = 35		
		if (!texte)
			return texte;
			
		texteConverti="";
		
		for (i=0;i<texte.length;i++) {
			caractere=texte.charAt(i);
			if (caractere>"9") {
				if (caractere>="A" && caractere<="Z") {
					texteConverti+=(caractere.charCodeAt(0)-55).toString();
				}
			}else if (caractere>="0"){
				texteConverti+=caractere;
			}
		}
		return texteConverti;
	},
	
	getCleIBANInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_sepaibancle');
	},
	getPaysIBANInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_sepaibanpays');
	},
	getBBANIBANInput : function(container){
		return container.find('#RsnPrelevements_editView_fieldName_sepaibanbban');
	},

	registerEvents: function(){
		var editViewForm = this.getForm();
		this._super();
		this.registerRIBChangeEvent(editViewForm);
		this.registerIBANChangeEvent(editViewForm);
	}
});


