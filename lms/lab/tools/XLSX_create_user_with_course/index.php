<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
include_once "api.php";


if (isset($_POST['submit'])) {
    $data = json_decode($_POST['data']);
    foreach ($data as $key => $userinfo) {
        // create user
        $user = [
            "firstname" => $userinfo->firstname,
            "lastname" => $userinfo->lastname,
            "midname" => $userinfo->midname,
            "email" => $userinfo->email,
            "role" => $userinfo->role
        ];
        //check user exist
        $users = Users_Search(["fields"=>"id","email"=> $user["email"],"count"=>1]);
        if(!$users)
        {
            // check role local to change to teacher role when create user
            $userCustom = $user;
            if($user["role"] == "local") $userCustom["role"] = "teacher";
            $userid = User_Create($userCustom);
            echo "USER ID: ".$userid." --  ROLE: ".$user["role"]. "  -- EMAIL: ".$user["email"]. " --  NAME: ".$user["lastname"]." ".$user["firstname"]."<br/>"; 
            //assign to course
            $courseid = $userinfo->courseid ?? 0;
            if($courseid && $userid){
                if($user["role"] == "student"){
                    $seat = Courses_Students_Add($courseid,$userid,false,0,["checkseats"=>true]);
                    if($seat == "no seats") echo " No seats in course to assign student. <br/>";
                }else {
                    $classes = Courses_Classes_List($courseid,"197001010000","210001012359", $fields = "*");
                    // check local classes and teacher classes
                    $classesCustom = [];
                    switch ($user["role"]) {
                      case 'teacher':
                        foreach ($classes as $key => $class) {
                          if($class["teacher_type"] == "native") array_push($classesCustom,$class);
                        }
                        break;
                      case 'local':
                        foreach ($classes as $key => $class) {
                          if($class["teacher_type"] == "local") array_push($classesCustom,$class);
                        }
                        break;
                      default:
                        $classesCustom = $classes;
                        break;
                    }
                    $classes = array_column($classesCustom, 'id');
                    $staffs = Courses_Read($courseid,"staff");
                    $position = $user["role"]."_id";

                    if($staffs["staff"])
                    {
                        $staffs = $staffs["staff"];
                        if($user["role"] == "ta"){
                            if(!array_key_exists("ta1_id",$staffs)) {
                                $staffs["ta1_id"] = $userid;
                                $position = "ta1_id";
                            }else 
                            if(!array_key_exists("ta2_id",$staffs)) {
                                $staffs["ta2_id"] = $userid;
                                $position = "ta2_id";
                            }
                            else 
                            if(!array_key_exists("ta3_id",$staffs)) {
                                $staffs["ta3_id"] = $userid;
                                $position = "ta3_id";
                            }
                        }else{
                            $staffs[$position] = $userid;
                        }
                    }    
                    else {
                        $staffs = [];
                        if($user["role"] == "ta"){
                            $staffs["ta1_id"] = $userid;
                            $position = "ta1_id";
                        }else{
                            $staffs[$position] = $userid;
                        }
                    }
                    if($user["role"] == "local") $position = "teacher_id";
                    Courses_Staff_Set($classes,$position,$userid);
                    Courses_Update_Field($courseid,"staff",$staffs,true);
                }
            } 
        }
        else{
            $userid = $users[0]["id"];
            $courseid = $userinfo->courseid ?? 0;
            if($courseid && $userid){
                if($user["role"] == "student"){
                    $seat = Courses_Students_Add($courseid,$userid,false,0,["checkseats"=>true]);
                    if($seat == "no seats") echo " No seats in course to assign student. <br/>";
                }else {
                    $classes = Courses_Classes_List($courseid,"197001010000","210001012359", $fields = "*");
                    // check local classes and teacher classes
                    $classesCustom = [];
                    switch ($user["role"]) {
                      case 'teacher':
                        foreach ($classes as $key => $class) {
                          if($class["teacher_type"] == "native") array_push($classesCustom,$class);
                        }
                        break;
                      case 'local':
                        foreach ($classes as $key => $class) {
                          if($class["teacher_type"] == "local") array_push($classesCustom,$class);
                        }
                        break;
                      default:
                        $classesCustom = $classes;
                        break;
                    }
                    $classes = array_column($classesCustom, 'id');
                    $staffs = Courses_Read($courseid,"staff");
                    $position = $user["role"]."_id";

                    if($staffs["staff"])
                    {
                        $staffs = $staffs["staff"];
                        if($user["role"] == "ta"){
                            if(!array_key_exists("ta1_id",$staffs)) {
                                $staffs["ta1_id"] = $userid;
                                $position = "ta1_id";
                            }else 
                            if(!array_key_exists("ta2_id",$staffs)) {
                                $staffs["ta2_id"] = $userid;
                                $position = "ta2_id";
                            }
                            else 
                            if(!array_key_exists("ta3_id",$staffs)) {
                                $staffs["ta3_id"] = $userid;
                                $position = "ta3_id";
                            }
                        }else{
                            $staffs[$position] = $userid;
                        }
                    }    
                    else {
                        $staffs = [];
                        if($user["role"] == "ta"){
                            $staffs["ta1_id"] = $userid;
                            $position = "ta1_id";
                        }else{
                            $staffs[$position] = $userid;
                        }
                    }
                    if($user["role"] == "local") $position = "teacher_id";
                    Courses_Staff_Set($classes,$position,$userid);
                    Courses_Update_Field($courseid,"staff",$staffs,true);
                }
            } 
            echo "EMAIL: ".$user["email"]. " already exist <br/>"; 
        }
    }

    echo "Action Successfuly";
}
if (isset($_POST['submitdownload'])) {

    if(file_exists("tools/XLSX_create_user_with_course/user_list.xlsx")) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename("tools/XLSX_create_user_with_course/user_list.xlsx").'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize("tools/XLSX_create_user_with_course/user_list.xlsx"));
        flush(); 
        readfile("tools/XLSX_create_user_with_course/user_list.xlsx");
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        #createForm {
            display: none;
        }
    </style>
</head>

<body onload="init()">
    <p>Choose file lesson info </p>
    <input id="fileInput" type="file" name="file" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
    <pre id="fileContent"></pre>
    <form action="" method="post" id="createForm">
        <input type="hidden" name="data" id="data">
        <input type="submit" name="submit" value="create" id="submit" />
    </form>
    <p>Download example list user </p>
    <form action="" method="post" id="download">
        <input type="hidden" name="examplefile" id="examplefile">
        <input type="submit" name="submitdownload" value="download" id="submitdownload" />
    </form>

</body>
<script>
    function init() {
        document.getElementById('fileInput').addEventListener('change', handleFileSelect, false);
    }

    async function handleFileSelect(e) {
        const file = e.target.files[0];
        const data = await file.arrayBuffer();
        /* data is an ArrayBuffer */

        const workbook = XLSX.read(data);

        document.getElementById('data').value = toJson(workbook);
        console.log(toJson(workbook));
        document.getElementById("submit").click();
    }

    function toJson(workbook) {
        var json
        workbook.SheetNames.forEach(function(sheetName) {
            json = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
                defval: ""
            });
        });
        //console.log(json)
        return JSON.stringify(json);
    }
</script>

</html>