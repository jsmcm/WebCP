<?php

$DomainUserName = $_REQUEST["DomainUserName"];
$Percentage = $_REQUEST["Percentage"];
$HostName = $_REQUEST["HostName"];

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oDomain = new Domain();
$oSettings = new Settings();
$oUser = new User();

$SendSystemEmails = $oSettings->GetSendSystemEmails();

if($SendSystemEmails == "off") {
  exit();
}

$AdminEmail = $oSettings->GetForwardSystemEmailsTo();
 
$DomainName = $oDomain->GetDomainName($DomainUserName);
$DomainID = $oDomain->GetDomainIDFromDomainName($DomainName);

$oSimpleNonce = new SimpleNonce();

$random = random_int(1, 100000);
$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
  $DomainID,
  $random
];

$nonce = $oSimpleNonce->GenerateNonce("getDomainOwner", $nonceArray);
$UserID = $oDomain->GetDomainOwner($DomainID, $random, $nonce);

$Username = "";
$EmailAddress = "";
$FirstName = "";
$Surname = "";
$UserRole = "";
$oUser->GetUserDetails($UserID, $FirstName, $Surname, $EmailAddress, $Username, $UserRole);



/*
print "DomainUserName: ".$DomainUserName."<br>";
print "Percentage: ".$Percentage."<br>";
print "DomainName: ".$DomainName."<br>";
print "EmailAddress: ".$EmailAddress."<br>";
print "FirstName: ".$FirstName."<br>";
print "Surname: ".$Surname."<br>";
print "HostName: ".$HostName."<br>";
*/



function SetSentStatus($ID)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();

	try
	{
		$query = $DatabaseConnection->prepare("UPDATE mail_addresses SET sent = 1 WHERE id = :id");
		$query->bindParam(":id", $ID);
		$query->execute();
	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "quota/SendTrafficMail.php -> SetSentStatus(); Error = ".$e);
	}


}



$Mail = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

$Mail = $Mail."<html xmlns=\"http://www.w3.org/1999/xhtml\">";

$Mail = $Mail."<head>";



$Mail = $Mail."<style type=\"text/css\">";



$Mail = $Mail."#ver-zebra";

$Mail = $Mail."{";

	$Mail = $Mail."font-family: \"Lucida Sans Unicode\", \"Lucida Grande\", Sans-Serif;";

	$Mail = $Mail."font-size: 12px;";

	$Mail = $Mail."margin: 5px;";

	$Mail = $Mail."width: 550px;";

	$Mail = $Mail."text-align: left;";

	$Mail = $Mail."border-collapse: collapse;";

$Mail = $Mail."}";

$Mail = $Mail."#ver-zebra th";

$Mail = $Mail."{";

	$Mail = $Mail."font-size: 14px;";

	$Mail = $Mail."font-weight: normal;";

	$Mail = $Mail."padding: 12px 15px;";

	$Mail = $Mail."border-right: 1px solid #fff;";

	$Mail = $Mail."border-left: 1px solid #fff;";

	$Mail = $Mail."color: #039;";

$Mail = $Mail."}";

$Mail = $Mail."#ver-zebra td";

$Mail = $Mail."{";

	$Mail = $Mail."padding: 8px 15px;";

	$Mail = $Mail."border-right: 1px solid #fff;";

	$Mail = $Mail."border-left: 1px solid #fff;";

	$Mail = $Mail."color: #669;";

$Mail = $Mail."}";

$Mail = $Mail.".vzebra-odd";

$Mail = $Mail."{";

	$Mail = $Mail."background: #eff2ff;";

$Mail = $Mail."}";

$Mail = $Mail.".vzebra-even";

$Mail = $Mail."{";

	$Mail = $Mail."background: #e8edff;";

$Mail = $Mail."}";

$Mail = $Mail."#ver-zebra #vzebra-adventure, #ver-zebra #vzebra-children";

$Mail = $Mail."{";

	$Mail = $Mail."background: #d0dafd;";

	$Mail = $Mail."border-bottom: 1px solid #c8d4fd;";

$Mail = $Mail."}";

$Mail = $Mail."#ver-zebra #vzebra-comedy, #ver-zebra #vzebra-action";

$Mail = $Mail."{";

	$Mail = $Mail."background: #dce4ff;";

	$Mail = $Mail."border-bottom: 1px solid #d6dfff;";

$Mail = $Mail."}";



$Mail = $Mail."</style>";



$Mail = $Mail."<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";

$Mail = $Mail."<meta http-equiv=\"Content-Language\" content=\"en-us\" />";

$Mail = $Mail."</head>";

$Mail = $Mail."<body>";

$Mail = $Mail."<table bgcolor=\"#b9b093\" width=\"100%\" name=\"tid\" description=\"mediumBgcolor\">";

$Mail = $Mail."<tr>";

$Mail = $Mail."<td>";

$Mail = $Mail."<div style=\"padding: 30px; margin: 0px;\">";

  $Mail = $Mail."<table width=\"600\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family: 'Verdana';\">";

    $Mail = $Mail."<tr>";

      $Mail = $Mail."<td>";

	  

	  $Mail = $Mail."<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"border-bottom: solid 1px #fff;\">";

          $Mail = $Mail."<tr>";

            $Mail = $Mail."<td width=\"10\" bgcolor=\"#FFFFFF\"></td>";

            $Mail = $Mail."<td width=\"580\" bgcolor=\"#FFFFFF\"></td>";

            $Mail = $Mail."<td width=\"10\" bgcolor=\"#FFFFFF\"></td>";

          $Mail = $Mail."</tr>";

          

          $Mail = $Mail."<tr bgcolor=\"#FFFFFF\">";

            $Mail = $Mail."<td width=\"10\" align=\"left\" valign=\"top\" bgcolor=\"#FFFFFF\">&nbsp;</td>";

            $Mail = $Mail."<td width=\"580\" align=\"left\" valign=\"top\" style=\"padding: 10px 0px 20px 20px;\">";

			  $Mail = $Mail."<h1 style=\"font-family: Georgia, 'Times New Roman', Times, serif; font-weight: normal; letter-spacing:-1px; color: #5a5a52; font-size:32px; line-height: 32px; padding: 2px 0px; margin: 0px;\" name=\"tid\" description=\"darkestColor\">".$HostName."</h1>";

			$Mail = $Mail."<span style=\"font-size: 10px; font-family: 'Verdana'; color:#4b4925; padding: 2px 0px; margin: 0px;\" name=\"tid\" description=\"darkestColor\"><strong>Web hosting control panel</strong></span></td>";

            $Mail = $Mail."<td width=\"10\">&nbsp;</td>";

          $Mail = $Mail."</tr>";

      $Mail = $Mail."</table>";

	  


        $Mail = $Mail."</td>";

    $Mail = $Mail."</tr>";

    $Mail = $Mail."<tr>";

      $Mail = $Mail."<td bgcolor=\"#FFFFFF\" style=\"padding: 10px 25px;\">";

	  

	    $Mail = $Mail."<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" bgcolor=\"#ffffff\">";



        

          $Mail = $Mail."<tr>";

            $Mail = $Mail."<td valign=\"top\" style=\"padding: 0px 25px 0px 0px;\">";

$Mail = $Mail."<h1 style=\"font-family: 'Georgia'; border-bottom: dashed 1px #cccccc; padding: 5px 0px 5px 0px; margin: 0px; color:#986f3f; font-size: 22px; font-weight: normal;\" name=\"tid\" description=\"darkColor\">".$DomainName." - Traffic Warning</h1>";


		$Mail = $Mail."<p>Good day ".$FirstName.",<br>&nbsp<br>Your traffic usage on domain <b>".$DomainName."</b> has reached a warning level of <b>".$Percentage."</b>%<p>Please note that your account may be stop working until the beginning of the next month, or until you upgrade your account";




          $Mail = $Mail."</tr>";

      $Mail = $Mail."</table></td>";

    $Mail = $Mail."</tr>";

    $Mail = $Mail."<tr>";

      $Mail = $Mail."<td>";

	  

	  $Mail = $Mail."<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"padding: 0px; font-size: 10px; line-height: 1.5em; font-family: 'Verdana'; color:#999;\">";

          $Mail = $Mail."<tr bgcolor=\"#FFFFFF\" >";

            $Mail = $Mail."<td></td>";

            $Mail = $Mail."<td style=\"padding:5px 15px 15px 15px;\">";

$Mail = $Mail."</td>";

            $Mail = $Mail."<td></td>";

          $Mail = $Mail."</tr>";

          $Mail = $Mail."<tr>";

            $Mail = $Mail."<td width=\"10\" height=\"25\" bgcolor=\"#FFFFFF\"></td>";

            $Mail = $Mail."<td bgcolor=\"#FFFFFF\" ></td>";

            $Mail = $Mail."<td width=\"10\" height=\"25\" bgcolor=\"#FFFFFF\"></td>";

          $Mail = $Mail."</tr>";

      $Mail = $Mail."</table>";

	  

	  

	  $Mail = $Mail."</td>";

    $Mail = $Mail."</tr>";

  $Mail = $Mail."</table>";

$Mail = $Mail."</div>";



$Mail = $Mail."</body>";

$Mail = $Mail."</html>";

$PlainTextMail = "Good day ".$FirstName.",\r\n\r\nYour traffic usage on domain ".$DomainName." has reached a warning level of ".$Percentage."%.\r\n\r\nPlease note that your account may be stop working until the beginning of the next month, or until you upgrade your account";


    //set_include_path("../");
 	require_once($_SERVER["DOCUMENT_ROOT"]."/includes/class.phpmailer.php");

            
            $mail = new PHPMailer();
            
            $mail->ClearAddresses(); 
            $mail->ClearAttachments();
            
	    $mail->IsSMTP();
            $mail->AddReplyTo("noreply@".$HostName, $HostName);
            $mail->From = "noreply@".$HostName;
            $mail->FromName = $HostName;
            $mail->IsHTML(true);
            $mail->AddAddress($EmailAddress);
            
            if($AdminEmail != "")
	    {
	    	$mail->AddBCC($AdminEmail);
	    }
            
            $mail->Subject = "Website traffic warning";
            $mail->Body = $Mail;
	    $mail->AltBody = $PlainTextMail;
            $mail->WordWrap = 100;
            
            $mail->Send();

		

?>
