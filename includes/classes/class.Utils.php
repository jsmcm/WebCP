<?php
class Utils
{



	function ValidateHash($Hash, $LicenseKey)
	{
		$Formula = substr($Hash, strlen($Hash) - 2, 2);
		$Hash = substr($Hash, 0, strlen($Hash) - 2);


		$FormulaNumber = hexdec($Formula);
		$FormulaNumber = $FormulaNumber & 0x03;

                   if($FormulaNumber == 1)
                   {
                       $NewHash = "";

                       for($x = strlen($Hash) - 1; $x >= 0; $x--)
                       {
                           $NewHash = $NewHash.$Hash[$x];
                       }
                      
                   }
                   else if($FormulaNumber == 2)
                   {
                       $NewHash = "";

                       for($x = strlen($Hash) - 1; $x >= 0; $x--)
                       {
                           $NewHash = $NewHash.$Hash[$x];
                       }

                       $Hash = $NewHash;
			$NewHash = "";

                       for($x = 0; $x < strlen($Hash); $x = $x + 2)
                       {
                            $NewHash = $NewHash.$Hash[$x + 1].$Hash[$x];
                       }


                   }
                   else if($FormulaNumber == 3)
                   {
                       $NewHash = "";

                       for($x = 0; $x < strlen($Hash); $x = $x + 2)
                       {
                            $NewHash = $NewHash.$Hash[$x + 1].$Hash[$x];
                       }

                   }
                   else
                   {
                       $NewHash = $Hash;
                   }
	
		$RemoteIP = gethostbyname("bug.webcp.pw");

		for($x = 1; $x < 11; $x++)
		{
			$CalculatedHash = md5($LicenseKey.$RemoteIP.$_SERVER["SERVER_ADDR"].$x);

			if($CalculatedHash == $NewHash)
			{
				return true;
			}
		}

		return false;
				
	}
 	
	function GetValidationHash($LicenseKey)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
		$oDomain = new Domain();

		$AccountsCreated = $oDomain->GetAccountsCreatedCount();

		//print "AccountsCreated: ".$AccountsCreated."<p>";
                $options = array(
                'uri' => 'http://api.webcp.pw/',
                'location' => 'http://api.webcp.pw/updates/check.php',
                'trace' => 1);

                $client = new SoapClient(NULL, $options);
                return $client->GetValidationHash($LicenseKey, $AccountsCreated);
	}

        
	function GetTrafficStats(&$TotalTraffic, &$TotalUsed, &$TotalAvailable, &$PercentageUsed)
        {
		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
		$oPackage = new Package();

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Settings.php");
		$oSettings = new Settings();

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
		$oDomain = new Domain();

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
		$oUtils = new Utils();
		
		$TotalTraffic = $oSettings->GetServerTrafficAllowance();
		$TotalUsed = $oDomain->GetPackageTrafficUsage();
		
		$PercentageUsed = $TotalUsed / $TotalTraffic * 100;
		$TotalAvailable = $TotalTraffic - $TotalUsed;

	}



	function GetDiskSpaceStats(&$TotalDiskSpace, &$TotalUsed, &$TotalAvailable, &$PercentageUsed)
	{
		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Package.php");
		$oPackage = new Package();

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Domain.php");
		$oDomain = new Domain();

		require_once($_SERVER["DOCUMENT_ROOT"]."/includes/classes/class.Utils.php");
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
                'uri' => 'http://webcp.pw/api',
                'location' => 'http://webcp.pw/api/Country.php',
                'trace' => 1);

                $client = new SoapClient(NULL, $options);
                return $client->GetCountryCode($IPAddress);
	}

	function GetCountryName($CountryCode)
	{
		$Countries = $this->GetCountryCodeArray();
		return $Countries[strtoupper($CountryCode)];
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

?>
