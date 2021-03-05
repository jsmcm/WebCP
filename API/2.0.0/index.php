<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

function validateUser($userName, $password)
{
        $oUser = new User();

        $userId = $oUser->CheckLoginCredentials($userName, $password);

        if($userId < 1) {
        	return false;
        }

        if($oUser->GetUserRole($userId) != "admin") {
        	return false;
        }

	return true;
}

function remoteAddressAllowed($remoteAddress)
{
	file_put_contents(dirname(__FILE__)."/log.log", "in remoteAddressAllowed\r\n", FILE_APPEND);
	file_put_contents(dirname(__FILE__)."/log.log", "remoteAddress: ".$remoteAddress."\r\n", FILE_APPEND);

	if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/API/conf/server_list.txt")) {
		file_put_contents(dirname(__FILE__)."/log.log", "return false at 1\r\n", FILE_APPEND);
       		return false;
        }

        $domainArray = file($_SERVER["DOCUMENT_ROOT"]."/API/conf/server_list.txt");

	file_put_contents(dirname(__FILE__)."/log.log", "domainArray: ".print_r($domainArray, true)."\r\n", FILE_APPEND);
        for($x = 0; $x < count($domainArray); $x++) {

        	if($remoteAddress == trim($domainArray[$x])) {
                	return true;
               	}
        }

       	return false;
}

header("Content-Type: application/json");


if ( !isset($_SERVER["REQUEST_SCHEME"]) || $_SERVER["REQUEST_SCHEME"] != "https" ) {
	print json_encode( array("status" => "error", "message" => "Invalid Request Scheme - Retry over HTTPS" ) );
	header("x-status: 400 - Invalid Request Scheme - Retry over HTTPS", true, 400);
	exit();
}

$requestParts = array();

if ( isset($_SERVER["REQUEST_URI"]) ) {
	$request = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_STRING);
	
	if ( substr($request, 0, 11) != "/API/2.0.0/" ) {
		print json_encode( array("status" => "error", "message" => "Invalid Request Uri" ) );
		header("x-status: 400 - Invalid Request Uri", true, 400);
		exit();
	}

	$request = substr($request, 11);

	$requestParts = explode("/", $request);
}


if ( !is_array($requestParts) || empty($requestParts) ) {
	print json_encode( array("status" => "error", "message" => "Missing Uri Parts" ) );
	header("x-status: 400 - Missing Uri Parts", true, 400);
	exit();
}

$controller = $requestParts[0];


if ( substr($controller, strlen($controller) - 1, 1) != "s") {
	$controller = $controller."s";
}

$controller = trim(ucfirst($controller));

if ( ! file_exists(dirname(__FILE__)."/controllers/".$controller.".php" ) ) {
	print json_encode( array("status" => "error", "message" => "Controller not found" ) );
	header("x-status: 404 - Controller Not Found", true, 404);
	exit();
}

$auth = "";
$login="";
$password="";

if ( isset($_SERVER["HTTP_AUTHORIZATION"]) ) {
	if ( substr($_SERVER["HTTP_AUTHORIZATION"], 0, 6) == "basic " ) {
		$auth = substr($_SERVER["HTTP_AUTHORIZATION"], 6);
	}

	$auth = base64_decode($auth);

	if ( strstr($auth, " ") ) {
		$login = trim(substr($auth, 0, strpos($auth, " ")));
		$password = trim(substr($auth, strpos($auth, " ") + 1));
	}
}

if ( $login == "" || $password == "" ) {
	file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);

	print json_encode( array("status" => "error", "message" => "Not authorized, please send HTTP_AUTHORIZATION Header" ) );
	header("x-status: 401 - Not authorized, please send HTTP_AUTHORIZATION Header", true, 401);
	exit();
}

/*****************************************************************************
*       
* CHECK IF REMOTE SERVER IS ALLOWED HERE!
*
*****************************************************************************/
if(remoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false) {
	file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);
	print json_encode( array("status" => "error", "message" => "Not authorized, White list IP in WebCP" ) );

	header("x-status: 401 - Not authorized, White list IP in WebCP", true, 401);
	exit();
}


// Check login
if(validateUser($login, $password) == false) {
	file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);

	print json_encode( array("status" => "error", "message" => "Not authorized, login failed" ) );
	header("x-status: 401 - Not authorized, login/password failed", true, 401);
	exit();
}

$action = filter_var($_SERVER["REQUEST_METHOD"], FILTER_SANITIZE_STRING);

$requestBody = file_get_contents('php://input');

$className = "\\WebCP\\API\\Controllers\\".$controller;

$oController = new $className($requestParts, $action, $requestBody);

