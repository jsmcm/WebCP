<?php
session_start();

$somecontent = "";

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();
$TempPassword = $oUser->ResetPassword($_POST["email"]);
$UserName = $oUser->GetUserName($oUser->UserExistsByEmail($_POST["email"]));

if($UserName != "")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
	$oSettings = new Settings();
	$BCC = $oSettings->GetForwardSystemEmailsTo();

    	//set_include_path("../");
   	 require($_SERVER["DOCUMENT_ROOT"]."/includes/class.phpmailer.php");

   	// Send Client Email
    	$somecontent = $somecontent."Your login to the ".$_SERVER["SERVER_NAME"]." admin panel is:<p><b>Username:</b> ".$UserName."<br><b>Password:</b> ".$TempPassword."<p>&nbsp;<p>Please login at <a href=\"http://".$_SERVER["SERVER_NAME"]."/webcp\">http://".$_SERVER["SERVER_NAME"]."/webcp</a> using this password to change this temporary password to a new one";
      
    	$message = $somecontent;
          
	$PlainTextMail = "Your login to the ".$_SERVER["SERVER_NAME"]." admin panel is:\r\n\r\nUsername: ".$UserName."\r\nPassword: ".$TempPassword."\r\n\r\nPlease login at http://".$_SERVER["SERVER_NAME"]."/webcp using this password to change this temporary password to a new one";
      
     	$mail = new PHPMailer();
            
	$mail->IsSMTP();
        $mail->ClearAddresses(); 
        $mail->ClearAttachments();
        $mail->IsHTML(true);
        $mail->AddReplyTo("noreply@".substr($_SERVER["SERVER_NAME"], 4), $_SERVER["SERVER_NAME"]);
        $mail->From = "noreply@".substr($_SERVER["SERVER_NAME"], 4);
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
?>
