<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
ini_set('memory_limit', '4096M');
ini_set('max_execution_time', '1500');
/**
 * @param $src - a valid file location
 * @param $dest - a valid file target
 * @param $targetWidth - desired output width
 * @param $targetHeight - desired output height or null
 */
function createThumbnail($src, $dest, $targetWidth, $targetHeight = null)
{

   list($width, $height) = getimagesize($src);
   if ($targetHeight == null)
      $targetHeight = number_format($targetWidth * $height / $width, 2);;
   // create duplicate image based on calculated target size
   $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

   $source = imagecreatefromstring(file_get_contents($src));

   // copy entire source image to duplicate image and resize
   imagecopyresampled(
      $thumbnail,
      $source,
      0,
      0,
      0,
      0,
      $targetWidth,
      $targetHeight,
      $width,
      $height
   );
   imagepng($thumbnail, $dest);
}
function updateImgThumbnail()
{
   $folder = "content/lessons";
   $lessons = scandir($folder);
   echo "<html>";
   foreach ($lessons as $item) {
      if (($item != '.') && ($item != '..')) {
         $imageLink = "$folder/$item/cover.png";
         $imageOR = "$folder/$item/cover_OR.png";
         if (file_exists($imageLink) && !file_exists($imageOR)) {
            echo "$imageLink</br>";
            //change name 
            rename($imageLink, $imageOR);
            //create thumbnail
            if (!file_exists($imageLink) && file_exists($imageOR)) {
               $imagick = new Imagick($imageOR);
               $d = $imagick->getImageGeometry();

               $w = $d['width'];
   
               $h = $d['height'];
               $newHeight = 600 * $h / $w;
               $imagick->resizeImage(600,$newHeight,Imagick::FILTER_LANCZOS,1);
               $imagick->writeImage($imageLink);
            }
         }
      }
   }
   echo "finish";
   echo "</html>";
}

define("page", <<<HTML
<html>
<body>
   <form action="#" method="POST">
      <p>Click to resize 'cover.png' in lessons</p>
      <button type="submit" name="resize" value="1" >Resize</button>
   </form>
</body>
</html>
HTML);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   echo page;
}

if(isset($_POST["resize"])) updateImgThumbnail();
