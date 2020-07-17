<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSimpleNonce = new SimpleNonce();
$oUser = new User();
$oDomain = new Domain();


$MaxJobs = 10;
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/cron/max_jobs.dat")) {
    $MaxJobs = (int)file_get_contents($_SERVER["DOCUMENT_ROOT"]."/cron/max_jobs.dat");
}


/**
 * @author Jordi Salvat i Alabart - with thanks to <a href="www.salir.com">Salir.com</a>.
 */

function RemoveSpaces($Text)
{
	$Replacement = "";

	for($x = 0; $x < strlen($Text); $x++)
	{
		if(substr($Text, $x, 1) != " ")
		{
			$Replacement = $Replacement.substr($Text, $x, 1);
		}
	}

	return $Replacement;
}

    function assertLineIsValid($line) {
        $regexp= buildRegexp();

        return preg_match("/$regexp/", $line);
    }

   function buildRegexp() {
        $numbers= array(
            'min'=>'[0-5]?\d',
            'hour'=>'[01]?\d|2[0-3]',
            'day'=>'0?[1-9]|[12]\d|3[01]',
            'month'=>'[1-9]|1[012]',
            'dow'=>'[0-7]'
        );

        foreach($numbers as $field=>$number) {
            $range= "($number)(-($number)(\/\d+)?)?";
            $field_re[$field]= "\*(\/\d+)?|$range(,$range)*";
        }

        $field_re['month'].='|jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec';
        $field_re['dow'].='|mon|tue|wed|thu|fri|sat|sun';

        $fields_re= '('.join(')\s+(', $field_re).')';

        $replacements= '@reboot|@yearly|@annually|@monthly|@weekly|@daily|@midnight|@hourly';

        return '^\s*('.
                '$'.
                '|#'.
                '|\w+\s*='.
                "|$fields_re\s+\S".
                "|($replacements)\s+\S".
            ')';
    }


//foreach($_POST as $key=>$val)
//{
	//print $key." = ".$val."<br>";
//}

//print "<p><hr><p>";

$url = "";
if (isset($_POST["URL"])) {
	$url = filter_var($_POST["URL"], FILTER_SANITIZE_URL);
}

$x = 0;

if(isset($_POST["Command_new"])) {
	$x = 1;
}
$Count = ((count($_POST) - $x) / 6) - 1;

$NextRow = "";
$OutPut = "";
$InvalidEntries = "";

//print "Count: ".$Count."<br>";
//print "MaxJobs: ".$MaxJobs."<br>";
if($Count > $MaxJobs) {
	$Count = $MaxJobs;
}

$domainId = $oDomain->GetDomainIDFromDomainName($url);
	
$random = random_int(1, 1000000);

$nonceArray = [	
	$oUser->Role,
	$oUser->ClientID,
	$domainId,
	$random
];
$nonce = $oSimpleNonce->GenerateNonce("getDomainInfo", $nonceArray);

$DomainInfoArray = array();
$oDomain->GetDomainInfo($domainId, $random, $DomainInfoArray, $nonce);


for($x = 0; $x < $Count; $x++)
{

	$command = $_POST["Command_".$x];
	
	$command = str_replace("/home/".$DomainInfoArray["UserName"], "/home/".$DomainInfoArray["UserName"]."/home/".$DomainInfoArray["UserName"], $command);
	

	$NextRow = RemoveSpaces($_POST["Minute_".$x])." ".RemoveSpaces($_POST["Hour_".$x])." ".RemoveSpaces($_POST["Day_".$x])." ".RemoveSpaces($_POST["Month_".$x])." ".RemoveSpaces($_POST["Weekday_".$x])." ".$command;

	if(trim($NextRow) != "")
	{
		if(assertLineIsValid($NextRow) == false)
		{
			$InvalidEntries =$InvalidEntries.$NextRow."<br>";
			//print "Invalid Entry: ".$NextRow."<br>";
		}
		else
		{
			$OutPut = $OutPut.$NextRow."\n";
			//print "Next Row: ".$NextRow."<br>";
		}
	}
}

if($Count < $MaxJobs) {

	if(isset($_POST["Command_new"])) {
	
		if(trim($_POST["Command_new"]) != "") {
			$command = $_POST["Command_new"];
			$command = str_replace("/home/".$DomainInfoArray["UserName"], "/home/".$DomainInfoArray["UserName"]."/home/".$DomainInfoArray["UserName"], $command);

			$NextRow = RemoveSpaces($_POST["Minute_new"])." ".RemoveSpaces($_POST["Hour_new"])." ".RemoveSpaces($_POST["Day_new"])." ".RemoveSpaces($_POST["Month_new"])." ".RemoveSpaces($_POST["Weekday_new"])." ".$command;

			if(trim($NextRow) != "") {

				if(assertLineIsValid($NextRow) == false) {

					$InvalidEntries =$InvalidEntries.$NextRow."<br>";
				
				} else {

					$OutPut = $OutPut.$NextRow."\n";
				
				}
			}
		}
	}
}


$Action = "saveUserCron";
$Meta = array();
array_push($Meta, $url);

$NonceValues = $oSimpleNonce->GenerateNonce($Action, $Meta);

$PostData = "CronData=".urlencode(addslashes($OutPut))."&Nonce=".$NonceValues["Nonce"]."&TimeStamp=".$NonceValues["TimeStamp"];


$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_POSTFIELDS,  $PostData);
curl_setopt($c, CURLOPT_POST, 1);

if ( file_exists("/etc/letsencrypt/renewal/".$url.".conf") ) {
	curl_setopt($c, CURLOPT_URL, "https://".$url.":2083/write.php");
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);	
} else {
	curl_setopt($c, CURLOPT_URL, "http://".$url.":2082/write.php");
}

curl_exec($c);

curl_close($c);

if($InvalidEntries != "")
{
	header("location: CronEditor.php?URL=".$url."&Notes=<b>ERROR!</b>. There was an error in your cron entries. Invalid entries were not saved!<p>".$InvalidEntries);
}
else
{
	header("Location: CronEditor.php?Notes=Crontab saved&URL=".$url);
}
