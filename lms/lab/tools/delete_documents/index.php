<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
function view_delete_file()
{
   $folder    = 'content/lessons';
   echo "<div style ='position: fixed; right: 20px; top: 20px;'><form method='POST' action='#'><button name='delete' value='1'>Delete All</button></form></div>";
   $data = scandir($folder);
   foreach ($data as $item) {
      if (($item != '.') && ($item != '..')) {
         $documents = "$folder/$item/documents";
         if (file_exists($documents) && strpos($item, "SJ") !== false) {
            echo "<h3>".$folder."/".$item."</h3></br>";
            foreach (scandir($documents) as $document) {
               if (($document != '.') && ($document != '..')) {
                  $nameExtension = substr($document,strlen($document)-3);
                  if ($nameExtension != "pdf") {
                     echo "<p>".$document ."</p></br>";
                    // unlink("$documents/$document");
                  }
               }
            }
         }
      }
   }
}
function delete_file()
{
   $folder    = 'content/lessons';
   $data = scandir($folder);
   $check = 0;
   foreach ($data as $item) {
      if (($item != '.') && ($item != '..')) {
         $documents = "$folder/$item/documents";
         if (file_exists($documents) && strpos($item, "SJ") !== false) {
            foreach (scandir($documents) as $document) {
               if (($document != '.') && ($document != '..')) {
                  $nameExtension = substr($document,strlen($document)-3);
                  if ($nameExtension != "pdf") {
                     $check = 1;
                     unlink("$documents/$document");
                  }
               }
            }
         }
      }
   }
   if($check == 1)
   echo "Success";
   else echo "File not found.";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   view_delete_file();
}

if($_POST["delete"]){
   delete_file();
}
?>

