<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oReseller = new Reseller();
$oDomain = new Domain();
$oMySQL = new MySQL();

$ClientID = $oUser->getClientId();
$Role = $oUser->Role;
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}


$MySQLID = $_POST["MySQLID"];
$MySQLDatabaseName = $_POST["MySQLDatabaseName"];
$MySQLUserName = trim($_POST["MySQLUserName"]);

if(strlen($MySQLUserName) > 7)
{
	header("location: AddMySQL.php?NoteType=Error&Notes=The user name cannot exceed 7 characters");
	exit();
}



$Password = $_POST["Password"];
$Action = $_POST["Action"];
$DomainID = $_POST["DomainID"];

$DomainInfoArray = array();
$oDomain->GetDomainInfo($DomainID, $DomainInfoArray);

$PackageID = $DomainInfoArray["PackageID"];
$DomainOwnerClientID = $DomainInfoArray["ClientID"];
$DomainUserName = $DomainInfoArray["UserName"];

$MySQLDatabaseName = $DomainUserName."_".$MySQLDatabaseName;
$MySQLUserName = $DomainUserName."_".$MySQLUserName;

/*
print "Role: ".$oUser->Role."<p>";
print "UserName: ".$oUser->UserName."<p>";
print "DomainUserName: ".$DomainUserName."<p>";
print "MySQLDatabaseName: ".$MySQLDatabaseName."<p>";
print "MySQLUserName: ".$MySQLUserName."<p>";
print "Password: ".$Password."<p>";
print "DomainID: ".$DomainID."<p>";
print "ClientID: ".$ClientID."<p>";
print "DomainOwnerClientID: ".$DomainOwnerClientID."<p>";
print "MySQLID: ".$MySQLID."<p>";
print "Action: ".$Action."<p>";
print "PackageID:  ".$PackageID."<p>";
*/

$ResellerPermission = false;
if($oUser->Role == "reseller")
{
        if($oReseller->GetClientResellerID($DomainOwnerID) == $ClientID)
        {
                $ResellerPermission = true;
        }
}

if( ($ClientID != $DomainOwnerClientID) && ($Role != "admin"))
{
	if($ResellerPermission == false)
	{
		header("location: index.php?Notes=No%20permission!");
		exit();
	}
}


if($Action == 'add')
{
	//print "<p>10<p>";

	if($oMySQL->MySQLExists($MySQLDatabaseName) > 0)
	{
		header("location: index.php?Notes=The database already exists");
		exit();
	}

	/*
	print "<p>20<p>";
	print "DomainID: ".$DomainID."<br>";
	print "MySQLDatabaseName: ".$MySQLDatabaseName."<br>";
	print "MySQLUserName: ".$MySQLUserName."<br>";
	print "Password: ".$Password."<br>";
	print "DomainID: ".$DomainID."<br>";
	exit();
	*/
	
	$x = $oMySQL->AddMySQL($DomainID, $MySQLDatabaseName, $MySQLUserName, $Password, $DomainOwnerClientID, $PackageID);

	//print "<p>30<p>";
	
	if($x < 1)
	{ 
		$Message = "Cannot add MySQL database";
		
		if($x == -1)
		{
			$Message = "You do not have any more MySQL databases on your current hosting plan";
		}

		header("location: index.php?NoteType=Error&Notes=".$Message);
		exit();
	}
	//exit();
	header("location: index.php?Notes=MySQL database added");
}
else
{
	if($oMySQL->EditUser($cpDatabaseName, $DatabaseUsername, $Password, $MySQLID, $ClientID) < 1)
	{
		header("location: index.php?Notes=Cannot edit the database");
		exit();
	}
	exit();
	header("location: index.php?Notes=MySQL updated");
}

?>


