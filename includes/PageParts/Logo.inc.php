<a class="navbar-brand" href="/domains/index.php">
<?php 
require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
$oSettings = new Settings();

print $oSettings->GetWebCPName();
?>
</a>
