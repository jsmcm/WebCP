<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
$oDomain = new Domain();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
$oPackage = new Package();

  

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$LocalPart = $_POST["LocalPart"];
$DomainID = $_POST["DomainID"];
$Password = $_POST["Password"];

$Role = $oUser->Role;




if($DomainID > -1)
{
        $DomainInfoArray = array();
        $oDomain->GetDomainInfo($DomainID, $DomainInfoArray);
		
	$DomainName = $DomainInfoArray["DomainName"];	
        $DomainUserName = $DomainInfoArray["UserName"];
        $EmailAllowance = $oPackage->GetPackageAllowance("Emails", $DomainInfoArray["PackageID"]);
        $EmailUsage = $oPackage->GetEmailUsage($DomainID);
        $DomainOwnerClientID = $DomainInfoArray["ClientID"];
        $Role = 'client';
        $ClientID = $DomainOwnerClientID;

        //print "EmailAllowance: ".$EmailAllowance."<br>";
        //print "EmailUsage: ".$EmailUsage."<br>";
        //print "DomainID: ".$DomainID."<br>";
        //print "ClientID: ".$ClientID."<br>";
        //print "DomainUserName: ".$DomainUserName."<br>";
        //print "DomainOwnerClientID: ".$DomainOwnerClientID."<br>";

        //print "<b>You have used ".$EmailUsage. " of ".$EmailAllowance." email addresses</b><p>";
        if($EmailUsage >= $EmailAllowance)
        {
		header("location: index.php?Notes=Sorry, you have used all of your email accounts on this plan (max: ".$EmailAllowance.")");
       		exit();
	}
}



$Environmental = array('webcp', $LocalPart, $DomainName);
$Options = array('disable' => array('special', "upper"));
$CustomLanguage = array(
'length' => 'must be between %s and %s characters long',
'upper'  => 'must contain at least one uppercase character',
'lower'  => 'must contain at least one lowercase character',
'numeric'=> 'must contain at least one numeric character',
'special'=> 'must contain at least one special character',
'common' => 'too common and / or easily guessed',
'environ'=> "Cannot use the email address or part thereof");

$sp = new StupidPass(40, $Environmental, null, $CustomLanguage, $Options);
///print "Checking ".$Password."<br>";

$bool = $sp->validate($Password);  

if( ! $bool)  
{ 
	$PasswordFailedMessage = "Password verification failed!<p>";

	foreach($sp->GetErrors() as $e)  
	{  
		$PasswordFailedMessage = $PasswordFailedMessage."<font color=\"red\">";  
		$PasswordFailedMessage = $PasswordFailedMessage.$e."<br />";  
		$PasswordFailedMessage = $PasswordFailedMessage."</font>";  
	}  
			
	header("location: index.php?NoteType=error&Notes=".$PasswordFailedMessage);
	exit();
} 



for($x = 0; $x < strlen($LocalPart); $x++)
{
				
		
	if(!ctype_alnum($LocalPart[$x]))
	{
		if($LocalPart[$x] != '_' && $LocalPart[$x] != '-' && $LocalPart[$x] != '.')
		{
			header("location: index.php?Notes=Incorrectly formatted email address");
			exit();
		}
		
	}
}




if($oEmail->EmailExists($LocalPart, $DomainID) > 0)
{
	header("location: index.php?Notes=Email name already exists");
	exit();
}

$Reply = $oEmail->AddEmail($LocalPart, $DomainID, $Password, $ClientID);

if($Reply < 1)
{
	$Message = "Cannot add email address";

	if($Reply == -1)
	{
		$Message = "No more emails left on this hosting plan";
	}
	header("location: index.php?Notes=".$Message);
	exit();
}
header("location: index.php?Notes=Email added");

?>


