<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
$oUtils = new Utils();

$oUtils->GetTrafficStats($TotalTraffic, $TotalUsed, $TotalAvailable, $PercentageUsed);

print "TotalTraffic: ".$TotalTraffic."<br>";
print "TotalUsed: ".$TotalUsed."<br>";
print "TotalAvailable: ".$TotalAvailable."<br>";
print "PercentageUsed: ".$PercentageUsed."<p>";

$oUtils->GetDiskSpaceStats($TotalDiskSpace, $TotalUsed, $TotalAvailable, $PercentageUsed);

print "TotalDiskSpace: ".$TotalDiskSpace."<br>";
print "TotalUsed: ".$TotalUsed."<br>";
print "TotalAvailable: ".$TotalAvailable."<br>";
print "PercentageUsed: ".$PercentageUsed."<br>";

?>
