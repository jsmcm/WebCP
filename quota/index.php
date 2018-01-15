<?php
session_start();

if(! file_exists($_SERVER["DOCUMENT_ROOT"]."/quota/quota_files"))
{
	mkdir($_SERVER["DOCUMENT_ROOT"]."/quota/quota_files");
}

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Database.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Log.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
        header("Location: /domains/");
        exit();
}



function UserNameBelongsToLoggedInUser($UserName, $Role, $ClientID)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();


	try
	{

		if($Role == "reseller")
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE UserName = :user_name AND deleted = 0 AND client_id IN (SELECT client_id FROM reseller_relationships WHERE deleted = 0 AND reseller_id = :client_id UNION SELECT ".$ClientID." AS client_id");
		}
		else
		{
			$query = $this->DatabaseConnection->prepare("SELECT id FROM domains WHERE UserName = :user_name AND deleted = 0 AND client_id = :client_id");
		}

	
		
		$query->bindParam(":user_name", $UserName);
		$query->bindParam(":client_id", $ClientID);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			return $result["id"];

		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/quota/index.php -> UserNameBelongsToLoggedInUser(); Error = ".$e);
	}

	return -1;
	
}

function GetUserDetails($UserName, &$Domain, &$FirstName, &$Surname)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();


	try
	{

		$query = $DatabaseConnection->prepare("SELECT fqdn, first_name, surname from domains, admin WHERE domains.UserName = :user_name AND domains.deleted = 0 AND admin.deleted = 0 AND domains.client_id = admin.id AND domains.domain_type = 'primary'");
	
		
		$query->bindParam(":user_name", $UserName);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			$Domain = $result["fqdn"];
			$FirstName = $result["first_name"];
			$Surname = $result["surname"];
		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/quota/index.php -> GetUserDetails(); Error = ".$e);
	}

}




function GetUserDiskAllowance($UserName)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();

	try
	{

		$query = $DatabaseConnection->prepare("SELECT value FROM domains, package_options WHERE domains.deleted = 0 AND package_options.deleted = 0 AND package_options.package_id = domains.package_id AND setting = 'DiskSpace' AND domains.UserName = :user_name AND domains.domain_type = 'primary'");
	
		
		$query->bindParam(":user_name", $UserName);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			return floatval($result["value"]);
		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/quota/index.php -> GetUserDiskAllowance(); Error = ".$e);
	}

        return 0;
}



function GetTrafficAllowance($UserName)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();



	
	try
	{

		$query = $DatabaseConnection->prepare("SELECT value FROM domains, package_options WHERE domains.deleted = 0 AND package_options.deleted = 0 AND package_options.package_id = domains.package_id AND setting = 'Traffic' AND domains.UserName = :user_name AND domains.domain_type = 'primary'");
	
		
		$query->bindParam(":user_name", $UserName);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			return floatval($result["value"]);
		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/quota/index.php -> GetTrafficAllowance(); Error = ".$e);
	}


        return 0;
}



function GetTrafficUsage($UserName)
{

	$oDatabase = new Database();
	$DatabaseConnection = $oDatabase->GetConnection();


	 
	 
	
	
	try
	{

		$query = $DatabaseConnection->prepare("SELECT SUM(bandwidth) AS bandwidth FROM bandwidth WHERE domain_user_name = :user_name");
	
		
		$query->bindParam(":user_name", $UserName);
		$query->execute();

		if($result = $query->fetch(PDO::FETCH_ASSOC))
		{
			return floatval($result["bandwidth"]);
		}

	}
	catch(PDOException $e)
	{
		$oLog = new Log();
		$oLog->WriteLog("error", "/quota/index.php -> GetTrafficUsage(); Error = ".$e);
	}



        return 0;
}



?>

<!DOCTYPE html>
<!--[if IE 8]><html class="ie8 no-js" lang="en"><![endif]-->
<!--[if IE 9]><html class="ie9 no-js" lang="en"><![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
	<!--<![endif]-->
	<!-- start: HEAD -->
	<head>
		<title>Quota Usage | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		<link rel="stylesheet" type="text/css" href="/assets/plugins/select2/select2.css" />
		<link rel="stylesheet" href="/assets/plugins/DataTables/media/css/DT_bootstrap.css" />
		<!-- end: CSS REQUIRED FOR THIS PAGE ONLY -->
		<link rel="shortcut icon" href="/favicon.ico" />
		
		

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
						<!-- start: USER DROPDOWN -->
						<?php
						require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/UserSection.inc.php");
						?>
					</ul>
					<!-- end: TOP NAVIGATION MENU -->
				</div>
			</div>
			<!-- end: TOP NAVIGATION CONTAINER -->
		</div>
		<!-- end: HEADER -->
		<!-- start: MAIN CONTAINER -->
		<div class="main-container">
			<div class="navbar-content">
				<!-- start: SIDEBAR -->
				<div class="main-navigation navbar-collapse collapse">
					<!-- start: MAIN MENU TOGGLER BUTTON -->
					<div class="navigation-toggler">
						<i class="clip-chevron-left"></i>
						<i class="clip-chevron-right"></i>
					</div>
					<!-- end: MAIN MENU TOGGLER BUTTON -->

					<?php
					require($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/SideNav.inc.php");
					?>
					
				</div>
				<!-- end: SIDEBAR -->
			</div>
			<!-- start: PAGE -->
			<div class="main-content">

				<div class="container">
					<!-- start: PAGE HEADER -->
					<div class="row">
						<div class="col-sm-12">
							<!-- start: PAGE TITLE & BREADCRUMB -->
							<ol class="breadcrumb">
								<li>
									<i class="active"></i>
									<a href="/quota/">
										Quota Usage
									</a>
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Quota Usage <small> View current usage</small></h1>
							</div>
							<!-- end: PAGE TITLE & BREADCRUMB -->
						</div>
					</div>
					<!-- end: PAGE HEADER -->
					<!-- start: PAGE CONTENT -->
					<div class="row">
					
			
					<?php
					if(isset($_REQUEST["Notes"]))
					{
						$NoteType = "Message";
						
						if(isset($_REQUEST["NoteType"]))
						{
							$NoteType = $_REQUEST["NoteType"];
						}
						
						if($NoteType == "Error")
						{
							print "<div class=\"alert alert-danger\">";
								print "<button data-dismiss=\"alert\" class=\"close\">";
									print "&times;";
								print "</button>";
								print "<i class=\"fa fa-times-circle\"></i>";
						
						}
						else
						{
							print "<div class=\"alert alert-success\">";
								print "<button data-dismiss=\"alert\" class=\"close\">";
									print "&times;";
								print "</button>";
								print "<i class=\"fa fa-check-circle\"></i>";
						}
					
							print $_REQUEST["Notes"];
						print "</div>";
					
					}
					?>
					



					<?php

					$MainDivBackgroundColour = "";

					    if ($handle = opendir(getcwd().'/quota_files/')) {

						/* This is the correct way to loop over the directory. */
						while (false !== ($file = readdir($handle))) {

						    if($file != "." && $file != "..")
						    {
							if(strstr($file, "uquota"))
							{

								for($x = 0; $x < strlen($file); $x++)
								{
									if(substr($file, $x, 1) == ".")
									{
										break;
									}
								}

								$UserName = substr($file, 0, $x);
		
								if( ($oUser->Role == 'admin') || (UserNameBelongsToLoggedInUser($UserName, $oUser->Role, $ClientID) > 0) )
								{
									$InputLine = file_get_contents("./quota_files/".$file);

									for($x = 0; $x < strlen($InputLine); $x++)
									{
										if(substr($InputLine, $x, 1) == " ")
										{
											break;
										}
									}



									$InputLine = substr($InputLine, 0, $x);

									for($x = 0; $x < strlen($InputLine); $x++)
									{
										if( substr($InputLine, $x, 1) > '9' )
										{
											$Usage = floatval(substr($InputLine, 0, $x));
											$Scale = substr($InputLine, $x);
											break;
										}
									}

									if( ($MainDivBackgroundColour == "") || ($MainDivBackgroundColour == "fff2ca") )
									{
										$MainDivBackgroundColour = "cad3ff";
										$MainDivBorderColour = "090461";
									}
									else
									{
										$MainDivBackgroundColour = "fff2ca";
										$MainDivBorderColour = "d4693d";
									}
	

									$Domain = "";
									$FirstName = ""; 
									$Surname = "";
								
					
									GetUserDetails($UserName, $Domain, $FirstName, $Surname);

					
									
									if( $Domain == "" )
									{
										continue;
									}
									
									//print "<div style=\"text-align:left; border-color: #".$MainDivBorderColour."; border-style:solid; border-width:1px; background:#".$MainDivBackgroundColour."\">";
									
									print "<div class=\"col-md-12\">";
									print "<div class=\"panel panel-default\">";
									print "<div class=\"panel-body\" style=\"border-color: #".$MainDivBorderColour."; border-style:solid; border-width:1px; background:#".$MainDivBackgroundColour."\">";
									
									$UserAllowance = GetUserDiskAllowance($UserName);
						
									print "<p>Checking: <b>".$Domain."</b><br>";
									print "User: <b>".$UserName."</b><br>";
									print "User: <b>".$FirstName." ".$Surname."</b><p>";

									print "&nbsp;<br><h3>Disk Usage</h3>";
									print "Disk Usage: <b>".$oUtils->ConvertToScale($Usage,  $Scale, "M")." M</b><br>";
									print "Disk Allowance: <b>".$oUtils->ConvertToScale($UserAllowance, "b", "M")." M</b><br>";

									$Usage = $oUtils->ConvertToBytes($Usage, $Scale);
									
									$Percentage = number_format(($Usage / $UserAllowance) * 100, 2);
			
									$BackGroundColour = "red";
									$TextColour = "white";

									if($Percentage <= 25)
									{
										$BackGroundColour = "green";
										$TextColour = "orange";
									}
									else if($Percentage <= 50)
									{
										$BackGroundColour = "blue";
									}
									else if($Percentage <= 75)
									{
										$BackGroundColour = "orange";
									}

									print "<div id=\"SliderParent\" style=\"text-align:left; width:100%; border-style: solid; background: white; border-color:".$BackGroundColour."; border-width:1px;\">";

									print "<div id=\"Slider\" style=\"width:";

if($Percentage > 100)
{
	print "100";
}
else
{
	print $Percentage;
}
print "%; border-width:0px; background-color:".$BackGroundColour."; color:".$TextColour."\">";
									print $Percentage."%";
									print "</div>";
									print "</div>";






	


									$TrafficAllowance = GetTrafficAllowance($UserName);
									$TrafficUsage = GetTrafficUsage($UserName);
									$Percentage = number_format(($TrafficUsage / $TrafficAllowance) * 100, 2);


									print "&nbsp;<br><h3>Traffic Usage</h3>";
									$UsageScale = "b";
									print "Traffic Usage: <b>".$oUtils->ConvertFromBytes($TrafficUsage, $UsageScale)." </b><br>";
									$AllowanceScale = "b";
									print "Traffic Allowance: <b>".$oUtils->ConvertFromBytes($TrafficAllowance, $AllowanceScale)."</b><br>";


									$BackGroundColour = "red";
									$TextColour = "white";

									if($Percentage <= 25)
									{
										$BackGroundColour = "green";
										$TextColour = "orange";
									}
									else if($Percentage <= 50)
									{
										$BackGroundColour = "blue";
									}
									else if($Percentage <= 75)
									{
										$BackGroundColour = "orange";
									}

									print "<div id=\"SliderParent\" style=\"text-align:left; width:100%; border-style: solid; background: white; border-color:".$BackGroundColour."; border-width:1px;\">";

									print "<div id=\"Slider\" style=\"width:";

if($Percentage > 100)
{
	print "100";
}
else
{
	print $Percentage;
}
print "%; border-width:0px; background-color:".$BackGroundColour."; color:".$TextColour."\">";
									print $Percentage."%";
									print "</div>";
									print "</div>";



































									print "</div>";
									print "</div>";
									print "</div>";

									//print "</div>";
	
									//print "<p><hr style=\"border-width:1px; border-style:solid; border-color:black;\"><p>";

								}

							}
						    }
						}

						closedir($handle);
					    }


					?>						







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
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="/assets/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js"></script>
		<script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="/assets/plugins/blockUI/jquery.blockUI.js"></script>
		<script src="/assets/plugins/iCheck/jquery.icheck.min.js"></script>
		<script src="/assets/plugins/perfect-scrollbar/src/jquery.mousewheel.js"></script>
		<script src="/assets/plugins/perfect-scrollbar/src/perfect-scrollbar.js"></script>
		<script src="/assets/plugins/less/less-1.5.0.min.js"></script>
		<script src="/assets/plugins/jquery-cookie/jquery.cookie.js"></script>
		<script src="/assets/plugins/bootstrap-colorpalette/js/bootstrap-colorpalette.js"></script>
		<script src="/assets/js/main.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script type="text/javascript" src="/assets/plugins/select2/select2.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="/assets/plugins/DataTables/media/js/DT_bootstrap.js"></script>
		<script src="/assets/js/table-data.js"></script>
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				TableData.init();
			});
		</script>
	</body>
	<!-- end: BODY -->
</html>
