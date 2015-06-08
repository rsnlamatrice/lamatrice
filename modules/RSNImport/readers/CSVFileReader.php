<?php

class RSNImport_CSVFileReader_Reader extends RSNImport_FileReader_Reader {

	public function readNextDataLine() {
		$nextLine = $this->readNextLine();

		if ($nextLine != false) {
			return str_getcsv($nextLine, $this->request->get('delimiter'));
		}

		return false;
	}
}
?>