<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) {
    session_start();
}
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class SSH
{
	var $oDatabase = null; 
	var $DatabaseConnection = null;
	var $LastErrorDescription = "";

	function __construct()
	{
		$this->oDatabase = new Database();
		$this->DatabaseConnection = $this->oDatabase->GetConnection();
	}


	function changeKeyAuthorisation($publicKeyId, $domainId, $authorisation, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); domainId cannot be blank in SSH::changeKeyAuthorisation");
			throw new Exception("<p><b>domainId cannot be blank in SSH::changeKeyAuthorisation</b><p>");
		}

		if ( intVal($publicKeyId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); publicKeyId cannot be blank in SSH::changeKeyAuthorisation");
			throw new Exception("<p><b>publicKeyId cannot be blank in SSH::changeKeyAuthorisation</b><p>");
		}


		if ( intVal($authorisation) < 0 || intVal($authorisation) > 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); authorisation cannot be blank in SSH::changeKeyAuthorisation");
			throw new Exception("<p><b>authorisation cannot be blank in SSH::changeKeyAuthorisation</b><p>");
		}


	

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::changeKeyAuthorisation</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$publicKeyId,
			$domainId,
			$authorisation
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "changeKeyAuthorisation", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::changeKeyAuthorisation</b></p>");
		}

		try {
			$query = $this->DatabaseConnection->prepare("UPDATE ssh SET authorised = :authorisation WHERE domain_id = :domain_id AND id = :id");

			$query->bindParam(":authorisation", $authorisation);
			$query->bindParam(":domain_id", $domainId);
			$query->bindParam(":id", $publicKeyId);

			$query->execute();
			
			touch( $_SERVER["DOCUMENT_ROOT"]."/nm/".$domainId.".authorise_domain_pub_key", 0755);

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> changeKeyAuthorisation(); Error = ".$e);
			return false;
		}

		return true;

	}
	







	function deleteDomainPublicKey($domainId, $keyId, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> deleteDomainPublicKey(); domainId cannot be blank in SSH::getDomainPublicKeyList");
			throw new Exception("<p><b>domainId cannot be blank in SSH::deleteDomainPublicKey</b><p>");
		}

		if ( intVal($keyId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> deleteDomainPublicKey(); keyId cannot be blank in SSH::deleteDomainPublicKey");
			throw new Exception("<p><b>keyId cannot be blank in SSH::deleteDomainPublicKey</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> deleteDomainPublicKey(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::deleteDomainPublicKey</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$keyId
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "deleteDomainPublicKey", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> deleteDomainPublicKey(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::deleteDomainPublicKey</b></p>");
		}

		try {
			$query = $this->DatabaseConnection->prepare("UPDATE ssh SET deleted = 1 WHERE domain_id = :domain_id AND id = :id");

			$query->bindParam(":domain_id", $domainId);
			$query->bindParam(":id", $keyId);

			$query->execute();
			
			touch( $_SERVER["DOCUMENT_ROOT"]."/nm/".$keyId.".delete_pub_key", 0755);
			

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> deleteDomainPublicKey(); Error = ".$e);
			return false;
		}

		return true;

	}
	












	function checkForDuplicateDomainPublicKey($domainId, $keyName, $publicKey, $domainUserName, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); domainId cannot be blank in SSH::checkForDuplicateDomainPublicKey");
			throw new Exception("<p><b>domainId cannot be blank in SSH::checkForDuplicateDomainPublicKey</b><p>");
		}

		if ( $keyName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); keyName cannot be blank in SSH::checkForDuplicateDomainPublicKey");
			throw new Exception("<p><b>keyName cannot be blank in SSH::checkForDuplicateDomainPublicKey</b><p>");
		}

		if ( $publicKey == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); publicKey cannot be blank in SSH::checkForDuplicateDomainPublicKey");
			throw new Exception("<p><b>publicKey cannot be blank in SSH::checkForDuplicateDomainPublicKey</b><p>");
		}

		if ( $domainUserName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); domainUserName cannot be blank in SSH::checkForDuplicateDomainPublicKey");
			throw new Exception("<p><b>domainUserName cannot be blank in SSH::checkForDuplicateDomainPublicKey</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::checkForDuplicateDomainPublicKey</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$keyName,
			$domainUserName
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "checkForDuplicateDomainPublicKey", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateDomainPublicKey(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::checkForDuplicateDomainPublicKey</b></p>");
		}

		$oUtils = new Utils();
		$fileName = $oUtils->slugify( $keyName );


		$dirBase = "/home/".$domainUserName."/.ssh_hashes/";

		if ($handle = opendir($dirBase))  {	
			/* This is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle))) {
	
				if(is_file($dirBase.$file)) {
					if( ($file != ".") && ($file != "..") ) {
						$filesHash = file_get_contents($dirBase.$file);
						$thisKeysHash = md5( $publicKey );

						if ( $filesHash == $thisKeysHash ) {
							// This key already exists, return the key name
							return $keyName;
						}
					}
				}
	
			}
	
			closedir($handle);	
		}
	
		return "";
	}
	






	function checkForDuplicateFileName($domainId, $keyName, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateFileName(); domainId cannot be blank in SSH::checkForDuplicateFileName");
			throw new Exception("<p><b>domainId cannot be blank in SSH::checkForDuplicateFileName</b><p>");
		}

		if ( $keyName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateFileName(); keyName cannot be blank in SSH::checkForDuplicateFileName");
			throw new Exception("<p><b>keyName cannot be blank in SSH::checkForDuplicateFileName</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateFileName(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::checkForDuplicateFileName</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$keyName
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "checkForDuplicateFileName", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateFileName(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::checkForDuplicateFileName</b></p>");
		}

		
		
		try {

			$oUtils = new Utils();
			$fileName = $oUtils->slugify( $keyName );

			$query = $this->DatabaseConnection->prepare("SELECT id FROM ssh WHERE deleted = 0 AND domain_id = :domain_id AND (public_key_name = :key_name OR file_name = :file_name)");

			$query->bindParam(":domain_id", $domainId);
			$query->bindParam(":key_name", $keyName);
			$query->bindParam(":file_name", $fileName);

			$query->execute();

			if($result = $query->fetch(PDO::FETCH_ASSOC)) {
				return true;
			}

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> checkForDuplicateFileName(); Error = ".$e);
			return false;
		}

		return false;

	}
	





	function addDomainPublicKey($domainId, $keyName, $publicKey, $domainUserName, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); domainId cannot be blank in SSH::addDomainPublicKey");
			throw new Exception("<p><b>domainId cannot be blank in SSH::addDomainPublicKey</b><p>");
		}

		if ( $keyName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); keyName cannot be blank in SSH::addDomainPublicKey");
			throw new Exception("<p><b>keyName cannot be blank in SSH::addDomainPublicKey</b><p>");
		}


		if ( $publicKey == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); publicKey cannot be blank in SSH::addDomainPublicKey");
			throw new Exception("<p><b>publicKey cannot be blank in SSH::addDomainPublicKey</b><p>");
		}


		if ( $domainUserName == "" ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); domainUserName cannot be blank in SSH::addDomainPublicKey");
			throw new Exception("<p><b>domainUserName cannot be blank in SSH::addDomainPublicKey</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::addDomainPublicKey</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId,
			$keyName,
			$domainUserName
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "addDomainPublicKey", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::addDomainPublicKey</b></p>");
		}


		try {

			$oUtils = new Utils();
			$fileName = $oUtils->slugify( $keyName );

			$query = $this->DatabaseConnection->prepare("INSERT INTO ssh VALUES (0, :domain_id, :key_name, :file_name, 0, now(), 0)");

			$query->bindParam(":domain_id", $domainId);
			$query->bindParam(":key_name", $keyName);
			$query->bindParam(":file_name", $fileName);

			$query->execute();
			$id = $this->DatabaseConnection->lastInsertId();

			file_put_contents( $_SERVER["DOCUMENT_ROOT"]."/nm/".$id.".add_pub_key", $publicKey );
			chmod( $_SERVER["DOCUMENT_ROOT"]."/nm/".$id.".add_pub_key", 0000 );

			file_put_contents( "/home/".$domainUserName."/.ssh_hashes/".$fileName, md5($publicKey) );
			chmod( "/home/".$domainUserName."/.ssh_hashes/".$fileName, 0660 );

			

		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> addDomainPublicKey(); Error = ".$e);
			return false;
		}

		return true;

	}
	





	function getFileName($keyId, $nonceArray)
	{		

		if ( intVal($keyId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getFileName(); keyId cannot be blank in SSH::getFileName");
			throw new Exception("<p><b>keyId cannot be blank in SSH::getFileName</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getFileName(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::getFileName</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$keyId
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getFileName", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getFileName(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::getFileName</b></p>");
		}
		$key = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT file_name FROM ssh WHERE deleted = 0 AND id = :keyId");

			$query->bindParam(":keyId", $keyId);

			$query->execute();
			
			if($result = $query->fetch(PDO::FETCH_ASSOC)) {

				return $result["file_name"];
			
			}
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getFileName(); Error = ".$e);
			return false;
		}

		return false;

	}





	function getPublicKey($keyId, $nonceArray)
	{		

		if ( intVal($keyId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getPublicKey(); keyId cannot be blank in SSH::getPublicKey");
			throw new Exception("<p><b>keyId cannot be blank in SSH::getPublicKey</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getPublicKey(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::getPublicKey</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$keyId
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getPublicKey", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getPublicKey(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::getPublicKey</b></p>");
		}
		$key = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT * FROM ssh WHERE deleted = 0 AND id = :keyId");

			$query->bindParam(":keyId", $keyId);

			$query->execute();
			
			if($result = $query->fetch(PDO::FETCH_ASSOC)) {

				$key["id"] = $result["id"];
				$key["domainId"] = $result["domain_id"];
				$key["publicKeyName"] = $result["public_key_name"];


				$oDomain = new Domain();
				$nonceArray = [
					$oUser->Role,
					$ClientID,
					$result["domain_id"]
				];
				
				$nonce = $oSimpleNonce->GenerateNonce("getDomainPath", $nonceArray);
				$domainPath = $oDomain->getDomainPath($result["domain_id"], $nonce);
		
				if (strstr($domainPath, "public_html") ) {
					$domainPath = substr($domainPath, 0, strpos($domainPath, "public_html"));
				}
		
				if (substr($domainPath, strlen($domainPath) - 1 ) != "/") {
					$domainPath = $domainPath."/";
				}
		
				$domainPath = $domainPath.".ssh/";

				
				$key["fileName"] = $domainPath.$result["file_name"];
				$key["authorised"] = $result["authorised"];
				$key["date"] = $result["date"];
			
			}
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getPublicKey(); Error = ".$e);
			return false;
		}

		return $key;

	}



	function getDomainPublicKeyList($domainId, $nonceArray)
	{		

		if ( intVal($domainId) < 1 ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getDomainPublicKeyList(); domainId cannot be blank in SSH::getDomainPublicKeyList");
			throw new Exception("<p><b>domainId cannot be blank in SSH::getDomainPublicKeyList</b><p>");
		}

		if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getDomainPublicKeyList(); Nonce not set");
			throw new Exception("<p><b>Nonce not set in SSH::getDomainPublicKeyList</b><p>");
		}
		
		$oUser = new User();
		$ClientID = $oUser->getClientId();

		$nonceMeta = [
			$oUser->Role,
			$ClientID,
			$domainId
		];

		$oSimpleNonce = new SimpleNonce();
		$nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "getDomainPublicKeyList", $nonceArray["TimeStamp"], $nonceMeta);

		if ( ! $nonceResult ) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getDomainPublicKeyList(); Nonce failed");
			throw new Exception("<p><b>Nonce failed in SSH::getDomainPublicKeyList</b></p>");
		}

		$oDomain = new Domain();
		$nonceArray = [
			$oUser->Role,
			$ClientID,
			$domainId
		];
		
		$nonce = $oSimpleNonce->GenerateNonce("getDomainPath", $nonceArray);
		$domainPath = $oDomain->getDomainPath($domainId, $nonce);

		if (strstr($domainPath, "public_html") ) {
			$domainPath = substr($domainPath, 0, strpos($domainPath, "public_html"));
		}

		if (substr($domainPath, strlen($domainPath) - 1 ) != "/") {
			$domainPath = $domainPath."/";
		}

		$domainPath = $domainPath.".ssh/";

		$list = array();

		try {
			$query = $this->DatabaseConnection->prepare("SELECT * FROM ssh WHERE deleted = 0 AND domain_id = :domain_id");

			$query->bindParam(":domain_id", $domainId);

			$query->execute();
			
			$x = 0;
			while($result = $query->fetch(PDO::FETCH_ASSOC)) {

				//if ( file_exists($domainPath.$result["file_name"])  ) {
					$list[$x]["id"] = $result["id"];
					$list[$x]["domainId"] = $result["domain_id"];
					$list[$x]["publicKeyName"] = $result["public_key_name"];
					$list[$x]["fileName"] = $domainPath.$result["file_name"];
					$list[$x]["authorised"] = $result["authorised"];
					$list[$x++]["date"] = $result["date"];
				//}
			}
		} catch(PDOException $e) {
			$oLog = new Log();
			$oLog->WriteLog("error", "/class.SSH.php -> getDomainPublicKeyList(); Error = ".$e);
			return false;
		}

		return $list;

	}
	
}

