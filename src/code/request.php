<?php

class Request {
	
	const ua = 'Mozilla/5.0 (Windows NT 10.0; WOW64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3202.75 Safari/537.36';
	
	public $handler;
	
	public function __construct($url, $data) {
		$this->handler = curl_init();
		curl_setopt($this->handler, CURLOPT_URL, $url);
		
		// Disable SSL validation
		curl_setopt($this->handler, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($this->handler, CURLOPT_SSL_VERIFYPEER, 0);
		
		// Follow redirects and return reponse
		curl_setopt($this->handler, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($this->handler, CURLOPT_RETURNTRANSFER, 1);
		
		// Add synthetic UA
		curl_setopt($this->handler, CURLOPT_USERAGENT, self::ua);
		
		// Add payload
		if(!empty($data)) {
			curl_setopt($this->handler, CURLOPT_POST, count($data));
			curl_setopt($this->handler, CURLOPT_POSTFIELDS, http_build_query($data));
		}	
	}
	
	public function submit() {
		
		// Load response
		$response = curl_exec($this->handler);
		if(!$response) throw new Exception('gateway');
		
		// Check status code
		$status = curl_getinfo($this->handler, CURLINFO_HTTP_CODE);
		if($status != 200) throw new Exception('503');
		
		// Return response
		return $response;
	}
	
	public function setCookie($name, $value) {
		curl_setopt($this->handler, CURLOPT_COOKIE, $name.'='.$value);
	}
	
	public function setReferrer($referrer) {
		curl_setopt($this->handler, CURLOPT_REFERER, $referrer);
	}
	
	public function getEffectiveUrl() {
		return curl_getinfo($this->handler, CURLINFO_EFFECTIVE_URL);	
	}
	
	public function close() {
		curl_close($this->handler);
	}
}