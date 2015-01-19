<?php
/*+***********************************************************************************
 * ED141022
 *
 * Attention hŽritage de Save_Action et non SaveAjax_Action
 *************************************************************************************/

class RsnDons_SaveAjax_Action extends Invoice_SaveAjax_Action {

	var $serviceType = 'Don';
	var $invoicedate_field = 'datedon';
	
	public function process(Vtiger_Request $request) {
		//die("EN COURS");
		if ($_REQUEST['action'] == 'SaveAjax') {
			$_REQUEST['action'] = 'InvoiceAjax';
			$_REQUEST['module'] = "Invoice";
			
			if($request->get('sourceModule') == 'Contacts'
			   && !$request->get('account_id')){
				/* ED141016 generation du compte du contact si manquant */
				$sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
				$request->setGlobal('contact_id', $sourceRecordModel->getId());
				$accountRecordModel = $sourceRecordModel->getAccountRecordModel();
				$request->setGlobal('sourceModule', $accountRecordModel->getModuleName());
				$request->setGlobal('sourceRecord', $accountRecordModel->getId());     
			}
			
			/* Le montant fournit est TTC */			
			/* sŽparateur de dŽcimales */			
			if(is_string($request->get('montant')))
				$request->set('montant', str_replace( ',', '.', $request->get('montant') ));
			
			/* get taxe associŽe au service */
			$totalTTC = $request->get('montant');
			if($totalTTC != 0){
				$taxes = getTaxDetailsForProduct($request->get('serviceid'));
				if(count($taxes)){
					foreach($taxes as $taxe)
						if($taxe['deleted'] == '0'
						   && $taxe["percentage"] > 0){
							$totalTVA = $totalTTC / ( 1 + 1 /( ( float )$taxe["percentage"] / 100) );
							
							$request->setGlobal('hidtax_row_no1', $taxe["taxid"]);
							
							$request->setGlobal('tax' . $taxe["taxid"] . '_percentage1', $taxe["percentage"]);
							$request->setGlobal('tax' . $taxe["taxid"] . '_group_percentage', $taxe["percentage"]);
							$request->setGlobal('tax' . $taxe["taxid"] . '_group_amount', $totalTVA);
			
							break; //une seule taxe
						}
				}
			}
			else
				$totalTVA = 0;
				
			$totalHT = $totalTTC - $totalTVA;
				
			$request->setGlobal('action', 'Save');
			$request->setGlobal('module', 'Invoice');
			$request->setGlobal('record', '');
			$request->setGlobal('subject', $this->serviceType );
			$request->setGlobal('account_id', $request->get('sourceRecord'));
			$request->setGlobal('invoicedate', $request->get( $this->invoicedate_field ));
			$request->setGlobal('typedossier', $this->serviceType);
			$request->setGlobal('invoicestatus', 'Approved');
			
			$request->setGlobal('copyAddressFromRight', "on");
			$request->setGlobal('copyAddressFromLeft',"on");
			$request->setGlobal('bill_street',"-");//TODO
			$request->setGlobal('ship_street',"-");
			$request->setGlobal('bill_pobox',"");
			$request->setGlobal('ship_pobox',"");
			$request->setGlobal('bill_city',"");
			$request->setGlobal('ship_city',"");
			$request->setGlobal('bill_state',"");
			$request->setGlobal('ship_state',"");
			$request->setGlobal('bill_code',"");
			$request->setGlobal('ship_code',"");
			$request->setGlobal('bill_country',"");
			$request->setGlobal('ship_country',"");
			$request->setGlobal('description',"");
			$request->setGlobal('terms_conditions',"-");

			$request->setGlobal('currency_id',1); //TODO constante
			$request->setGlobal('conversion_rate',1.0); 
			$request->setGlobal('taxtype','individual'); 
			$request->setGlobal('exciseduty', 0.0);
			$request->setGlobal('duedate', $request->get('invoicedate'));
			$request->setGlobal('salescommission', 0.0);
			$request->setGlobal('vtiger_purchaseorder', '');
			$request->setGlobal('customerno', '');
			$request->setGlobal('salesorder_id', '');
			
			$request->setGlobal('tax1_group_percentage', "20.000");
			$request->setGlobal('tax1_group_amount', "");
			$request->setGlobal('tax2_group_percentage', "20.000");
			$request->setGlobal('tax2_group_amount', "");
			$request->setGlobal('tax3_group_percentage', "20.000");
			$request->setGlobal('tax3_group_amount', "");
			$request->setGlobal('tax4_group_percentage', "2.100");
			$request->setGlobal('tax4_group_amount', "");
			$request->setGlobal('shtax1_sh_percent', "20.000");
			$request->setGlobal('shtax1_sh_amount', "");
			$request->setGlobal('shtax2_sh_percent', "20.000");
			$request->setGlobal('shtax2_sh_amount', "");
			$request->setGlobal('shtax3_sh_percent', "20.000");
			$request->setGlobal('shtax3_sh_amount', "");
					
			$request->setGlobal('hidtax_row_no0',"");
			$request->setGlobal('productName0',"");
			$request->setGlobal('hdnProductId0',"");
			$request->setGlobal('lineItemType0',"");
			$request->setGlobal('subproduct_ids0',"");
			$request->setGlobal('comment0',"");
			$request->setGlobal('qty0',"1");
			$request->setGlobal('listPrice0',"0");
			$request->setGlobal('discount_type0',"zero");
			$request->setGlobal('discount_percentage0',"");
			$request->setGlobal('discount_amount0',"");
			$request->setGlobal('discount0', 'on');
			
			$request->setGlobal('hidtax_row_no1', '');
			$request->setGlobal('hdnProductId1', $request->get('serviceid'));
			$request->setGlobal('lineItemType1', 'Services');
			$request->setGlobal('subproduct_ids1', '');
			$request->setGlobal('comment1', '');
			$request->setGlobal('qty1', 1);
			$request->setGlobal('listPrice1', $totalHT);
			$request->setGlobal('discount_type1', 'amount');
			$request->setGlobal('discount_percentage1', 0);
			$request->setGlobal('discount1', 'on');
			$request->setGlobal('discount_type1',"zero");
			$request->setGlobal('discount_amount1', 0);
			$request->setGlobal('popup_tax_row1', $totalTVA);
			
			$request->setGlobal('discount_type_final', 'zero');
			$request->setGlobal('discount_final', 'on');
			$request->setGlobal('discount_percentage_final', 0);
			$request->setGlobal('discount_amount_final', 0.0);
			$request->setGlobal('shipping_handling_charge', 0.0);
			$request->setGlobal('pre_tax_total', $totalTTC);
			
			$request->setGlobal('adjustmentType', "+");
			$request->setGlobal('adjustment', "0.00");
			$request->setGlobal('received', "0.00");
			$request->setGlobal('balance', "0.00");

			
			$request->setGlobal('totalProductCount', 1);
			$request->setGlobal('total', $totalTTC);
			$request->setGlobal('subtotal', $totalTTC);

		}

		return parent::process($request);
	}
}
