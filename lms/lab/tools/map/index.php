<?php

chdir("../..");

include ".\application\lib\utils.php";

$map = [];

$lessons = Storage_Folder_ListFolders("content/lessons");

$components = ["bookpage", "presentation", "test", "dialogue", "cover.png"];
foreach($lessons as $lesson)
{
 $row    = [];
 $row[0] = $lesson;
 
 foreach($components as $folder)
 {
  if(file_exists("content/lessons/$lesson/$folder"))
  {
   $data = '"x"';
  }
  else
  {
   $data = '""';	  
  }
  
  array_push($row, $data);
 }
 
 array_push($map, $row);
}


$file = "lesson," . implode(",", $components);

foreach($map as $row)
{
 $line = implode(",", $row);
 $file = $file . $line ."\r\n";
}

file_put_contents("map.csv", $file);

?>