<?php

$nonceArray = [
	$oUser->Role,
	$ClientID,
	"soa"
];

$nonce = $oSimpleNonce->GenerateNonce("tableExists", $nonceArray);

//print "userRole: ".$oUser->Role."<p>";
//print "clientId: ".$ClientID."<p>";
//print "soa<p>";
//print "dns_db: ".print_r($nonce,true)."<p>";

if($oDatabase->TableExists("soa", $nonce) == false) {
	$TableName = "soa";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "client_id";
	$TableInfoArray[1]["type"] = "int";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "domain";
	$TableInfoArray[2]["type"] = "tinytext";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "ttl";
	$TableInfoArray[3]["type"] = "int";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "";

	$TableInfoArray[4]["name"] = "name_server";
	$TableInfoArray[4]["type"] = "tinytext";
	$TableInfoArray[4]["key"] = "";
	$TableInfoArray[4]["default"] = "";

	$TableInfoArray[5]["name"] = "email_address";
	$TableInfoArray[5]["type"] = "tinytext";
	$TableInfoArray[5]["key"] = "";
	$TableInfoArray[5]["default"] = "";

	$TableInfoArray[6]["name"] = "serial_number";
	$TableInfoArray[6]["type"] = "int";
	$TableInfoArray[6]["key"] = "";
	$TableInfoArray[6]["default"] = "";

	$TableInfoArray[7]["name"] = "refresh";
	$TableInfoArray[7]["type"] = "int";
	$TableInfoArray[7]["key"] = "";
	$TableInfoArray[7]["default"] = "";

	$TableInfoArray[8]["name"] = "retry";
	$TableInfoArray[8]["type"] = "int";
	$TableInfoArray[8]["key"] = "";
	$TableInfoArray[8]["default"] = "";

	$TableInfoArray[9]["name"] = "expire";
	$TableInfoArray[9]["type"] = "int";
	$TableInfoArray[9]["key"] = "";
	$TableInfoArray[9]["default"] = "";

	$TableInfoArray[10]["name"] = "negative_ttl";
	$TableInfoArray[10]["type"] = "int";
	$TableInfoArray[10]["key"] = "";
	$TableInfoArray[10]["default"] = "";

	$TableInfoArray[11]["name"] = "status";
	$TableInfoArray[11]["type"] = "tinytext";
	$TableInfoArray[11]["key"] = "";
	$TableInfoArray[11]["default"] = "";

	$TableInfoArray[12]["name"] = "deleted";
	$TableInfoArray[12]["type"] = "int";
	$TableInfoArray[12]["key"] = "";
	$TableInfoArray[12]["default"] = "0";


	$nonceArray = [
		$oUser->Role,
		$ClientID,
		$TableName
	];
	
	$nonce = $oSimpleNonce->GenerateNonce("createTableFromArray", $nonceArray);
	
	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray, $nonce);
}

$nonceArray = [
	$oUser->Role,
	$ClientID,
	"rrs"
];

$nonce = $oSimpleNonce->GenerateNonce("tableExists", $nonceArray);

if($oDatabase->TableExists("rrs", $nonce) == false) {
	$TableName = "rrs";

	$TableInfoArray[0]["name"] = "id";
	$TableInfoArray[0]["type"] = "int";
	$TableInfoArray[0]["key"] = "primary key auto_increment";
	$TableInfoArray[0]["default"] = "";

	$TableInfoArray[1]["name"] = "soa_id";
	$TableInfoArray[1]["type"] = "int";
	$TableInfoArray[1]["key"] = "";
	$TableInfoArray[1]["default"] = "";

	$TableInfoArray[2]["name"] = "domain";
	$TableInfoArray[2]["type"] = "tinytext";
	$TableInfoArray[2]["key"] = "";
	$TableInfoArray[2]["default"] = "";

	$TableInfoArray[3]["name"] = "ttl";
	$TableInfoArray[3]["type"] = "int";
	$TableInfoArray[3]["key"] = "";
	$TableInfoArray[3]["default"] = "";

	$TableInfoArray[4]["name"] = "class";
	$TableInfoArray[4]["type"] = "tinytext";
	$TableInfoArray[4]["key"] = "";
	$TableInfoArray[4]["default"] = "";

	$TableInfoArray[5]["name"] = "type";
	$TableInfoArray[5]["type"] = "tinytext";
	$TableInfoArray[5]["key"] = "";
	$TableInfoArray[5]["default"] = "";

	$TableInfoArray[6]["name"] = "value1";
	$TableInfoArray[6]["type"] = "text";
	$TableInfoArray[6]["key"] = "";
	$TableInfoArray[6]["default"] = "";

	$TableInfoArray[7]["name"] = "value2";
	$TableInfoArray[7]["type"] = "tinytext";
	$TableInfoArray[7]["key"] = "";
	$TableInfoArray[7]["default"] = "";

	$TableInfoArray[8]["name"] = "value3";
	$TableInfoArray[8]["type"] = "tinytext";
	$TableInfoArray[8]["key"] = "";
	$TableInfoArray[8]["default"] = "";

	$TableInfoArray[9]["name"] = "value4";
	$TableInfoArray[9]["type"] = "tinytext";
	$TableInfoArray[9]["key"] = "";
	$TableInfoArray[9]["default"] = "";

	$TableInfoArray[10]["name"] = "value5";
	$TableInfoArray[10]["type"] = "tinytext";
	$TableInfoArray[10]["key"] = "";
	$TableInfoArray[10]["default"] = "";

	$TableInfoArray[11]["name"] = "value6";
	$TableInfoArray[11]["type"] = "tinytext";
	$TableInfoArray[11]["key"] = "";
	$TableInfoArray[11]["default"] = "";

	$TableInfoArray[12]["name"] = "value7";
	$TableInfoArray[12]["type"] = "tinytext";
	$TableInfoArray[12]["key"] = "";
	$TableInfoArray[12]["default"] = "";

	$TableInfoArray[13]["name"] = "value8";
	$TableInfoArray[13]["type"] = "tinytext";
	$TableInfoArray[13]["key"] = "";
	$TableInfoArray[13]["default"] = "";

	$TableInfoArray[14]["name"] = "value9";
	$TableInfoArray[14]["type"] = "tinytext";
	$TableInfoArray[14]["key"] = "";
	$TableInfoArray[14]["default"] = "";

	$TableInfoArray[15]["name"] = "value10";
	$TableInfoArray[15]["type"] = "tinytext";
	$TableInfoArray[15]["key"] = "";
	$TableInfoArray[15]["default"] = "";

	$TableInfoArray[16]["name"] = "deleted";
	$TableInfoArray[16]["type"] = "int";
	$TableInfoArray[16]["key"] = "";
	$TableInfoArray[16]["default"] = "0";

	$nonceArray = [
		$oUser->Role,
		$ClientID,
		$TableName
	];
	
	$nonce = $oSimpleNonce->GenerateNonce("createTableFromArray", $nonceArray);
	
	$oDatabase->CreateTableFromArray($TableName, $TableInfoArray, $nonce);
}


