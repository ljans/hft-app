<?php class Guard {
	
	// Constructor
	public function __construct($db) {
		$this->db = $db;
	}
	
	// Log request
	private function log() {
		$this->db->query('INSERT INTO requests (ip) VALUES (?)', $_SERVER['REMOTE_ADDR']);
	}
	
	// Count requests
	private function count() {
		return $this->db->query('SELECT * FROM requests WHERE ip = ?', $_SERVER['REMOTE_ADDR'])->rowCount();
	}
	
	// Clear expired requests
	private function clear() {
		$this->db->query('DELETE FROM requests WHERE time < ADDDATE(CURRENT_TIMESTAMP, INTERVAL -10 MINUTE)');
	}
	
	// Check if request can pass
	public function pass() {
		$this->clear();
		$this->log();
		return $this->count() < 100;
	}
}