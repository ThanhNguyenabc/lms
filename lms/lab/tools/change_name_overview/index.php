<?php
chdir("../..");
//quet foler, doc tat ca cac file trong document folder va tim overwiew
function Change_name_overView()
{
   $folder    = 'content/lessons';

   $data = scandir($folder);
   foreach ($data as $item) {
      if (($item != '.') && ($item != '..')) {
         $documents = "$folder/$item/documents";
         if (file_exists($documents)) {
            foreach (scandir($documents) as $document) {
               if (($document != '.') && ($document != '..')) {

                  $name = strtolower($document);
                  if (strpos($name, "overview") === false) {
                  } else {
                     echo "$folder/$item/documents/" . $document . "   => $folder/$item/documents/Plan.pdf";
                     echo "<br>";
                     rename("$folder/$item/documents/$document","$folder/$item/documents/Plan.pdf");
                     // edit file info
                     $ini = file_get_contents("$folder/$item/info.dat");
                     $ini = str_replace($document,"Plan.pdf",$ini);

                     $lessonName = substr($document,0,strlen("$document")-4);
                     $ini = str_replace($lessonName,"Lesson Plan",$ini);

                     file_put_contents("$folder/$item/info.dat", $ini);
                     break;
                  }
               }
            }
         }
      }
   }
   return 1;
}

if (isset($_POST["change"])) {
   $result =  Change_name_overView();
   if ($result == 1) echo "Changed successfuly";
}
?>
<html>

<body>
   <p>Click to change the file name with "overview" to a file named "plan.pdf"</p>
   <form action="#" method="POST">
      <button type="submit" name="change" value="1" <?php if (isset($result)) echo "disabled"; ?>>Change</button>
   </form>

</body>

</html>