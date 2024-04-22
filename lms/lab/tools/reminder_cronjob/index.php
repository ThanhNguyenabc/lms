<?php
chdir('../..');
error_reporting(E_ERROR | E_PARSE);
include_once "./application/lib/utils.php";
include_once "./application/modules/core/core.php";
include_once "./application/modules/users/users.php";
// include_once "lib-sendmail.php";
include_once "./application/modules/messages/messages.php";

$db           = Core_Database_Open();

$date_start = Date_Format_As(Date_Now(), "date-only") . "0000";
$date_end = Date_Format_As(Date_Now(), "date-only") . "2359";
 
$query = "SELECT classes.lesson_id, classes.center_id, classes.teacher_id, classes.ta1_id, classes.ta2_id, classes.ta3_id, courses.id, courses.name as course_name FROM classes LEFT JOIN courses ON classes.course_id = courses.id WHERE classes.date_start BETWEEN $date_start AND $date_end ";

$classes = SQL_Query($query, $db);

//GET ELT 
$centers = array_unique(array_column($classes,"center_id"));
$centerFormat = SQL_Format_IN($centers,$db);

$queryElt = "SELECT id, center from users WHERE role = 'desk' AND center IN ($centerFormat)";

$eltUsers = SQL_Query($queryElt, $db);

$eltByCenter = [];
foreach ($eltUsers as $key => $user) {
  if($user["center"]) $eltByCenter[$user["center"]][] = $user["id"];
}

SQL_Close($db);

$lessons = array_column($classes,"lesson_id");

$taskConfig = parse_ini_file("partners/default/admin-task.cfg",true);

$teacherTask = $taskConfig["teacher admin"];
$taTask = $taskConfig["ta admin"];
$eltTask = $taskConfig["elt admin"];

foreach ($lessons as $k => $lesson) {

  // TEACHER AND TA
  foreach ($teacherTask as $task => $lessonList) {
    $lessonArr = explode(",",$lessonList);
    if(in_array($lesson,$lessonArr))
    {
      $userids = [];
      if($classes[$k]["teacher_id"]) $userids[] = $classes[$k]["teacher_id"]; 
      if($classes[$k]["ta1_id"]) $userids[] = $classes[$k]["ta1_id"]; 
      if($classes[$k]["ta2_id"]) $userids[] = $classes[$k]["ta2_id"]; 
      if($classes[$k]["ta3_id"]) $userids[] = $classes[$k]["ta3_id"]; 
      if(count($userids)) Messages_Send_Multiple(138, $userids, strtoupper($task . ', lesson: ' . $lesson . ', courses: ' . $classes[$k]["course_name"]), $task);
    } 
  }

  // TA
  foreach ($taTask as $task => $lessonList) {
    $lessonArr = explode(",",$lessonList);
    if(in_array($lesson,$lessonArr))
    { 
      $userids = [];
      if($classes[$k]["ta1_id"]) $userids[] = $classes[$k]["ta1_id"]; 
      if($classes[$k]["ta2_id"]) $userids[] = $classes[$k]["ta2_id"]; 
      if($classes[$k]["ta3_id"]) $userids[] = $classes[$k]["ta3_id"]; 
      if(count($userids)) Messages_Send_Multiple(138, $userids, strtoupper($task . ', lesson: ' . $lesson . ', courses: ' . $classes[$k]["course_name"]), $task);
    } 
  }

  // ELT
  foreach ($eltTask as $task => $lessonList) {
    $lessonArr = explode(",",$lessonList);
    if(in_array($lesson,$lessonArr))
    {
      $center = $classes[$k]["center_id"];
      if(isset($eltByCenter[$center]))
      Messages_Send_Multiple(138, $eltByCenter[$center], strtoupper($task . ', lesson: ' . $lesson . ', courses: ' . $classes[$k]["course_name"]), $task);
    } 
  }

}

?>