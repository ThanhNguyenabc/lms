<?php

define("SENDMAIL_SERVICE_URL",$_SERVER["SERVER_NAME"]."/services/sendmail/index.php");


function checkReminder($userid,$courseid,$type){
    $file = "partners/default/users/$userid/reminder.dat";
    $check = true;
    if(file_exists($file)){
        $content = parse_ini_file($file,true);
        if(array_key_exists("course-$courseid",$content))
        {
            if(array_key_exists($type,$content["course-$courseid"])) $check = false;
            else $content["course-$courseid"][$type] = "0";
        } 
        else  {
            $content["course-$courseid"][$type] = "0";
            $check = true;
        }
    }
    else
    {
        if(!is_dir("partners/default/users/$userid")) mkdir("partners/default/users/$userid");
        touch($file);
        $content["course-$courseid"][$type] = "0";
        $check = true;
    } 
    if($check) Ini_File_Write($file, $content);
    return $check;
}

/*
option:[
    'role': 'ta','staff','student'
    'type': calling, near-end , near-begin 
]
*/
function CustomInfomation($courseid,$userid,$option = []){
    //Get user infomation
    $db           = Core_Database_Open();

    $userquery = "SELECT * FROM users WHERE id = $userid";
    $users = SQL_Query($userquery, $db);
    $user = $users[0] ?? [];

    $coursequery = "SELECT courses.name as course_name, courses.date_start, courses.center_id FROM courses
    WHERE courses.id = $courseid ";
    $courseclass = SQL_Query($coursequery, $db);
    $course = $courseclass[0] ?? [];

    // setting url
	//Hard code recive email for testing - will remove when golive
    $user["email"] = "nhatnamnbk@gmail.com";
    // $course["course_name"] = "Test course 2";
    // $course["date_start"] = "202305290000";
    // $course["center_id"] = "TC-HCM1";
    $result = [];

    if(!empty($user) && !empty($course)){
        if($option["role"] == "staff"){
            switch ($option["type"]) {
                case 'near-begin':
                    $subject = "Notice to start the course : ". $course["course_name"];
                    $template = "staffnearbegin";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "datestart" => $course["date_start"] ,
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                        "center" => $course["center_id"] ,
                    ];
                    
                    break;
                case 'near-end':
                    $subject = "Notice to near end of the course:". $course["course_name"];
                    $template = "staffnearend";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                        "center" => $course["center_id"] ,
                    ];
                    
                    break;
                case 'calling':
                    $subject = "Notice to monthly calling of the course:". $course["course_name"];
                    $template = "calling";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                case 'mmtest':
                    $subject = "Notice to Mid Module Test of the course:". $course["course_name"];
                    $template = "mmtest";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                case 'eomtest':
                    $subject = "Notice to End Of Module/Course Test of the course:". $course["course_name"];
                    $template = "eomtest";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                default:
                    # code...
                    break;
            }
        }
        else
        if($option["role"] == "ta"){
            switch ($option["type"]) {
                case 'near-begin':
                    $subject = "Notice to start the course : ". $course["course_name"];
                    $template = "tanearbegin";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "datestart" => $course["date_start"] ,
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                        "center" => $course["center_id"] ,
                    ];
                    
                    break;
                case 'near-end':
                    $subject = "Notice to near end of the course:". $course["course_name"];
                    $template = "tanearend";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                        "center" => $course["center_id"] ,
                    ];
                    
                    break;
                case 'calling':
                    $subject = "Notice to monthly calling of the course:". $course["course_name"];
                    $template = "calling";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                case 'mmtest':
                    $subject = "Notice to Mid Module Test of the course:". $course["course_name"];
                    $template = "mmtest";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                case 'eomtest':
                    $subject = "Notice to End Of Module/Course Test of the course:". $course["course_name"];
                    $template = "eomtest";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                default:
                    # code...
                    break;
            }
        }
        else
        if($option["role"] == "student"){
            switch ($option["type"]) {
                case 'near-begin':
                    $subject = "Notice to start the course : ". $course["course_name"];
                    $template = "studentnearbegin";
                    $data = [
                        "coursename"=> $course["course_name"],
                    ];
                    
                    break;
                case 'near-end':
                    $subject = "Notice to near end of the course:". $course["course_name"];
                    $template = "studentnearend";
                    $data = [
                        "coursename"=> $course["course_name"],
                        "username" => $user["lastname"].' '.$user["midname"].' '.$user["firstname"],
                    ];
                    
                    break;
                default:
                    # code...
                    break;
            }
        }
        $result = [
            "email" => $user["email"],
            "subject" => $subject,
            "template" => $template,
            "data" => $data
        ];
    }
    
    
    SQL_Close($db);

    return $result;
}

function SendMailAndCreateMessage($courseid,$userid,$option = []){
    //Get user infomation
    $information = CustomInfomation($courseid,$userid,$option);
    if(!empty($information)){
        $subject = $information["subject"];
        $options = ["template" => $information["template"],"type"=>NULL,"expiration"=>NULL];
        $text = $information["data"];

        $post = [
            "to" => json_encode($information["email"]),
            "subject" => json_encode($information["subject"]),
            "template" => json_encode($information["template"]),
            "data" => json_encode($information["data"])
        ];

        if(checkReminder($userid,$courseid,$option["type"]) && $information["email"] != null){
            //call service sendmail
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, SENDMAIL_SERVICE_URL);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query($post));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            
            curl_close($curl);
            // create message
            Messages_Create('138',$userid,$subject,$text,$options);
        }
    }
    
}

function SendMailAndCreateMessageStaff($courseid,$option = []){
    $db           = Core_Database_Open();

    $coursequery = "SELECT courses.name as course_name, courses.date_start, courses.center_id FROM courses
    WHERE courses.id = $courseid ";
    $courseclass = SQL_Query($coursequery, $db);
    $course = $courseclass[0] ?? [];

    if(!empty($course)){
        $center = $course["center_id"];

        $staffquery = "SELECT id FROM users
        WHERE users.center = '$center' AND role in ('desk')";
        $staffs = SQL_Query($staffquery, $db);
        
        foreach ($staffs as $key => $value) {
            SendMailAndCreateMessage($courseid,$value["id"],$option);
        }
    }
    

    SQL_Close($db);
}

function CreateMessage($courseid,$userid,$option = []){
    //Get user infomation
    $information = CustomInfomation($courseid,$userid,$option);
    $subject = $information["subject"];
    $options = ["template" => $information["template"]];
    $text = $information["data"];
    
    if(checkReminder($userid,$courseid,$option["type"])){
        Messages_Create('138',$userid,$subject,$text,$options);
    }
    
}


function SendMail($courseid,$userid,$option = []){
    //Get user infomation
    $information = CustomInfomation($courseid,$userid,$option);

    $to = json_encode($information["email"]);
    $subject = json_encode($information["subject"]);
    $template = json_encode($information["template"]);
    $data = json_encode($information["data"]);
    
    $post = [
        "to" => $to,
        "subject" => $subject,
        "template" => $template,
        "data" => $data
    ];

    if(checkReminder($userid,$courseid,$option["type"]) && $information["email"] != null){
        //call service sendmail
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, SENDMAIL_SERVICE_URL);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,http_build_query($post));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
    
        curl_close($curl);
    }
    
}
