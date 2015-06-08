<?php

class RSNImport_ImportInvoicesFromCogilog_View extends RSNImport_Import_View {

	public function getSource() {
		return 'LBL_COGILOG';
	}

	public function getSourceType() {
		return 'LBL_DATABASE';
	}
}