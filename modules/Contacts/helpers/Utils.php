<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Utils_Helper {
	public function getFormatedAddress($data) {
		return $data->get("mailingstreet") . " " . $data->get("mailingzip") . " " . $data->get("mailingcity");//tmp ??
	}

	public function getGPSCoordinate($address) {
		$GOOGLE_API_KEY = "AIzaSyBVETRNZfByx9_kGIfJ72wOkKTyGHtVNQ8";
		$url = "https://maps.googleapis.com/maps/api/geocode/json?key=" . $GOOGLE_API_KEY . "&address=" . urlencode($address);
		$html = file_get_contents($url);
		$data = json_decode($html, true);

		$GPSCoordinate = array("latitude" => $data['results'][0]["geometry"]["location"]['lat'],
								"longitude" => $data['results'][0]["geometry"]["location"]['lng'],
								"status" => $data['status'] == 'OK',
								"partial_match" => $data['results'][0]["partial_match"]);
		return $GPSCoordinate;
	}
}
