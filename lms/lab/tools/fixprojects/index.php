<?php

chdir("../..");

include "application/lib/utils.php";

$folders = Storage_Folders_Collect("./content/characters");

foreach($folders as $folder)
{
 $name = Storage_Path_GetFilename($folder);
 
 $info = [];
 $info["info"]["name"] = ucwords($name);
 
 Ini_File_Write("$folder/info.dat", $info);
 
 echo $name;
 echo "<br>";
}


?>