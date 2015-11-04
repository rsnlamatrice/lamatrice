<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/TCPDF.php';
include_once dirname(__FILE__) . '/Frame.php';

class Vtiger_PDF_Generator {
	private $headerViewer = false, $footerViewer = false, $contentViewer = false, $pagerViewer = false;
	private $headerFrame, $footerFrame, $contentFrame;
	
	private $pdf;
	
	private $isFirstPage = false;
	private $isLastPage = false;
	private $totalWidth = 0;
	private $totalHeight = 0;
	
	function __construct() {
		$this->pdf = new Vtiger_PDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT);

		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);
	}

	function setHeaderViewer($viewer) {
		$this->headerViewer = $viewer;
	}
	function setFooterViewer($viewer) {
		$this->footerViewer = $viewer;
	}
	function setContentViewer($viewer) {
		$this->contentViewer = $viewer;
	}
	function setPagerViewer($viewer) {
		$this->pagerViewer = $viewer;
	}

	function getHeaderFrame() {
		return $this->headerFrame;
	}
	function getFooterFrame() {
		return $this->footerFrame;
	}	
	function getContentFrame() {
		return $this->contentFrame;
	}

	function getPDF() {
		return $this->pdf;
	}
	
	function onLastPage() {
		return $this->isLastPage;
	}
	
	function onFirstPage() {
		return $this->isFirstPage;
	}
	
	function getTotalWidth() {
		return $this->totalWidth;
	}
	
	function getTotalHeight() {
		return $this->totalHeight;
	}
	
	function createLastPage() {
		// Detect if there is a last page already.
		if($this->isLastPage) return false;
		// Check if the last page is required for adding footer
		if(!$this->footerViewer || !$this->footerViewer->onLastPage()) return false;
		
		$pdf = $this->pdf;
		
		// Create a new page
		$pdf->AddPage();
		
		$this->isFirstPage = false;
		$this->isLastPage = true;
		
		$margins = $pdf->getMargins();
		$totalHeightFooter = $this->footerViewer? $this->footerViewer->totalHeight($this) : 0;

		if ($totalHeightFooter) {
			$this->footerFrame = new Vtiger_PDF_Frame();
			$this->footerFrame->x = $pdf->GetX();
			$this->footerFrame->y = $margins['top'];
			$this->footerFrame->h = $totalHeightFooter;
			$this->footerFrame->w = $this->totalWidth;
			
			$this->footerViewer->initDisplay($this);
			$this->footerViewer->display($this);
		}
		if($this->pagerViewer) {
			$this->pagerViewer->display($this);
		}
		return true;
	}
	
	function createPage($isLastPage = false) {
		$pdf = $this->pdf;
		
		// Create a new page
		$pdf->AddPage();
		
		if($isLastPage) {
			$this->isFirstPage = false;
			$this->isLastPage = true;
		} else {
			if($pdf->getPage() > 1) $this->isFirstPage = false;
			else $this->isFirstPage = true;
		}
	
		$margins = $pdf->getMargins();
		
		$this->totalWidth  = $pdf->getPageWidth()-$margins['left']-$margins['right'];
		
		$this->totalHeight = $totalHeight = $pdf->getPageHeight() - $margins['top'] - $margins['bottom'];
		$totalHeightHeader = $this->headerViewer? $this->headerViewer->totalHeight($this) : 0;
		$totalHeightFooter = $this->footerViewer? $this->footerViewer->totalHeight($this) : 0;

		$totalHeightContent= $this->contentViewer->totalHeight($this);
		if ($totalHeightContent === 0) $totalHeightContent = $totalHeight - $totalHeightHeader - $totalHeightFooter;

		if ($totalHeightHeader) {
			$this->headerFrame = new Vtiger_PDF_Frame();
			$this->headerFrame->x = $pdf->GetX();
			$this->headerFrame->y = $pdf->GetY();
			$this->headerFrame->h = $totalHeightHeader;
			$this->headerFrame->w = $this->totalWidth;

			$this->headerViewer->initDisplay($this);
			$this->headerViewer->display($this);
		}
		
		// ContentViewer
		$this->contentFrame = new Vtiger_PDF_Frame();
		$this->contentFrame->x = $pdf->GetX();
		$this->contentFrame->y = $pdf->GetY();
		
		$this->contentFrame->h = $totalHeightContent;
		$this->contentFrame->w = $this->totalWidth;

		$this->contentViewer->initDisplay($this);
		
		if ($totalHeightFooter) {
			$this->footerFrame = new Vtiger_PDF_Frame();
			$this->footerFrame->x = $pdf->GetX();
			$this->footerFrame->y = $totalHeight+$margins['top']-$totalHeightFooter;
			$this->footerFrame->h = $totalHeightFooter;
			$this->footerFrame->w = $this->totalWidth;

			$this->footerViewer->initDisplay($this);
			$this->footerViewer->display($this);
		}
		
		if($this->pagerViewer) {
			$this->pagerViewer->display($this);
		}
	}
	
	function generate($name, $outputMode='D') {
		$this->contentViewer->display($this);		
		$this->pdf->Output($name, $outputMode);
	}

	function getImageSize($file) {
		// get image dimensions
		$imsize = @getimagesize($file);
		if ($imsize === FALSE) {
			// encode spaces on filename
			$file = str_replace(' ', '%20', $file);
			$imsize = @getimagesize($file);
			if ($imsize === FALSE) {
				//TODO handle error better.
				//values here are consistent with one that should be max size of logo.
				return array(60, 30, null, null);
			}
		}
		return $imsize;
	}

	/** ED151004
	 * Merge PDF files into one
	 * Needs ghostscript package
	 * If failed, returns a zip file, extending $outputName with ".zip" extension
	 * @params $fileNames : file names array
	 * @param $outputName : desired output file name
	 * @return output file name
	 */
	public static function mergeFiles($fileNames, $outputName = false){
		if(!$outputName)
			$outputName = tempnam(sys_get_temp_dir(), 'mrg_'.count($fileNames).'_pdf') . '.pdf';
		
		$outputName = str_replace(' ', '_', $outputName);
		
		if(file_exists($outputName))
			unlink($outputName);
			
		$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outputName ";
		//Add each pdf file to the end of the command
		$cmd .= implode(' ', $fileNames);
		try {
			$result = shell_exec($cmd);
			if(!file_exists($outputName)){
				$outputName .= '.zip';
				$zip = new ZipArchive();
				if ($zip->open($outputName, ZipArchive::CREATE)!==TRUE) {
					exit("Impossible d'ouvrir le fichier <$outputName>\n");
				}
				foreach($fileNames as $fileName)
					$zip->addFile($fileName, '/'.basename($fileName));
				$zip->close();
			}
		}
		catch(Exception $ex){
			throw($ex);
		}
		return $outputName;
	}
}

?>
