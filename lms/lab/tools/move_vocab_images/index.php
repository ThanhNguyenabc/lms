<?php
chdir("../..");
define("script", <<<SCRIPT
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
   <script>
      
   $(document).ready(function (e) {
      $('#imageUploadForm').on('submit',(function(e) {
          e.preventDefault();
          var formData = new FormData(this);
  
          $.ajax({
              type:'POST',
              url: $(this).attr('data-action'),
              data:formData,
              cache:false,
              contentType: false,
              processData: false,
              success:function(data){
                  console.log("success");
                  console.log(data);
                  data = JSON.parse(data);
                  item = data.item;
                  $('#link-image-'+data.id).text('content/vocabulary/'+item+'/picture.png');
                  $('#image-'+data.id).attr('src','content/vocabulary/'+item+'/picture.png?t='+ new Date().getTime());
              },
              error: function(data){
                  console.log("error");
                  console.log(data);
              }
          });
      }));
  
      $("#fileToUpload").on("change", function() {
          $("#imageUploadForm").submit();
      });
      $("#save-page").click(function(){
         console.log($("#tableImage"))
         htmlContent = $("#tableImage").html();
         console.log(typeof htmlContent)
         $.ajax({
            type:'POST',
            url: $("#tableImage").attr('data-action'),
            data: {html: htmlContent},
            success:function(data){
                console.log("success");
                console.log(data);
                alert("Saved success");
            },
            error: function(data){
                console.log("error");
                console.log(data);
            }
        });
      });
  });
      
   </script>
SCRIPT);
function add_Image_Vocab()
{
   $folderImage = 'content/vocabimages';
   $listImage = scandir($folderImage);
   
   echo "<html>";
   echo "<div style ='position: fixed; right: 20px; top: 20px;'><button id='save-page' style='background: darkorange'>Save page</button></div>";
   echo "<div id='tableImage' data-action = '".$_SERVER['HTTP_REFERER']."'><table><tr><th>Folder</th><th>Link image</th><th>Image</th><th>Upload</th>";
   
   //var_dump($listImage);
   //echo (in_array("banana.png",$listImage));
   $folder    = 'content/vocabulary';
   $data = scandir($folder);
   foreach ($data as $itemkey => $item) {
      if (($item != '.') && ($item != '..')) {
         $infoFile = $folder."/".$item."/info.dat";
         if(file_exists($infoFile) && !file_exists("$folder/$item/picture.png")){
            $infoIni = parse_ini_file($infoFile,true);
            $imageName = $infoIni["info"]["en"];
            preg_match('/^[a-zA-Z]{1}$/', $imageName,$match);
            if($match){
               $imageName = strtoupper($imageName);
            }
            if(in_array("$imageName.png",$listImage)){
               echo "<tr ><td>". $item. " </td><td id='link-image-$itemkey'>  ".$folderImage."/".$imageName.".png </td><td> <img id='image-$itemkey' width='50px' height='50px' src='".$folderImage."/".$imageName.".png'/>" ."</td><td>
               <form  method='POST' id='imageUploadForm' data-action = '".$_SERVER['HTTP_REFERER']."'>
               <input type='hidden' name='folder' value='".$item."'/>
               <input type='hidden' name='folderId' value='".$itemkey."'/>
               <input type='file' name='fileToUpload' id='fileToUpload'>
               </form></td></tr>";
               //copy("$folderImage/$imageName.png","$folder/$item/picture.png");
            }
            else{
               // check fuzzy search
               $shortest = -1;
               $guestImage = '';

               foreach ($listImage as $key => $image) {
                  if(($image != '.') && ($image != '..'))
                  {
                     if((strpos(strtolower($image),strtolower($imageName)) !== false)) {
                        $guestImage  = $image;
                        $shortest = -99;
                        break;
                     }
                     
                     $lev = levenshtein($imageName,$image);
                     if ($lev <= $shortest || $shortest < 0) {
                        // set the closest match, and shortest distance
                        $guestImage  = $image;
                        $shortest = $lev;
                     }
                  } 
               }
               
               if($shortest == -99)
               {
                  echo "<tr><td style='border:1px solid'>".$item."</td><td id='link-image-$itemkey'>$folderImage/$guestImage</td><td><img id='image-$itemkey' width='50px' height='50px' src='$folderImage/$guestImage'/></td>
               <td>
               <form  method='POST' id='imageUploadForm' data-action = '".$_SERVER['HTTP_REFERER']."'>
               <input type='hidden' name='folder' value='".$item."'/>
               <input type='hidden' name='folderId' value='".$itemkey."'/>
               <input type='file' name='fileToUpload' id='fileToUpload'>
               </form></td></tr>";
               //copy("$folderImage/$guestImage","$folder/$item/picture.png");
               }
               else{
                  echo "<tr><td style='border:1px solid'>".$item."</td><td id='link-image-$itemkey' style='border:1px solid;color:red'>$folderImage/$guestImage</td><td><img id='image-$itemkey' width='50px' height='50px' src='$folderImage/$guestImage'/></td><td>
               <form  method='POST' id='imageUploadForm' data-action = '".$_SERVER['HTTP_REFERER']."'>
               <input type='hidden' name='folder' value='".$item."'/>
               <input type='hidden' name='folderId' value='".$itemkey."'/>
               <input type='file' name='fileToUpload' id='fileToUpload'>
               </form></td></tr>";
               } 
            } 
         }
      }
   }
   echo "</table></div> ".script."</html>";
   return 1;
}

if(isset($_POST["submit"])){
   add_Image_Vocab();
}
if(isset($_POST["folder"])){
   $img = $_FILES['fileToUpload']['name'];
   $tmp = $_FILES['fileToUpload']['tmp_name'];
   $vocabFolder = $_POST["folder"];
   $id = $_POST["folderId"];
   // get uploaded file's extension
   if(move_uploaded_file($tmp,"content/vocabulary/".$vocabFolder."/picture.png"))
   echo json_encode(["item"=>$vocabFolder,"id" => $id]);
   else echo json_encode([]);
}
if(isset($_POST["html"])){
   $pageHtml = $_POST["html"];
   $pageHtml = "<h3>".date("d/m/Y H:i:s") ."</h3><br>" . $pageHtml;
   $f = fopen("historyImageVocab.html",'a+');
   fwrite($f,$pageHtml);
   echo 1;
}
define("page", <<<HTML
<html>
   <body>
      <p>Click to generate image to vocabulary dont have image</p>
      <form action="#" method="POST">
            <button name="submit" value="1">Run</button>
      </form>
   </body>
</html>
HTML);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   if(file_exists("historyImageVocab.html")){
      $page = file_get_contents("historyImageVocab.html");
      $tablePosition = strrpos($page,"<table>");
      $htmlResult = substr($page,$tablePosition);
      $pageLoad = "<div style ='position: fixed; right: 20px; top: 20px;'><button id='save-page' style='background: darkorange'>Save page</button></div><div id='tableImage' data-action ='moveImageVocab.php'>".$htmlResult ."</div>".script;
      echo $pageLoad;
   }
   else
   echo page;
}
?>
