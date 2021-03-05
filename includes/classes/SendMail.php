<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once("/var/www/html/webcp/vendor/autoload.php");

class SendMail
{
	
	function __construct() 
	{

	}



        function SendEmail($EmailAddress, $Subject, $Message)
        {

                require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.phpmailer.php");
		$oSettings = new Settings();

		$SendSystemEmails = $oSettings->GetSendSystemEmails();

		if($SendSystemEmails == "off")
		{
			return -2;
		}

		$BCC = $oSettings->GetForwardSystemEmailsTo();


                $MailFrom = gethostname(); //$_SERVER["SERVER_NAME"];

                if(strstr($MailFrom, "www."))
                {
                        $MailFrom = substr($MailFrom, 4);
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
                $somecontent = $somecontent."   border:none; ";
                $somecontent = $somecontent."   margin:0; ";
                $somecontent = $somecontent."   padding:0;";

               $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."body {";
                $somecontent = $somecontent."   color: #000; ";
                $somecontent = $somecontent."   font:12.35px Verdana, sans-serif;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."a:link. a:visited {";
                $somecontent = $somecontent."   color:#0054a6;";
                $somecontent = $somecontent."   text-decoration:none; ";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."a:hover {";
                $somecontent = $somecontent."   text-decoration:underline";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."h1 {";
                $somecontent = $somecontent."   font-size:20px;";
                $somecontent = $somecontent."   margin-bottom:20px; ";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."h3 {";
                $somecontent = $somecontent."   text-decoration: underline;";
                $somecontent = $somecontent."   margin-bottom:10px;";
                $somecontent = $somecontent."}";

                $somecontent = $somecontent."";
                $somecontent = $somecontent."#wrap {";
                $somecontent = $somecontent."   margin:20px 100px;";
                $somecontent = $somecontent."   width:900px; ";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."p {";
                $somecontent = $somecontent."   margin:15px 0;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#header {";
                $somecontent = $somecontent."   margin-bottom:20px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";


                $somecontent = $somecontent."label {";
                $somecontent = $somecontent."   display:block; ";
                $somecontent = $somecontent."   padding-bottom:5px; ";
                $somecontent = $somecontent."   margin-top:10px;";
                $somecontent = $somecontent."   font-size:13px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform {";
                $somecontent = $somecontent."   width:900px; ";
                $somecontent = $somecontent."   overflow:hidden;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li {";
                $somecontent = $somecontent."   list-style:none; ";
                $somecontent = $somecontent."   padding-bottom:20px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li .fieldbox {";
                $somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top left; ";
                $somecontent = $somecontent."   float:left; ";
                $somecontent = $somecontent."   height:27px; ";
                $somecontent = $somecontent."   padding-left:5px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li .fieldbox select {";
                $somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top right; ";
                $somecontent = $somecontent."   height:27px; ";
                $somecontent = $somecontent."   padding-top:1px;";
                $somecontent = $somecontent."   width:400px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."#contactform li .fieldbox input {";
                $somecontent = $somecontent."   background:transparent url(/img/subfield.jpg) no-repeat top right; ";
                $somecontent = $somecontent."   height:27px; ";
                $somecontent = $somecontent."   padding-top:1px;";
                $somecontent = $somecontent."   width:400px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li .fieldbox #contact {";
                $somecontent = $somecontent."   width:200px;";



                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li .msgbox {";
                $somecontent = $somecontent."   background:transparent url(/img/msgfield.jpg) no-repeat top left; ";
                $somecontent = $somecontent."   float:left; ";
                $somecontent = $somecontent."   height:110px; ";
                $somecontent = $somecontent."   padding-left:5px;";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#contactform li .msgbox textarea {";
                $somecontent = $somecontent."   background:transparent url(/img/msgfield.jpg) no-repeat top right; ";
                $somecontent = $somecontent."   height:110px;";
                $somecontent = $somecontent."   padding-top:5px;";
                $somecontent = $somecontent."   width:500px;     ";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."#button {";
                $somecontent = $somecontent."   background:#acb4cb; color:#fff; ";
                $somecontent = $somecontent."   cursor:pointer;";
                $somecontent = $somecontent."   padding:5px 20px; ";
                $somecontent = $somecontent."   -moz-border-radius:4px;";
                $somecontent = $somecontent."   -webkit-border-radius:4px";
                $somecontent = $somecontent."}";
                $somecontent = $somecontent."</style>";
                $somecontent = $somecontent."</head>";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."<body style=\"margin:0; background: #ededed\">";
                $somecontent = $somecontent."";
                  $somecontent = $somecontent."";
                $somecontent = $somecontent."<div style=\"height:95px; background: #000000; \">";
                $somecontent = $somecontent."<div style=\"float: left; width:100%; margin-top:20px;\"><font style=\"margin-left:50px; color:white; font-family: 'Droid Sans', Verdana; font-size:50px;\">".$MailFrom."</font> </div>";


                $somecontent = $somecontent."";
                $somecontent = $somecontent."</div>";
                $somecontent = $somecontent."<div style=\"font-weight: bold; height:35px; background-color:blue; font-size:18px; padding-top:8px; padding-left:85px; color:white; font-family: 'Droid Sans', Verdana;\">".$Subject."</div>";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."";

                $somecontent = $somecontent."";
                $somecontent = $somecontent."        <div id=\"wrap\">";
                $somecontent = $somecontent."";
                $somecontent = $somecontent.$Message;
                $somecontent = $somecontent."";
                $somecontent = $somecontent."</div>";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."";
                $somecontent = $somecontent."</body>";
                $somecontent = $somecontent."</html>";


                $message = $somecontent;

                $AltMessage = strip_tags($Message);


                $mail = new PHPMailer();

		//$mail->IsSMTP();
                $mail->ClearAddresses();
                $mail->ClearAttachments();
                $mail->IsHTML(true);


                $mail->AddReplyTo("noreply@".$MailFrom, $MailFrom);
                $mail->From = "noreply@".$MailFrom;
                $mail->FromName = $MailFrom;

                $mail->AddAddress($EmailAddress);
		
		if(strlen(trim($BCC)) > 0)
		{
			$mail->AddBCC($BCC);
		}
		

                $mail->Subject = $Subject;
                $mail->Body = $message;
                $mail->AltBody = $AltMessage;
                $mail->WordWrap = 80;

                return $mail->Send();

	}
		


}
 
