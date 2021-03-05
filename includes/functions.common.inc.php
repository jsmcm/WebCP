<?php
  
function CheckForVariablesFile() 
{

  if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/Variables.inc.php"))
  {
    return 1;
  }
  
  if(file_exists($_SERVER["DOCUMENT_ROOT"]."/includes/VariablesSamples.inc.php"))
  { 
    copy($_SERVER["DOCUMENT_ROOT"]."/includes/VariablesSamples.inc.php", $_SERVER["DOCUMENT_ROOT"]."/eclfiles/includes/Variables.inc.php");
    return 0;
  }




  // Nothing exists, create a default Variables file now.
    $fh = fopen($_SERVER["DOCUMENT_ROOT"]."/includes/Variables.inc.php", 'w') or die("can't open file (2)");
      fwrite($fh, "<?\r\n\r\n");
      fwrite($fh, "// Database settings\r\n");
      fwrite($fh, "\$DatabaseServerAddress = \"localhost\";\r\n");
      fwrite($fh, "\$DatabaseName = \"\";\r\n");
      fwrite($fh, "\$DatabaseUserName = \"\";\r\n");
      fwrite($fh, "\$DatabasePassword = \"\";\r\n");
      fwrite($fh, "\r\n");
      fwrite($fh, "?>\r\n");
    fclose($fh);
    chmod($_SERVER["DOCUMENT_ROOT"]."/includes/Variables.inc.php", 0755);
    
    return 0;

}
