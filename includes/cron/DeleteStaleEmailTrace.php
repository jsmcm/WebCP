<?php
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/DeleteStaleEmailTrace_".date("ymd")))
{
        exit();
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/DeleteStaleEmailTrace_".date("ymd"));

require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Email.php");
$oEmail = new Email();

$date = new DateTime(date("Y-m-d"));
$date->sub(new DateInterval('P15D'));

$oEmail->DeleteStaleEmailTrace($date->format('Y-m-d'));
exit();
?>
