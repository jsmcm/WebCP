<?php

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");

class Utils
{



	function __construct()
	{

		if ( ! file_exists("/tmp/webcp/") ) {
			mkdir("/tmp/webcp");
		}

	}



	function slugify($string)
	{
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
	}
	

	function DELETED_ValidateHash($Hash, $LicenseKey)
	{
		$Formula = substr($Hash, strlen($Hash) - 2, 2);
		$Hash = substr($Hash, 0, strlen($Hash) - 2);

		$FormulaNumber = hexdec($Formula);
		$FormulaNumber = $FormulaNumber & 0x03;

		if($FormulaNumber == 1) {
			$NewHash = "";

			for($x = strlen($Hash) - 1; $x >= 0; $x--) {
				$NewHash = $NewHash.$Hash[$x];
			}
			
		} else if($FormulaNumber == 2) {
			$NewHash = "";

			for($x = strlen($Hash) - 1; $x >= 0; $x--) {
				$NewHash = $NewHash.$Hash[$x];
			}

			$Hash = $NewHash;
			$NewHash = "";

			for($x = 0; $x < strlen($Hash); $x = $x + 2) {
			
				if ( isset( $Hash[$x + 1] ) ) {
					$NewHash = $NewHash.$Hash[$x + 1].$Hash[$x];
				}
			}

		} else if($FormulaNumber == 3) {
			$NewHash = "";

			for($x = 0; $x < strlen($Hash); $x = $x + 2) {
				$NewHash = $NewHash.$Hash[$x + 1].$Hash[$x];
			}

		} else {
			$NewHash = $Hash;
		}

		$RemoteIP = gethostbyname("bug.webcp.io");

		for($x = 1; $x < 11; $x++) {
			$CalculatedHash = md5($LicenseKey.$RemoteIP.$_SERVER["SERVER_ADDR"].$x);

			if($CalculatedHash == $NewHash) {
				return true;
			}
		}

		return false;
				
	}
 	
	function DELETED_getValidationData($hash)
	{
		if ( file_exists("/tmp/webcp/getValidationData_".$hash) ) {
			
			if( (time() - filemtime("/tmp/webcp/getValidationData_".$hash)) > 3600 ) {
				unlink("/tmp/webcp/getValidationData_".$hash);
			} else {
				$data = file_get_contents("/tmp/webcp/getValidationData_".$hash);
				return $data;
			}

		}


		
		$LicenseKey = "free";

		if ( file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf")) {
			$LicenseKey = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/includes/license.conf");
		}

		$oDomain = new Domain();

		$AccountsCreated = $oDomain->GetAccountsCreatedCount();

		//if ($AccountsCreated < 6 && $LicenseKey == "free") {
		if ($LicenseKey == "free") {
		
			$data = '{"allowed":0,"type":"free","date":"'.date("Y-m-d H:i:s").'","status":"failed","hash":"","message":"License not valid for free service"}';

			if ($this->ValidateFreeKey($hash)) {
				$data = '{"allowed":5,"type":"free","date":"'.date("Y-m-d H:i:s").'","status":"valid","hash":"16e2eebda7c4c0fcba6a253f79607ca2ab","message":""}';
			}
			

		} else {



		}

		file_put_contents("/tmp/webcp/getValidationData_".$hash, $data);

		return $data;
	}
	 
	

	function getLicense($licenseKey)
	{

		$licenseCacheTime = 900;
		$license = "";


		if ( file_exists("/tmp/webcp/getLicense_".$licenseKey) ) {

			if( (time() - filemtime("/tmp/webcp/getLicense_".$licenseKey)) > $licenseCacheTime ) {
				unlink("/tmp/webcp/getLicense_".$licenseKey);
			} else {
				return json_decode(file_get_contents("/tmp/webcp/getLicense_".$licenseKey));
			}
		}


		$oDomain = new Domain();

		$AccountsCreated = $oDomain->GetAccountsCreatedCount();

		//print "AccountsCreated: ".$AccountsCreated."<p>";

		if ($AccountsCreated <= 5 && $licenseKey == "free") {

			// free, doesn't need license
			$license = new stdClass();

			$license->success = "1";
			$license->license = "valid";
			$license->item_id = "";
			$license->item_name = "WebCP";
			$license->checksum = "";
			$license->expires = "lifetime";
			$license->payment_id = "";
			$license->customer_name = "";
			$license->customer_email = "";
			$license->license_limit = 1;
			$license->site_count = 1;
			$license->activations_left = 0;
			$license->price_id = "";
			$license->error = "";
			$license->type = "free";
			$license->allowed = 5;


		} else {

			//print "<P>getting license from remote<p>";
			$license = $this->getRemoteLicense($licenseKey);
			//print "<P>license: ".print_r($license, true)."<p>";
		}

		file_put_contents("/tmp/webcp/getLicense_".$licenseKey, json_encode($license));
		return $license;

	}





	function activateLicense($licenseKey) 
	{
	
	
		$store_url = 'https://webcp.io';
		$item_name = "WebCP";
	
		// data to send in our API request
		$api_params = array(
			'edd_action'	=> 'activate_license',
			'license'    	=> $licenseKey,
			'item_name' 	=> urlencode( $item_name ),
			'url' 			=> $_SERVER["SERVER_ADDR"]
		);
	
		// Call the custom API.
		$license_data = $this->postToWebCP( $store_url, array('body' => $api_params ) );
	
		if ( false === $license_data->success ) {
	
			switch( $license_data->error ) {
	
				case 'expired' :
	
					$message = sprintf(
						__( 'Your license key expired on %s.' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;
	
				case 'revoked' :
	
					$message = __( 'Your license key has been disabled.' );
					break;
	
				case 'missing' :
	
					$message = __( 'Invalid license.' );
					break;
	
				case 'invalid' :
				case 'site_inactive' :
	
					$message = __( 'Your license is not active for this URL.' );
					break;
	
				case 'item_name_mismatch' :
	
					$message = 'This appears to be an invalid license key for '.$item_name.'.';
					break;
	
				case 'no_activations_left':
	
					$message = __( 'Your license key has reached its activation limit.' );
					break;
	
				default :
	
					$message = __( 'An error occurred, please try again.' );
					break;
			}
	
		}
	
	
		// Check if anything passed on a message constituting a failure
		if ( ! empty( $message ) ) {
	
			return $message;
		
		}
	
		if (!isset($license_data->type)) {
			$license_data->type = "paid";
		}

		return $license_data;
	
	}
	
	
	function postToWebCP($url, $post_array)
	{
	
		$c = curl_init();
	
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $url);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post_array["body"]);
	
	
		$resultString = curl_exec($c);
		curl_close($c);
	
		if ($resultString != "" && strstr($resultString, "success") ) {
		
			return json_decode($resultString);
	
		}
	
		return false;
	
		
	
	}
	
	
	function getRemoteLicense($licenseKey)
	{
		$store_url = 'https://webcp.io';
		$item_name = 'WebCP';
		
		$api_params = array(
			'edd_action' => 'check_license',
			'license' => $licenseKey,
			'item_name' => urlencode( $item_name ),
			'url' => $_SERVER["SERVER_ADDR"]
		);
	
		$license_data = $this->postToWebCP( $store_url, array( 'body' => $api_params ) );
	
		if ($license_data === false) {
	
			return false;
	
		}
	
	
		if( $license_data->license == 'inactive' ) {
	
			$license_data = $this->activateLicense($licenseKey);

		} 
	

		return $license_data;
		
	
	
	}

	


	function DELETED_ValidateFreeKey($key)
	{

		$free = $key[1].$key[5].$key[10].$key[16];

		if ($free != "fee0") {
			return false;
		}

		$date1 = "20".$key[3].$key[8]."-".$key[19].$key[25]."-".date("d");
		
		$date2 = date("Y-m-d");

		$ts1 = strtotime($date1);
		$ts2 = strtotime($date2);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);

		$diff = (($year2 - $year1) * 12) + ($month2 - $month1);

		if ($diff > 2) {
			return false;
		}

		$number[0] = hexdec($key[2]);
		$number[1] = hexdec($key[6]);
		$number[2] = hexdec($key[12]);
		$number[3] = hexdec($key[14]);
		$number[4] = hexdec($key[18]);
		$number[5] = hexdec($key[21]);
		$number[6] = hexdec($key[23]);
		$number[7] = hexdec($key[24]);
		$number[8] = hexdec($key[27]);
		$number[9] = hexdec($key[29]);
		
		$total = 0;
		for ($x = 0; $x < 10; $x++) {
				
				if ( !($x == 3 || $x == 4 ) ) {
				
					//print "its ".$x.", doing<p>";
		
					if ($total > $number[$x]) {
						$total = $total - $number[$x];
					} else {
						$total = $total + $number[$x];
					}
				}
		
		}
		
		if ($total > 2) {
			$number[3] = intVal($total / 2);
			$total = $total - $number[3];
			$number[4] = $total;
			$total = $total - $number[4];
		
		} else {
			$number[3] = $total;
			$number[4] = 0;
			$total = $total - $number[3];
			$total = $total - $number[4];
		}
		
		if ($total == 0) {
			return true;
		}

		return false;
		
	}


	function DELETED_makeFreeKey()
	{

		$number[0] = mt_rand(0, 15);
		$number[1] = mt_rand(0, 15);
		$number[2] = mt_rand(0, 15);
		$number[3] = mt_rand(0, 15);
		$number[4] = mt_rand(0, 15);
		$number[5] = mt_rand(0, 15);
		$number[6] = mt_rand(0, 15);
		$number[7] = mt_rand(0, 15);
		$number[8] = mt_rand(0, 15);
		$number[9] = mt_rand(0, 15);
				
		//2[Ff][N0][y2]2[Ee][N1]5[y0]7[Ee]9[N2]f[N3]1[Zero0]e[N4][M0]6[N5]d[N6][N7][M8]f[N8]d[N9]6f
		
		$key = "";
		for($x = 0; $x < 32; $x++) {
		
			$key = $key.dechex(mt_rand(0, 15));
		
		}
		
		
		$total = 0;
		for ($x = 0; $x < 10; $x++) {
		
				if ( !($x == 3 || $x == 4 ) ) {
					if ($total > 0 && ($total - $number[$x] > 0) ) {
						$total = $total - $number[$x];
					} else {
						$total = $total + $number[$x];
					}
				}
		
		}
		
		
		if ($total > 2) {
			$number[3] = intVal($total / 2);
			$total = $total - $number[3];
			$number[4] = $total;
			$total = $total - $number[4];
		
		} else {
			$number[3] = $total;
			$number[4] = 0;
			$total = $total - $number[3];
			$total = $total - $number[4];
		}
		
		$key[1] = 'f';
		$key[2] = dechex($number[0]);
		$key[3] = substr(date("Y"), 2,1);
		$key[5] = 'e';
		$key[6] = dechex($number[1]);
		$key[8] = substr(date("Y"), 3,1);
		$key[10] = 'e';
		$key[12] = dechex($number[2]);
		$key[14] = dechex($number[3]);
		$key[16] = '0';
		$key[18] = dechex($number[4]);
		$key[19] = substr(date("m"), 0,1);
		$key[21] = dechex($number[5]);
		$key[23] = dechex($number[6]);
		$key[24] = dechex($number[7]);
		$key[25] = substr(date("m"), 1,1);
		$key[27] = dechex($number[8]);
		$key[29] = dechex($number[9]);
		
		return $key;

	}


	function GetTrafficStats(&$TotalTraffic, &$TotalUsed, &$TotalAvailable, &$PercentageUsed)
        {
		$oPackage = new Package();

		$oSettings = new Settings();

		$oDomain = new Domain();

		$oUtils = new Utils();
		
		$TotalTraffic = $oSettings->GetServerTrafficAllowance();
		$TotalUsed = $oDomain->GetPackageTrafficUsage();
		
		$PercentageUsed = $TotalUsed / $TotalTraffic * 100;
		$TotalAvailable = $TotalTraffic - $TotalUsed;

	}



	function GetDiskSpaceStats(&$TotalDiskSpace, &$TotalUsed, &$TotalAvailable, &$PercentageUsed)
	{
		$oPackage = new Package();

		$oDomain = new Domain();

		$oUtils = new Utils();

		$TotalDiskSpace = $oPackage->GetTotalDiskSpace();                                                   

		$TotalUsed = $oDomain->GetPackageDiskSpaceUsage();
	
		$PercentageUsed = $TotalUsed / $TotalDiskSpace * 100;

		$TotalAvailable = $TotalDiskSpace - $TotalUsed;
	}


	function CheckPassword($pwd)
	{
		$strength = array("Blank","Very Weak","Weak","Medium","Strong","Very Strong");
		$score = 1;
		
		if (strlen($pwd) < 1)
		{
			return $strength[0];
		}
		if (strlen($pwd) < 4)
		{
			return $strength[1];
		}
		if (strlen($pwd) >= 8)
		{
			$score++;
		}
		if (strlen($pwd) >= 10)
		{
			$score++;
		}
		if (preg_match("/[a-z]/", $pwd) && preg_match("/[A-Z]/", $pwd))
		{
			$score++;
		}
		if (preg_match("/[0-9]/", $pwd))
		{
			$score++;
		}
		if (preg_match("/.[!,@,#,$,%,^,&,*,?,_,~,-,£,(,)]/", $pwd))
		{
			$score++;
		}
		
		return($strength[$score]);
	}




	function NumericScale($Scale)
	{
		if($Scale == "b")
		{
			return 1;
		}
		else if($Scale == "k")
		{
			return 2;
		}
		else if($Scale == "m")
		{
			return 3;
		}
		else if($Scale == "g")
		{
			return 4;
		}
		else if($Scale == "t")
		{
			return 5;
		}
		
		return 0;
	}

	function ConvertToScale($Value, $Scale, $NewScale)
	{	

		$Scale = substr(strtolower($Scale), 0, 1);
		$NewScale = substr(strtolower($NewScale), 0, 1);

		if($Scale == $NewScale)
		{
			return $Value;
		}

		if($this->NumericScale($Scale) < $this->NumericScale($NewScale) )
		{
			$this->ConvertFromBytes($Value, $Scale, $NewScale);
			return $Value;
		}
		else
		{
			return $this->ConvertToBytes($Value, $Scale, $NewScale);
		}
	}
	
        function ConvertToBytes($Value, $Scale, $StopValue="")
        {
		$Scale = strtolower($Scale);

		if($StopValue != "")
		{
			$StopValue = substr(strtolower($StopValue), 0, 1);
		}


                if( ($Scale == "t") || ($Scale == "tb") )
                {
                        $Value = $Value * 1024;
			
			if($StopValue == "g")
			{
				return $Value;
			}

                        $Value = $this->ConvertToBytes($Value, "g", $StopValue);
                }
                else if( ($Scale == "g") || ($Scale == "gb") )
                {
                        $Value = $Value * 1024;
			
			if($StopValue == "m")
			{
				return $Value;
			}

                        $Value = $this->ConvertToBytes($Value, "m", $StopValue);
                }
                else if( ($Scale == "m") || ($Scale == "mb") )
                {
                        $Value = $Value * 1024;
			
			if($StopValue == "k")
			{
				return $Value;
			}

                        $Value = $this->ConvertToBytes($Value, "k", $StopValue);
                }
                else if( ($Scale == "k") || ($Scale == "kb") )
                {
                        $Value = $Value * 1024;
			
			if($StopValue == "b")
			{
				return $Value;
			}

                        $Value = $this->ConvertToBytes($Value, "b", $StopValue);
                }

                return $Value;

        }


        function ConvertFromBytes(&$Value, &$Scale = "b", $StopValue = "")
        {

		if($StopValue != "")
		{
			$StopValue = strtolower(substr($StopValue, 0, 1));
		}

		//print "StopValue: ".$StopValue."<p>";

                if($Value < 1024)
                {
                        if($Scale != "b")
                        {
                                return number_format($Value, 2)." ".$Scale;
                        }
                        else
                        {
                                return number_format($Value, 0)." ".$Scale;
                        }
                }

                if( ($Scale == "b") )
                {
			$Scale = "Kb";
                }
                else if( ($Scale == "K") || ($Scale == "Kb") )
                {
               		$Scale = "Mb";     
             	}
                else if( ($Scale == "M") || ($Scale == "Mb") )
                {
               		$Scale = "Gb";     
                }
                else if( ($Scale == "G") || ($Scale == "Gb") )
                {
               		$Scale = "Tb";     
                }

             	$Value = $Value / 1024;
		if($StopValue == strtolower(substr($Scale, 0, 1)))
		{
			return number_format($Value, 2)." ".$Scale;
		}
                return $this->ConvertFromBytes($Value, $Scale, $StopValue);
        }

	function ConvertStringArrayToIPAddresses($StringArray)
	{
		$Count = count($StringArray);
		for($x = 0; $x < $Count; $x++)
		{
			$StringArray[$x] = $this->GetIPFromString($StringArray[$x]);
		}
	
		return $StringArray;
	}

	function GetIPFromString($StringToConvert)
	{
		if(substr($StringToConvert, 0, 1) == "(")
		{
			$StringToConvert = substr($StringToConvert, 1);
		}

		if(substr($StringToConvert, strlen($StringToConvert) -1, 1) == ")")
		{
			$StringToConvert = substr($StringToConvert, 0, strlen($StringToConvert) - 1);
		}


		$IP = filter_var($StringToConvert, FILTER_VALIDATE_IP);
		if($IP == $StringToConvert)
		{
			return $IP;
		}
	
		$URL = filter_var($StringToConvert, FILTER_SANITIZE_URL);
		$IP = gethostbyname($URL);

		if($IP != "")
		{
			return $IP;
		}
		return $StringToConvert;
	}

	function FixEmailFromEximAuthDataArray($DataArray)
	{
		$ArrayCount = count($DataArray);

		for($x = 0; $x < $ArrayCount; $x++)
		{
			$DataArray[$x] = $this->GetEmailFromEximAuthData($DataArray[$x]);
		}
		return $DataArray;
	}

	function GetEmailFromEximAuthData($Data)
	{
		if(substr($Data, 0, 11) == "auth_plain:")
		{
			return trim(substr($Data, 11));
		}
		else if(substr($Data, 0, 6) == "login:")
		{
			return trim(substr($Data, 6));
		}

		return $Data;
	}


	function GetCountryCode($IPAddress)
	{
		$options = array(
			'uri' => 'https://api.webcp.io',
			'location' => 'https://api.webcp.io/Country.php',
			'trace' => 1);
	
			$client = new SoapClient(NULL, $options);
		
		return $client->GetCountryCode($IPAddress);
	}

	function GetCountryName($CountryCode)
	{
		$Countries = $this->GetCountryCodeArray();
		return $Countries[strtoupper($CountryCode)];
	}
	


    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


	

	function GetCountryCodeArray()
	{

		$countries = array
		(
			'AF' => 'Afghanistan',
			'AX' => 'Aland Islands',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua And Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia And Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, Democratic Republic',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote D\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GG' => 'Guernsey',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island & Mcdonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic Of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IM' => 'Isle Of Man',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JE' => 'Jersey',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KR' => 'Korea',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States Of',
			'MD' => 'Moldova',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'ME' => 'Montenegro',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'BL' => 'Saint Barthelemy',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts And Nevis',
			'LC' => 'Saint Lucia',
			'MF' => 'Saint Martin',
			'PM' => 'Saint Pierre And Miquelon',
			'VC' => 'Saint Vincent And Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome And Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'RS' => 'Serbia',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia And Sandwich Isl.',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard And Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad And Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks And Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis And Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
		);

		return $countries;
	}
}		

