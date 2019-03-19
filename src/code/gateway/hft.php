<?php namespace Gateway;
class HFT extends \Gateway {
	
	// Setup host
	const host = 'http://www.hft-stuttgart.de';
	
	// Fetch request
	public function fetch($url, $data=[]) {
		$request = $this->request($url, $data);
		return parent::load($request->submit());
	}
}