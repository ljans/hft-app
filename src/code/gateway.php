<?php abstract class Gateway {
	
	// Setup request limiting
	private $lastRequest = 0.0;
	public $requestLimit = 0.0;
	
	// Load HTML
	protected static function load($html) {
		$view = new DOMDocument();
		$cleaned = self::clean($html);
		@$view->loadHTML($cleaned);
		return new DOMXpath($view);
	}
	
	// Create fetch request
	protected function request($url, $data) {
		
		// Await the request limit
		$await = $this->requestLimit - microtime(true) + $this->lastRequest;
		if($await > 0) usleep(1000000 * $await);
		
		// Perform the request
		$this->lastRequest = microtime(true);
		return new \Request($url, $data);
	}
	
	// Clean HTML
	protected static function clean($html) {
		$html = str_replace('&nbsp;', ' ', $html);	// Decode spaces
		$html = preg_replace('/\s+/', ' ', $html);	// Clean up multiple spaces
		$html = str_replace('> ', '>', $html);		// Remove right sided spaces
		$html = str_replace(' <', '<', $html);		// Remove left sided spaces	
		return $html;
	}
}