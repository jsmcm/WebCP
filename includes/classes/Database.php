<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once("/var/www/html/webcp/vendor/autoload.php");

use \Matomo\Ini\IniReader;

class Database
{
     
    function __construct() 
    {
    }

    public function GetConnection()
    {
        global $DatabaseName;
        global $DatabaseHost;
        global $DatabaseUserName;
        global $DatabasePassword;

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
	$array = $reader->readFile("/var/www/html/config.php");

	$DatabaseName = $array["DATABASE_NAME"];
	$DatabaseHost = $array["DATABASE_HOST"];
	$DatabaseUserName = $array["DATABASE_USER"];
	$DatabasePassword = $array["DATABASE_PASSWORD"];

        try {
            $DBConnection = new PDO("mysql:dbname=".$DatabaseName.";host=".$DatabaseHost.";charset=utf8", $DatabaseUserName, $DatabasePassword);
            $DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $DBConnection;
        } catch(PDOException $e) {
            print "Cannot connect to database..";
        }

    }

     
     function FieldExists($TableName, $FieldName, $AllowedTypesArray)
     {

          $DatabaseConnection = $this->GetConnection();
                
          try
          {
               $query = $DatabaseConnection->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'cpadmin' AND column_name = :field_name AND table_name= :table_name");
               
               $query->bindParam(":field_name", $FieldName);
               $query->bindParam(":table_name", $TableName);
               $query->execute();
     
 
               if($result = $query->fetch(PDO::FETCH_ASSOC))
               {
                    // so far, so good, now check if it matches the field type...
                    for($x = 0; $x < count($AllowedTypesArray); $x++)
                    {
                         if( (isset($result["data_type"])) && ($AllowedTypesArray[$x] == $result["data_type"]) )
                         {
                              return 1;
                         }
                    }
     
                    // field found, but no matching data type...
                    return -1;
               }
     
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> FieldExists(); Error = ".$e);
          }
     
  
          
          return 0;
          
     }

     function CreateTableFromArray($TableName, $TableInfoArray, $nonceArray)  
     {  
          if ( $TableName == "" ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> CreateTableFromArray(); Table name cannot be blank in Database::TableExists");
               throw new Exception("<p><b>Table name cannot be blank in Database::CreateTableFromArray</b><p>");
          }

          if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> CreateTableFromArray(); Nonce not set");
               throw new Exception("<p><b>Nonce not set in Database::CreateTableFromArray</b><p>");
          }
          
          $oUser = new User();
          $ClientID = $oUser->getClientId();

          $nonceMeta = [
               $oUser->Role,
               $ClientID,
               $TableName
          ];

          $oSimpleNonce = new SimpleNonce();
          $nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "createTableFromArray", $nonceArray["TimeStamp"], $nonceMeta);

          if ( ! $nonceResult ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> CreateTableFromArray(); Nonce failed");
               throw new Exception("<p><b>Nonce failed in Database::CreateTableFromArray</b></p>");
          }

          $ColumnInfo = "";  

          $query = "CREATE TABLE ".$TableName." (";  

          for($x = 0; $x < count($TableInfoArray); $x++) {  
               $ColumnInfo = $ColumnInfo.$TableInfoArray[$x]["name"]." ".$TableInfoArray[$x]["type"]." ";  

               if($TableInfoArray[$x]["key"] != "") {  
                    $ColumnInfo = $ColumnInfo." ".$TableInfoArray[$x]["key"];  
               }  

               if( (isset($TableInfoArray[$x]["default"])) && ($TableInfoArray[$x]["default"] != "") ) {  
                    $ColumnInfo = $ColumnInfo." default ".$TableInfoArray[$x]["default"];  
               }  

               if($x < count($TableInfoArray) - 1) {  
                    $ColumnInfo = $ColumnInfo.", ";  
               }
          }

          $query = $query.$ColumnInfo.");";

          $this->DoSQL($query);
     }

     function TableExists($TableName, $nonceArray)
     {
          if ( $TableName == "" ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> TableExists(); Table name cannot be blank in Database::TableExists");
               throw new Exception("<p><b>Table name cannot be blank in Database::TableExists</b><p>");
          }

          if ( ! (is_array($nonceArray) && !empty($nonceArray) ) ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> TableExists(); Nonce not set");
               throw new Exception("<p><b>Nonce not set in Database::TableExists</b><p>");
          }
          
          $oUser = new User();
          $ClientID = $oUser->getClientId();

          $nonceMeta = [
               $oUser->Role,
               $ClientID,
               $TableName
          ];

          //print "in class<p>";
          //print "role: ".$oUser->Role."<p>";
          //print "ClientID: ".$ClientID."<p>";
          //print "Table: ".$TableName."<p>";

          $oSimpleNonce = new SimpleNonce();
          $nonceResult = $oSimpleNonce->VerifyNonce($nonceArray["Nonce"], "tableExists", $nonceArray["TimeStamp"], $nonceMeta);

          //print "nonceArray: ".print_r($nonceArray, true)."<p>";

          if ( ! $nonceResult ) {
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> TableExists(); Nonce failed");
               throw new Exception("<p><b>Nonce failed in Database::TableExists (".$TableName.")</b><p>");
          }


          try {
          
               $DBConnection = $this->GetConnection();

               $query = $DBConnection->prepare("SELECT TABLE_SCHEMA, TABLE_NAME FROM information_schema.tables WHERE table_name = :table_name;");
               $query->bindParam(":table_name", $TableName);
               
               $query->execute();
               if($result = $query->fetch(PDO::FETCH_ASSOC)) {
                    return true;
               }
               
          } catch(PDOException $e) {
          
               $oLog = new Log();
               $oLog->WriteLog("error", "/class.Database.php -> TableExists(); Error = ".$e);
          
          }
          
          return false;
     }




     
     function DoSQL($SQLCommand)
     {
               
          if( (strstr(strtolower($SQLCommand), "insert into")) || (strstr(strtolower($SQLCommand), "create ")) || (strstr(strtolower($SQLCommand), "alter")) || (strstr(strtolower($SQLCommand), "update")) || (strstr(strtolower($SQLCommand), "delete")) || (strstr(strtolower($SQLCommand), "drop")) ) {
               return $this->InsertUpdateStatement($SQLCommand);
          } else if( (strstr(strtolower($SQLCommand), "select ")) ) {
               return "error, Sorry, not yet implemented<p>";
          }
     }
     
     
     function InsertUpdateStatement($Statement)
     {
          $DatabaseConnection = $this->GetConnection();
     
          $result = true;
          
          try {
               $query = $DatabaseConnection->prepare($Statement);
               $query->execute();
          } catch(PDOException $e) {
               $result = false;
               $oLog = new Log();
               $oLog->WriteLog("error", "class.Database.php -> InsertUpdateStatement(); Error = ".$e);
          }
     
     
          return $result;
     }

     

     function AlterColumnType($Table, $Column, $NewType)
     {    
          $DatabaseConnection = $this->GetConnection();
     
          $result = true;
          
          try
          {
               $query = $DatabaseConnection->prepare("ALTER TABLE :table MODIFY :column :new_type");
               
               $query->bindParam(":table", $Table);
               $query->bindParam(":column", $Column);
               $query->bindParam(":new_type", $NewType);
               $query->execute();
          }
          catch(PDOException $e)
          {
               $result = false;
               $oLog = new Log();
               $oLog->WriteLog("error", "class.Database.php -> AlterColumnType(); Error = ".$e);
          }
     
     
     }
     
}
