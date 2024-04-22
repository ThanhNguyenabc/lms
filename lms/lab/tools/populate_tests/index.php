<?php

chdir("../..");

include ".\application\lib\utils.php";

$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

$student = $_REQUEST["student"];

$data    = SQL_Query("SELECT classes.lesson_id, classes.date_start FROM classes_seats, classes WHERE student_id = $student AND classes_seats.class_id = classes.id", $db);


echo "<pre>";

function CreateResult($questions)
{
 $score     = 0;
 $result    = [];
 
 foreach($questions as $question)
 {  
  $id    = Storage_Path_RemoveExtension(Storage_Path_GetFilename($question));
   
  $token             = [];
  $token["question"] = $id;
  $token["score"]    =  rand(0, 10) / 10;
   
  array_push($result, $token);
   
  $score = $score + $token["score"];
 }
 $score = $score / count($questions);
  
 $result = json_encode($result);

 $data           = [];
 $data["score"]  = $score;
 $data["result"] = $result;
 
 return $data;
}


$db->beginTransaction();

foreach($data as $item)
{
 $result    = [];
 $lesson_id = $item["lesson_id"];
 
 // FOR EACH TEST RELATED TO A PAST LESSON...
 if($item["date_start"] < Date_Now())
 {
  if(file_exists("content/lessons/$lesson_id/test"))
  {
   $questions = Storage_Folder_ListFiles("content/lessons/$lesson_id/test", "*.dat") ?? [];
   
   // ...IF THERE IS A TEST FOR THIS LESSON...
   if(count($questions) > 0)
   {
    // ...CREATE AND ENTER TWO RESULTS
    for($i = 0; $i < 2; $i++)
    {	  
     $data      = CreateResult($questions);
     $result    = $data["result"];
     $score     = $data["score"];
    
     SQL_Query("INSERT INTO users_activities (student_id, source, mode, data, score) VALUES($student, '$lesson_id/test', 'test', '$result', $score)", $db); 
    }
   }
   
  }   
 }
}

$db->commit();


echo "<br><br>done";

?>