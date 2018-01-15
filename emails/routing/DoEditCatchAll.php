<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}
$Role = $oUser->Role;


require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

$oEmail->EditAutoReply($_POST["AutoReplyID"], $_POST["Subject"], $_POST["MessageBody"], $ClientID);

header("location: index.php");

?>
