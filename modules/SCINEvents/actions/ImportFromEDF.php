<?php
/*+***********************************************************************************
 * ED150707
 ************************************************************************************ */

class SCINSourceEDF extends SCINSources {

	var $url_template = 'https://www.edf.fr/groupe-edf/producteur-industriel/carte-des-implantations/%s/actualites?';
	var $page_url_template = 'page=%s';
	
	static $sourceName = 'EDF';
	static $pageCodeField = 'edf_pagecode';
	
	public function __construct($scinInstallation) {
		$this->scinInstallation = $scinInstallation;
	}

	public static function getSourceName(){
		return self::$sourceName;
	}

	public static function getPageCodeField(){
		return self::$pageCodeField;
	}
	
	public function getPageCode(){
		return $this->scinInstallation->get(self::getPageCodeField());
	}

	/**
	 * Retourne un tableau d'instance d'installations concernées
	 * @param $installationUniqueId (optionel) : identifiant de la seule installation traitée
	 */
	protected static function getInstances($installationUniqueId = false) {
		$scinInstallations = self::getInstallations(self::getPageCodeField());
		$records = array();
		foreach($scinInstallations as $id => $record){
			if((!$installationUniqueId || $installationUniqueId == $id)
			&& $record->get(self::getPageCodeField()))
				$records[$id] = new self($record);
		}
		return $records;
	}
	
	public function importData(){
		
		$lastEvent = $this->getLastSCINEvent();
		if(is_object($lastEvent)){
			$lastDate = new DateTime($lastEvent->get('dateevent'));
		}
		else
			$lastDate = false;
		
		$page = 0;
		while ($this->importPageData($page, $lastDate)){
			if(++$page > 8)
				break;
		}
		return $page > 0;
	}

	public function importPageData($page, $lastDate){
		$url = $this->getPageUrl($page);
		$data = self::url_get_contents($url);
		if(!$data)
			return false;
		
		$nArticle = 0;
		do {
			
			$article = $data->find('div[class="minisite-actu"]', $nArticle);
			if(!$article) break;
			$date = $this->parseDate($article->find('p[class="minisite-actu-date"]', 0)->plaintext);
			$title = $this->cleanContent($article->find('h2[class="minisite-actu-title"]', 0)->plaintext);
			$content = $this->cleanContent($article->find('div[class="minisite-actu-content"]', 0)->plaintext);
			if($lastDate && $date <= $lastDate)
				break;
			if(!$this->createEvent($url, $article, $title, $date, $content))
				break;
			$nArticle++;
		} while($article);
		return $nArticle;
	}
	
	private function parseDate($date){
		$matches = array();
		if(preg_match_all('/(?P<d>\d+)\/(?P<m>\d+)\/(?P<y>\d+)/', $date, $matches)){
			$dateTime = new DateTime($matches['y'][0] . '-' . $matches['m'][0] . '-' . $matches['d'][0]);
			return $dateTime;
		}
	}
	
	private function cleanContent($html){
		//file_put_contents('c:\\temp\\ajeter.txt', $html);
		return str_replace(array('’', '&nbsp;'), array('\'', ' '), trim($html));
	}
	
	protected function createEvent($url, $article, $title, $date, $content){
		
		$record = Vtiger_Record_Model::getCleanInstance('SCINEvents');
		
		$record->set('mode', 'create');
		$record->set('scininstallationsid', $this->scinInstallation->getId());
		$record->set('title', $title);
		$record->set('dateevent', $date->format('d-m-Y'));
		$record->set('pagecontent', $content);
		$record->set('scinsource', $this->getSourceName());
		$record->set('urlsource', $url);
		$record->set('gravite', '');
		$record->set('assigned_user_id', ASSIGNEDTO_ALL);
		
		$record->save();
		
		echo '<li>' .  $date->format('d-m-Y') . ' : ' .  $title;
		
		return true;
		
	}
	
	public function getPageUrl($page){
		
		$url = sprintf($this->url_template, $this->getPageCode());
		if($page > 0)
			$url .= sprintf($this->page_url_template, $page);
		return $url;
	}
}

?>