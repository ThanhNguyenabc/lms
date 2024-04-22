<?php

chdir("..\..");

include "application\lib\utils.php";

$lessons = Storage_Folder_ListFolders("content/lessons");

foreach($lessons as $lesson)
{
 echo "content\\lessons\\$lesson\\cover.png<br>";
}

?>