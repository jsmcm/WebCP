<?php

namespace WebCP\API\Controllers;

include_once $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";

class DNS
{
	//var $oUser = null;
	public function __construct($requestParts, $action, $requestBody)
	{
		$this->oDNS = new \DNS();

		$functionName = strtolower($action);
		$this->$functionName($requestParts, $requestBody);
	}

	function delete($requestParts, $requstBody)
	{
		

		if (count($requestParts) != 2) {
			header("HTTP/1.1 400 - Invalid request");
			header("x-status: 400 - Invalid request", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Invalid request"] );
			return;
		}



		$zoneId = intVal($requestParts[1]);
		$soa = $this->oDNS->GetSOAInfo($zoneId);
		
		if ($soa === false) {
			header("HTTP/1.1 400 - No Domain");
			header("x-status: 400 - No Domain", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Zone not found"] );
			return;
		}

		$this->oDNS->DeleteZone($soa["Domain"]);

		header("HTTP/1.1 200 - Zone deleted");
		header("x-status: 200 - Zone deleted", true, 200);
		print json_encode( ["status"=>"success", "message"=>"Zone deleted"] );

	}


	function post($requestParts, $requestBody)
	{
	

		$body = json_decode($requestBody);

		$clientId = 0;
		if ( isset($body->client_id) ) {
			$clientId = intVal($body->client_id);
		}

			
		$oUser = new \User();
		$user = $oUser->getUserBy("id", $clientId);
		
		if ($user === false) {
			header("HTTP/1.1 404 - No user");
			header("x-status: 404 - No user", true, 404);
			print json_encode( ["status"=>"error", "message"=>"User with id ".$clientId." does not exists"] );
			return;
		} 


		$domain = "";
		if (isset($body->domain)) {
			$domain = filter_var($body->domain, FILTER_SANITIZE_STRING);
		}
		if ($domain == "") {
			header("HTTP/1.1 400 - No domain");
			header("x-status: 400 - No domain", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Please specify a valid domain"] );
			return;
		}


		$ipv4 = "";
		if (isset($body->ipv4)) {
			$ipv4 = filter_var($body->ipv4, FILTER_VALIDATE_IP);
		}


		$ipv6 = "";
		if (isset($body->ipv6)) {
			$ipv6 = filter_var($body->ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
		}


		if ($ipv4 == "" && $ipv6 == "") {
			header("HTTP/1.1 400 - No IP");
			header("x-status: 400 - No IP", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Please specify a valid IPv4 or IPv6 address (one or both)"] );
			return;
		}


		$result = $this->oDNS->AddZone($domain, $ipv4, $ipv6, "", $clientId);

		if ($result == -2) {
			header("HTTP/1.1 400 - Domain Exists");
			header("x-status: 400 - Domain Exists", true, 400);
			print json_encode( ["status"=>"error", "message"=>"That SOA record already exists"] );
			return;
		}


		if ($result == -6) {
			header("HTTP/1.1 400 - Invalid Domain");
			header("x-status: 400 - Invalid Domain", true, 400);
			print json_encode( ["status"=>"error", "message"=>$domain." is not a valid domain name"] );
			return;
		}


		if ($result < 1) {
			header("HTTP/1.1 400 - Unspecified");
			header("x-status: 400 - Unspecified", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Error occured. Return value: ".$result] );
			return;
		}
		
		header("HTTP/1.1 200 - SOA added");
		header("x-status: 200 - SOA added", true, 200);
		print json_encode( ["status"=>"success", "message"=>"SOA record added"] );

	}

	function get($requestParts, $requestBody)
	{
	
		$soa = $this->oDNS->GETSOAList();
		
		if ( count( $requestParts ) == 1 ) {

			// Just  the naked request (DNS)... This is a list request
			header("HTTP/1.1 200 - DNS List");
			header("x-status: 200 - DNS List", true, 200);
			print json_encode( ["status"=>"success", "soa"=>$soa] );

		} else if ( count( $requestParts ) == 2 ) {

			// Query individual domain

			$domain = filter_var($requestParts[1], FILTER_SANITIZE_STRING);

			$domainId = $this->oDNS->GetDomainID($domain);

			if ($domainId < 1) {

				header("HTTP/1.1 404 - No domain");
				header("x-status: 404 - No domain", true, 404);
				print json_encode( ["status"=>"error", "message"=>"Domain ".$domain." does not exists"] );
				return;

			} 

			header("HTTP/1.1 200 - DNS Record");
			header("x-status: 200 - DNS Record", true, 200);
			$soa = $this->oDNS->GetSOAInfo($domainId);
			$rrs = $this->oDNS->GetRRSList($domainId);

			print json_encode( ["status"=>"success", "soa"=>$soa, "rrs"=>$rrs] );

		}

	}

	/*
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
				
				header("x-status: 200 - Password updated", true, 200);
 				print json_encode( array("status" => "success", "message" => "Password updated" ) );
			}
		}
	}
	 */
}
