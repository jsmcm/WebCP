<?php
session_start();

$somecontent = "";

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$oUser = new User();
$TempPassword = $oUser->ResetPassword($_POST["email"]);
$UserName = $oUser->GetUserName($oUser->UserExistsByEmail($_POST["email"]));

if($UserName != "")
{
	$oSettings = new Settings();
	$BCC = $oSettings->GetForwardSystemEmailsTo();

    	//set_include_path("../");

   	// Send Client Email
    	$somecontent = $somecontent."Your login to the ".$_SERVER["SERVER_NAME"]." admin panel is:<p><b>Username:</b> ".$UserName."<br><b>Password:</b> ".$TempPassword."<p>&nbsp;<p>Please login at <a href=\"http://".$_SERVER["SERVER_NAME"]."/webcp\">http://".$_SERVER["SERVER_NAME"]."/webcp</a> using this password to change this temporary password to a new one";
      
    	$message = $somecontent;
          
	$PlainTextMail = "Your login to the ".$_SERVER["SERVER_NAME"]." admin panel is:\r\n\r\nUsername: ".$UserName."\r\nPassword: ".$TempPassword."\r\n\r\nPlease login at http://".$_SERVER["SERVER_NAME"]."/webcp using this password to change this temporary password to a new one";
      
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
        $mail->AddReplyTo("noreply@".$_SERVER["SERVER_NAME"]);
        $mail->From = "noreply@".$_SERVER["SERVER_NAME"];
        $mail->FromName = $_SERVER["SERVER_NAME"];
            
        $mail->AddAddress($_POST["email"]);
    	
	if(strlen(trim($BCC)) > 0)
	{
       		$mail->AddBCC($BCC);
	}

       	$mail->Subject = $_SERVER["SERVER_NAME"]." - Password Reset";
       	$mail->Body = $message;
	$mail->AltBody = $PlainTextMail;
       	$mail->WordWrap = 80;
            
     	$mail->Send();
    
}

header("Location: index.php?Notes=Password reset mail sent...");    
