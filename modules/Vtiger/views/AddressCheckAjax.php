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
			$viewer->assign('ORIGINAL_ADDRESS', $result['original']);
			$viewer->assign('NEW_ADDRESS', $result['new']);
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
		
		$result = array(
						'original' => $originalAddress,
						'new' => $items,
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
			'fldDestinataire' => $address['name'],
			'fldRemise' => $address['street2'],
			'fldComplement' => $address['street3'],
			'deptVoie' => '',
			'fldVoie' => $address['street'],
			'fldServicePostal' => $address['pobox'],
			'fldLocalite' =>  trim($address['zip'] . ' ' .  $address['city']),
		);
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
			&& strpos($key, 'format') === false){
				$srcFieldName = substr($key, strlen('address_'));
				$fieldName = substr($key, strlen('address_'.$namesRoot));
				if($fieldName == 'zipcode' || $fieldName == 'code')
					$fieldName = 'zip';
				$mapping[$fieldName] = $srcFieldName;
				$address[$fieldName] = $value;
			}
		
		$address['name'] = trim((isset($address['firstname']) ? $address['firstname'] : '')
								. ' '
								. (isset($address['lastname']) ? $address['lastname'] : ''));
		if(!$address['name'])
			unset($address['name']);
		
		$address['mapping'] = $mapping;
		//var_dump('$address : ', $address);
		return $address;
	}
	
	private function getCookie($cache = true){
		if($cache && isset($_SESSION['mascadia-jsessionid']))
			return $_SESSION['mascadia-jsessionid'];
		
		$data = http_request('GET', $this->url, 80, $this->jspPage, false, false, false, false, 1, false, true /* Include HTTP response headers */);
		
		if(!$data) return;
		$cookie = preg_replace('/^[\s\S]+Set-Cookie:([^;]+;)[\s\S]+$/', '$1', $data);
		if(!$cookie) return;
		$cookie = preg_replace('/^[\s\S]+JSESSIONID=([^;]+);$/', '$1', $cookie);
		var_dump('cookie : ', $cookie);
		$_SESSION['mascadia-jsessionid'] = $cookie;
		return $cookie;
	}
	
	private function clearCookie(){
		if(isset($_SESSION['mascadia-jsessionid']))
			unset($_SESSION['mascadia-jsessionid']);
	}
}
