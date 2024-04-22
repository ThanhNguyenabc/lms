<?php
chdir("../..");
define("page", <<<HTML
<html>
<body>
    <p> Enter the class id to create students activities fake data</p>
   <form action="#" method="POST">
      <input type="text" name="class_id" namespace="Class ID"/>
      <button type="submit" name="submit" value="1" >Submit</button>
   </form>
</body>
</html>
HTML);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo page;
}

if(isset($_POST["submit"])){
    include ".\application\lib\utils.php";

    $partner = $_REQUEST["partner"] ?? "default";
    $config  = parse_ini_File("partners/$partner/system.cfg", true);
    $db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

    $classid = $_REQUEST["class_id"];

    $data    = SQL_Query("SELECT classes.lesson_id, classes.date_start, classes_seats.student_id FROM classes_seats, classes WHERE classes.id = $classid AND classes_seats.class_id = classes.id", $db);


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
    if($item["date_start"] < Date_Now())
    {
        $result    = [];
        $lesson_id = $item["lesson_id"];
        $student   = $item["student_id"];
        // VOCAB
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

        //TEST
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

    $db->commit();
    echo page;
    echo "<br><br>done class ".$_POST["class_id"];
}