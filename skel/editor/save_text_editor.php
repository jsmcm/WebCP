<?php

$fp = fopen($_POST["FileName"], "w");
fwrite($fp, str_rot13($_POST["FileContents"]));
fclose($fp);

header("location: text_editor.php?Notes=File%20Saved.&FileName=".$_POST["FileName"]);
?>
