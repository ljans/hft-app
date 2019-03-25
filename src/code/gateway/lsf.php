<?php namespace Gateway;
class LSF extends \Gateway {
	
	// Setup host
	const host = 'https://lsf.hft-stuttgart.de/qisserver/rds';
	
	// Session store
	public $session;
	
	// Fetch request
	public function fetch($url, $data=[]) {
		$request = $this->request($url, $data);
		
		// Add session cookie
		if(isset($this->session)) $request->setCookie('JSESSIONID', $this->session['id']);
		
		// Submit request and return view
		return parent::load($request->submit());
	}
	
	// Perform login
	public function login($username, $password) {
		
		// Submit request
		$request = $this->request(self::host.'?state=user&type=1&category=auth.login', ['asdf' => $username, 'fdsa' => \Crypto::decrypt($password)]);
		$view = parent::load($request->submit());
		
		// Extract session
		$target = parse_url($request->getEffectiveUrl());
		if(!preg_match('/^\/qisserver\/rds;jsessionid=([a-zA-Z0-9]+)$/', $target['path'], $session)) return false;
		
		// Parse displayname
		$status = $view->query('//div[@class="divloginstatus"]/text()');
		if($status->length == 0) throw new \Warning('div.divloginstatus {textnode}:first-child');
		$this->session = [
			'id' => $session[1],
			'username' => $username,
			'displayname' => preg_replace('/^(Herr|Frau) /', '', $status[0]->nodeValue)
		]; return true;
	}
	
	// Perform logout
	public function logout() {
		$this->fetch(self::host.'?state=user&type=4&category=auth.logout');
		unset($this->session);
	}
}
