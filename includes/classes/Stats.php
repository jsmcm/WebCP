<?php
/*********************************************************************
*********************************************************************/
if(!isset($_SESSION)) 
{
     session_start();
}

include_once($_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php");


class Stats
{
    var $oDatabase = null;
    var $DatabaseConnection = null;

    function __construct()
    {

        try {

            $this->oDatabase = new Database();
            $this->DatabaseConnection = $this->oDatabase->GetConnection();

     	} catch (Exception $e) {

	    if ($e->getMessage() == "class.Database->getConnection Matomo\Ini not found") {
		print "<h1>Missing dependencies</h1><p>I'm going to try and install them. This might take several minutes. Please wait 30 minutes then try again</p><p>If you've already waited 30 minutes and still see this please contact support@webcp.io for support</p>";

                touch($_SERVER["DOCUMENT_ROOT"]."/nm/composer_install");
            } else {
                print "<h1>Unknown Error</h1><p>Something bad happened and I can't continue. Please contact support@webcp.io for support</p>";
            }

            exit();   		    
		    
        }  
    }


     function getStats($statType)
     {
          $statsArray = array();

          try
          {
		$query = $this->DatabaseConnection->prepare("SELECT * FROM server_stats WHERE stat_type =:stat_type AND date BETWEEN :date1 AND :date2 ORDER BY date ASC");

		$query->bindParam(":stat_type", $statType);

		$yesterday = time() - (24 * 60 * 60);

		$date1 = date("Y-m-d H:i:s", $yesterday);
		$date2 = date("Y-m-d H:i:s");
		$query->bindParam(":date1", $date1);
		$query->bindParam(":date2", $date2);

               	$query->execute();

		$x = 0;
               	while($result = $query->fetch(PDO::FETCH_ASSOC)) {
                    	$statsArray[$x]["id"] = $result["id"];
                    	$statsArray[$x]["stat_type"] = $result["stat_type"];
                    	$statsArray[$x]["total"] = $result["total"];
                    	$statsArray[$x]["used"] = $result["used"];
                    	$statsArray[$x]["available"] = $result["available"];
                    	$statsArray[$x++]["date"] = $result["date"];
               	}
          }
          catch(PDOException $e)
          {
               $oLog = new Log();
               $oLog->WriteLog("error", "/classes/Stats.php -> getStats(); Error = ".$e);
	  }

	  return $statsArray;
     }

}
