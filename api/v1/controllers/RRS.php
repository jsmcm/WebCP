<?php

namespace WebCP\API\Controllers;

include_once $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
class RRS
{
	//var $oUser = null;
	public function __construct($requestParts, $action, $requestBody)
	{
		$this->oDNS = new \DNS();

		$functionName = strtolower($action);
		$this->$functionName($requestParts, $requestBody);
	}


	function get($requestParts, $requestBody)
	{
	
		// Just  the naked request (DNS)... This is a list request
		header("HTTP/1.1 400 - get not implemented");
		header("x-status: 400 - get not implemented", true, 400);
		print json_encode( ["status"=>"error", "message"=>"get method not implemented for RRS"] );

	}

	function delete($requestParts, $requestBody)
	{

		$rrsId = intVal($requestParts[2]);

		if ($rrsId < 1) {
			
			header("HTTP/1.1 400 - Invalid RRS ID");
			header("x-status: 400 - Invalid RRS ID", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Invalid RRS ID"] );
			return;

		}

		$rrs = $this->oDNS->getRRS($rrsId);
		
		if ($rrs === false) {

			header("HTTP/1.1 404 - ID does not exist");
			header("x-status: 404 - ID does not exist", true, 404);
			print json_encode( ["status"=>"error", "message"=>"RRS ID does not exist"] );
			return;

		}


		if ( $this->oDNS->DeleteRRS($rrsId) ) {
			
			header("HTTP/1.1 200 - RRS Deleted");
			header("x-status: 200 - RRS Deleted", true, 200);
			print json_encode( ["status"=>"success", "message"=>"RRS Deleted"] );
			
			$this->oDNS->IncrementSerialNumber($rrs["soa_id"]);
			return;

		}
		
		header("HTTP/1.1 400 - Unknown error");
		header("x-status: 400 - Unknown error", true, 400);
		print json_encode( ["status"=>"error", "message"=>"Unknown error"] );

	}

	function post($requestParts, $requestBody)
	{


		$body = json_decode($requestBody);


		$acceptableTypes = [
			"CNAME",
			"A",
			"AAAA",
			"MX",
			"NS",
			"TXT"
		];


		$soaId = 0;
		if (isset($body->soaId)) {
			$soaId = intVal($body->soaId);
		}

		if ($soaId < 1) {

			header("HTTP/1.1 400 - Invalid SOA ID");
			header("x-status: 400 - Invalid SOA ID", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Invalid SOA ID"] );
			return;

		}


		$name = "";
		if (isset($body->name)) {
			$name = filter_var($body->name, FILTER_SANITIZE_STRING);
		} else {
			$name = $rrs["domain"];
		}


		$ttl = 0;
		if (isset($body->ttl)) {
			$ttl = intVal($body->ttl);
		} else {
			$ttl = intVal($rrs["ttl"]);
		}


		$class = "";
		if (isset($body->class)) {
			$class = filter_var($body->class, FILTER_SANITIZE_STRING);
		} else {
			$class = $rrs["class"];
		}

		
		$type = "";
		if (isset($body->type)) {
			$type = strtoupper(filter_var($body->type, FILTER_SANITIZE_STRING));
		} else {
			$type = $rrs["type"];
		}

		if ( ! in_array($type, $acceptableTypes) ) {

			header("HTTP/1.1 400 - Unsupported rrs type");
			header("x-status: 400 - Unsupported rrs type", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Unsupported rrs type. Supported types are: ".implode(", ", $acceptableTypes)] );

			return;

		}
		
		$priority = 0;
		if (strtolower($type) == "mx") {
			if (isset($body->priority)) {
				$priority = intVal($body->priority);
			} else {
				$priority = $rrs["value1"];
			}
		}


		$record = "";
		if (isset($body->record)) {
			$record = filter_var($body->record, FILTER_SANITIZE_STRING);
		} else {

			if (strtolower($type) == "mx") {
				$record = $rrs["value2"];
			} else {
				$record = $rrs["value1"];
			}

		}

		if (strtolower($type) == "mx") {
			
			$this->oDNS->addRRS($soaId, $name, $type, $priority, $record, "", "", "", "", "", "", "", "", $ttl, $class);
		
		} else {
		
			$this->oDNS->addRRS($soaId, $name, $type, $record, "", "", "", "", "", "", "", "", "", $ttl, $class);
		
		}

		$this->oDNS->IncrementSerialNumber($soaId);


		header("HTTP/1.1 200 - Record added");
		header("x-status: 200 - Record added", true, 200);
		print json_encode( ["status"=>"success", "message"=>"Record added"] );

	}



	function patch($requestParts, $requestBody)
	{

		$rrsId = intVal($requestParts[2]);

		$body = json_decode($requestBody);

		if ($rrsId < 1) {
			
			header("HTTP/1.1 400 - Invalid RRS ID");
			header("x-status: 400 - Invalid RRS ID", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Invalid RRS ID"] );
			return;

		}

		$rrs = $this->oDNS->getRRS($rrsId);

		if ($rrs === false) {

			header("HTTP/1.1 404 - ID does not exist");
			header("x-status: 404 - ID does not exist", true, 404);
			print json_encode( ["status"=>"error", "message"=>"RRS ID does not exist"] );
			return;

		}


		$acceptableTypes = [
			"CNAME",
			"A",
			"AAAA",
			"MX",
			"NS",
			"TXT"
		];


		$name = "";
		if (isset($body->name)) {
			$name = filter_var($body->name, FILTER_SANITIZE_STRING);
		} else {
			$name = $rrs["domain"];
		}


		$ttl = 0;
		if (isset($body->ttl)) {
			$ttl = intVal($body->ttl);
		} else {
			$ttl = intVal($rrs["ttl"]);
		}


		$class = "";
		if (isset($body->class)) {
			$class = filter_var($body->class, FILTER_SANITIZE_STRING);
		} else {
			$class = $rrs["class"];
		}

		
		$type = "";
		if (isset($body->type)) {
			$type = strtoupper(filter_var($body->type, FILTER_SANITIZE_STRING));
		} else {
			$type = $rrs["type"];
		}

		if ( ! in_array($type, $acceptableTypes) ) {

			header("HTTP/1.1 400 - Unsupported rrs type");
			header("x-status: 400 - Unsupported rrs type", true, 400);
			print json_encode( ["status"=>"error", "message"=>"Unsupported rrs type. Supported types are: ".implode(", ", $acceptableTypes)] );

			return;

		}
		
		$priority = 0;
		if (strtolower($type) == "mx") {
			if (isset($body->priority)) {
				$priority = intVal($body->priority);
			} else {
				$priority = $rrs["value1"];
			}
		}


		$record = "";
		if (isset($body->record)) {
			$record = filter_var($body->record, FILTER_SANITIZE_STRING);
		} else {

			if (strtolower($type) == "mx") {
				$record = $rrs["value2"];
			} else {
				$record = $rrs["value1"];
			}

		}

		if (strtolower($type) == "mx") {
			
			$this->oDNS->EditRRS($rrsId, $name, $type, $priority, $record, "", "", "", "", "", "", "", "", $ttl, $class);
		
		} else {
		
			$this->oDNS->EditRRS($rrsId, $name, $type, $record, "", "", "", "", "", "", "", "", "", $ttl, $class);
		
		}


		$this->oDNS->IncrementSerialNumber($rrs["soa_id"]);

		header("HTTP/1.1 200 - Record updated");
		header("x-status: 200 - Record updated", true, 200);
		print json_encode( ["status"=>"success", "message"=>"Record updated"] );

	}



}
