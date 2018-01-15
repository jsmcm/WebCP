<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.SimpleNonce.php");

class API_Nonce
{ 

	function VerifyNonce($InputNonce, $Action, $TimeStamp, $MetaDataArray=null)
	{
		$oSimpleNonce = new SimpleNonce();
		return $oSimpleNonce->VerifyNonce($InputNonce, $Action, $TimeStamp, $MetaDataArray);
	}

	function GenerateNonce($Action, $MetaDataArray=null)
	{
		$oSimpleNonce = new SimpleNonce();
		return $oSimpleNonce->GenerateNonce($Action, $MetaDataArray);
        }

	

}
?>
