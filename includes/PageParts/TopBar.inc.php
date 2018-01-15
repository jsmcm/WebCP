<?php

 
  $ImgPath = "http://".$_SERVER["SERVER_NAME"].":10025/img/";
  
?>
  
<div style="height:95px; background:transparent url(<?php print $ImgPath; ?>TopBackground.gif) repeat top right; ">
<div style="float: left; width:100%; margin-top:20px;"><font style="margin-left:50px; color:white; font-family: 'Droid Sans', Verdana; font-size:50px;">Web Control Panel Lite</font> </div>
<div style="margin-left:100px; color:white;">
<?php
if(isset($oUser))
{
	if($oUser->ClientID > 0)
	{
	  print "Logged in as: ".$oUser->FirstName." ".$oUser->Surname." (<a href=\"/logout.php\"><font color=\"yellow\">Log out</font></a>)";
	}
}
?>
</div>
</div>
