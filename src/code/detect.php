<?php final class Detect {
	
	// Match selection with user agent
	private static function match(array $selection) {
		foreach($selection as $item) if(stripos($_SERVER['HTTP_USER_AGENT'], $item)) return $item;
	}

	// Detect browser
	public static function browser() { return self::match(['Firefox', 'SamsungBrowser', 'Chrome', 'Safari']); }
	
	// Detect os
	public static function os() { return self::match(['Windows', 'Android', 'Apple']); }
}