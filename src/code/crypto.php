<?php class Crypto {
	
	// Encryption method
	const method = 'AES-256-CBC';
	
	// Key and initialization vector
	private static $key, $iv;
	
	// Class initialization
	public static function init($key, $iv) {
		self::$key = hash('sha256', $key);
		self::$iv = substr(hash('sha256', $iv), 0, 16);
	}
	
	// Encryption
	public static function encrypt($data) {
		return openssl_encrypt($data, self::method, self::$key, 0, self::$iv);
	}
	
	// Decryption
	public static function decrypt($data) {
		return openssl_decrypt($data, self::method, self::$key, 0, self::$iv);
	}
}