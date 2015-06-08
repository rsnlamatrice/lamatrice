<?php

//TODO : implement import!
class RSNImport_ImportInvoicesFromCogilog_View extends RSNImport_Import_View {

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_COGILOG';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_DATABASE';
	}
}