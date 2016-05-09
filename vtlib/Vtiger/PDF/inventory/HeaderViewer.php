<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
include_once dirname(__FILE__) . '/../viewers/HeaderViewer.php';

class Vtiger_PDF_InventoryHeaderViewer extends Vtiger_PDF_HeaderViewer {

	function totalHeight($parent) {
		$height = 100;
		
		if($this->onEveryPage) return $height;
		if($this->onFirstPage && $parent->onFirstPage()) $height;
		return 0;
	}
	
	function display($parent) {
		$pdf = $parent->getPDF();
		$headerFrame = $parent->getHeaderFrame();
		if($this->model) {
			$headerColumnWidths = array(
						    80,
						    40,
						    $headerFrame->w/3,
						);
			$modelColumns = $this->model->get('columns');
			
			// Column 1
			$offsetX = 5;
			
			$nColumn = 0;
			$headerColumnWidth = $headerColumnWidths[$nColumn];
			$modelColumn0 = $modelColumns[$nColumn];

			list($imageWidth, $imageHeight, $imageType, $imageAttr) = $parent->getimagesize(
					$modelColumn0['logo']);
			//division because of mm to px conversion
			$w = $imageWidth/3;
			if($w > $headerColumnWidth) {
				$w = $headerColumnWidth;
			}
			$h = $imageHeight * $w / $imageWidth;
			if($h > 30) {
				$h = 30;
				$w = $imageWidth * $h / $imageHeight;
			}
			$pdf->Image($modelColumn0['logo'], $headerFrame->x, $headerFrame->y, $w, $h);
			$imageHeightInMM = $h;
			
			$pdf->SetFont(PDF_FONT_NAME, 'B');
			$contentHeight = $pdf->GetStringHeight( $modelColumn0['summary'], $headerColumnWidth);
			$pdf->MultiCell($headerColumnWidth, $contentHeight, $modelColumn0['summary'], 0, 'L', 0, 1, 
				$headerFrame->x, $headerFrame->y+$imageHeightInMM+2);
			
			$pdf->SetFont(PDF_FONT_NAME, '');
			$contentHeight = $pdf->GetStringHeight( $modelColumn0['content'], $headerColumnWidth);			
			$pdf->MultiCell($headerColumnWidth, $contentHeight, $modelColumn0['content'], 0, 'L', 0, 1, 
				$headerFrame->x, $pdf->GetY());
				
			// Column 2
			$nColumn = 1;
			$headerColumnWidth = $headerColumnWidths[$nColumn];
			$offsetX = 5;
			$pdf->SetY($headerFrame->y);

			$modelColumn1 = $modelColumns[1];
			
			$offsetY = 8;
			foreach($modelColumn1 as $label => $value) {

				if(!empty($value)) {
					$pdf->SetFont(PDF_FONT_NAME, 'B');
					$pdf->SetFillColor(205,201,201);
					$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $label, 1, 'C', 1, 1, $headerFrame->x+$headerColumnWidth+$offsetX, $pdf->GetY()+$offsetY);

					$pdf->SetFont(PDF_FONT_NAME, '');
					$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $value, 1, 'C', 0, 1, $headerFrame->x+$headerColumnWidth+$offsetX, $pdf->GetY());
					$offsetY = 2;
				}
			}
			
			// Column 3
			$nColumn = 2;
			$headerColumnWidth = $headerColumnWidths[$nColumn];
			$offsetX = 10;
			
			$modelColumn2 = $modelColumns[2];
			
			$title = $this->model->get('title');
			$titleRows = explode("\n", $title);
			$contentWidth = $pdf->GetStringWidth($titleRows[0]);
			for ($i = 1; $i < sizeof($titleRows); ++$i) {
				$line_width = $pdf->GetStringWidth($titleRows[$i]);
				$contentWidth = ($contentWidth < $line_width) ? $line_width : $contentWidth;
			}
			$contentHeight = $pdf->GetStringHeight($titleRows[0], $contentWidth) * count($titleRows);
			
			$roundedRectW = $contentWidth + 2*15;
			$roundedRectX = $headerFrame->w+$headerFrame->x-$roundedRectW;
			$roundedRectH = 10 + (5 * (count($titleRows) -1));
			$pdf->RoundedRect($roundedRectX, 10, $roundedRectW, $roundedRectH, 3, '1111', 'DF', array(), array(205,201,201));
			
			$contentX = $roundedRectX + (($roundedRectW - $contentWidth)/2.0);
			$pdf->SetFont(PDF_FONT_NAME, 'B');
			$pdf->MultiCell($contentWidth*2.0, $contentHeight, $this->model->get('title'), 0, 'R', 0, 1, $contentX-$contentWidth,
				 $headerFrame->y+2);

			$offsetY = 0;//4

			foreach($modelColumn2 as $label => $value) {
				if(is_array($value)) {
					$pdf->SetFont(PDF_FONT_NAME, '');
					foreach($value as $l => $v) {
						$pdf->MultiCell($headerColumnWidth-$offsetX, 7, sprintf('%s : %s', $l, $v), 0, 'R', 0, 1, 
							$headerFrame->x+$headerColumnWidth*2.0+$offsetX, $pdf->GetY()+$offsetY);
						$offsetY = 0;
					}
				} else {
					$offsetY = 1;
					
				$pdf->SetFont(PDF_FONT_NAME, 'B');
				$pdf->SetFillColor(205,201,201);
				$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $label, 1, 'L', 1, 1, $headerFrame->x+$headerColumnWidth*2.0+$offsetX,
					$pdf->GetY()+$offsetY);
					
				$pdf->SetFont(PDF_FONT_NAME, '');
				$pdf->MultiCell($headerColumnWidth-$offsetX, 7, $value, 1, 'L', 0, 1, $headerFrame->x+$headerColumnWidth*2.0+$offsetX, 
					$pdf->GetY());
				}
			}
			$pdf->setFont(PDF_FONT_NAME, '');

			// Add the border cell at the end
			// This is required to reset Y position for next write
			$pdf->MultiCell($headerFrame->w, $headerFrame->h-$headerFrame->y, "", 0, 'L', 0, 1, $headerFrame->x, $headerFrame->y);
			
			$this->displayDestinationAddress($parent);
		}	
		
	}
	
	/**
	 * ED151020
	 * Adresse du destinataire dans un bloc fixe et sans bordure
	 */
	function displayDestinationAddress($parent){
		$pdf = $parent->getPDF();
		$headerFrame = $parent->getHeaderFrame();
		if($this->model) {
			
			$pdf->SetFont(PDF_FONT_NAME, '');
				
			$location = array(
					'x' => 210 * (100 / 210),
					'y' => 297 * (40 / 297),
					'w' => 210 * (80 / 210),
					'h' => 297 * (40 / 297),
				);
			
			//rectangle $pdf->MultiCell($location['w'], $location['h'], ' ', 1/*border*/, 'L', 0, 1, $location['x'], $location['y']);
			
			$location['x'] += 5;
			$location['y'] += 10;
			$model = $this->model->get('destinationAddress');
			//var_dump($location, $headerFrame, $model);
			foreach($model as $label => $value) {//first only
				$pdf->SetFont(PDF_FONT_NAME, '');
				$h = $location['h'];
				$pdf->MultiCell($location['w'], $h, $value, 0/*border*/, 'L', 0, 1, $location['x'], $location['y']);
				break;
			}	
		}
		
	}
	
}