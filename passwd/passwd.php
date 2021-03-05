<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oPackage = new Package();
$oLog = new Log();
$oDomain = new Domain();
$oSettings = new Settings(); 
$oReseller = new Reseller();

require($_SERVER["DOCUMENT_ROOT"]."/includes/License.inc.php");

$ClientID = $oUser->getClientId();
if($ClientID < 1) {
	header("Location: /passwd/");
	exit();
}



$URL = $_REQUEST["URL"];

if(isset($_REQUEST["Path"])) {
	$Path = $_REQUEST["Path"];
}

if(substr($Path, strlen($Path) - 1) != "/") {
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

$nonceArray = [
	$oUser->Role,
	$oUser->ClientID,
	$URL
];

$oSimpleNonce = new SimpleNonce();
$nonce = $oSimpleNonce->GenerateNonce("getDomainOwnerFromDomainName", $nonceArray);
$DomainOwnerID = $oDomain->GetDomainOwnerFromDomainName($URL, $nonce);  
  
if($oUser->Role == "client")   {  
	if($DomainOwnerID != $ClientID)   {  
		header("location: index.php?Notes=You do not have permission to access this sites detail");
		exit();  
	}  
} else if($oUser->Role == "reseller")   {  
        $ResellerID = $oReseller->GetDomainResellerID($URL);  
  
        if( ($DomainOwnerID != $ClientID) && ($ResellerID != $ClientID) ) {  
			header("location: index.php?Notes=You do not have permission to access this sites detail");
            exit();  
        }  
}  


$LeftNavPath = substr($Path, 0, strpos($Path, "public_html") + 12);

function PrintDirectories($Path, $Iteration, $URL)
{

	$ULPrinted = false;
	
	$LeftNavFileArray = array();
	if ($handle = opendir($Path))	
	{

		/* This is the correct way to loop over the directory. */
	        while (false !== ($file = readdir($handle)))
	        {
	                if($file != "." && $file != "..")
	                {
				array_push($LeftNavFileArray, $file);
			}
		}
	
		natcasesort($LeftNavFileArray);

		foreach($LeftNavFileArray as $file)
		{
	        	if(is_dir($Path.$file))
	                {
				if($ULPrinted == false)
				{
					if($Iteration == 0)
					{
						print "<ul id=\"browser\" class=\"filetree\">";
					}
					else
					{
						print "<ul>";
					}
				}
 
				print "<li class=\"closed\" ondblclick=\"d('".$URL."', '".$Path.$file."/');\"><span class=\"folder\">".$file."</span>";
	
				PrintDirectories($Path.$file."/", $Iteration++, $URL);

				print "</li>";

				if($ULPrinted == false)
				{
					$ULPrinted = true;
				}
                        }
                }
	
	        closedir($handle);
	}

	if($ULPrinted == true)
	{
		print "</ul>";
	}

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
		<title>Password Protect Directories | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
	



	 
	<link rel="stylesheet" href="js/jquery.treeview.css" />
    	<link rel="stylesheet" href="js/red-treeview.css" />
	
	<script src="js/lib/jquery.js" type="text/javascript"></script>
	<script src="js/lib/jquery.cookie.js" type="text/javascript"></script>
	<script src="js/jquery.treeview.js" type="text/javascript"></script>

	<style>	 
	td
	{
		border-bottom-color:grey; border-bottom-style:solid; border-bottom-width:1px;
	}
	</style>






	<script type="text/javascript"> 
	$(document).ready(function(){ 
		$("#browser").treeview({ 
			toggle: function() { 
				console.log("%s was toggled.", $(this).find(">span").text()); 
			} 
		}); 
	}); 


	var KillDoubleClick = false;

	function d(URL, Path)
	{
		if(KillDoubleClick == false)
		{
			KillDoubleClick = true;
			window.location = "passwd.php?URL=" + URL + "&Path="+Path;
		}
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
								<li><a href="passwd.php?URL=<?php print $_REQUEST["URL"]; ?>&Path=<?php print $_REQUEST["Path"]; ?>"><h1>Passwd Main List</h1></a></li>
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









	<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
	<td width="300" valign="top" style="padding: 10px 5px 5px; border-right-width: 1px; border-right-color: black; border-right-style: solid;">
		<div style="width:300px; overflow:auto;">		 

		<?php
		

		print "<b><img src=\"./js/images/folder-closed.gif\"> ".$LeftNavPath."</b><br>";
		PrintDirectories($LeftNavPath, 0, $URL);
		?>



		</div> 
	 </td>
	<td width="*" valign="top" style="color:#0000cc; font-size:14px;padding:10px 10px 10px; line-height:200%;">



            

        <?php

	if(strlen($Path) > strpos($Path, "public_html") + 12)
	{

		$x = strrpos($Path, "/", -2) + 1;
	

		print "<a href=\"passwd.php?URL=".$URL."&Path=".substr($Path, 0, $x)."\" style=\"text-decoration:none;\"><img width=\"16\" height=\"16\" src=\"./js/images/folder-up.gif\"> Back...</a><br>";
	}


	print "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";

	print "<tr>";
	print "<td width=\"80%\" colspan=\"2\"><img border=\"0\" src=\"./js/images/folder.gif\"> <b>".$Path."</b></td>";
	print "<td width=\"*\"><a href=\"manage.php?URL=".$URL."&Path=".$Path."\">[ Manage ]</a></td>";
	print "</tr>";

 	if ($handle = opendir($Path)) 
	{
		$FileArray = array();


                while (false !== ($file = readdir($handle)))
                {
                        if($file != "." && $file != "..")
                        {
                                array_push($FileArray, $file);
                        }

                }
		
		natcasesort($FileArray);

		foreach($FileArray as $file)
		{
			if(is_dir($Path.$file))
			{
				print "<tr>";
				print "<td width=\"5\">&nbsp;</td>";
		               	print "<td width=\"80%\"><a href=\"passwd.php?URL=".$URL."&Path=".$Path.$file."/\" style=\"text-decoration: none;\"><img border=0 src=\"./js/images/folder-closed.gif\"> ".$file."</a></td>";
				print "<td width=\"*\"><a href=\"manage.php?URL=".$URL."&Path=".$Path.$file."\">[ Manage ]</a></td>";
				print "</tr>";
			}
            	}
   
  	     	closedir($handle);

    	}

	print "</table>";

      	?>

	</td>
	</table>
	 




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
