<?php

chdir("../..");

include ".\application\lib\utils.php";

$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

$student = $_REQUEST["student"];

$data    = SQL_Query("SELECT classes.lesson_id, classes.date_start FROM classes_seats, classes WHERE student_id = $student AND classes_seats.class_id = classes.id", $db);


echo "<pre>";

$db->beginTransaction();

foreach($data as $item)
if($item["date_start"] < Date_Now())
{
 $result    = [];
 $lesson_id = $item["lesson_id"];
 
 if(file_exists("content/lessons/$lesson_id/info.dat"))
 {
  $file      = parse_ini_file("content/lessons/$lesson_id/info.dat", true);
  $terms     = array_values($file["vocabulary"] ?? []);
  
  $score     = 0;
  foreach($terms as $term)
  {  
   $token          = [];
   $token["term"]  = $term;
   $token["score"] =  rand(0, 10) / 10;
   
   array_push($result, $token);
   
   $score = $score + $token["score"];
  }
  $score = $score / count($terms);
  
  $result = json_encode($result);
  
  SQL_Query("INSERT INTO users_activities (student_id, source, mode, data, score) VALUES($student, '$lesson_id/vocabulary', 'test', '$result', $score)", $db); 
 }
}

$db->commit();


echo "<br><br>done";

?>