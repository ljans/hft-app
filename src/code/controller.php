<?php

// Load dependencies
foreach(['db', 'guard', 'config', 'request', 'gateway', 'collection', 'crypto'] as $dependency) require "$dependency.php";
foreach(['lsf', 'hft', 'sws'] as $gateway) require "gateway/$gateway.php";
foreach(['exams', 'courses', 'subjects', 'lectures', 'events', 'professors', 'meals'] as $collection) require "collection/$collection.php";

// Controller
class Controller {
	public $db, $lsf, $hft, $sws, $guard;
	
	// Constructor
	public function __construct() {
		Crypto::init(Config::CRYPTO_KEY, Config::CRYPTO_IV);
		$this->db = new DB(Config::DB_USER, Config::DB_PASS, Config::DB_NAME);
		$this->lsf = new Gateway\LSF();
		$this->hft = new Gateway\HFT();
		$this->sws = new Gateway\SWS();
		$this->guard = new Guard($this->db);
	}
	
	// Generate random token
	public static function token($length) {
		$token = ''; $pool = [];
		for($i=0; $i<=9; $i++) $pool[] = $i;
		for($i=65; $i<=90; $i++) $pool[] = chr($i);
		for($i=97; $i<=122; $i++) $pool[] = chr($i);
		for($i=0; $i<$length; $i++) $token.= $pool[rand(0, count($pool)-1)];
		return $token;
	}
	
	// Filter array
	public static function filter($array, $keys) {
		return array_intersect_key($array, array_flip($keys));
	}
	
	// Get request parameter
	public static function get($index) {
		return isset($_REQUEST[$index]) ? $_REQUEST[$index] : NULL;
	}
	
	// Perform login from cache or network
	public function login($username, $password) {
		
		// Load cached user
		$query['user'] = $this->db->query('SELECT username, password, displayname, enabled, valid FROM users WHERE username = ?', $username);
		$cached = $query['user']->rowCount() == 1;
		
		// Check credentials against cache
		if($cached) {
			$user = $query['user']->fetch();
			
			// Disabled user
			if(!$user['enabled']) throw new Exception('disabled');
			
			// Check credentials
			if($user['valid'] && $user['password'] == $password) {
				$this->user = $user;
				return true;
			}
		}
		
		// Check credentials against network
		if($this->lsf->login($username, $password)) {
			$this->user = $this->lsf->session;
			
			// Insert or update user
			$this->db->query('
				INSERT INTO users (username, displayname, password) 
				VALUES (:username, :displayname, :password)
				ON DUPLICATE KEY UPDATE displayname = :displayname, password = :password, valid = TRUE
			', [
				'username' => $this->user['username'],
				'password' => $password,
				'displayname' => $this->user['displayname'],
			]);
			
			// User initialization
			if(!$cached) {
				
				// Load exams
				$exams = new Collection\Exams($this->user['username']);
				$exams->fetch($this->lsf);
				$exams->write($this->db);
				
				// Setup welcome message
				$this->db->query('INSERT INTO messages (receiver, title, text, href) VALUES (:receiver, :title, :text, :href)', [
					'receiver' => $this->user['username'],
					'title' => 'Hallo '.strstr($this->user['displayname'], ' ', true).'!',
					'text' => 'Lorem ipsum dolor sit amet',
					'href' => '/courses',
				]);
			}
			
			// Logout at gateway
			$this->lsf->logout();
			
			// Return login state
			return true;
		} return false;
	}
	
	// Register device
	public function register() {
		$this->user['device'] = self::token(64);
		$this->db->query('INSERT INTO devices (id, user) VALUES (:device, :username)', self::filter($this->user, ['device', 'username']));
	}
}