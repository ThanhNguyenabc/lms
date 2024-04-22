<?php

chdir("../..");

include ".\application\lib\utils.php";

$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

$student = $_REQUEST["student"];

$data    = SQL_Query("SELECT classes_seats.id, classes.lesson_id, classes.date_start FROM classes_seats, classes WHERE student_id = $student AND classes_seats.class_id = classes.id", $db);



$all_badges = array_keys(parse_ini_file("application/modules/classroom/attract.dat", true));



echo "<pre>";

$db->beginTransaction();

foreach($data as $item)
{
 $lesson   = parse_ini_file("content/lessons/" . $item["lesson_id"] . "/info.dat", true);
 $outcomes = array_values($lesson["outcomes"] ?? []);
 
 
 // ASSESSMENT
 $assessment = [];
 
 // outcomes
 foreach($outcomes as $outcome)
 {
  $assessment[$outcome] = rand(1, 5);
 }
 
 // skills
 foreach(["reading", "writing", "listening", "speaking"] as $skill)
 {
  $assessment[$skill] = rand(1, 5);
 }
 
 $assessment = json_encode($assessment);
 
 
 // BADGES
 $badges     = [];
 $n          = rand(0, 5);
 for($i = 0; $i < $n; $i++)
 {
  $badge = $all_badges[array_rand($all_badges)];
  array_push($badges, $badge);
 }  
 $badges = json_encode($badges);

 
 // BEHAVIOR
 $behavior   = rand(1, 5);
 
 
 // ATTENDANCE 
 if($item["date_start"] > Date_Now())
 {
  $attendance = "";
 }
 else
 {
  $n = rand(1, 100);
  
  if($n == 1) $attendance = "miss";
  else
  if($n > 1 && $n < 75) $attendance = "yes";
  else
  $attendance = "late";
 }
 
 
 // UPDATE SEAT
 $seat_id    = $item["id"];
 SQL_Query("UPDATE classes_seats SET attendance = '$attendance', behavior = '$behavior', badges = '$badges', assessment = '$assessment' WHERE id = $seat_id", $db); 
}

$db->commit();


echo "<br><br>done";

?>