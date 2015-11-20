<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/


class Vtiger_PDF_RejetPrelvntContentViewer extends Vtiger_PDF_LetterToAccountContentViewer {


	function displayPreLastPage($parent) {
		
		parent::displayPreLastPage($parent);
		
		$models = $this->contentModels;
		$contentString = $models[0]->get('coupon');
				
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