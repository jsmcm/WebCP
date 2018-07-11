<?php
session_start();

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.User.php");
$oUser = new User();

$ClientID = $oUser->getClientId();
if($ClientID < 1)
{
	header("Location: /index.php");
	exit();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	

<title>Dashboard | Web Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />


<style type="text/css">
<!--
@import url("/includes/styles/tablestyle.css");
-->
</style>


<script language="javascript">

function DoSubmit()
{
	if(document.EditPassword.Password.value == "")
	{
		alert("Please enter a password");
		document.EditPassword.Password.focus();
		return false;
	}

	return true;
}

</script>


<link rel="stylesheet" type="text/css" href="/includes/styles/main.css" media="screen"/>
<link rel="stylesheet" type="text/css" href="/includes/styles/curveddivs.css" media="screen"/>
	
<script src="/includes/javascript/jquery.js"></script>
<script src="/includes/javascript/main.js"></script>
<script src="/includes/javascript/sorttable.js"></script>

</head>

<body style="margin:0; background: #ededed">

<?php
include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopBar.inc");


include($_SERVER["DOCUMENT_ROOT"]."/includes/PageParts/TopNav.inc");


$id = $_REQUEST["id"];
$oFTP = new FTP();

$FTPUser = $oFTP->GetFTPUser($id);
?>



<div id="wrap">

        <div id="wrap">



                <h1>Edit Password for <?php print $FTPUser; ?></h1>

                <form name="EditPassword" method="post" action="DoEditPassword.php">
                        <ul id="contactform">
				
				<input type="hidden" value="<?php print $id; ?>" name="id">				
				
                                <li>
                                        <label for="Password">Password</label>
                                        <span class="fieldbox"><input type="password" name="Password" id="email"></span>
                                </li>

                        </ul>

                        </p>
                        <input type="submit" value="Change Password" id="button" name="button" onclick="return DoSubmit(); return false;"/>
                </form>

        </div>

</div>
	
</body>
</html>
