<?php namespace Gateway;
class SWS extends \Gateway {
	
	// Setup host
	const host = 'http://sws.maxmanager.xyz/extern/mensa_stuttgart-mitte.json';
	
	// Fetch request
	public function fetch($url, $data=[]) {
		$request = $this->request($url, $data);
		return json_decode($request->submit(), true);
	}
}