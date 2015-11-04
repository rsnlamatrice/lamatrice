<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once dirname(__FILE__) . '/../viewers/ContentViewer.php';

class Vtiger_PDF_LetterToAccountContentViewer extends Vtiger_PDF_ContentViewer {

	protected $headerRowHeight = 8;
	protected $onSummaryPage   = false;

	function __construct() {
	}
	
	function initDisplay($parent) {

		$pdf = $parent->getPDF();
		$contentFrame = $parent->getContentFrame();
		
		if(!$parent->onLastPage()) {
			$this->displayWatermark($parent);
		}
		$pdf->SetFont('','');
		
	}

	function display($parent) {
		$this->displayPreLastPage($parent);
		
		$this->displayAfterSummaryContent($parent);
		
		$this->displayLastPage($parent);
	}

	function displayPreLastPage($parent) {

		$models = $this->contentModels;
		$totalModels = count($models);
		$pdf = $parent->getPDF();

		$parent->createPage();
		$contentFrame = $parent->getContentFrame();

		$contentLineX = $contentFrame->x; $contentLineY = $contentFrame->y;
		$overflowOffsetH = 8; // This is offset used to detect overflow to next page
		for ($index = 0; $index < $totalModels; ++$index) {
			$model = $models[$index];
			
			$contentHeight = 1;
			
			$cellWidth = 189;
			
			$contentString = $model->get('reference');
			if($contentString){
				$alignment = 'L';
				$pdf->MultiCell($cellWidth, $contentHeight, $contentString, 0, $alignment, 0, 1, $contentLineX, $contentLineY);
				$contentLineY = $pdf->GetY();
			}
			
			$contentString = $model->get('date');
			if($contentString){
				$alignment = 'R';
				$pdf->MultiCell($cellWidth, $contentHeight, $contentString, 0, $alignment, 0, 1, $contentLineX, $contentLineY);
			}
			
			$contentString = $model->get('subject');
			if($contentString){
				$alignment = 'L';
				$pdf->MultiCell($cellWidth, $contentHeight, $contentString, 0, $alignment, 0, 1, $contentLineX, $contentLineY);
				$contentLineY = $pdf->GetY();
			}
			
			if($contentString)
				$contentLineY += $this->headerRowHeight;
			
			
			$contentString = $model->get('text');
			if($contentString){
				$alignment = 'J';
				$isHtml = false;//isHtml = true ne permet pas le padding
				//$contentString = str_replace("\n", '<br>', htmlspecialchars($contentString));
				//bugg de "ê" précédé d'un espace si passage à la ligne avant le "ê"
				$contentString = $contentString;
				$pdf->MultiCell($cellWidth, $contentHeight, $contentString, 0, $alignment, 0, 1, $contentLineX, $contentLineY,/*$reseth=*/true, /*$stretch=*/0, $isHtml);
				$contentLineY = $pdf->GetY();
			}
		}
		$this->onSummaryPage = true;
	}

	function displayLastPage($parent) {
		// Add last page to take care of footer display
		if($parent->createLastPage()) {
			$this->onSummaryPage = false;
		}
	}

	//ED151020
	function displayAfterSummaryContent($parent) {
		
		if ($this->afterSummaryModel) {
			$pdf = $parent->getPDF();
			$contentFrame = $parent->getContentFrame();
			
			$originalFontSize = $pdf->getFontSize();
			$pdf->SetFont(PDF_FONT_NAME,'I', $originalFontSize * 0.8);
			$y = 297 - 5;
			
			$contentStrings = array();
			foreach($this->afterSummaryModel->keys() as $key) {	
				$text = $this->afterSummaryModel->get($key);
				if(!$text)
					continue;
				$contentStrings = array_merge($contentStrings, explode("\n", decode_html($text)));
				
			}
			foreach($contentStrings as $contentString){
				$h += $pdf->GetStringHeight($contentString, $contentFrame->w) - 2;
			}
			$y -= $h;
			$pdf->SetY($y);
			foreach($contentStrings as $contentString){
				//$h = $pdf->GetStringHeight($contentString, $contentFrame->w);
				//var_dump($contentFrame->w, $h, $contentString, 0, 'L', 0, 1, $contentFrame->x, $y - $h);
				//$pdf->MultiCell($contentFrame->w, $h, $contentString, 0, 'L', 0, 1, $contentFrame->x, $y - $h, true);
				$pdf->Text($contentFrame->x, $y, $contentString);
				$y += $pdf->GetStringHeight($contentString, $contentFrame->w) - 2;
			}
			$pdf->SetFont(PDF_FONT_NAME,'', $originalFontSize);
		}
	}

	function drawStatusWaterMark($parent) {
		$pdf = $parent->getPDF();

		$waterMarkPositions=array("30","180");
		$waterMarkRotate=array("45","50","180");

		$pdf->SetFont('Arial','B',50);
		$pdf->SetTextColor(230,230,230);
		$pdf->Rotate($waterMarkRotate[0], $waterMarkRotate[1], $waterMarkRotate[2]);
		$pdf->Text($waterMarkPositions[0], $waterMarkPositions[1], 'created');
		$pdf->Rotate(0);
		$pdf->SetTextColor(0,0,0);
	}
}