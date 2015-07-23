<?php
/*+***********************************************************************************
 * ED150713
 * Contrôle du format d'adresse par le Service National des Adresses
 *************************************************************************************/

class Vtiger_AddressCheckAjax_View extends Vtiger_BasicAjax_View {

	var $url = "pst-mascadia-prod.multimediabs.com";
	var $jspPage = "/IHM/saisieFR.jsp";
	var $referer = 'http://mascadia2-prod.cvf.fr/IHM/saisieFR.jsp';
	var $servlet = '/ServletMascadia';

	public function process(Vtiger_Request $request) {
		$originalAddress = $this->getAddressFromRequest($request);
		$result = $this->getNewAddress($originalAddress);
		if(!$result){
			$response = new Vtiger_Response();
			$response->setError('Adresse introuvable');
			$response->emit();
			return;
		}
		
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		$viewer->assign('MODULE', $moduleName);
		
		if(isset($result['html'])){
			$tmp_url = $this->makeTempFile($result['html']);
			unset($result['html']);
			$viewer->assign('IFRAME_SRC', $tmp_url);
		}
		else{
			$viewer->assign('MAPPING', $result['original']['_mapping_']);
			$viewer->assign('ORIGINAL_ADDRESS', $result['original']);
			$viewer->assign('NEW_ADDRESS', $result['new']);
			$viewer->assign('COMPARAISON', $result['comparaison']);
		}
		$viewer->view('AddressCheckAjax.tpl', $moduleName);
	}
	
	/**
	 *
	 * @return url
	 */
	private function makeTempFile($data){
		
		$user = Users_Record_Model::getCurrentUserModel();
			
		$data = $this->removeScriptTags($data);
		$data = preg_replace('/\<(input type="button"|img)[^\>]+\>/s', '', $data);
		
		$rootDirectory = vglobal('root_directory');
		$tmpDir = vglobal('tmp_dir');

		$tempFileName = $rootDirectory.$tmpDir . '/sna_' . $user->getId() . '.html';
		$handle = fopen($tempFileName, "w");
		fwrite($handle, $data);
		fclose($handle);

		return substr($tempFileName, strlen($rootDirectory));
	}
	private function removeScriptTags($html){
		$doc = new DOMDocument();
		
		// load the HTML string we want to strip
		@$doc->loadHTML($html);
		
		// get all the script tags
		$script_tags = $doc->getElementsByTagName('script');
		
		$length = $script_tags->length;
		
		// for each tag, remove it from the DOM
		for ($i = 0; $i < $length; $i++) {
		  if($script_tags->item($i)->parentNode)
			  $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
		}
		
		// get the HTML string back
		return $doc->saveHTML();
	}
	
	private function getNewAddress($originalAddress) {
		$newAddress = $this->getAddressFromSNA($originalAddress);
		
		if(!$newAddress)
			return false;
		
		return $newAddress;
	}
	
	private function getAddressFromSNA($originalAddress) {
		$cookie = $this->getCookie();
		if(!$cookie)
			return false;
		
		$params = $this->getSNAParams($originalAddress);
				
		http_request('POST', $this->url, 80, $this->servlet, false, $params
							, array('JSESSIONID' => $cookie)
							, array('Referer' => $this->referer)); 
		
		
		$html = http_request('GET', $this->url, 80, $this->jspPage, false, false
							, array('JSESSIONID' => $cookie)
							, array('Referer' => $this->referer)); 
		
		if(!$html){
			$this->clearCookie();
			echo('<code>Aucune réponse du service</code><br>');
			return false;
		}
		$items = $this->parseHtmlResponse($html);
		
		if(!$items){
			$this->clearCookie();
			var_dump('<code>Aucun élément de réponse</code><br>');
			return $html;
		}
		
		if(is_string($items)){
			//Adresse non déterminée, on affiche le html
			echo('<code>Adresse non déterminée<code><br>');
			return array(
				'html' => $html
			);
		}
		$comparaison = $this->compareAddresses($originalAddress, $items);
		$result = array(
					'original' => $originalAddress,
					'new' => $items,
					'comparaison' => $comparaison,
					);
		return $result;
	}
	
	private function parseHtmlResponse($html){
		//print_r(htmlentities($html));

		require_once 'include/simple_html_dom.php';
		$dom = new simple_html_dom($html);
		$table = $dom->find('div[id="adresse"] > TABLE', 0);
		if(!$table){
			return $html;
		}
		
		$items = array();
		for($i = 0; $i < 8; $i++){
			$item = $table->find('font[class="texteenveloppe"]', $i);
			if(!$item)
				break;
			$items[] = trim(str_replace('&nbsp;', ' ', $item->plaintext));
		}
		return $items;
	}
	
	private function getSNAParams($address) {	
		return array(
			'from' => 'saisieFR',
			'cible' => 'saisie',
			'action' => 'controler',
			'langue' => 'FR',
			'fldRefClient' => '',
			'fldDestinataire' => remove_accent($address['name']),
			'fldRemise' => remove_accent($address['street2']),
			'fldComplement' => remove_accent($address['street3']),
			'deptVoie' => '',
			'fldVoie' => remove_accent($address['street']),
			'fldServicePostal' => preg_replace('/^(BP)(\d+)/', '$1 $2', remove_accent($address['pobox'])),
			'fldLocalite' =>  trim($address['zip'] . ' ' .  remove_accent($address['city'])),
		);
	}
	private function escapeAddress($string){
		return str_replace(array(), array(), $string);
	}
	
	private function getAddressFromRequest(Vtiger_Request $request) {
		$mapping = array();
		$address = array();
		$namesRoot = '';
		//cherche la racine des noms des champs
		foreach($request->getAll() as $key=>$value)
			if(strpos($key, 'address_') === 0){
				$key = substr($key, strlen('address_'));
				if(strpos($key, 'city') !== false){
					$namesRoot = substr($key, 0, strpos($key, 'city'));
					break;
				}
				elseif(strpos($key, 'street') !== false){
					$namesRoot = substr($key, 0, strpos($key, 'street'));
					break;
				}
			}
		//cherche les champs d'adresse
		foreach($request->getAll() as $key=>$value)
			if(strpos($key, 'address_' . $namesRoot) === 0
			&& strpos($key, 'npai') === false
			&& strpos($key, 'format') === false
			&& strpos($key, 'country') === false){
				$srcFieldName = substr($key, strlen('address_'));
				$fieldName = substr($key, strlen('address_'.$namesRoot));
				if($fieldName == 'zipcode' || $fieldName == 'code' || $fieldName == 'postalcode')
					$fieldName = 'zip';
				$mapping[$fieldName] = $srcFieldName;
				$address[$fieldName] = $value;
			}
		
		$address['name'] = trim(
						  (isset($address['firstname']) ? $address['firstname'] : '')
						. ' '
						. (isset($address['lastname']) ? $address['lastname'] : ''));
		if(!$address['name'])
			unset($address['name']);
		
		$address['_mapping_'] = $mapping;
		//var_dump('$address : ', $address);
		return $address;
	}
	
	/**
	 * &param $cache = keep cookie value in $_SESSION. DOES NOT WORK, must be false
	 */
	private function getCookie($cache = false){
		if($cache && isset($_SESSION['mascadia-jsessionid']))
			return $_SESSION['mascadia-jsessionid'];
		
		$data = http_request('GET', $this->url, 80, $this->jspPage, false, false, false, false, 1, false, true /* Include HTTP response headers */);
		
		if(!$data) return;
		$cookie = preg_replace('/^[\s\S]+Set-Cookie:([^;]+;)[\s\S]+$/', '$1', $data);
		if(!$cookie) return;
		$cookie = preg_replace('/^[\s\S]+JSESSIONID=([^;]+);$/', '$1', $cookie);
		//var_dump('cookie : ', $cookie);
		$_SESSION['mascadia-jsessionid'] = $cookie;
		return $cookie;
	}
	
	private function clearCookie(){
		if(isset($_SESSION['mascadia-jsessionid']))
			unset($_SESSION['mascadia-jsessionid']);
	}
	
	public function compareAddresses($originalAddress, $newAddress){
		$allFieldNames = array('name', 'street2', 'street3', 'street', 'pobox', 'zip', 'city');
		$fields = array(); //array_combine($allFieldNames, explode(';', str_repeat(';', count($allFieldNames) - 1)));
		$nItem = 0;
		
		$countOriginalItems = count($originalAddress) - 1; //-1 car '_mapping_'
		$countNewItems = count($newAddress) + ($originalAddress['zip'] || $originalAddress['city'] ? 1 : 0); //combine zip et city
		//var_dump($countOriginalItems, $countNewItems, $originalAddress, $newAddress);
		$fieldName = 'name';
		if($originalAddress[$fieldName])
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
			
		$fieldName = 'street2';
		if($originalAddress[$fieldName])
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
		elseif($countOriginalItems < $countNewItems){
			//Un élément de plus
			//Eventuellement, street3 découpé en 2, donc à décaler vers le haut
			$countOriginalItems++;
			$fields[$fieldName] = array(
					'original' => '',
					'new' => $newAddress[$nItem++],
			);
		}
		$fieldName = 'street3';
		if($originalAddress[$fieldName])
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
		elseif($countOriginalItems < $countNewItems){
			//Un élément de plus
			//Eventuellement, street découpé en 2, donc à décaler vers le haut
			$countOriginalItems++;
			$fields[$fieldName] = array(
					'original' => '',
					'new' => $newAddress[$nItem++],
			);
		}
		$fieldName = 'street';
		if($originalAddress[$fieldName])
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
		elseif($countOriginalItems < $countNewItems){
			//Un élément de plus
			//Eventuellement, street3 découpé en 2
			$countOriginalItems++;
			$fields[$fieldName] = array(
					'original' => '',
					'new' => $newAddress[$nItem++],
			);
		}
		$fieldName = 'pobox';
		if($originalAddress[$fieldName])
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
		
		if($originalAddress['zip'] || $originalAddress['city']){
			if(preg_match('/^(\w+\-)?(\d+)\s+(.+)$/', $newAddress[$nItem])){
				$newAddress[$nItem + 1] = preg_replace('/^(\w+\-)?(\d+)\s+(.+)$/', '$3', $newAddress[$nItem]); //city
				$newAddress[$nItem] = preg_replace('/^(\w+\-)?([ABab\d]+)\s+(.+)$/', '$1$2', $newAddress[$nItem]); //zip
			}
			
			$fieldName = 'zip';
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
			
			$fieldName = 'city';
			$fields[$fieldName] = array(
									'original' => $originalAddress[$fieldName],
									'new' => $newAddress[$nItem++],
							);
		}
			
		//var_dump($countOriginalItems, $countNewItems, $fields);
		
		$score = false;
		foreach($fields as $fieldName => $field){
			if($field === null)
				continue;
			$status = $this->compareAddressFieldValues($fieldName, $originalAddress, $field['new']);
			$fields[$fieldName]['status'] = $status;
			if($status === 'different')
				$score = 'different';
			elseif($status !== 'equal' && $score !== 'different')
				$score = $status;
		}
		$fields['_status_'] = $score;
		//var_dump($fields);
		return $fields;
	}
	
	public function compareAddressFieldValues($fieldName, $originalAddress, $newValue){
		if($originalAddress[$fieldName] == $newValue)
			return 'equal';
		if(trim(strtoupper(remove_accent($originalAddress[$fieldName]))) == trim(strtoupper(remove_accent($newValue))))
			return 'update';
		return 'different';
	}
}
