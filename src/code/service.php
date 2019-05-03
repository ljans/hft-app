<?php class Service {
	
	// Setup constants
	const pause = 3;
	const logpath = '../../logs';
	
	// Configuration and status store
	private $config;
	private $status;
	
	// Constructor
	public function __construct($config, $runner) {
		$this->config = $config;
		$this->runner = $runner;
	}
	
	// Append logfile
	public static function log($message) {
		if(!is_dir(self::logpath)) mkdir(self::logpath);
		file_put_contents(self::logpath.'/'.date('Y-m-d').'.txt', date('Y-m-d H:i:s')."\t".$message."\r\n", FILE_APPEND);
	}
	
	// Set the service status
	private function set($status) {
		win32_set_service_status($status);
		if($this->status == $status) return false;
		$this->status = $status;
		return true;
	}
	
	// Process command messages
	private function process($message) {
		switch($message) {
			
			// Run the service
			default: { // START, CONTINUE, SESSIONCHANGE (user logon)
				if(self::set(WIN32_SERVICE_RUNNING)) self::log('start/continue');
				
				// Exception handling
				try { return call_user_func($this->runner); }
				catch(Problem $e) { return self::log('PROBLEM: '.var_export($e->getMessage(), true)); }
				catch(Exception $e) { return error_log($e); }
			}
			
			// Stop the service
			case WIN32_SERVICE_CONTROL_STOP:
			case WIN32_SERVICE_CONTROL_PRESHUTDOWN: {
				if(self::set(WIN32_SERVICE_STOPPED)) self::log('stopping');
				exit;
			}
			
			// Pause the service
			case WIN32_SERVICE_CONTROL_PAUSE: {
				if(self::set(WIN32_SERVICE_PAUSED)) self::log('pausing');
				return sleep(self::pause);
			}
		}
	}
	
	// Install the service
	public function install() {
		$response = win32_create_service($this->config);
		return $response === 0 ? 'Successfully installed '.$this->config['service'] : 'Error 0x'.dechex($response);
	}
	
	// Uninstall the service
	public function uninstall() {
		$response = win32_delete_service($this->config['service']);
		return $response === 0 ? 'Successfully uninstalled '.$this->config['service'] : 'Error 0x'.dechex($response);
	}	
	
	// Run the service
	public function run() {
		win32_start_service_ctrl_dispatcher($this->config['service']);
		while(true) self::process(win32_get_last_control_message());
	}
}