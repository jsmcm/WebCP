<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oDNS = new DNS();
$oLog = new Log();
$oDomain = new Domain();

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /index.php");
	exit();
}

$Role = $oUser->Role;
if($Role != "admin") {
	header("Location: /index.php");
	exit();
}



$SOAID = 0;
if(isset($_POST["SOAID"])) {
	$SOAID = intVal($_POST["SOAID"]);
} else {
	header("Location: index.php?Notes=There was an error (no zone selected).&NoteType=Error");
	exit();
}

$ErrorString = "";
foreach($_POST as $key => $val) {
	$Error = false;
	$ID = 0;
	$x = strpos($key, "Name_");
	if($x > -1) {
		$ID = intVal(substr($key, $x + 5));
		$New = "";
		if(substr($key, 0, 4) == "New_") {
			$New = "New_";
		}

		$Name = filter_var($_POST[$New."Name_".$ID], FILTER_SANITIZE_STRING);
		$TTL = filter_var($_POST[$New."TTL_".$ID], FILTER_SANITIZE_NUMBER_INT);
		$Type = filter_var($_POST[$New."Type_".$ID], FILTER_SANITIZE_STRING);
		$Priority = filter_var($_POST[$New."Priority_".$ID], FILTER_SANITIZE_NUMBER_INT);
		$Record = htmlspecialchars_decode(filter_var($_POST[$New."Record_".$ID], FILTER_SANITIZE_STRING));

		if($Name == "" && $Record == "") {
			if($New == "") {
				$oDNS->DeleteRRS($ID);
			}
		} else {

			$allowUnderscore = false;
                        if ($Type == "TXT") {
                                $allowUnderscore = true;
                        }

			
			if($oDNS->ValidateDomainName($Name, false, $allowUnderscore) < 1) {
				$Error = true;
			}

			if(intVal($TTL) < 0) {	
				$Error = true;
			}		

			if($Type == "MX" && intVal($Priority) < 0) {
				$Error = true;
			}

			if($Type == "A" || $Type == "AAAA") {
				$Record = filter_var($Record, FILTER_VALIDATE_IP);
				if($Record != $_POST[$New."Record_".$ID])
				{
					$Error = true;
				}
			} else if ($Type == "TXT" ) {
				$first = substr( $Record, 0, 1);
				$last = substr( $Record, strlen($Record) - 1, 1);

				if ( $first != "\"" ) {
					$Record = "\"".$Record;
				}
				
				if ( $last != "\"" ) {
					$Record = $Record."\"";
				}
				
			}else {
				if($Type != "TXT") {
					if($oDNS->ValidateDomainName($Record) < 1) {
						$Error = true;
					}
				}	
			}

			if($Error == false) {
					
				$Value1 = $Record;
				$Value2 = "";
				
				if($Type == "MX") {
					$Value1 = $Priority;
					$Value2 = $Record;
				}

				if($New == "") {
					$oDNS->EditRRS($ID, $Name, $Type, $Value1, $Value2, "", "", "", "", "", "", "", "", $TTL, "IN");
				} else {
					$oDNS->AddRRS($SOAID, $Name, $Type, $Value1, $Value2, "", "", "", "", "", "", "", "", $TTL, "IN");
				}
			} else {
				$ErrorString = $ErrorString."Name: ".$Name."; TTL: ".$TTL."; Type: ".$Type."; Priority: ".$Priority."; Record: ".$Record."<br>";
			}
		}		
	}
}

$oDNS->IncrementSerialNumber($SOAID);

$NoteType = "Success";
$Notes = "DNS settings saved.";
if($ErrorString != "") {
	$Notes = $Notes." There were some errors and these lines were skipped:<p>".$ErrorString;
	$NoteType = "Error";
}	
header("Location: EditZone.php?ID=".$SOAID."&Notes=".$Notes."&NoteType=".$NoteType);

