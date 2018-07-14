<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

use \Matomo\Ini\IniReader;


/*********************************************************************
class.SimpleNonce.php
John McMurray <john@softsmart.co.za>
http://www.softsmart.co.za
Version: 1.0.0
**********************************************************************/
class SimpleNonce 
{
    protected $NonceSalt = "YourSaltHere";
    protected $NonceExpiryTime = 600; // In seconds. 3600 = 1 hour
    protected $Path;
    function __construct() 
    {
	
        try {
            $reader = new IniReader();
        } catch (Error $e) {

            if ($e->getMessage() == "Class 'Matomo\Ini\IniReader' not found") {
                throw new Exception("class.Database->getConnection Matomo\Ini not found");
            } else {
                throw new Exception("class.Database->getConnection unknown error initting IniReader");
            }

            exit();
        }

        // Read a file
        $array = $reader->readFile($_SERVER["DOCUMENT_ROOT"]."/../config.php");

        $DatabasePassword = $array["DATABASE_PASSWORD"];

	$this->NonceSalt = $DatabasePassword;
	$this->Path = $_SERVER["DOCUMENT_ROOT"]."/tmp/nonce"; // where will the nonce files be saved
	$this->ManageNonceTempFiles();
    }
    private function ManageNonceTempFiles()
    {
        if( ! file_exists($this->Path) )
        {
            mkdir($this->Path);
        }
        // Start off by deleting stale nonce files
        $dh = opendir($this->Path);
        while (($file = readdir($dh)) !== false)
        {
            if($file != '.' && $file != '..')
            {
                if( file_exists($this->Path."/".$file) )
                {
                    if( (time() - filemtime($this->Path."/".$file)) > ($this->NonceExpiryTime) )
                    {
                        unlink($this->Path."/".$file);
                    }
                }
            }
        }
        closedir($dh);
    }
    /**
     * Verify a given nonce
     * @param string InputNonce
     * @param string Action
     * @param string TimeStamp
     * @param mixed MetaDataArray optional
     * 
     * @return boolean
     */
    function VerifyNonce($InputNonce, $Action, $TimeStamp, $MetaDataArray=null)
    {
        $expires = $TimeStamp + $this->NonceExpiryTime;
        $now = time();
        if($expires - $now < 0)
        {
            return false;
        }
        $NonceSeedString = $Action;
        if( is_array($MetaDataArray) && $MetaDataArray != null)
        {
            foreach($MetaDataArray as $Meta)
            {
                $NonceSeedString = $NonceSeedString.$Meta;
            }
        }
        $NonceSeedString = $NonceSeedString.$TimeStamp;
        $NonceSeedString = $NonceSeedString.$this->NonceSalt;
        $nonce = md5($NonceSeedString);
        	
	if($nonce != $InputNonce)
        {
            return false;
        }
        
	$ActionBuffer = $Action;
        for($x = 0; $x < strlen($Action); $x++)
        {
            if( ! ctype_alnum(substr($Action, $x, 1)) )
            {
                $ActionBuffer = substr($Action, 0, $x);
                $ActionBuffer = $ActionBuffer."_";
                $ActionBuffer = $ActionBuffer.substr($Action, $x + 1);
                $Action = $ActionBuffer;
            }
        }
        if( file_exists($this->Path."/".$Action.$nonce) )
        {
            return false;
        }
        touch($this->Path."/".$Action.$nonce);
        return true;
    }


    function GenerateNonce($Action, $MetaDataArray=null)
    {
        $ReturnArray = array();
        $NonceSeedString = $Action;
        $TimeStamp = time();
        if( is_array($MetaDataArray) && $MetaDataArray != null)
        {
            foreach($MetaDataArray as $Meta)
            {
                $NonceSeedString = $NonceSeedString.$Meta;
            }
        }
        $NonceSeedString = $NonceSeedString.$TimeStamp;
        $NonceSeedString = $NonceSeedString.$this->NonceSalt;
        $nonce = md5($NonceSeedString);
        $ReturnArray["Nonce"] = $nonce;
        $ReturnArray["TimeStamp"] = $TimeStamp;
        return $ReturnArray;
    }
}
?>
