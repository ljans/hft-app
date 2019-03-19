<?php class DB extends PDO {
	
	// Unique fetching
	const UNIQUE = parent::FETCH_ASSOC | parent::FETCH_UNIQUE;
	
	// Extended MySQL DB connector
	public function __construct($user, $pass, $name=NULL) {
		parent::__construct('mysql:dbname='.(is_null($name) ? $user : $name).';host=127.0.0.1;charset=utf8mb4', $user, $pass, [
			self::ATTR_EMULATE_PREPARES, false,						// Enable prepared queries
			self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION,			// Enable exceptions
			self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,		// Default fetch mode
		]);
	}
	
	// Automated queries
	public function query($statement, $parameters=[]) {
		
		// Derive parameters from object
		if(is_object($parameters)) $parameters = get_object_vars($parameters);
		
		// Array-ify single parameter
		if(!is_array($parameters)) $parameters = [$parameters];
		
		// Prepared statement
		$query = parent::prepare($statement);
		
		// Return executed query
		$query->execute($parameters);
		return $query;
	}
}