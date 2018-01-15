<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oReseller = new Reseller();
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}


$UserID = $_POST["UserID"];
$FirstName = $_POST["FirstName"];
$Surname = $_POST["Surname"];
$Password = $_POST["Password"];
$EmailAddress = $_POST["EmailAddress"];
$Action = $_POST["Action"];
$Username = $_POST["Username"];

$UserRole = "client";
if(isset($_POST["UserRole"]))
{
	$UserRole = $_POST["UserRole"];
}
	
$Role = $oUser->Role;


if($Action == 'add')
{
	if( ($oUser->UserExists($Username) > 0) || ($oUser->UserExistsByEmail($EmailAddress) > 0) )
	{
		//print "User exists!!!";
		header("location: index.php?NoteType=Error&Notes=The username already exists, please delete them first, then retry");
		exit();
	}
	
	$NewUserID = $oUser->AddUser($FirstName, $Surname, $EmailAddress, $Password, $UserRole, $Username, $ClientID);
	if($NewUserID < 1)
	{ 
		//print "Cannot add user!!!";
		header("location: index.php?NoteType=Error&Notes=Cannot add user: ".$Username);
		exit();
	}
	
	if($Role == "reseller")
	{
		$oReseller->AssignClientToReseller($ClientID, $NewUserID);
	}

	//print "User added";
	header("location: index.php?NoteType=Success&Notes=User ".$Username." added");
}
else
{

	if( ($oUser->UserExistsByEmail($EmailAddress) > 0) && ($oUser->UserExistsByEmail($EmailAddress) != $UserID) )
	{
		//print "Email address belongs to a difrerent user";
		header("location: index.php?NoteType=Error&Notes=Email address is already in use!!");
		exit();
	}

	if($oUser->EditUser($FirstName, $Surname, $EmailAddress, $Password, $UserRole, $UserID, $Username, $ClientID) < 1)
	{
		//print "Cannot edit user";
		header("location: index.php?NoteType=Error&Notes=Cannot edit user ".$Username);
		exit();
	}
	//print "User updated";
	header("location: index.php?NoteType=Success&Notes=User ".$Username." updated");
}

?>


