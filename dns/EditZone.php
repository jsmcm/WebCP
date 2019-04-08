<?php
session_start();

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oUser = new User();
$oLog = new Log();
$oUtils = new Utils();
$oDNS = new DNS();
$oReseller = new Reseller();
$oSettings = new Settings();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	$oLog->WriteLog("DEBUG", "/domains/index.php -> client_id not set, redirecting to /index.php");
	header("Location: /index.php");
	exit();
}
	
if( ! $oUser->Role == "admin" )
{
	header("Location: ./index.php?NoteType=Error&Notes=No Permissions");
	exit();
}

$ZoneID = -1;
if(isset($_GET["ID"]))
{
	$ZoneID = intVal($_GET["ID"]);
}

$SOADataArray = array();
$oDNS->GetSOAInfo($ZoneID, $SOADataArray);

if($SOADataArray["ID"] == "")
{
	header("Location: index.php?Notes=Invalid Zone ID given&NoteType=Error");
	exit();
}

$ZoneDataArray = array();
$oDNS->GetRRSList($ZoneID, $ZoneDataArray, $ZoneDataArrayCount);

$TTL = $oDNS->GetSetting("TTL");
if( ! is_numeric($TTL))
{
	$TTL = 7200;
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
		<title>Edit Zone | <?php print $oSettings->GetWebCPTitle(); ?></title>
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
		
		
		<script language="javascript">
		var Total = <?php print ($ZoneDataArrayCount + 1); ?>;
	
		function ValidateForm()
		{

			var elements = document.getElementsByTagName("input")
			for(i = 0; i < elements.length; i++)
			{	
				if(elements[i].name.indexOf("Name_") > -1)
				{
					ID = elements[i].name.substr(elements[i].name.indexOf("Name_") + 5);

					New = "";				
					if(elements[i].name.substr(0, 4) == "New_")
					{
						New = "new";
					}

					if(ValidateRow(ID, New) == false)
					{
						alert("There are errors on this form, please fix them then try again.");
						return false;
					}
				}
			}

			return true;
		}
	
		function AddNewBoxes(Count)
		{

			 
			if(Count == Total)
			{
				Total++;
				jQuery("#table-rows").append("<tr id=\"New_tr_" + Total + "\"><td><input onblur=\"ValidateRow(" + Total + ", 'new');\" onkeypress=\"ResetBorder(this);\"  onkeyup=\"AddNewBoxes(" + Total + ");\" type=\"text\" name=\"New_Name_" + Total + "\" id=\"New_Name_" + Total + "\"></td><td class=\"narrow\"><input onblur=\"ValidateRow(" + Total + ", 'new');\"  onkeypress=\"ResetBorder(this);\"  type=\"number\" name=\"New_TTL_" + Total + "\" id=\"New_TTL_" + Total + "\" value=\"<?php print $TTL; ?>\"></td><td class=\"narrow\"><input value=\"IN\" disabled type=\"text\" name=\"New_Class_" + Total + "\" id=\"New_Class_" + Total + "\"></td><td class=\"narrow\"><select onblur=\"ValidateRow(" + Total + ", 'new');\"   name=\"New_Type_" + Total + "\" id=\"New_Type_" + Total + "\"><option value=\"A\">A</option><option value=\"AAAA\">AAAA</option><option value=\"CNAME\">CNAME</option><option value=\"NS\">NS</option><option value=\"MX\">MX</option><option value=\"TXT\">TXT</option></select></td><td class=\"narrow\"><input onblur=\"ValidateRow(" + Total + ", 'new');\"  type=\"number\" onkeypress=\"ResetBorder(this);\"   name=\"New_Priority_" + Total + "\" id=\"New_Priority_" + Total + "\"></td><td><input onblur=\"ValidateRow(" + Total + ", 'new');\"  onkeypress=\"ResetBorder(this);\"  type=\"text\"  name=\"New_Record_" + Total + "\" id=\"New_Record_" + Total + "\" ></td><td><a role=\"menuitem\" onclick=\"return DeleteRow(" + Total + ", 'new'); return false;\"><i class=\"fa fa-times\"></i> Delete Row</a></td></tr>");
			}
		
		} 

		function ResetBorder(Obj)
		{
		
			Obj.style.border = "1px solid #D5D5D5";
		}

		function DeleteRow(ID, ControlType)
		{
			if(confirm("Really delete this row?"))
			{
				if(ControlType == 'new')
				{
					TR = document.getElementById("New_tr_" + ID);
					Name = document.getElementById("New_Name_" + ID);
					Record = document.getElementById("New_Record_" + ID);
				}
				else
				{
					TR = document.getElementById("tr_" + ID);
					Name = document.getElementById("Name_" + ID);
					Record = document.getElementById("Record_" + ID);
				}
				
				Name.value = "";
				Record.value = "";
				TR.style.visibility = "hidden";
				TR.style.display = "none";
			}

			return false;
		}

		function ValidateRow(ID, ControlType)
		{
			Return = true;

			Domain = "<?php print $oDNS->RemoveLastPeriod($SOADataArray["Domain"]); ?>";


			if(ControlType == 'new')
			{
				Name = document.getElementById("New_Name_" + ID);
				TTL = document.getElementById("New_TTL_" + ID);
				Type = document.getElementById("New_Type_" + ID);
				Priority = document.getElementById("New_Priority_" + ID);
				Record = document.getElementById("New_Record_" + ID);
			}
			else
			{
				Name = document.getElementById("Name_" + ID);
				TTL = document.getElementById("TTL_" + ID);
				Type = document.getElementById("Type_" + ID);
				Priority = document.getElementById("Priority_" + ID);
				Record = document.getElementById("Record_" + ID);
			}
			
			if( (Name.value == "") && (Record.value == ""))
			{
				return Return;
			}

			if(Name.value.substr(Name.value.length - Domain.length) == Domain)
			{
				Name.value = Name.value + ".";
			}

			if(ValidateDomain(Name.value, false) == false)
			{
				Name.style.border = "2px red solid";
				Return = false;
			}

                        if( isNaN(TTL.value))
                        {
                                TTL.style.border = "2px red solid";
				Return = false;
                        }
                        else if(TTL.value == "")
                        {
                                TTL.style.border = "2px red solid";
                        	Return = false;
                        }
                        else if(parseInt(TTL.value) < 0)
                        {
        	               	TTL.style.border = "2px red solid";
 	                       	Return = false;
                        }

			if(Type.value == "A" || Type.value == "AAAA")
			{
				if(Type.value == "A")
				{
					if(ValidateIPv4(Record.value) == false)
					{
						Record.style.border = "2px red solid";
						Return = false;
					}
				}		
				else if(Type.value == "AAAA")
				{
					if(ValidateIPv6(Record.value) == false)
					{
						Record.style.border = "2px red solid";
						Return = false;
					}
				}		
			}
			else		
			{
				// Record should be a domain
				if(Record.value.length > 0)
				{
					if(Type.value != "TXT")
					{
						if(Record.value.substr(Record.value.length - 1, 1) != ".")
						{
							Record.value = Record.value + ".";
						}
					
						if(ValidateDomain(Record.value, true) == false)
						{
							Record.style.border = "2px red solid";
							Return = false;
						}
					}
				}
				else
				{
					Record.style.border = "2px red solid";
					Return = false;	
				}
			}

			if(Type.value == "MX")
			{
				// priority must be a non negative number
				if( isNaN(Priority.value))
				{
					Priority.value = 0;									
					Priority.style.border = "2px red solid";
					Return = false;
				}
				else if(Priority.value == "")
				{
					Priority.value = 0;									
				}

				else if(parseInt(Priority.value) < 0)
				{
					Priority.value = 0;									
					Priority.style.border = "2px red solid";
					Return = false;
				}
			}

			return Return;
		} 
		</script>
	
		<script language="javascript">


		function ValidateIPv4(IP)
		{

			if(IP.length > 0)
			{	 
				if (/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/.test(IP)) 
				{
					;
				} 
				else 
				{
					return false;
				}
			}

			if(IP.length > 15)
			{
				return false;
			}
			if(IP.length < 7)
			{
				return false;
			}
			return true;
		}

		function ValidateIPv6(IP)
		{

			if(IP.length > 0)
			{
				if (/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$|^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$|^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/.test(IP)) 
				{
					;
				} 
				else 
				{
					return false;
				}
			
				if(IP.indexOf(":") < 0)
				{
					return false;
				}
			}
			else
			{
				return false;
			}

			return true;
		}

		function ValidateDomain(Domain, FQDN)
		{

		if(/^[a-z0-9.\-_]+$/i.test(Domain))
		{
		}
		else
		{
			alert(Domain + " is false at 1");
			return false;
		}

			if(Domain.indexOf("..") > -1)
			{
				return false;
			}	

			/*
			// _ needed for dkim
			if(Domain.indexOf("_") > -1) 
			{
				return false;
			}
			*/

			if(FQDN == true)
			{
				if(Domain.indexOf(".") < 0)
				{
					return false;
				}
			}
		
			return true;
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
							
									<a href="/dns/index.php">
										DNS
									</a>
								</li>
					
								<li>
									<i class="active"></i>
										Edit Zone
								</li>
					
							</ol>
							<div class="page-header">
								<h1>Edit Zone: <?php print $oDNS->RemoveLastPeriod($SOADataArray["Domain"]); ?> </h1>
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
					


						<div class="col-md-12">
						
						
							<!-- start: DYNAMIC TABLE PANEL -->
							<div class="panel panel-default">
									
								<div class="panel-body">
					

								<form name="EditZone" action="DoEditZone.php" method="post">
								<table border="0" cellpadding="1" cellspacing="1">
								<tr>
									<th>Name</th>
									<th class="narrow">TTL</th>
									<th class="narrow">Class</th>
									<th class="narrow">Type</th>
									<th class="narrow">Priority</th>
									<th>Record</th>
									<th></th>
								</tr>
								<tbody id="table-rows">
								<input type="hidden" name="SOAID" value="<?php print $SOADataArray["ID"]; ?>">
							
								<?php
								for($x = 0; $x < $ZoneDataArrayCount; $x++)
								{
									print "<tr id=\"tr_".$ZoneDataArray[$x]["ID"]."\">\r\n";
										print "<td><input onkeypress=\"ResetBorder(this);\" type=\"text\" name=\"Name_".$ZoneDataArray[$x]["ID"]."\" id=\"Name_".$ZoneDataArray[$x]["ID"]."\" value=\"".$ZoneDataArray[$x]["Domain"]."\" onkeyup=\"AddNewBoxes(".($x + 1).");\" onblur=\"ValidateRow(".$ZoneDataArray[$x]["ID"].", '');\"></td>\r\n";
										print "<td class=\"narrow\"><input onkeypress=\"ResetBorder(this);\" type=\"number\" name=\"TTL_".$ZoneDataArray[$x]["ID"]."\" id=\"TTL_".$ZoneDataArray[$x]["ID"]."\" value=\"".$ZoneDataArray[$x]["TTL"]."\" onblur=\"ValidateRow(".$ZoneDataArray[$x]["ID"].", '');\"></td>\r\n";
										print "<td class=\"narrow\"><input  disabled class=\"\" type=\"text\" name=\"Class_".$ZoneDataArray[$x]["ID"]."\" id=\"Class_".$ZoneDataArray[$x]["ID"]."\" value=\"".$ZoneDataArray[$x]["Class"]."\"></td>\r\n";
										print "<td class=\"narrow\">";
					
										print "<select onblur=\"ValidateRow(".$ZoneDataArray[$x]["ID"].", '');\" name=\"Type_".$ZoneDataArray[$x]["ID"]."\" id=\"Type_".$ZoneDataArray[$x]["ID"]."\">";
										print "<option value=\"A\" ".(($ZoneDataArray[$x]["Type"] == "A")? " selected ": "").">A</option>";
										print "<option value=\"AAAA\" ".(($ZoneDataArray[$x]["Type"] == "AAAA")? " selected ": "").">AAAA</option>";
										print "<option value=\"CNAME\" ".(($ZoneDataArray[$x]["Type"] == "CNAME")? " selected ": "").">CNAME</option>";
										print "<option value=\"NS\" ".(($ZoneDataArray[$x]["Type"] == "NS")? " selected ": "").">NS</option>";
										print "<option value=\"MX\" ".(($ZoneDataArray[$x]["Type"] == "MX")? " selected ": "").">MX</option>";
										print "<option value=\"TXT\" ".(($ZoneDataArray[$x]["Type"] == "TXT")? " selected ": "").">TXT</option>";

										print "</select>";
										print "</td>\r\n";

										$Priority = "";
										$Record = $ZoneDataArray[$x]["Value1"];
										if($ZoneDataArray[$x]["Type"] == "MX")
										{
											$Priority = $ZoneDataArray[$x]["Value1"];
											$Record = $ZoneDataArray[$x]["Value2"];
										}
										print "<td class=\"narrow\"><input onkeyup=\"ResetBorder(this);\" onblur=\"ValidateRow(".$ZoneDataArray[$x]["ID"].", '');\" type=\"number\" name=\"Priority_".$ZoneDataArray[$x]["ID"]."\" id=\"Priority_".$ZoneDataArray[$x]["ID"]."\" value=\"".$Priority."\"></td>\r\n";
										print "<td><input onkeyup=\"ResetBorder(this);\" type=\"text\"  onblur=\"ValidateRow(".$ZoneDataArray[$x]["ID"].", '');\" name=\"Record_".$ZoneDataArray[$x]["ID"]."\" id=\"Record_".$ZoneDataArray[$x]["ID"]."\" value=\"".htmlspecialchars($Record)."\"></td>\r\n";
										print "<td><a role=\"menuitem\" onclick=\"return DeleteRow(".$ZoneDataArray[$x]["ID"].", ''); return false;\"><i class=\"fa fa-times\"></i> Delete Row</a></td>\r\n";
									print "</tr>\r\n";
								}

								print "<tr id=\"New_tr_".($x + 1)."\">\r\n";
									print "<td><input  onkeypress=\"ResetBorder(this);\" type=\"text\" onblur=\"ValidateRow(".($x + 1).", 'new');\" name=\"New_Name_".($x + 1)."\" id=\"New_Name_".($x + 1)."\" onkeyup=\"AddNewBoxes(".($x + 1).");\"></td>\r\n";
									print "<td class=\"narrow\"><input  onkeypress=\"ResetBorder(this);\"  type=\"number\" name=\"New_TTL_".($x + 1)."\" id=\"New_TTL_".($x + 1)."\" value=\"".$TTL."\" onblur=\"ValidateRow(".($x + 1).", 'new');\"></td>\r\n";
									print "<td class=\"narrow\"><input  disabled type=\"text\" name=\"New_Class_".($x + 1)."\" id=\"New_Class_".($x + 1)."\" value=\"IN\"></td>\r\n";
									print "<td class=\"narrow\">";

									print "<select name=\"New_Type_".($x + 1)."\" id=\"New_Type_".($x + 1)."\" onblur=\"ValidateRow(".($x + 1).", 'new');\">";
									print "<option value=\"A\">A</option>";
									print "<option value=\"AAAA\">AAAA</option>";
									print "<option value=\"CNAME\">CNAME</option>";
									print "<option value=\"NS\">NS</option>";
									print "<option value=\"MX\">MX</option>";
									print "<option value=\"TXT\">TXT</option>";
									print "</select>";
									print "</td>\r\n";

									print "<td class=\"narrow\"><input  onblur=\"ValidateRow(".($x + 1).", 'new');\" onkeypress=\"ResetBorder(this);\"  type=\"number\" name=\"New_Priority_".($x + 1)."\" id=\"New_Priority_".($x + 1)."\"></td>\r\n";
									print "<td><input   onblur=\"ValidateRow(".($x + 1).", 'new');\" onkeypress=\"ResetBorder(this);\"  type=\"text\" name=\"New_Record_".($x + 1)."\" id=\"New_Record_".($x + 1)."\"></td>\r\n";
									print "<td><a role=\"menuitem\" onclick=\"return DeleteRow(".($x + 1).", 'new'); return false;\"><i class=\"fa fa-times\"></i> Delete Row</a></td>\r\n";
								print "</tr>\r\n";

								?>	
								</tbody>	
								</table>
	
								</div>
							</div>
										<div class="form-group">										
											<div class="col-sm-4">
												<input type="submit" value="Save Zone" data-style="zoom-in" class="btn btn-info ladda-button" onclick="return ValidateForm(); return false;">
													<span class="ladda-spinner"></span>
													<span class="ladda-progress" style="width: 0px;"></span>
												</input>
											</div>
										</div>
							<!-- end: DYNAMIC TABLE PANEL -->
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
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
		<script src="/assets/plugins/perfect-scrollbar/src/perfect-scrollbar.js"></script>
		<script src="/assets/js/main.js"></script>
		<!-- end: MAIN JAVASCRIPTS -->
		<!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
		<script>
			jQuery(document).ready(function() {
				Main.init();
				//TableData.init();
			});
		</script>





	</body>
		<style>
		.narrow input
		{
			max-width: 120px;
		}

		.with-border
		{
			border: 1px solid #D5D5D5;
		}

	select {
background-color: #FFFFFF;
border: 1px solid #D5D5D5;
border-radius: 0 0 0 0 !important;
color: #858585;
font-family: inherit;
font-size: 14px;
line-height: 1.2;
padding: 5px 4px;
transition-duration: 0.1s;
box-shadow: none;
}

		</style>	
	<!-- end: BODY -->
</html>

