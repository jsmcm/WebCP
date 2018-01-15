<?php
session_start();

function __autoload($classname)
{
        require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.".$classname.".php");
}

$oUser = new User();
$oPackage = new Package();
$oLog = new Log();
$oDomain = new Domain();
$oSettings = new Settings();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /passwd/");
        exit();
}



$URL = $_REQUEST["URL"];

if(isset($_REQUEST["Path"]))
{
	$Path = $_REQUEST["Path"];
}

if(substr($Path, strlen($Path) - 1) != "/")
{
	$Path = $Path."/";
}

/*
print "URL: ".$URL."<br>";
print "Path: ".$Path."<br>";
print "User Role: ".$oUser->Role."<br>";
print "Domain Owner: ".$oDomain->GetDomainOwnerFromDomainName($URL)."<br>";
print "client_id: ".$ClientID."<br>";

exit();
*/

if( ($oDomain->GetDomainOwnerFromDomainName($URL) != $ClientID) && ($oUser->Role != "admin") )
{
	header("location: index.php?Notes=You do not have permission to access this sites detail");
	exit();
}


$HTAccessPath = $_REQUEST["Path"];


$PasswordPath = substr($HTAccessPath, 0, strpos($HTAccessPath, "public_html")).".passwd/".substr($HTAccessPath, strpos($HTAccessPath, "public_html"))."/passwd";

$HTAccessPath = $HTAccessPath."/.htaccess";

$PostData = "Path=".$HTAccessPath;

$c = curl_init();
curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($c, CURLOPT_POSTFIELDS,  $PostData);
curl_setopt($c, CURLOPT_POST, 1);
curl_setopt($c, CURLOPT_URL, "http://".$URL.":20001/read.php");

$ResultString = trim(curl_exec($c));
curl_close($c);

$DirectoryProtected = false;
$UserArray = array();
$Title = "";
$PasswordFileFromHTAccess = "";

if($ResultString != "")
{
	//print "<p>HTACCESS<p>";
	//print $ResultString;
	//print "<p>";
	
	// Header
	// PasswordFileFromHTAccess

	if(strstr($ResultString, "AuthType Basic"))
	{
		$DirectoryProtected = true;
	}

	$Title = trim(substr($ResultString, strpos($ResultString, "AuthName") + 8));

	$Title = trim(substr($Title,0, strpos($Title, "\n")));

	if(substr($Title, 0, 1) == "\"")
	{
		$Title = substr($Title, 1);
	}

	if(substr($Title, strlen($Title) - 1) == "\"")
	{
		$Title = substr($Title, 0, strlen($Title) - 1);
	}

	//print "Title: '".$Title."'<p>";
	



	$PasswordFileFromHTAccess = trim(substr($ResultString, strpos($ResultString, "AuthUserFile") + 12));
	
	$PasswordFileFromHTAccess = trim(substr($PasswordFileFromHTAccess, 0, strpos($PasswordFileFromHTAccess, "\n")));

	if(substr($PasswordFileFromHTAccess, 0, 1) == "\"")
	{
		$PasswordFileFromHTAccess = substr($PasswordFileFromHTAccess, 1);
	}

	if(substr($PasswordFileFromHTAccess, strlen($PasswordFileFromHTAccess) - 1) == "\"")
	{
		$PasswordFileFromHTAccess = substr($PasswordFileFromHTAccess, 0, strlen($PasswordFileFromHTAccess) - 1);
	}

	//print "PasswordFileFromHTAccess: '".$PasswordFileFromHTAccess."'<p>";
}

$ActualPasswordPath = "";
if( ($PasswordFileFromHTAccess != "") && ($PasswordFileFromHTAccess != $PasswordPath) )
{
	if(file_exists($PasswordFileFromHTAccess))
	{
		$ActualPasswordPath = $PasswordFileFromHTAccess;
		$UserArray = explode("\n", file_get_contents($PasswordFileFromHTAccess));
	}
}
else
{
	if(file_exists($PasswordPath))
	{
		$ActualPasswordPath = $PasswordPath;
		$UserArray = explode("\n", file_get_contents($PasswordPath));
	}
}

//for($x = 0; $x < count($UserArray); $x++)
//{
//	print ($x + 1).$UserArray[$x]."<br>";
//}





?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Manage Permissions | <?php print $oSettings->GetWebCPTitle(); ?></title>
		<!-- start: META -->
		<meta charset="utf-8" />
		<!--[if IE]><meta http-equiv='X-UA-Compatible' content="IE=edge,IE=9,IE=8,chrome=1" /><![endif]-->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black">
		<meta content="" name="description" />
		<meta content="" name="author" />
		<!-- end: META -->
		<!-- start: MAIN CSS -->
		<link href="/assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
		<link rel="stylesheet" href="/assets/fonts/style.css">
		<link rel="stylesheet" href="/assets/css/main.css">
		<link rel="stylesheet" href="/assets/css/main-responsive.css">
		<link rel="stylesheet" href="/assets/plugins/iCheck/skins/all.css">
		<link rel="stylesheet" href="/assets/plugins/bootstrap-colorpalette/css/bootstrap-colorpalette.css">
		<link rel="stylesheet" href="/assets/plugins/perfect-scrollbar/src/perfect-scrollbar.css">
		<link rel="stylesheet" href="/assets/css/theme_light.css" id="skin_color">
		<!--[if IE 7]>
		<link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome-ie7.min.css">
		<![endif]-->
		<!-- end: MAIN CSS -->
		<!-- start: CSS REQUIRED FOR THIS PAGE ONLY -->
		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
	


<script language="javascript">

function ValidateAdd()
{
	Password = document.Users.Password.value;
	ConfirmPassword = document.Users.ConfirmPassword.value;

	if(Password != ConfirmPassword)
	{
		alert("Those passwords don't match!");
		document.Users.Password.focus();

		document.Users.Password.value = "";
		document.Users.ConfirmPassword.value = "";

		return false;
	}

	if(Password == "")
	{
		alert("Password cannot be blank!");
		document.Users.Password.focus();
		return false;
	}
	
	return true;

}

function ValidateDelete(User)
{
	if(User.length == 0)
	{
		return false;
	}

	if(confirm("Really delete: " + User))
	{
		return true;
	}

	return false;
}
</script>

		

	</head>
	<!-- end: HEAD -->
	<!-- start: BODY -->
	<body>
		<!-- start: HEADER -->
		<div class="navbar navbar-inverse navbar-fixed-top">
			<!-- start: TOP NAVIGATION CONTAINER -->
			<div class="container">
				<div class="navbar-header">
					<!-- start: RESPONSIVE MENU TOGGLER -->
					<button data-target=".navbar-collapse" data-toggle="collapse" class="navbar-toggle" type="button">
						<span class="clip-list-2"></span>
					</button>
					<!-- end: RESPONSIVE MENU TOGGLER -->
					<!-- start: LOGO -->
					<?php
					require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/Logo.inc.php");
					?>					

					<!-- end: LOGO -->
				</div>
				<div class="navbar-tools">
					<!-- start: TOP NAVIGATION MENU -->
					<ul class="nav navbar-right">
								<li><a href="/domains/"><h1>Home >> </h1></a></li>
								<li><a href="passwd.php?URL=<?php print $_REQUEST["URL"]; ?>&Path=<?php print $_REQUEST["Path"]; ?>"><h1>Passwd Main List >></h1></a></li>
								<li><a href="manage.php?URL=<?php print $_REQUEST["URL"]; ?>&Path=<?php print $_REQUEST["Path"]; ?>"><h1>Edit</h1></a></li>
					</ul>
					<!-- end: TOP NAVIGATION MENU -->
				</div>
			</div>
			<!-- end: TOP NAVIGATION CONTAINER -->
		</div>
		<!-- end: HEADER -->
		<!-- start: MAIN CONTAINER -->
		<div class="main-container">
			</div>
			<!-- start: PAGE -->
			<div class="ddmain-content">

				<div class="container" style="min-height:640px; background-color:white;">
					<!-- start: PAGE CONTENT -->
					<div class="row" style="padding-top:15px;">
					



				<div class="col-md-12" style="padding-top:15px;">
				<div class="panel panel-default">
				<div class="panel-body">










<h1>Protection for: <?php print $_REQUEST["Path"]; ?></h1>

<form name="passwd" action="protect.php" method="post">
<input type="hidden" name="HTAccessPath" value="<?php print $HTAccessPath; ?>">
<input type="hidden" name="URL" value="<?php print $URL; ?>">
<input type="hidden" name="PasswordPath" value="<?php print $PasswordPath; ?>">
<input type="hidden" name="ActualPasswordPath" value="<?php print $ActualPasswordPath; ?>">
<input type="hidden" name="Path" value="<?php print $_REQUEST["Path"]; ?>">
<h3>Settings</h3>
<table border="0" cellpadding="0" cellspacing="5" width="85%">
<tr>
<td width="25%">
	Enable password protection: 
</td>
<td align="left">
	<input name="Status" type="checkbox" class="form-control" style="width: 400px; margin-left:0;" <?php $DirectoryProtected? print " checked ": print ""; ?>>
</td>
</tr>
<tr>
<td>
	Protected directory title: 
</td>
<td>
	<input type="text" class="form-control" style="width:400px;" name="Title" value="<?php print $Title; ?>">
</td>
</tr>

<tr>
<td>
	&nbsp;
</td>
<td>
<button type="submit" class="btn btn-blue btn-sm">
Save settings
</button>
</td>
</tr>

</table>
</form>

<p><hr style="border: 1px solid black;"><p>

<?php

if( ($PasswordPath != $ActualPasswordPath) && ($ActualPasswordPath != "") )
{
	print "The password file is not in a place I can use... Please hit the \"Save settings\" button above to fix this, then you will be able to manage users.<p>";
}
else if( ! file_exists($PasswordPath) )
{
	print "The password file does not exist. Enable protection above before you can manage users.<p>";
}
else
{
?>

<form name="Users" method="post" action="edituser.php">
<input type="hidden" name="URL" value="<?php print $URL; ?>">
<input type="hidden" name="PasswordPath" value="<?php print $PasswordPath; ?>">
<input type="hidden" name="Path" value="<?php print $_REQUEST["Path"]; ?>">
<h3>Users</h3>
<table border="0" cellpadding="0" cellspacing="5" width="85%">
<tr>
	<td width="25%">
		User name:
	</td>
	<td width="*">
		<input type="text" class="form-control" style="width:400px;" name="UserName">
	</td>
</tr>

<tr>
	<td>
		Password:
	</td>
	<td>
		<input type="password" class="form-control" style="width:400px;" name="Password">
	</td>
</tr>

<tr>
	<td>	
		Confirm Password:
	</td>
	<td>
		<input type="password" class="form-control" style="width:400px;" name="ConfirmPassword">
	</td>
</tr>

<tr>
	<td>
		&nbsp;
	</td>
	<td>
<button type="submit" class="btn btn-blue btn-sm" onclick="return ValidateAdd(); return false;">
Add / Edit user
</button>
	</td>
</tr>
</table>
</form>


<p><hr style="border: 1px solid black;"><p>


<h3>Existing Users</h3>
<form name="ExistingUsers" action="deleteuser.php" method="post">
<input type="hidden" name="URL" value="<?php print $URL; ?>">
<input type="hidden" name="PasswordPath" value="<?php print $PasswordPath; ?>">
<input type="hidden" name="Path" value="<?php print $_REQUEST["Path"]; ?>">

<table border="0" cellpadding="0" cellspacing="5" width="85%">
<tr>
<td style="width:260px;">
	<select name="UserName" size="10"  class="form-control" style="width:400px;">

	<?php
	for($x = 0; $x < count($UserArray); $x++)
	{

                while( substr($UserArray[$x], strlen($UserArray[$x]) - 1, 1) == '\n')
                {
                        $UserArray[$x] = substr($UserArray, 0, strlen($UserArray) - 1);
                }
	
		if(strlen($UserArray[$x]) > 0)
		{
			$UserArray[$x] = substr($UserArray[$x], 0, strpos($UserArray[$x], ":"));
			print "<option value=\"".$UserArray[$x]."\">".$UserArray[$x]."</option>";
		}
	}
	?>

	</select>
</td>
<td width="*">

<button type="submit" class="btn btn-blue btn-sm" onclick="return ValidateDelete(document.ExistingUsers.UserName.value); return false;">
Delete user
</button>

</td>
</tr>
</table>
</form>

<?php
}
?>





				</div>
				</div>
				</div>

					</div>
					<!-- end: PAGE CONTENT-->
				</div>
			</div>
			<!-- end: PAGE -->
		</div>
		<!-- end: MAIN CONTAINER -->
		<!-- start: FOOTER -->
		<div class="footer clearfix">
			<?php
			require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/footer.inc.php");
			?>
			<div class="footer-items">
				<span class="go-top"><i class="clip-chevron-up"></i></span>
			</div>
		</div>
		<!-- end: FOOTER -->
		<!-- start: MAIN JAVASCRIPTS -->
		<!--[if lt IE 9]>
                <script src="/assets/plugins/respond.min.js"></script>
                <script src="/assets/plugins/excanvas.min.js"></script>
                <![endif]-->


		<!-- end: MAIN JAVASCRIPTS -->
	</body>
	<!-- end: BODY -->
</html>
