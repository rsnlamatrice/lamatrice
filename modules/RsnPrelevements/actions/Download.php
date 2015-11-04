<?php
/*+***********************************************************************************
 * Download RsnPrelVirements
 * Fichier XML à envoyer à la banque
 *************************************************************************************/

class RsnPrelevements_Download_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(){
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$msgVirements = $request->get('msg_virements');
		
		$recur_first = $request->get('recur_first');
		$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
		
		if(!$msgVirements){
			$loadUrl = $moduleModel->getGenererPrelVirementsUrl($dateVir);
			echo '<html><body>
				<code style="border: 1px solid red; color: red;">Le message aux donateurs est vide !</code>
				<br><br><a href="' . $loadUrl . '">Retour</a>
				</body></html>
			';
			return;
		}
		
		$prelVirements = $moduleModel->getExistingPrelVirements( $dateVir, $recur_first );
		
		$nbVirnts = 0;
		$sumMontants = 0;
		
		foreach($prelVirements as $prelVirnt){
			$nbVirnts++;
			$sumMontants += decimalFormat($prelVirnt->get('montant'));
		}

		$xml = $this->initXML($dateVir, $recur_first);
		$this->writerSEPAHeader($xml, $dateVir, $recur_first, $nbVirnts, $sumMontants );
		
		foreach($prelVirements as $prelVirnt){
			$this->writeXmlPrelVirement($xml, $prelVirnt, $msgVirements, $moduleModel);
		}
		
		$this->closeXml($xml);
	}
	
	function initXML($dateVir, $recur_first){
		
		$filename = 'Prelevements_' . ($recur_first == 'FIRST' ? 'FRST' : 'RCUR') . '_' . $dateVir->format('d-m-Y') . '.xml';
		
		header("Content-Encoding: UTF-8");
		header('Pragma: no-cache');
		header("Expires: 0");
		
		//header 
		header('Content-type: application/csv; charset=UTF-8');
		header('Content-disposition: attachment; filename="'.$filename.'"');
		
		//create a new xmlwriter object
		$xml = new XMLWriter(); 
		//using output for string output
		$xml->openURI("php://output");
		//set the indentation to true (if false all the xml will be written on one line)
		$xml->setIndent(true);
		//create the document tag, you can specify the version and encoding here
		$xml->startDocument();
		
		$node = $xml->startElement('Document');
		$xml->writeAttribute('xmlns',"urn:iso:std:iso:20022:tech:xsd:pain.008.001.02");
		$xml->writeAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-intance");
		
		return $xml;
	}
		
	function closeXml($xml) {
		
		$xml->endElement();//'PmtInf');
		$xml->endElement();//CstmrDrctDbtInitn
		$xml->endElement();//Document
		$xml->flush();
	}
		
	function writerSEPAHeader($xml, $dateVir, $recur_first, $nbVirnts, $sumMontants ){
		//include('config.RSN.inc.php');
		
		include_once 'modules/Settings/Vtiger/models/CompanyDetails.php';
		$organization = Settings_Vtiger_CompanyDetails_Model::getInstance();
		
		$today = new DateTime();
				
		$xml->startElement('CstmrDrctDbtInitn');
		
		//$tTexte:=$tTexte+"<GrpHdr><MsgId>"
		$xml->startElement('GrpHdr');
		
		$xml->startElement('MsgId');
		//$tTexte:=$tTexte+$aTypeSepa+"-"+g_uti_DateToDateMysql (Date du jour)+"--"+Chaine heure(Heure courante)
		$xml->text(($recur_first == 'FIRST' ? 'Nouveaux' : 'DejaPvt') . '-' . $today->format('Y-m-d') . '--' . $today->format('H:i:s'));
		//$tTexte:=$tTexte+"</MsgId>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CreDtTm>"
		$xml->startElement('CreDtTm');
		//$tTexte:=$tTexte+g_uti_DateToDateMysql (Date du jour)+"T"+Chaine heure(Heure courante)
		$xml->text($today->format('Y-m-d') . 'T' . $today->format('H:i:s'));
		//$tTexte:=$tTexte+"</CreDtTm>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		
		//$tTexte:=$tTexte+"<NbOfTxs>"
		$xml->startElement('NbOfTxs');
		//$tTexte:=$tTexte+Chaine($nbPvt)
		$xml->text($nbVirnts);
		//$tTexte:=$tTexte+"</NbOfTxs>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CtrlSum>"
		$xml->startElement('CtrlSum');
		//$tTexte:=$tTexte+Remplacer chaine(Chaine($sommePvt);",";".")
		$xml->text(decimalFormat($sumMontants));
		//$tTexte:=$tTexte+"</CtrlSum>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<InitgPty>"  `+Caractere(Retour chariot )
		$xml->startElement('InitgPty');
		//$tTexte:=$tTexte+"<Nm>"+[Emetteur]Nom+"</Nm>"  `+Caractere(Retour chariot )
		$xml->startElement('Nm');
		$xml->text($organization->get('sepa::emetteur'));//'RES.SORTIR DU NUCLEAIRE');
		$xml->endElement();
		//$tTexte:=$tTexte+"</InitgPty>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"</GrpHdr>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<PmtInf>"  `+Caractere(Retour chariot )
		$xml->startElement('PmtInf');
		//$tTexte:=$tTexte+"<PmtInfId>"+$aTypeSepa+"-Pmt"+g_uti_DateToDateMysql (Date du jour)+"--"+Chaine heure(Heure courante)+"</PmtInfId>"  `+Caractere(Retour chariot )
		$xml->startElement('PmtInfId');
		$xml->text(($recur_first == 'FIRST' ? 'Nouveaux' : 'DejaPvt') . '-Pmt' . $today->format('Y-m-d') . '--' . $today->format('H:i:s'));
		$xml->endElement();
		//$tTexte:=$tTexte+"<PmtMtd>DD</PmtMtd>"
		$xml->startElement('PmtMtd');
		$xml->text('DD');
		$xml->endElement();
		//$tTexte:=$tTexte+"<BtchBookg>true</BtchBookg>"
		$xml->startElement('BtchBookg');
		$xml->text('true');
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<NbOfTxs>"
		$xml->startElement('NbOfTxs');
		//$tTexte:=$tTexte+Chaine($nbPvt)
		$xml->text($nbVirnts);
		//$tTexte:=$tTexte+"</NbOfTxs>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CtrlSum>"
		$xml->startElement('CtrlSum');
		//$tTexte:=$tTexte+Remplacer chaine(Chaine($sommePvt);",";".")
		$xml->text(decimalFormat($sumMontants));
		//$tTexte:=$tTexte+"</CtrlSum>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		
		$xml->startElement('PmtTpInf');
		$xml->startElement('SvcLvl');
		$xml->startElement('Cd');
		$xml->text('SEPA');
		$xml->endElement();
		$xml->endElement();
		$xml->startElement('LclInstrm');
		$xml->startElement('Cd');
		$xml->text('CORE');
		$xml->endElement();
		$xml->endElement();
		$xml->startElement('SeqTp');
		//Si ($aTypeSepa="Nouveaux")
		if($recur_first == 'FIRST'){
			//$tTexte:=$tTexte+"<PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqT"+"p>FRST</SeqTp></PmtTpInf>"			
			$xml->text('FRST');
		}
		//Sinon
		else{
			//$tTexte:=$tTexte+"<PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqT"+"p>RCUR</SeqTp></PmtTpInf>"
			$xml->text('RCUR');
		}
		//Fin de si 
		$xml->endElement();
		$xml->endElement();//PmtTpInf
		
		//Si (dDatePrlv<Ajouter a date(Date du jour;0;0;8))
		//	ALERTE("Au Credit Coop, ils demandent 6 jours ouvrés avant de prendre l'argent")
		//Fin de si 
		//  `
		//  ` la date est du J+6 au crédit coop, donc J+8 avec les week ends
		//$tTexte:=$tTexte+"<ReqdColltnDt>"+g_uti_DateToDateMysql (dDatePrlv)+"</ReqdColltnDt>"
		$xml->startElement('ReqdColltnDt');
		$xml->text($dateVir->format('Y-m-d'));
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<Cdtr><Nm>"+[Emetteur]Nom+"</Nm></Cdtr>"  `+Caractere(Retour chariot )
		$xml->startElement('Cdtr');
		$xml->startElement('Nm');
		$xml->text($organization->get('sepa::emetteur'));//'RES.SORTIR DU NUCLEAIRE');
		$xml->endElement();
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CdtrAcct>"  `+Caractere(Retour chariot )
		$xml->startElement('CdtrAcct');
		//$tTexte:=$tTexte+"<Id><IBAN>"+[Emetteur]SEPAibanPays+[Emetteur]SEPAibanClé+[Emetteur]Etablissement+[Emetteur]Guichet+[Emetteur]NuméroCC+[Emetteur]RIBclé+"</IBAN></Id>"  `+Caractere(Retour chariot )
		$xml->startElement('Id');
		$xml->startElement('IBAN');
		$xml->text($organization->get('sepa::ibanpays') . $organization->get('sepa::ibancle') . $organization->get('sepa::etablissement') . $organization->get('sepa::guichet') . $organization->get('sepa::compte') . str_pad($organization->get('sepa::ribcle'), 2, '0', STR_PAD_LEFT));
		$xml->endElement();
		$xml->endElement();
		//$tTexte:=$tTexte+"</CdtrAcct>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CdtrAgt>"  `+Caractere(Retour chariot )
		$xml->startElement('CdtrAgt');
		//$tTexte:=$tTexte+"<FinInstnId><BIC>"+[Emetteur]SEPAbic+"</BIC></FinInstnId>"
		$xml->startElement('FinInstnId');
		$xml->startElement('BIC');
		$xml->text($organization->get('sepa::bic'));
		$xml->endElement();
		$xml->endElement();
		//$tTexte:=$tTexte+"</CdtrAgt>"  `+Caractere(Retour chariot )
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<ChrgBr>SLEV</ChrgBr>"  `+Caractere(Retour chariot )
		$xml->startElement('ChrgBr');
		$xml->text('SLEV');
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<CdtrSchmeId>"  `+Caractere(Retour chariot )
		$xml->startElement('CdtrSchmeId');
		//$tTexte:=$tTexte+"<Nm>"+[Emetteur]Nom+"</Nm>"  `+Caractere(Retour chariot )
		$xml->startElement('Nm');
		$xml->text($organization->get('sepa::emetteur'));
		$xml->endElement();
		//$tTexte:=$tTexte+"<Id><PrvtId><Othr><Id>"+[Emetteur]NuméroICS+"</Id><SchmeNm><Prtry>SEPA</Prtry></SchmeNm></Othr></Prv"+"tId></Id>"  `+Caractere(Retour chariot )
		$xml->startElement('Id');
		$xml->startElement('PrvtId');
		$xml->startElement('Othr');
		$xml->startElement('Id');
		$xml->text($organization->get('sepa::ics'));
		$xml->endElement();
		$xml->startElement('SchmeNm');
		$xml->startElement('Prtry');
		$xml->text('SEPA');
		$xml->endElement();
		$xml->endElement();
		$xml->endElement();
		$xml->endElement();
		$xml->endElement();
		//$tTexte:=$tTexte+"</CdtrSchmeId>"
		$xml->endElement();
		
		
	}
	function writeXmlPrelVirement($xml, $prelVirnt, $msgVirements, $moduleModel) {
		
		$dateVir = $moduleModel->getNextDateToGenerateVirnts($prelVirnt->get('dateexport') );
		$rsnPrelevement = $prelVirnt->getRsnPrelevement();
		
		//$tTexte:="<DrctDbtTxInf>"  `+Caractere(Retour chariot )
		$xml->startElement('DrctDbtTxInf');
		
	
		//$tTexte:=$tTexte+"<PmtId><EndToEndId>"+[Prélèvements]SEPArum+"-"+Chaine(Annee de(dDatePrlv);"0000")+Chaine(Mois de(dDatePrlv);"00")+"</EndToEndId></PmtId>"  `+Caractere(Retour chariot )
		$xml->startElement('PmtId');
		$xml->startElement('EndToEndId');
		$xml->text($rsnPrelevement->get('separum') . '-' . $dateVir->format('Y') . str_pad($dateVir->format('m'), '0', 2));
		$xml->endElement();//EndToEndId
		$xml->endElement();//PmtId
		
		//$tTexte:=$tTexte+"<InstdAmt Ccy="+Caractere(Guillemets )+"EUR"+Caractere(Guillemets )+">"+Remplacer chaine(Chaine([Prélèvements]Montant);",";".")+"</InstdAmt>"  `+Caractere(Retour chariot )
		$xml->startElement('InstdAmt');
		$xml->writeAttribute('Ccy', 'EUR');
		$xml->text(decimalFormat($prelVirnt->get('montant')));
		$xml->endElement();//InstdAmt
		
		//$tTexte:=$tTexte+"<DrctDbtTx><MndtRltdInf>"  `+Caractere(Retour chariot )
		$xml->startElement('DrctDbtTx');
		$xml->startElement('MndtRltdInf');
		//$tTexte:=$tTexte+"<MndtId>"+[Prélèvements]SEPArum+"</MndtId><DtOfSgntr>"+g_uti_DateToDateMysql ([Prélèvements]SEPAdateSignature)+"</DtOfSgntr>"  `+Caractere(Retour chariot )
		$xml->startElement('MndtId');
		$xml->text($rsnPrelevement->get('separum'));
		$xml->endElement();//MndtId
		$xml->startElement('DtOfSgntr');
		$xml->text(getValidDBInsertDateValue($rsnPrelevement->get('sepadatesignature')));
		$xml->endElement();//DtOfSgntr
		//$tTexte:=$tTexte+"</MndtRltdInf></DrctDbtTx>"  `+Caractere(Retour chariot )
		$xml->endElement();//MndtRltdInf
		$xml->endElement();//DrctDbtTx
		
		//$tTexte:=$tTexte+"<DbtrAgt><FinInstnId><BIC>"+[Prélèvements]SEPAbic+"</BIC></FinInstnId></DbtrAgt>"  `+Caractere(Retour chariot )
		$xml->startElement('DbtrAgt');
		$xml->startElement('FinInstnId');
		$xml->startElement('BIC');
		$xml->text($rsnPrelevement->get('sepabic'));
		$xml->endElement();//BIC
		$xml->endElement();//FinInstnId
		$xml->endElement();//DbtrAgt
		
		//$tTexte:=$tTexte+"<Dbtr><Nm>"+[Prélèvements]Nom+"</Nm></Dbtr>"  `+Caractere(Retour chariot )
		$xml->startElement('Dbtr');
		$xml->startElement('Nm');
		$xml->text($rsnPrelevement->get('nom'));
		$xml->endElement();
		$xml->endElement();
		
		//$tTexte:=$tTexte+"<DbtrAcct><Id><IBAN>"+[Prélèvements]SEPAibanPays+[Prélèvements]SEPAibanClé+[Prélèvements]SEPAibanBBAN+"</IBAN></Id></DbtrAcct>"  `+Caractere(Retour chariot )
		$xml->startElement('DbtrAcct');
		$xml->startElement('Id');
		$xml->startElement('IBAN');
		$xml->text($rsnPrelevement->get('sepaibanpays') . $rsnPrelevement->get('sepaibancle') . $rsnPrelevement->get('sepaibanbban'));
		$xml->endElement();
		$xml->endElement();
		$xml->endElement();
		//Si (Faux)  `$aTypeSepa="Nouveau")
		//	$tTexte:=$tTexte+"<RmtInf><Ustrd>soutien au Reseau Sortir du Nucleaire : </Ustrd></RmtInf>"  `+Caractere(Retour chariot )"
		//Sinon 
		//	$tTexte:=$tTexte+"<RmtInf><Ustrd>Sortir du nucleaire - "+aMsgPrlv+"</Ustrd></RmtInf>"  `+Caractere(Retour chariot )"
		//Fin de si
		$xml->startElement('RmtInf');
		$xml->startElement('Ustrd');
		$xml->text('Sortir du nucleaire - ' . $msgVirements);
		$xml->endElement();
		$xml->endElement();
		
		//$tTexte:=$tTexte+"</DrctDbtTxInf>"  `+Caractere(Retour chariot )
		$xml->endElement();//DrctDbtTxInf
	}
	
}
