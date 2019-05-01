<?php
require '../code/elements.php';
require '../code/controller.php';

// Construct controller
$controller = new Controller();

if(isset($_REQUEST['submit'])) try {
	
	// Check and log access
	if(!$controller->guard->pass()) throw new Warning('cooldown');
		
	// Login request (trim username because LSF ignores whitespaces)
	if(!isset($_REQUEST['username']) || !isset($_REQUEST['password']) || !isset($_REQUEST['accepted'])) throw new Warning('missing credentials');
	$response['login'] = $controller->login(trim($_REQUEST['username']), Crypto::encrypt($_REQUEST['password']));
	
	// Register device and add user data
	if($response['login']) {
		$controller->register();
		header('Location: launch?device='.$controller->user['device'].'&username='.$controller->user['username']);
	} else throw new Warning('login failed');
	
// Exception handling
} catch(Exception $e) {
	Elements::$data['info'] = $e->getMessage();
}

// Output response
Elements::$path = '../template/';
print Elements::renderFile('login');