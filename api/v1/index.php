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

	if( ! file_exists($_SERVER["DOCUMENT_ROOT"]."/api/conf/server_list.txt")) {
		file_put_contents(dirname(__FILE__)."/log.log", "return false at 1\r\n", FILE_APPEND);
       		return false;
        }

        $domainArray = explode("\n", file_get_contents($_SERVER["DOCUMENT_ROOT"]."/api/conf/server_list.txt"));

	file_put_contents(dirname(__FILE__)."/log.log", "domainArray: ".print_r($domainArray,true)."\r\n", FILE_APPEND);

	foreach($domainArray as $d) {
		file_put_contents(dirname(__FILE__)."/log.log", "d: '".$d."'\r\n", FILE_APPEND);
	}

	if (in_array("*", $domainArray)) {
		file_put_contents(dirname(__FILE__)."/log.log", "has *\r\n", FILE_APPEND);
		return true;
	}

	return in_array($remoteAddress, $domainArray);

}

header("Content-Type: application/json");


if ( !isset($_SERVER["REQUEST_SCHEME"]) || $_SERVER["REQUEST_SCHEME"] != "https" ) {
	header("x-status: 400 - Invalid Request Scheme - Retry over HTTPS", true, 400);
	header("HTTP/1.1 400 Invalid Request Scheme - Retry over HTTPS");
	print json_encode( array("status" => "error", "message" => "Invalid Request Scheme - Retry over HTTPS" ) );
	exit();
}

$requestParts = array();

if ( isset($_SERVER["REQUEST_URI"]) ) {
	$request = filter_var($_SERVER["REQUEST_URI"], FILTER_SANITIZE_STRING);
	
	if ( substr($request, 0, 8) != "/api/v1/" ) {
		header("x-status: 400 - Invalid Request Uri", true, 400);
		header("HTTP/1.1 400 Invalid Request Uri - Retry over HTTPS");
		print json_encode( array("status" => "error", "message" => "Invalid Request Uri" ) );
		exit();
	}

	$request = substr($request, 8);

	if (strstr($request, "?")) {
		$request = substr($request, 0, strpos($request, "?"));
	}

	$requestParts = explode("/", $request);
}


if ( !is_array($requestParts) || empty($requestParts) ) {
	header("HTTP/1.1 400 Missing Uri Parts");
	header("x-status: 400 - Missing Uri Parts", true, 400);
	print json_encode( array("status" => "error", "message" => "Missing Uri Parts" ) );
	exit();
}


$controller = $requestParts[0];
if (count($requestParts) == 3 && strtolower($requestParts[1]) == "rrs") {
	$controller = $requestParts[1];
}


if ( substr($controller, strlen($controller) - 1, 1) != "s") {
	
	$fileExists = current(preg_grep("/".preg_quote($controller.".php")."$/i", glob(dirname(__FILE__)."/controllers/*")));
	if ( ! $fileExists ) {
		$controller = $controller."s";
	}

}

$controller = trim(ucfirst($controller));

$fileExists = current(preg_grep("/".preg_quote($controller.".php")."$/i", glob(dirname(__FILE__)."/controllers/*")));
if ( ! $fileExists ) {
	header("HTTP/1.1 404 Controller (".$controller.") Not Found");
	header("x-status: 404 - Controller (".$controller.") Not Found", true, 404);
	print json_encode( array("status" => "error", "message" => "Controller (".$controller.") not found" ) );
	exit();
}

// match the file name case to the controller name, the file name and class name will share the same case.
$controller = substr($fileExists, strrpos($fileExists, "/") + 1);
$controller = substr($controller, 0, strlen($controller) - 4);

$auth = "";
$login="";
$password="";

if ( isset($_SERVER["HTTP_AUTHORIZATION"]) ) {
	if ( substr(strtolower($_SERVER["HTTP_AUTHORIZATION"]), 0, 6) == "basic " ) {
		$auth = substr($_SERVER["HTTP_AUTHORIZATION"], 6);
	}
	
	$auth = base64_decode($auth);

	if ( strstr($auth, " ") ) {
		$login = trim(substr($auth, 0, strpos($auth, " ")));
		$password = trim(substr($auth, strpos($auth, " ") + 1));
	} else if ( strstr($auth, ":") ) {
		$login = trim(substr($auth, 0, strpos($auth, ":")));
		$password = trim(substr($auth, strpos($auth, ":") + 1));
	}
}

if ( $login == "" || $password == "" ) {
	file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);

	header("HTTP/1.1 401 Not authorized, please send HTTP_AUTHORIZATION Header");
	header("x-status: 401 - Not authorized, please send HTTP_AUTHORIZATION Header", true, 401);
	print json_encode( array("status" => "error", "message" => "Not authorized, please send HTTP_AUTHORIZATION Header" ) );
	exit();
}

/*****************************************************************************
*       
* CHECK IF REMOTE SERVER IS ALLOWED HERE!
*
*****************************************************************************/
if(remoteAddressAllowed($_SERVER["REMOTE_ADDR"]) == false) {
	//file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);
	header("HTTP/1.1 401 - Not authorized, White list IP in WebCP");
	header("x-status: 401 - Not authorized, White list IP in WebCP", true, 401);
	print json_encode( array("status" => "error", "message" => "Not authorized, White list IP in WebCP" ) );

	exit();
}


// Check login
if(validateUser($login, $password) == false) {
	//file_put_contents("/var/log/webcp/failedlog", date("Y-m-d H:i:s")." - Failed Login Attempt - IP Address = ".$_SERVER["REMOTE_ADDR"]."\r\n", FILE_APPEND);

	header("HTTP/1.1 401 - Not authorized, login/password failed");
	header("x-status: 401 - Not authorized, login/password failed", true, 401);
	print json_encode( array("status" => "error", "message" => "Not authorized, login failed" ) );
	exit();
}

$action = filter_var($_SERVER["REQUEST_METHOD"], FILTER_SANITIZE_STRING);

$requestBody = file_get_contents('php://input');
$className = "\\WebCP\\API\\Controllers\\".$controller;
$oController = new $className($requestParts, $action, $requestBody);

