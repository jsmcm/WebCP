<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /index.php");
        exit();
}

$ID = 0;
if(isset($_GET["EmailTraceID"]))
{
	$ID = $_GET["EmailTraceID"];
}

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();


function GetLine($Label, $Value)
{
        $Data = "<tr>";
        $Data = $Data."<td width=\"25%\">";
        $Data = $Data.$Label." ";
        $Data = $Data."</td>";
        $Data = $Data."<td width=\"2%\">";
        $Data = $Data."&nbsp;";
        $Data = $Data."</td>";
        $Data = $Data."<td width=\"*\">";
        $Data = $Data.$Value;
        $Data = $Data."</td>";
        $Data = $Data."</tr>";

	return $Data;
}

$Output = "";


$Array = array();
if($oEmail->GetEmailTraceDetail($Array, $ID,  $oUser->GetUserName($ClientID), $oUser->Role))
{
        $Output = $Output."<h4 class=\"modal-title\">".$Array["StartDate"]." - ".$Array["Subject"]."</h4>";

       	$Output = $Output."<table border=\"0\" width=\"95%\">";
        $Output = $Output.GetLine("MailQueueID", $Array["MailQueueID"]);
        $Output = $Output.GetLine("", "");

        $Output = $Output.GetLine("From: ", $Array["FromAddress"]);
        $Output = $Output.GetLine("To: ", $Array["ToAddress"]);
        $Output = $Output.GetLine("Subject: ", $Array["Subject"]);
        $Output = $Output.GetLine("", "");

        $Output = $Output.GetLine("Start: ", $Array["StartDate"]);
        $Output = $Output.GetLine("End: ", $Array["UpdateDate"]);
        $Output = $Output.GetLine("Queue Time: ", $Array["QueueTime"]);
        $Output = $Output.GetLine("", "");

        $Output = $Output.GetLine("Sender Host: ", $Array["SenderHost"]);
        $Output = $Output.GetLine("Receiver Host: ", $Array["ReceiverHost"]);
        $Output = $Output.GetLine("Protocol: ", $Array["Protocol"]);
        $Output = $Output.GetLine("Transport: ", $Array["Transport"]);
        $Output = $Output.GetLine("Router: ", $Array["Router"]);
        $Output = $Output.GetLine("Auth Type: ", $Array["AuthType"]);
        $Output = $Output.GetLine("", "");

	if($Array["Confirmation"] == "SPAM")
	{
		$Array["Status"] = "Error";
	}
	else if($Array["Confirmation"] == "relay not permitted")
	{
		$Array["Status"] = "Auth failed";
	}
	
        $Output = $Output.GetLine("Confirmation: ", $Array["Confirmation"]);
        $Output = $Output.GetLine("Status: ", $Array["Status"]);
        $Output = $Output.GetLine("", "");

        $Output = $Output."</table>";

}
else
{

	$Output = "<h4 class=\"modal-title\">No Data</h4>";
        $Output = $Output."No for this eemail, please contact support for more info";
}

print $Output;

?>

<p><hr><p>
<h3>Email this form</h3>
<form name="EmailForm" action="EmailEmailTrace.php" method="POST">
<b>Email Address</b>
<br><i>comma delimited</i><br>
<input type="text" name="EmailAddress" size="75">
<input type="hidden" name="Body" value="<?php print htmlentities($Output); ?>">
</br>
<input type="submit" value="Email it!">
</form>

