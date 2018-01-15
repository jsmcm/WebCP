<?php

$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/fail2ban/perm.dat", "w");
fwrite($fp, str_rot13($_POST["FileContents"]));
fclose($fp);

header("location: perm.php?Notes=File%20Saved.&FileName=".$_POST["FileName"]);
?>
