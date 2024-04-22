<?php
chdir("../..");

if(isset($_POST["submit-user"]))
{
  $userFolder = "partners/default/users/";
  $usersList = scandir($userFolder);
  foreach ($usersList as $key => $userF) {
    if($userF != ".." && $userF != ".")
    {
      $propic = $userFolder . $userF . "/propic.jpg";
      if(is_dir($userFolder . $userF) && !file_exists($propic))
      {
        copy("resources/images/default/propic-generic.png",$propic);
        echo "<div>$userF</div></br>";
      } 
    }
  }
}
else 
if(isset($_POST["submit-lesson"]))
{
  $lessonFolder = "content/lessons/";
  $lessons = scandir($lessonFolder);
  foreach ($lessons as $key => $lesson) {
    if($lesson != ".." && $lesson != ".")
    {
      $cover = $lessonFolder . $lesson . "/cover.png";
      if(!file_exists($cover))
      {
        copy("resources/images/cover-lesson.jpg",$cover);
        echo "<div>$lesson</div></br>";
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>

<head>
</head>

<body>
    <form action="" method="post">
      <p>Click to add default cover image to lesson miss cover image</p>
      <input type="submit" name="submit-lesson" value="lesson" id="submit1" />
      <p>Click to add default profile image to user miss profile image</p>
      <input type="submit" name="submit-user" value="user" id="submit2" />
    </form>
</body>

</html>