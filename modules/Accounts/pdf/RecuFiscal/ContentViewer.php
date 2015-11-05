<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/


class Vtiger_PDF_RecuFiscalContentViewer extends Vtiger_PDF_LetterToAccountContentViewer {


	function initDisplay($parent) {

		if($parent->onFirstPage()){
			$contentFrame = $parent->getContentFrame();
			//gagne une ligne sur le corps du texte
			$contentFrame->y -= 1.5 * $this->headerRowHeight;
		
			$parent->setContentFrame($contentFrame);
		}
		parent::initDisplay($parent);
	}

	function displayPreLastPage($parent) {
		
		parent::displayPreLastPage($parent);
		
		$models = $this->contentModels;
		$contentString = $models[0]->get('recu_fiscal');
				
		$pdf = $parent->getPDF();
		$alignment = 'L';
		$isHtml = true;//isHtml = true ne permet pas le padding
		$cellWidth = 189;
		$contentFrame = $parent->getContentFrame();
		$contentLineX = $contentFrame->x;
		$contentLineY = $pdf->getY();
		$contentHeight = 1;
		$pdf->SetAutoPageBreak(false);
		$pdf->MultiCell($cellWidth, $contentHeight, $contentString, 0, $alignment, 0, 1, $contentLineX, $contentLineY,/*$reseth=*/true, /*$stretch=*/0, $isHtml);
		
	}
}