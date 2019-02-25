<a class="navbar-brand" href="/domains/index.php">
<?php 

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oSettings = new Settings();

print $oSettings->GetWebCPName();
?>
</a>
