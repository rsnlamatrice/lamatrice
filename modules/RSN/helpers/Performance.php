<?php

class RSN_Performance_Helper {
	var $startTime;
	var $maxItems = 0;
	var $nItem = 0;
	var $prevPercent = 0;
	var $tickCounter = 0;
	
	public function __construct($maxItems = 0){
		$this->maxItems = $maxItems;
		$this->startTime = new DateTime();
	}
	
	public function tick(){
		$this->tickCounter++;
		$perfPC = $this->maxItems ? (int)($this->tickCounter/$this->maxItems * 100) . ' %' : $this->nItem++;
		if($this->prevPercent != $perfPC){
			$perfNow = new DateTime();
			$perfElapsed = date_diff($this->startTime, $perfNow)->format('%H:%i:%s');
			echo "\n import $this->tickCounter"
				.($this->maxItems ? '/'.$this->maxItems : '')
				."( $perfPC, $perfElapsed, "
				. self::getMemoryUsage()
				." ) ";
			$this->prevPercent = $perfPC;
		}
	}
	public function terminate(){
		$perfPC = $this->maxItems ? (int)($this->tickCounter/$this->maxItems * 100) : 0;
		$perfNow = new DateTime();
		$perfElapsed = date_diff($this->startTime, $perfNow)->format('%H:%i:%s');
		echo "\n Importation terminée";
		if($this->maxItems > 1)
			echo " pour $this->tickCounter/$this->maxItems "
				."( $perfPC %, $perfElapsed, "
				.", memoire : ". self::getMemoryUsage()
				." ) "
			;
		else
			echo " ( $perfElapsed ) ";
	}
	static function getMemoryUsage(){
		$size = memory_get_usage();
		$unit=array('B','KB','MB','GB','TB','PB');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
}
