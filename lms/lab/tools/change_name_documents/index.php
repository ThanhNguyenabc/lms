<?php
chdir("../..");
define("page", <<<HTML
<html>
<body>
   <form action="#" method="POST">
      <p>Click to change all file name in content/lesson//document become new file name without lesson name(SJ- lesson)</p>
      Choose Lesson type (default SJ - Super Junior)
      <select name="lesson_option">
         <option value="SJ">SJ</option>
         <option value="all">Dont set</option>
      </select>
      <button type="submit" name="change_all" value="1" >Change All Document</button>
   </form>
</body>
</html>
HTML);

define("script", <<<SCRIPT
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
   <script>
      $("#btn-hide-changed").click(function(){
         $(this).css("display","none");
         $("#btn-show-changed").css("display","block");
         $(".has-new").parent().css("display","none");
      });
      $(".btn-delete-file").on("mouseover",function(){
         $(this).parent().parent().css('background',"#f5c8c8")
      });
      $(".btn-delete-file").on("mouseout",function(){
         $(this).parent().parent().css('background',"unset")
     });
      $("#btn-show-changed").click(function(){
         $(".has-new").parent().css("display","table-row");
         $(this).css("display","none");
         $("#btn-hide-changed").css("display","block");
      });
      $("input").on("change", function() {
         checkname = $(this).val().indexOf(".pdf");
         console.log(checkname);
         dir = $(this).attr("data-dir");
         id = $(this).attr("id");
         if(checkname > 0) {
             newname = $(this).val();
         }
         else  newname = $(this).val() + '.pdf';
         let oldname = '';
         if($(this).attr("data-new") == ''){
            
            oldname = $(this).attr("data-old");
         }else{
            oldname = $(this).attr("data-new");
         }
        
         console.log(newname)
         let url = $('#location').val();
         $.ajax({
            type: "POST",
            url: url,
            data: {dir:dir, oldname: oldname, newname : newname ,id:id}
         }).done(function(data){
            //change new name
            console.log(data);
            console.log(data.id);
            console.log(typeof(data.id));
            element = $("#"+data.id);
            console.log(element.parent());
            console.log(element.parent().parent().find('td').eq(1));
            element.parent().parent().find('td').eq(1).text(data.newname);
            element.attr("data-new",data.newname);
            element.attr("data-old",data.oldname);
         });
      });
      $(".btn-delete-file").on("click", function() {
         file = $(this).attr("data-file");
         id = $(this).attr("data-id");
         dir = $(this).attr("data-dir");
         let url = $('#location').val();
         $.ajax({
            type: "POST",
            url: url,
            data: {dir:dir,file:file ,id:id}
         }).done(function(data){
            console.log(data)
            element = $("#"+data.id);
            element.remove();
         });
      });
   </script>
SCRIPT);

function trimString($string, $countNoName = 0)
{
   preg_match('/[a-zA-Z0-9|\()]/', $string, $matches, PREG_OFFSET_CAPTURE);
   if ($matches) {
      $result = substr($string, $matches[0][1]);
      if ($result == "pdf" || $result == "docx" || $result == "docx") {
         if ($countNoName) return "No_name(" . $countNoName . ").$result";
         else return "No_name.pdf";
      } else return $result;
   }
   return $string;
}
// return position to substr filename
function checkReg($filename)
{
   $regArray = [
      '/[a-zA-Z]{2}\_\d\d\_\d\d\d/', //SC_01_610
      '/[a-zA-Z]{2}\.\d\d\.\d\d\d/', //SC.01.061
      '/[a-zA-Z]{2}\_\d\d\_\d\d/', //SC_01_61
      '/[a-zA-Z]{2}\_[a-zA-Z]{2}\_\d\d\d/', //SJ_PR_061
      '/[a-zA-Z]{1}\.\d\.\d\.\d/', //J.2.2.2
      '/[a-zA-Z]{1}\d\.[a-zA-Z]{1}\d\.[a-zA-Z]{1}\d/', //J2.U2.L2
      '/\slesson\s\d\d\d/', // lesson 097
      '/[\s|\_]lesson\s\d\d/', // lesson 97
      '/\slesson\s\d/', // lesson 4
      '/primary\s\d[a-zA-Z]{1}\s\d\d\d/', // primary 1B 046
      '/[a-zA-Z]{1}\d[a-zA-Z]{1}\s/', //P3C 

   ];
   $strRegArray = [9, 9, 8, 9, 7, 8, 11, 10, 9, 14, 4];
   foreach ($regArray as $key => $reg) {
      preg_match($reg, $filename, $matches, PREG_OFFSET_CAPTURE);
      if ($matches) {
         return $matches[0][1] + $strRegArray[$key];
      }
   }
   return 0;
}
function checkTrueLesson($lesson, $filename)
{
   $numberLesson = substr($lesson, -3);
   if (is_numeric($numberLesson)) {
      $numberLesson = intval($numberLesson);
      if (strpos($filename, $numberLesson) !== false) return true;
   }

   return false;
}
function Change_name_allDocuments($lessonType)
{
   $folder    = 'content/lessons';
   $historyChange = "$folder/changeDocumentNameHistory.html";
   $htmlPage = "<html><body><h3 style='color:#f7c845'> This page is history page , function manual change is not support .</h3>";
   $data = scandir($folder);
   echo "<div style ='position: fixed; right: 20px; top: 20px;'><button id='btn-hide-changed' style='background: darkorange'>Hide file can change</button><button id='btn-show-changed' style='display:none;background: darkseagreen'>Show file can change</button></div>";
   echo "<input type='hidden' id='location' value='" . $_SERVER["HTTP_REFERER"] . "'>";
   foreach ($data as $itemkey => $item) {
      if (($item != '.') && ($item != '..')) {
         $countNoName = 0;
         $countOverview = 0;
         $documents = "$folder/$item/documents";
         if (file_exists($documents) && strpos($item, $lessonType) !== false) {
            $echoTable = "<table style = 'border: 1px solid;width:100%'><h3 >$documents</h3><tr style = 'border: 1px solid;'><th style='width:30%'>Old</th><th style='width:30%'>new</th><th style='width:10%'>Action</th><th>Manual change</th></tr>";
            foreach (scandir($documents) as $key => $document) {
               if (($document != '.') && ($document != '..')) {
                  $echoItem = "<tr style = 'border: 1px solid;' id='" . $itemkey . "_" . $key . "'>";
                  $echoItem .= "<td  style = 'border: 1px solid;padding:5px;width:30%'>" . $document . "</td>";
                  $name = strtolower($document);
                  $newName = "";
                  if (strpos($name, "overview") !== false && $countOverview == 0) {
                     $newName = "Plan.pdf";
                     if (file_exists($documents . "/Plan.pdf")) {
                        $echoItem .= " <td style = 'padding:5px;width:30%' class='no-new'></td><td style = 'padding:5px;width:10%' class='no-new'>
                        <button class='btn-delete-file' data-dir = '" . $folder . "/" . $item . "' data-file = '" . $document . "' data-id = '" . $itemkey . "_" . $key . "'>Delete File</button>
                        </td>";
                     } else {
                        rename("$folder/$item/documents/$document", "$folder/$item/documents/$newName");

                        //edit file info
                        $ini = file_get_contents("$folder/$item/info.dat");
                        $ini = str_replace($document, $newName, $ini);
                        $lessonName = substr($document, 0, strlen("$document") - 4);
                        $ini = str_replace($lessonName, "Lesson Plan", $ini);
                        file_put_contents("$folder/$item/info.dat", $ini);
                        $echoItem .= " <td class='has-new' style = 'border: 1px solid;padding:5px;width:30%'> $newName </td><td></td>";
                     }
                     $countOverview++;
                  } else {
                     if (checkTrueLesson($item, $document)) {
                        $position = checkReg($name);
                        if ($position) {
                           $newName = trimString(substr($document, $position), $countNoName);
                           rename("$folder/$item/documents/$document", "$folder/$item/documents/$newName");

                           //edit file info
                           $ini = file_get_contents("$folder/$item/info.dat");
                           $ini = str_replace($document, $newName, $ini);
                           $enOldName  = substr($document, 0, strpos($document, ".pdf"));
                           $endNewName = substr($newName, 0, strpos($newName, ".pdf"));
                           $ini = str_replace($enOldName, $endNewName, $ini);
                           file_put_contents("$folder/$item/info.dat", $ini);
                           $echoItem .= " <td class='has-new' style = 'border: 1px solid;padding:5px;width:30%'> $newName </td><td></td>";
                        } else  $echoItem .= " <td style = 'padding:5px;width:30%' class='no-new'></td><td style = 'padding:5px;width:10%' class='no-new'><button class='btn-delete-file' data-file = '" . $folder . "/" . $item . "/" . $document . "' data-id = '" . $itemkey . "_" . $key . "'>Delete File</button></td>";
                     } else
                        $echoItem .= " <td style = 'padding:5px;width:30%' class='no-new'></td><td style = 'padding:5px;width:10%' class='no-new'>
                        <button class='btn-delete-file' data-dir = '" . $folder . "/" . $item . "' data-file = '" . $document . "' data-id = '" . $itemkey . "_" . $key . "'>Delete File</button>
                        </td>";
                  }
                  $echoItem .= "<td><input style='width:100%' type='text' data-dir = '" . $folder . "/" . $item . "' data-new='" . $newName . "'data-old='" . $document . "' placeholder = 'Enter change file name' name='change-name' id='" . $itemkey . "-" . $key . "'/></td>";
                  $echoItem .= "</tr>";
                  $echoTable .= $echoItem;
               }
            }
            $echoTable .= "</table>";
            echo $echoTable;
            $htmlPage .= $echoTable;
         }
      }
   }
   file_put_contents($historyChange, $htmlPage);
   return 1;
}

function Change_name_Document($oldname, $newname, $dir)
{
   if (file_exists($dir . "/documents/" . $oldname)) {
      rename($dir . "/documents/" . $oldname, $dir . "/documents/" . $newname);
      //edit file info
      $ini = file_get_contents("$dir/info.dat");
      $ini = str_replace($oldname, $newname, $ini);

      $enOldName  = substr($oldname, 0, strpos($oldname, ".pdf"));
      $endNewName = substr($newname, 0, strpos($newname, ".pdf"));
      $ini = str_replace($enOldName, $endNewName, $ini);

      file_put_contents("$dir/info.dat", $ini);
      //change file history html
      if(file_exists("changenamedocument.html"))
      {
         $ini = file_get_contents("changenamedocument.html");
         $id = str_replace("-","_",$_POST["id"]);
         $idPosition = strpos($ini,$id);
         $hasnewPosition = strpos($ini,"has-new",$idPosition);
         $textPosition = strpos($ini,$oldname,$hasnewPosition);
         $newIni = substr_replace($ini,$newname,$textPosition,strlen($oldname));
         file_put_contents("changenamedocument.html", $newIni);
      }
      return 1;
   } else return 0;
}

if (isset($_POST["change_all"])) {
   if ($_POST["lesson_option"] != "SJ") $lessonType = '';
   else $lessonType = $_POST["lesson_option"];
   $result =  Change_name_allDocuments($lessonType);
   echo script;
   if ($result == 1) echo "Changed successfuly";
}
if (isset($_POST["oldname"])) {
   header('Content-Type: application/json; charset=utf-8');
   $id = $_POST["id"];
   $dir = $_POST["dir"];
   $oldname = $_POST["oldname"];
   $newname = $_POST["newname"];
   $result = Change_name_Document($oldname, $newname, $dir);
   echo json_encode(["newname" => $newname, "oldname" => $oldname, "id" => $id]);
}


function DeleteContentHtml($id){
   if(file_exists("changenamedocument.html"))
    {
      $ini = file_get_contents("changenamedocument.html");
      $idPosition = strpos($ini,$id);
      $getIniEndidPosition = substr($ini,0,$idPosition);
      //echo $getIniEndidPosition;
      $trStartPosition = strrpos($getIniEndidPosition,"<tr");
      //echo substr($getIniEndidPosition,$trStartPosition);
      $trEndPosition = strpos($ini,"</tr>",$idPosition) + 5;
      $trContent = substr($ini,$trStartPosition,$trEndPosition -$trStartPosition);
      $ini = str_replace($trContent, '', $ini);
      file_put_contents("changenamedocument.html",$ini);
    }
}
if (isset($_POST["file"])) {
   $dir = $_POST["dir"];
   $file = $_POST["file"];
   header('Content-Type: application/json; charset=utf-8');
   unlink($dir."/documents/".$file);
   //edit file info
   $ini = file_get_contents("$dir/info.dat");
   $ini = str_replace($file, '', $ini);

   $enOldName  = substr($file, 0, strpos($file, ".pdf"));
   $ini = str_replace($enOldName, '', $ini);

   file_put_contents("$dir/info.dat", $ini);
   echo json_encode(["id" => $_POST["id"]]);

    //change file history html
    if(file_exists("changenamedocument.html"))
    {
      DeleteContentHtml($_POST["id"]);
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   echo page;
}
