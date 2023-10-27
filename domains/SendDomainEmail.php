<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



$FirstName = "";
if(isset($_POST["FirstName"]))
{
	$FirstName = $_POST["FirstName"];
}


$Surname = "";
if(isset($_POST["Surname"]))
{
	$Surname = $_POST["Surname"];
}

$UserName = "";
if(isset($_POST["UserName"]))
{
	$UserName = $_POST["UserName"];
}



$Password = "";
if(isset($_POST["Password"]))
{
	$Password = $_POST["Password"];
}


$EmailAddress = "";
if(isset($_POST["EmailAddress"]))
{
	$EmailAddress = $_POST["EmailAddress"];
}


$Domain = "";
if(isset($_POST["Domain"]))
{
	$Domain = $_POST["Domain"];
}




    // Send Client Email
   $somecontent = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";
$somecontent = $somecontent."<html xmlns=\"http://www.w3.org/1999/xhtml\">";
$somecontent = $somecontent."<head profile=\"http://gmpg.org/xfn/11\">";
$somecontent = $somecontent."";
$somecontent = $somecontent."<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />";
$somecontent = $somecontent."";
$somecontent = $somecontent."";
$somecontent = $somecontent."<style>";
$somecontent = $somecontent."input[type=\"text\"], input[type=\"password\"], textarea, select { ";
    $somecontent = $somecontent."outline: none;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."* {";
$somecontent = $somecontent."	border:none; ";
$somecontent = $somecontent."	margin:0; ";
$somecontent = $somecontent."	padding:0;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."body {";
$somecontent = $somecontent."	color: #000; ";
$somecontent = $somecontent."	font:12.35px Verdana, sans-serif;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."a:link. a:visited {";
$somecontent = $somecontent."	color:#0054a6;";
$somecontent = $somecontent."	text-decoration:none; ";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."a:hover {";
$somecontent = $somecontent."	text-decoration:underline";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."h1 {";
$somecontent = $somecontent."	font-size:20px;";
$somecontent = $somecontent."	margin-bottom:20px; ";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."h3 {";
$somecontent = $somecontent."	text-decoration: underline;";
$somecontent = $somecontent."	margin-bottom:10px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#wrap {";
$somecontent = $somecontent."	margin:20px 100px;";
$somecontent = $somecontent."	width:900px; ";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."p {";
$somecontent = $somecontent."	margin:15px 0;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#header {";
$somecontent = $somecontent."	margin-bottom:20px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."label {";
$somecontent = $somecontent."	display:block; ";
$somecontent = $somecontent."	padding-bottom:5px; ";
$somecontent = $somecontent."	margin-top:10px;";
$somecontent = $somecontent."	font-size:13px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform {";
$somecontent = $somecontent."	width:900px; ";
$somecontent = $somecontent."	overflow:hidden;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li {";
$somecontent = $somecontent."	list-style:none; ";
$somecontent = $somecontent."	padding-bottom:20px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li .fieldbox {";
$somecontent = $somecontent."	background:transparent url(/img/subfield.jpg) no-repeat top left; ";
$somecontent = $somecontent."	float:left; ";
$somecontent = $somecontent."	height:27px; ";
$somecontent = $somecontent."	padding-left:5px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li .fieldbox select {";
$somecontent = $somecontent."	background:transparent url(/img/subfield.jpg) no-repeat top right; ";
$somecontent = $somecontent."	height:27px; ";
$somecontent = $somecontent."	padding-top:1px;";
$somecontent = $somecontent."	width:400px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."#contactform li .fieldbox input {";
$somecontent = $somecontent."	background:transparent url(/img/subfield.jpg) no-repeat top right; ";
$somecontent = $somecontent."	height:27px; ";
$somecontent = $somecontent."	padding-top:1px;";
$somecontent = $somecontent."	width:400px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li .fieldbox #contact {";
$somecontent = $somecontent."	width:200px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li .msgbox {";
$somecontent = $somecontent."	background:transparent url(/img/msgfield.jpg) no-repeat top left; ";
$somecontent = $somecontent."	float:left; ";
$somecontent = $somecontent."	height:110px; ";
$somecontent = $somecontent."	padding-left:5px;";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#contactform li .msgbox textarea {";
$somecontent = $somecontent."	background:transparent url(/img/msgfield.jpg) no-repeat top right; ";
$somecontent = $somecontent."	height:110px;";
$somecontent = $somecontent."	padding-top:5px;";
$somecontent = $somecontent."	width:500px;	 ";
$somecontent = $somecontent."}";
$somecontent = $somecontent."";
$somecontent = $somecontent."#button {";
$somecontent = $somecontent."	background:#acb4cb; color:#fff; ";
$somecontent = $somecontent."	cursor:pointer;";
$somecontent = $somecontent."	padding:5px 20px; ";
$somecontent = $somecontent."	-moz-border-radius:4px;";
$somecontent = $somecontent."	-webkit-border-radius:4px";
$somecontent = $somecontent."}";
$somecontent = $somecontent."</style>";
$somecontent = $somecontent."</head>";
$somecontent = $somecontent."";
$somecontent = $somecontent."<body style=\"margin:0; background: #ededed\">";
$somecontent = $somecontent."";
  $somecontent = $somecontent."";
$somecontent = $somecontent."<div style=\"height:95px; background: #000000; \">";
$somecontent = $somecontent."<div style=\"float: left; width:100%; margin-top:20px;\"><font style=\"margin-left:50px; color:white; font-family: 'Droid Sans', Verdana; font-size:50px;\">Web Control Panel Lite</font> </div>";
$somecontent = $somecontent."";
$somecontent = $somecontent."</div>";
$somecontent = $somecontent."<div style=\"font-weight: bold; height:35px; background-color:blue; font-size:18px; padding-top:8px; padding-left:85px; color:white; font-family: 'Droid Sans', Verdana;\">User User Details</div>";
$somecontent = $somecontent."";
$somecontent = $somecontent."";
$somecontent = $somecontent."";
$somecontent = $somecontent."        <div id=\"wrap\">";
$somecontent = $somecontent."";
		$somecontent = $somecontent."<b>Good day ".$FirstName." ".$Surname.",</b>";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."A login to your web hosting control panel has been created. Here you will be able to manage your web hosting account,";
		$somecontent = $somecontent."including being able setup email accounts, FTP account and MySQL databases.";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<h3>Login Details</h3>";
		$somecontent = $somecontent."Username: <b>".$UserName."</b> or <b>".$EmailAddress."</b><br>";
		$somecontent = $somecontent."Password: <b>".$Password."</b> (you will be required to change this when you first log in).";
                $somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<h3>Important Links</h3>";
		$somecontent = $somecontent."Web Control Panel: <a href=\"http://".$Domain."/webcp\">http://".$Domain."/webcp</a><br>";
		$somecontent = $somecontent."Web Mail: <a href=\"http://".$Domain."/webmail\">http://".$Domain."/webmail</a><br>";
		$somecontent = $somecontent."phpMyAdmin: <a href=\"http://".$Domain."/mysql\">http://".$Domain."/mysql</a>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<h3>Temporary Links</h3>";
		$somecontent = $somecontent."<b>NOTE: </b> The above links will only work once your domain name has been registered. Once registered, it can take up ";
		$somecontent = $somecontent."to 24 hours to be live. This is known as propogation.";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."You should be able to access the same links as above using the following URLs:";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<br>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."Web Control Panel: <a href=\"http://".$_SERVER["SERVER_NAME"]."/webcp\">http://".$_SERVER["SERVER_NAME"]."/webcp</a><br>";
		$somecontent = $somecontent."Web Mail: <a href=\"http://".$_SERVER["SERVER_NAME"]."/webmail\">http://".$_SERVER["SERVER_NAME"]."/webmail</a><br>";
		$somecontent = $somecontent."phpMyAdmin: <a href=\"http://".$_SERVER["SERVER_NAME"]."/mysql\">http://".$_SERVER["SERVER_NAME"]."/mysql</a>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."Once your domain is live, you can use your own domain name to access these features.";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."<p>";
		$somecontent = $somecontent."";
		$somecontent = $somecontent."Regards....";
		$somecontent = $somecontent."<br>";
		$somecontent = $somecontent."<a href=\"http://".$_SERVER["SERVER_NAME"]."\">".$_SERVER["SERVER_NAME"]."</a>";
		$somecontent = $somecontent."";
        $somecontent = $somecontent."</div>";
$somecontent = $somecontent."";
$somecontent = $somecontent."";
$somecontent = $somecontent."</body>";
$somecontent = $somecontent."</html>";
 
    
    $message = $somecontent;





		$PlainTextMessage = $PlainTextMessage."Good day ".$FirstName." ".$Surname.",";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."A login to your web hosting control panel has been created. Here you will be able to manage your web hosting account,";
		$PlainTextMessage = $PlainTextMessage."including being able setup email accounts, FTP account and MySQL databases.";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Login Details\r\n-----------------------------\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."Username: ".$UserName." or ".$EmailAddress."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."Password: ".$Password." (you will be required to change this when you first log in).";
                $PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Important Links\r\n-----------------------------\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."Web Control Panel: http://".$Domain."/webcp\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."Web Mail: http://".$Domain."/mail\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."phpMyAdmin: http://".$Domain."/mysql";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Temporary Links\r\n-----------------------------\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."NOTE:  The above links will only work once your domain name has been registered. Once registered, it can take up ";
		$PlainTextMessage = $PlainTextMessage."to 24 hours to be live. This is known as propogation.";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."You should be able to access the same links as above using the following URLs:";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Web Control Panel: http://".$_SERVER["SERVER_NAME"]."/webcp\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."Web Mail: http://".$_SERVER["SERVER_NAME"]."/webmail\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."phpMyAdmin: http://".$_SERVER["SERVER_NAME"]."/mysql";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Once your domain is live, you can use your own domain name to access these features.";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage."";
		$PlainTextMessage = $PlainTextMessage."Regards....";
		$PlainTextMessage = $PlainTextMessage."\r\n\r\n";
		$PlainTextMessage = $PlainTextMessage.$_SERVER["SERVER_NAME"]."";
		$PlainTextMessage = $PlainTextMessage."";
		
            
            $mail = new PHPMailer(true);
            
		$mail->IsSMTP();
			$mail->SMTPOptions = [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				]
			];




            $mail->ClearAddresses(); 
            $mail->ClearAttachments();
            $mail->IsHTML(true); 
            $mail->AddReplyTo("noreply@".substr($_SERVER["SERVER_NAME"], 4), $_SERVER["SERVER_NAME"]);
            $mail->From = "noreply@".substr($_SERVER["SERVER_NAME"], 4);
            $mail->FromName = $_SERVER["SERVER_NAME"];
            
            $mail->AddAddress("john@softsmart.co.za");
            
            $mail->Subject = "Web Hosting User Login";
            $mail->AltBody = $PlainTextMessage;
            $mail->Body = $message;
            $mail->WordWrap = 80;
            
            $mail->Send();
    
print $message;

?>

