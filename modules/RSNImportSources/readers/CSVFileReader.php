<?php

class RSNImportSources_CSVFileReader_Reader extends RSNImportSources_FileReader_Reader {

	/**
	 * Method to read and parse the next line of the CSV file.
	 * @return array - the parsed read line.
	 */
	public function readNextDataLine() {
		$nextLine = $this->readNextLine();

		if ($nextLine != false) {
			return str_getcsv($nextLine, $this->request->get('delimiter'));
		}

		return false;
	}
}
?>