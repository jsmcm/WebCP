<?php
/*
function RecallIssuedTags($TagUserNumbers, $SessionID)
function GetNextTempIssuedTagsInsertOrder($SessionID)
function GetAdminName($UserID)
function InsertTempIssuedTag($TagData, $SessionID)
function InsertIntoTagData($TagData, $UserNumber)
function ClearTempIssuedTags($SessionID)
function ClearTempReadTags()
function GetTempReadTags($StartNumber)
function GetTempIssuedTags($StartNumber, $SessionID)
function TempIssuedTagExists($TagData, $SessionID)
function GetColumnValue($TagData, $ColumnNameID)
function GetTagDataID($TagData)
function NextUserNumber($UserCharacter, $UserNumber)
function DeleteStaleTempIssuedTags()
function DeleteTagDataSegment($UserCharacter)
function UpdateAdminShowIssuedTags()
function GetUserNumber()
function GetExistingUserNumber($TagData)
function GetColumnValueID($TagDataID, $ColumnNameID)
function GetChildName($ParentID)
function GetChildColumnNameID($ParentID)
function ReplaceSpecialChars($StringToFix)
function ColumnValueMatches($TagID, $ColumnValueID)
function GetReaderID($TagData)
function GetReaderName($ReaderID)
function GetSurveyorID($TagData)
function GetSurveyorName($SurveyorID)
function GetIssueDate($TagData)
function GetIssueTime($TagData)
function GetDateDiff($TagData)
function GetSeedDate($TagData)
function NextCharacter($Character)
function TestTagInReports()
function ChangeTestTagInReportsStatus($Status)
function ReaderGroupings($FoundAtReader, &$GroupingReaderTwo, &$GroupingAction);
function TagFoundAtReader($TagData, $ExpectedReader)
function UpdateElapsedDays($TagData, $SeedDate)
function GetDateDifference($StartDate, $EndDate)
function GenericInsertOrUpdateQuery($Query)
function GetAdminIDByEmail($EmailAddress);

*/



function GenericInsertOrUpdateQuery($Query)
{
    ini_set('date.timezone', 'Africa/Johannesburg');
    
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    //print $query."<p>";
    $Result = pg_query($Query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    //pg_close($link);
}


function UpdateElapsedDays($TagData, $SeedDate)
{

global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT * FROM ReadTags WHERE TagData = '".$TagData."' AND Deleted = 0;";
    //print $query."<br>";
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    $ItemID = "";
    $Days = "";
    $ReadDate = "";
    $SeedDate = substr($SeedDate, 0, 4)."/".substr($SeedDate, 4,2)."/".substr($SeedDate, 6, 2);
    
    while($line = pg_fetch_assoc($result))
    {
    
      $ItemID = $line["itemid"];
      $Days = $line["days"];
      $ReadDate = substr($line["readdate"], 0, 4)."/".substr($line["readdate"], 4,2)."/".substr($line["readdate"], 6, 2);
    
    
      
      //print "<p>ItemID: ".$ItemID."<br>";
      //print "Days: ".$Days."<br>";
      //print "ReadDate: ".$ReadDate."<br>";
      //print "SeedDate: ".$SeedDate."<br>";
      $Diff = GetDateDifference($SeedDate, $ReadDate);
      //print "Difference: ".$Diff['days']."<p>";
    
      //print "UPDATE ReadTags SET Days = ".$Diff['days']." WHERE ItemID = ".$ItemID.";";
      GenericInsertOrUpdateQuery("UPDATE ReadTags SET Days = ".$Diff['days']." WHERE ItemID = ".$ItemID.";");
    }
    
    
}

function GetDateDifference($StartDate, $EndDate)
{
    $uts['StartDate']      =    strtotime( $StartDate );
    $uts['EndDate']        =    strtotime( $EndDate );
    if( $uts['StartDate']!==-1 && $uts['EndDate']!==-1 )
    {
        if( $uts['EndDate'] >= $uts['StartDate'] )
        {
            $diff    =    $uts['EndDate'] - $uts['StartDate'];
            if( $days=intval((floor($diff/86400))) )
                $diff = $diff % 86400;
            if( $hours=intval((floor($diff/3600))) )
                $diff = $diff % 3600;
            if( $minutes=intval((floor($diff/60))) )
                $diff = $diff % 60;
            $diff    =    intval( $diff );            
            return( array('days'=>$days, 'hours'=>$hours, 'minutes'=>$minutes, 'seconds'=>$diff) );
        }

    }
    else
    {
        trigger_error( "Invalid date/time data detected", E_USER_WARNING );
    }
    return( false );
}
        

function TagFoundAtReader($TagData, $ExpectedReader)
{

global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ItemID FROM ReadTags WHERE TagData = '".$TagData."' AND ReaderID = '".$ExpectedReader."' AND Deleted = 0;";
    //print $query."<br>";
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        // was found here
        return 1;

    }
    else
    {
        pg_close($link);
        // was not found here
        return 0;
    
    }
    
}


function ReaderGroupings($FoundAtReader, &$GroupingReaderTwo, &$GroupingAction)
{

global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT SecondReaderID, Action FROM ReaderGrouping WHERE FirstReaderID = '".$FoundAtReader."' AND Deleted = 0;";
    //print $query."<br>";
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        $GroupingReaderTwo = $line[0];
        $GroupingAction = $line[1];

    }
    else
    {
        pg_close($link);
        $GroupingReaderTwo = "";
        $GroupingAction = "";
    
    }
    
  
}

require_once("Variables.inc.php");

if(!isset($_SESSION["SessionID"]))
{
    $_SESSION["SessionID"] = $_SERVER["REMOTE_ADDR"];
}

if($_SESSION["SessionID"] == "")
{
    $_SESSION["SessionID"] = $_SERVER["REMOTE_ADDR"];
}

//print "<p><b>".$_SESSION["SessionID"]."</b></p>";

function RecallIssuedTags($TagUserNumbers, $SessionID)
{
  global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    $TagUserNumbers = $TagUserNumbers.",";
    
    /* Connecting, selecting database */
    
    
    $Buffer = "";
    
    $x = 0;
    
    print "Start with ".$TagUserNumbers."<p>";
    
    while(strlen($TagUserNumbers) > 0)
    {
        
        if(substr($TagUserNumbers, $x, 1) != ",")
        {
            $Buffer = $Buffer.substr($TagUserNumbers, $x, 1);
            $x++;
        }
        else
        {
            
            print "Buffer = ".$Buffer."<br>";
            
            
            $TagUserNumbers = substr($TagUserNumbers, $x + 1);
            print "TagUserNumbers = ".$TagUserNumbers."<p>";
            
            
            $x = 0;
            
            if(trim($TagUserNumbers == ","))
            {
                $TagUserNumbers = "";
            }
            
            $query = "SELECT TagData FROM TagData WHERE UserCharacter = '".substr($Buffer, 0, 1)."' AND UserNumber = ".substr($Buffer, 1).";";
            $Buffer = "";
            $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
            $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");
        
            print $query."<br>";
            if($line = pg_fetch_row($result))
            {
                print "Tag (".$line[0].") found<br>";
                if(TempIssuedTagExists($line[0], $SessionID) == 0)
                {       
                    print "Tag Does not Exist in temp file<br>";
                    InsertTempIssuedTag($line[0], $SessionID);
                }
                else
                {
                    print "Tag does exist in temp file<br>";
                }
            }
            else
            {
                print "Tag Not Found<p>";
            }
        }
    
    }

    //pg_close($link);

}
    
    
    
function GetAdminIDByEmail($EmailAddress)
{
global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT AdminID FROM Admin WHERE Email = '".$EmailAddress."' AND Deleted = 0 LIMIT 1;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        return $line[0];
    }
    else
    {
        return -1;
    } 
    
}
    
    
function GetNextTempIssuedTagsInsertOrder($SessionID)
{
global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT InsertOrder FROM TempIssuedTags WHERE SessionID = '".$SessionID."' OR SessionID = 'Issuer' ORDER BY InsertOrder DESC LIMIT 1;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0] + 1;
    }
    else
    {
        pg_close($link);
        return 1;
    }  
}

    
function TestTagInReports()
{
global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT * FROM DisplayTestTag;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return 1;
    }  
} 

function ChangeTestTagInReportsStatus($Status)
{
global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "DELETE FROM DisplayTestTag;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

      
    
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "INSERT INTO DisplayTestTag VALUES (".$Status.");";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

      
      
}     
    
    
    
function GetAdminName($UserID)
{
  
  global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;
    
    if($UserID == 0)
    {
        return "System";
    }
    else
    {
    
        $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
        

        $query = "SELECT firstname, surname FROM Admin WHERE adminid = ".$UserID." AND Deleted = 0 LIMIT 1";
        $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    
        if($line = pg_fetch_assoc($result))
        {
            return trim($line["firstname"])." ".trim($line["surname"]);
        }
        else
        {
          return "Can't read name";
        }
        
    }
    
}



    

function InsertTempIssuedTag($TagData, $SessionID)
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    ini_set('date.timezone', 'Africa/Johannesburg');
    
    
    $query = "INSERT INTO TempIssuedTags VALUES (".GetNextTempIssuedTagsInsertOrder($SessionID).", '".$TagData."', '".$SessionID."', ".date("Ymd").");";
    
    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
  
    print $query;
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
}



function InsertIntoTagData($TagData, $UserNumber)
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    ini_set('date.timezone', 'Africa/Johannesburg');
    
    $UserCharacter = substr($UserNumber, 0, 1);
    $UserNumber = substr($UserNumber, 1);
    
    
    $query = "INSERT INTO TagData (TagDataID, TagData, UserCharacter, UserNumber, Deleted) VALUES (nextval('TagDataID'), '".$TagData."', '".$UserCharacter."', ".$UserNumber.", 0);";
    
    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    print $query;
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    return GetTagDataID($TagData);
}




function ClearTempIssuedTags($SessionID)
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    if($_COOKIE["ShowIssuedTags"] == "true")
    {
      $query = "DELETE FROM TempIssuedTags WHERE SessionID = '".$SessionID."' OR SessionID = 'Issuer'";
    }
    else
    {
      $query = "DELETE FROM TempIssuedTags WHERE SessionID = '".$SessionID."'";
    }
    
    
    //print $query;
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
}









function GetTempReadTags($StartNumber)
{
	global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    
    $query = "SELECT * FROM TempReadTags WHERE InsertOrder > ".$StartNumber." ORDER BY InsertOrder ASC;";

    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    while($line = pg_fetch_row($result))
    {
      print $line[1].";".$line[2].";".$line[3].";".$line[4].";".$line[5]."\r\n";
    }

    pg_close($link);
    
}
    
    
    
function ClearTempReadTags()
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    $query = "DELETE FROM TempReadTags;";
    
    
    
    //print $query;
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
}



function GetTempIssuedTags($StartNumber, $SessionID)
{
	global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    if(!isset($_COOKIE["ShowIssuedTags"]))
    {
      setcookie("ShowIssuedTags", "", $expire, "/");
    }
    
    if($_COOKIE["ShowIssuedTags"] == "true")
    {
      $query = "SELECT * FROM TempIssuedTags WHERE InsertOrder > ".$StartNumber." AND (SessionID = '".$SessionID."' OR SessionID = 'Issuer');";
    }
    else
    {
      $query = "SELECT * FROM TempIssuedTags WHERE InsertOrder > ".$StartNumber." AND SessionID = '".$SessionID."';";
    }
    
    //print "<p><b>".$query."</b><p>";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    while($line = pg_fetch_row($result))
    {
            print $line[1]."<br>";
    }

    pg_close($link);
    
}

function TempIssuedTagExists($TagData, $SessionID)
{
	global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT * FROM TempIssuedTags WHERE TagData = '".$TagData."' AND SessionID = '".$SessionID."';";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return 1;
    }
    else
    {
        pg_close($link);
        return 0;
    }
    
}


function GetColumnValue($TagData, $ColumnNameID)
{
global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    $TagDataID = GetTagDataID($TagData);
    
    if($TagDataID == "-1")
    {
        return "";
    }
    
    $ColumnValueID = GetColumnValueID($TagDataID, $ColumnNameID);
    
    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ColumnValueTitle FROM ColumnValues WHERE Deleted = 0 AND ColumnValueID = ".$ColumnValueID.";";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
    
}



function GetTagDataID($TagData)
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT TagDataID FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        return $line[0];
    }
    else
    {
        return "-1";
    }
    
    
}




function NextUserNumber($UserCharacter, $UserNumber)
{

    //print "In NextUserNumber<p>";
    if($UserNumber < 9999)
    {
        $UserNumber++;
    }
    else
    {
        $UserNumber = 1;
        
        if($UserCharacter != 'Z')
        {
            //print "Before, UserCharacter = ".$UserCharacter."<p>";
            $UserCharacter = NextCharacter($UserCharacter);
            //print "After, UserCharacter = ".$UserCharacter."<p>";
        }
        else
        {
            $UserCharacter = "A";
        }
        
        DeleteTagDataSegment($UserCharacter);
    }
    
    return $UserCharacter.$UserNumber;
}


function DeleteStaleTempIssuedTags()
{
    ini_set('date.timezone', 'Africa/Johannesburg');
    
        global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    $query = "DELETE FROM TempIssuedTags WHERE Date < ".(date("Ymd"));
    
    //print $query."<p>";
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
}

function DeleteTagDataSegment($UserCharacter)
{
    ini_set('date.timezone', 'Africa/Johannesburg');
    
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    $query = "UPDATE TagData SET Deleted = 1, UpdateDate = ".date("Ymd")." WHERE UserCharacter = '".$UserCharacter."'";
    
    //print $query."<p>";
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
}

function UpdateAdminShowIssuedTags()
{

    if($_COOKIE["ShowIssuedTags"] == "true")
    {
      $ShowIssuedTags = 1;
    }
    else
    {
      $ShowIssuedTags = 0;
    }
    
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    /* Connecting, selecting database */
    
    $query = "UPDATE Admin SET ShowIssuedTags = ".$ShowIssuedTags." WHERE AdminID = ".$_SESSION["AdminID"];
    
    //print $query."<p>";
    $Result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: Adm_Adm_3");
    
    pg_close($link);
    
}



function GetUserNumber()
{
    
    //print "In GetUserNumber<p>";
    $UserNumber = "";
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT UserCharacter FROM TagData ORDER BY UserCharacter DESC;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        $UserNumber = $line[0];
    }
    else
    {
        $UserNumber = "A";
    }
    
    //print "UserNumber so far..".$UserNumber."<p>";
    
    pg_close($link);

    $query = "SELECT UserNumber FROM TagData WHERE UserCharacter = '".$UserNumber."' ORDER BY UserNumber DESC;";
    
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        $UserNumber = NextUserNumber($UserNumber, $line[0]);
    }
    else
    {
        $UserNumber = $UserNumber."1";
    }
    
    //print "UserNumber so far..".$UserNumber."<p>";
  
    
    return $UserNumber;
    
}

function GetExistingUserNumber($TagData)
{
    /* Connecting, selecting database */
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT UserCharacter, UserNumber FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0;";
    
    //print $query."<br>";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    
    if($line = pg_fetch_row($result))
    {
        //pg_close($link);
        return $line[0].$line[1];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
    
}


function GetColumnValueID($TagDataID, $ColumnNameID)
{
    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ColumnValueID FROM TagDataColumns WHERE TagDataID = ".$TagDataID." AND ColumnNameID = ".$ColumnNameID." AND Deleted = 0;";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "-1";
    }
    
    
}




function GetChildName($ParentID)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ColumnName FROM ColumnNames WHERE ParentID = ".$ParentID." AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}


function GetChildColumnNameID($ParentID)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ColumnNameID FROM ColumnNames WHERE ParentID = ".$ParentID." AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "-1";
    }
    
}




function ReplaceSpecialChars($StringToFix)
{
    for($StringToFixPos = 0; $StringToFixPos < strlen($StringToFix); $StringToFixPos++)
    {
        if( substr($StringToFix, $StringToFixPos, 1) == " " || substr($StringToFix, $StringToFixPos, 1) == "/" || substr($StringToFix, $StringToFixPos, 1) == "\\" || substr($StringToFix, $StringToFixPos, 1) == "#")
        {
            $StringToFix = substr($StringToFix, 0, $StringToFixPos)."_".substr($StringToFix, $StringToFixPos + 1);
        }
        
    }
    
    return $StringToFix;
            
}
        
        

function ColumnValueMatches($TagID, $ColumnValueID)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT * FROM TagDataColumns WHERE TagDataID = ".$TagID." AND Deleted = 0 AND ColumnValueID = ".$ColumnValueID;
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return "True";
    }
    else
    {
        pg_close($link);
        return "False";
    }
    
}


function GetReaderID($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ExpectedReader FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}
    
    
function GetReaderName($ReaderID)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT ReaderName FROM ReaderName WHERE HardWareID = '".$ReaderID."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}



function GetSurveyorID($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT Surveyor FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "-1";
    }
    
}
    

function GetSurveyorName($SurveyorID)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT FirstName, Surname FROM Surveyor WHERE SurveyorID = ".$SurveyorID;
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0]." ".$line[1];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}
    
    
    
    

function GetIssueDate($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT SeedDate FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}
    

function GetIssueTime($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT SeedTime FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "";
    }
    
}
    
    
    
    
    
    
    

function GetDateDiff($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */
    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT Days FROM ReadTags WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        return $line[0];
    }
    else
    {
        pg_close($link);
        return "NA";
    }
    
}



function GetSeedDate($TagData)
{

    global $DatabaseName, $DatabaseUserName, $DatabasePassword, $DatabaseHost;

    /* Connecting, selecting database */

    $link = pg_connect("host=".$DatabaseHost." dbname=".$DatabaseName." user=".$DatabaseUserName." password=".$DatabasePassword."") or die("Could not connect to the database, please email the administrator. Err Reference: Adm_Adm_1");
    
    $query = "SELECT SeedDate, SeedTime FROM TagData WHERE TagData = '".$TagData."' AND Deleted = 0";
    
    $result = pg_query($query) or die("Query failed, please email the administrator. Err Reference: 1");

    //print $query."<br>";
    if($line = pg_fetch_row($result))
    {
        pg_close($link);
        
        if($line[1] != "0")
        {
            return trim($line[0]);
        }
        else
        {
            return trim($line[0]);
        }
    }
    else
    {
        pg_close($link);
        return "-1";
    }
    
}

        
function NextCharacter($Character)
{

    if($Character == "A")
    {
        $Character = "B";
    }
    else if($Character == "B")
    {
        $Character = "C";
    }
    else if($Character == "C")
    {
        $Character = "D";
    }
    else if($Character == "D")
    {
        $Character = "E";
    }
    else if($Character == "E")
    {
        $Character = "F";
    }
    else if($Character == "F")
    {
        $Character = "G";
    }
    else if($Character == "G")
    {
        $Character = "H";
    }
    else if($Character == "H")
    {
        $Character = "I";
    }
    else if($Character == "I")
    {
        $Character = "J";
    }
    else if($Character == "J")
    {
        $Character = "K";
    }
    else if($Character == "K")
    {
        $Character = "L";
    }
    else if($Character == "L")
    {
        $Character = "M";
    }
    else if($Character == "M")
    {
        $Character = "N";
    }
    else if($Character == "N")
    {
        $Character = "O";
    }
    else if($Character == "O")
    {
        $Character = "P";
    }
    else if($Character == "A")
    {
        $Character = "B";
    }
    else if($Character == "P")
    {
        $Character = "Q";
    }
    else if($Character == "Q")
    {
        $Character = "R";
    }
    else if($Character == "R")
    {
        $Character = "S";
    }
    else if($Character == "S")
    {
        $Character = "T";
    }
    else if($Character == "T")
    {
        $Character = "U";
    }
    else if($Character == "U")
    {
        $Character = "V";
    }
    else if($Character == "V")
    {
        $Character = "W";
    }
    else if($Character == "W")
    {
        $Character = "X";
    }
    else if($Character == "X")
    {
        $Character = "Y";
    }
    else if($Character == "Y")
    {
        $Character = "Z";
    }

    return $Character;

}


?>
