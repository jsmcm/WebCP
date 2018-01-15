<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oSettings = new Settings();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

if($oUser->Role != "admin")
{
	 header("Location: /index.php");
        exit();
}
	


$SubjectCount = filter_input(INPUT_POST, "SubjectCount", FILTER_SANITIZE_NUMBER_INT);
$BounceCount = filter_input(INPUT_POST, "BounceCount", FILTER_SANITIZE_NUMBER_INT);
$SpamAction = filter_input(INPUT_POST, "SpamAction", FILTER_SANITIZE_STRING);

$oSettings->SetOutBoundMail550Count($BounceCount);
$oSettings->SetOutBoundMailAction($SpamAction);
$oSettings->SetOutBoundMailSubjectCount($SubjectCount);

header("Location: index.php?Notes=Saved!");
?>

