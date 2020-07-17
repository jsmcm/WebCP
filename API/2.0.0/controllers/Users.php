<?php

namespace WebCP\API\Controllers;

include_once $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
class Users
{
	//var $oUser = null;
	public function __construct($requestParts, $action, $requestBody)
	{
		$this->oUser = new \User();

		$functionName = strtolower($action);
		$this->$functionName($requestParts, $requestBody);
	}

	public function patch($requestParts, $requestBody)
	{
		// Patch wants 1 request part (besides the controller) and that's the user email
		if ( count( $requestParts ) != 2 ) {
 			print json_encode( array("status" => "error", "message" => "Request body (end point) invalid" ) );
			header("x-status: 400 - Request body (end point) invalid", true, 400);
        		exit();
		}
		
		if ( ! filter_var($requestParts[1], FILTER_VALIDATE_EMAIL)) {
 			print json_encode( array("status" => "error", "message" => "Request body (email) not valid" ) );
		        header("x-status: 400 - Request body (email) not valid", true, 400);
        		exit();
		}

		if ( $requestBody == "" || !strstr($requestBody, "=") ) {
 			print json_encode( array("status" => "error", "message" => "Request body not valid" ) );
		        header("x-status: 400 - Request body not valid", true, 400);
        		exit();
		}

		$email = filter_var($requestParts[1], FILTER_SANITIZE_EMAIL);
			
		$requests = explode("&", $requestBody);

		foreach( $requests as $request ) {
			if ( substr($request, 0, 9 ) == "password=" ) {
				$this->oUser->changePassword(substr($request, 9), $email);
				
 				print json_encode( array("status" => "success", "message" => "Password updated" ) );
				header("x-status: 200 - Password updated", true, 200);
			}
		}
	}
}
