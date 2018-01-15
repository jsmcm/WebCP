<?php

include 'class.api.nonce.php';

$options = array('uri' => "http://".$_SERVER["SERVER_NAME"]);
$server = new SoapServer(NULL, $options);
$server->setClass('API_Nonce');
$server->handle();

?>
