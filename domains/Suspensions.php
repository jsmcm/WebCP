<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

$oUser = new User();
$oPackage = new Package();
$oLog = new Log();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$oLog->WriteLog("DEBUG", "/domains/Suspensions.php...");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	$oLog->WriteLog("DEBUG", "/domains/Suspensions.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}
	
if($oUser->Role != "admin")
{
	// Not an admin, get outta here
	header("Location: /index.php");
	exit();
}

$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id set, continuing");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	

<title>Dashboard | Web Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<script language="javascript">
function ConfirmChange(ChangeToState)
{

	ChangeToMessage = "unsuspend";

	if(ChangeToState == 1)
	{
		ChangeToMessage = "suspend";
	}

	if(confirm("Are you sure you want to " + ChangeToMessage + " this domain?\r\n"))
	{
		return true;
	}
	return false;
}
</script>

<style type="text/css">
<!--
@import url("/includes/styles/tablestyle.css");
-->
</style>

<?php
include($_SERVER["DOCUMENT_ROOT"]."/includes/styles/Styles.inc");
?>
	
<script src="/includes/javascript/jquery.js"></script>
<script src="/includes/javascript/main.js"></script>
<script src="/includes/javascript/sorttable.js"></script>

</head>

<body style="margin:0; background: #ededed">

<?php
include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopBar.inc");
include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopNav.inc");

?>



<div id="wrap">
<p>

When ordering domains from the registrar of your choice, use <b>ns7.pwhlinserve.com</b> and <b>ns8.pwhlinserve.com</b> as your nameservers.
<p>

<div align="center" style="width:100%; background:white; padding-top:20px; padding-bottom:20px; border-style:dotted; border-width:1px">

<?php
if(isset($_REQUEST["Notes"]))
{
	print "<font color=\"red\">".$_REQUEST["Notes"]."</font><p>";
}
?>

<table id="rounded-corner" class="sortable">
    <thead>
    	<tr>
        	<th scope="col" class="rounded-company">Domain</th>
            <th scope="col" class="rounded-q3">Username</th>
            <th scope="col" class="rounded-q3">Package</th>
            <th scope="col" class="rounded-q4">Change</th>
        </tr>
    </thead>
        <tfoot>
    	<tr>
        	<td colspan="3" class="rounded-foot-left"><em><!-- << previous --></em></td>
        	<td class="rounded-foot-right"><em><!-- next >> --></em></td>
        </tr>
    </tfoot>
	<tbody id="RandomShortURLList">

	<?php
	$oDomain = new Domain();

	$ClientID = $oUser->ClientID;

	if(isset($_REQUEST["ClientID"]))
	{
		if($oUser->Role == "admin")
		{
			//yes, permission..
			$ClientID = $_REQUEST["ClientID"];
		}
	}
//print "ClientID: ".$ClientID."<p>";
//print "Role: ".$oUser->Role."<p>";

	$oDomain->GetDomainList($Array, $ArrayCount, $ClientID, $oUser->Role);

	for($x = 0; $x < $ArrayCount; $x++)
	{

		if($Array[$x]["type"] == 'primary')
		{
		
			if($Array[$x]["Suspended"] == 1)
			{
			 	print "<tr class=\"Red\">";
			}
			else
			{
				print "<tr>";
			}

			print "<td><a href=\"http://".$Array[$x]["domain_name"]."\" target=\"_BLANK\">".$Array[$x]["domain_name"]."</a></td>\r\n";
			print "<td>".$Array[$x]["admin_username"]."</td>\r\n";
	
			if($oUser->Role == 'admin')
			{
				print "<td><a href=\"./EditPackage.php?DomainID=".$Array[$x]["id"]."\">[ ".$oPackage->GetPackageName($Array[$x]["PackageID"])." ]</a></td>\r\n";
			}
			else
			{
				print "<td>".$oPackage->GetPackageName($Array[$x]["PackageID"])."</td>\r\n";
			}	
	
			
			if($oUser->Role == 'admin')
			{
				if($Array[$x]["Suspended"] == 1)
				{
					print "<td><a href=\"ManageSuspension.php?ChangeTo=0&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(0); return false;\"> [ Unsuspend ] </a></td>";
				}
				else
				{
					print "<td><a href=\"ManageSuspension.php?ChangeTo=1&DomainID=".$Array[$x]["id"]."\" onclick=\"return ConfirmChange(1); return false;\"> [ Suspend ] </a></td>";
				}
			}
			else
			{
				print "<td>&nbsp;</td>";
			}
	
			print "</tr>";

		}
	}
	?>


</tbody>




</table>
<p>
<?php
if($oUser->Role == "admin")
{
?>
<input type="button" value="Add new domain" onclick="location.href='AddDomain.php'" id="button" name="button"/>
<?php
}
?>
</div>
<p>

</div>
	
</body>
</html>
