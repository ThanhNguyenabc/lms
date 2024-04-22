<?php
chdir("../..");
$folder = "content/lessons";
$data = scandir($folder);
foreach ($data as $key => $lesson) {
    $check2h = strpos($lesson,"2h-");
    if($lesson != "."  && $lesson != ".." && $check2h !== false)
    {
        $position = strpos($lesson,"-");
        $newname = substr($lesson,0,$position) . substr($lesson,$position + 1, strlen($lesson) - $position);
       
        echo $newname . "</br>";
        rename("$folder/$lesson","$folder/$newname");
    }
}
?>