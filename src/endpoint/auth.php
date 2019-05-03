<?php require '../code/controller.php';
try {
	
	// Constructor
	$controller = new Controller();
	$response = ['status' => 'OK'];
	
	// Check and log access
	if(!$controller->guard->pass()) throw new AccessLimit();
		
	// Login request (trim username because LSF ignores whitespaces)
	if(!isset($_REQUEST['username']) || !isset($_REQUEST['password']) || !isset($_REQUEST['accepted'])) throw new MissingCredentials();
	$response['login'] = $controller->login(trim($_REQUEST['username']), Crypto::encrypt($_REQUEST['password']));
	
	// Register device and add user data
	if($response['login']) {
		$controller->register();
		$response += $controller->filter($controller->user, ['username', 'displayname', 'device']);
	}
	
// Exception handling
} catch(Exception $e) {
	$response = [
		'status' => 'error',
		'error' => get_class($e),
	];
	
// Output response
} finally {
	header('Content-Type: application/json');
	print json_encode($response);
}