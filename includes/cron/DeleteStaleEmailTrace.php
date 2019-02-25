<?php
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/DeleteStaleEmailTrace_".date("ymd")))
{
        exit();
}

touch($_SERVER["DOCUMENT_ROOT"]."/includes/cron/tmp/DeleteStaleEmailTrace_".date("ymd"));

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");
$oEmail = new Email();

$date = new DateTime(date("Y-m-d"));
$date->sub(new DateInterval('P15D'));

$oEmail->DeleteStaleEmailTrace($date->format('Y-m-d'));
