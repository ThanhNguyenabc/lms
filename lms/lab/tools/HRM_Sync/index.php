<?php

chdir("../..");
ini_set('max_execution_time', '30');
include ".\application\lib\utils.php";
define("KEYS","tools/HRM_Sync/keys.dat");
define("PROFILES","tools/HRM_Sync/profiles.dat");
define("LOG","tools/HRM_Sync/".date("Ymd")."log.dat");

$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

//Connecting to Redis server on localhost 
$redis = new Redis(); 
$redis->connect('172.17.94.103', 6379); 
$redis->auth(['ilaodoo','redisOdoo2023']);
echo "Connection to server sucessfully";  
echo "<br/>";
//  GET CENTER FROM CENTER COST
$centerconfig = parse_ini_file("partners/$partner/centers-mapping.cfg", true);
function customcenters($center){
    if($center["cost"] != "")
    return $center["cost"]."_".$center["dept-code"];
    else return "-1";
}
$centerconfig = array_flip(array_map("customcenters",$centerconfig));

$profiles = [];
$run = $_REQUEST["run"] ?: "";
$now = date('Y-m-d');
$date7past = date("Ymd",strtotime("-7 days"));
$log7past = "tools/HRM_Sync/".$date7past."log.dat";
if(file_exists($log7past)) unlink($log7past);
if(file_exists(PROFILES) && $run === "")
{
    $profiles = json_decode(file_get_contents(PROFILES));
    foreach ($profiles as $key => $profile) {
        if($profile)
        updateorcreate($profile,$redis,$now,$db,$run,$centerconfig,1);
    }
}
else {
    if(file_exists(KEYS)) $profiles = json_decode(file_get_contents(KEYS));
    if($profiles == []) {
        $profiles = $redis->keys("employee:profile:*");
        JSON_Write(KEYS,$profiles);
    }

    echo count($profiles);
    $length = 100;
    if($run === "") $length = count($profiles);
    $now25next = date('Y-m-d H:i:s',strtotime('+25 seconds'));
    for ($i= 0; $i < $length; $i++) { 
        //update file keys if near maximum execution time 
        $checknow = date('Y-m-d H:i:s');
        if($checknow > $now25next) updatefile(KEYS,$profiles);
        if(count($profiles))
        {
            $profilekey = $profiles[$i];
            updateorcreate($profilekey,$redis,$now,$db,$run,$centerconfig);
            unset($profiles[$i]);
        } else break;
    }

    updatefile(KEYS,$profiles);

    SQL_Close($db);

    if(count($profiles)) header("Refresh:0");
    else 
    {
        unlink(KEYS);
        echo "finish";
    } 
}



function updatefile($file,$profiles)
{
    $profiles = array_values($profiles);
    JSON_Write($file,$profiles);
}


function writelog($file,$data){
    if(file_exists($file))
        $filedata = file_get_contents($file) ?? "";
    else $filedata = "";
    TXT_Write($file,$filedata . "\n".date('Y-m-d H:i:s')." : ".$data);
}


function updateorcreate($profilekey,$redis,$now,$db,$run, $centerconfig, $hasprofile = 0){
    if($hasprofile){ 
        $profile = $profilekey;
    }
    else{
        $profile =  $redis->get($profilekey);
        $profile = json_decode($profile);
    }
    
    $updatedAt = date('Y-m-d',strtotime($profile->write_date));
    $joindate = date('Y-m-d',strtotime($profile->join_date));
   
    if(isset($profile->work_email) && $profile->work_email)
    {
        if($joindate === $now || $updatedAt === $now || ($run == "first" && $profile->active)){
            //check ilo ila
            $bu = $profile->bu_id[1] ?? "true";
            if(!isset($profile->bu_id[1])) {
                echo "bu:".$profile->id;
                echo "</br>";
            }
            
            if(!str_contains($bu,"ILO"))
            {
                $user = 
                [
                    "status"        => $profile->active ? "active": "inactive",
                    "role"          => getRole($profile->job_title_id),
                    "center"        => getCenter($profile->center_cost,$profile->department_cost,$centerconfig),
                    "manager_id"    => isset($profile->parent_id[0]) ? ($profile->parent_id[0] ?: NULL): NULL,
                    "firstname"     => $profile->first_name ?? "",
                    "lastname"      => $profile->last_name ?? "",
                    "nickname"      => isset($profile->display_name) ? ($profile->display_name ?: NULL): NULL,
                    "midname"       => isset($profile->middle_name) ? ($profile->middle_name ?: NULL): NULL,
                    "email"         => $profile->work_email ?? "",
                    "mobile"        => isset($profile->mobile_phone) ? ($profile->mobile_phone ?: NULL): NULL,
                    "birthdate"     => date('Ymd',strtotime($profile->birthday)),
                    "gender"        => ("male" == $profile->gender)? "m" :"f",
                    "nationality"   => $profile->nationality_id[1] ?? NULL,
                    "idcard"        => $profile->identification_no ?? NULL,
                    "taxcode"       => $profile->tax_code ?: NULL,
                    "staffcode"     => $profile->code,
                    "country"       => $profile->permanent_country_id[1] ?? NULL,
                    "address"       => $profile->permanent_address_id[1] ?? NULL,
                ]; 

                if(gettype($user["manager_id"]) == "array")
                {
                    $user["manager_id"] = isset($user["manager_id"][0]) ? ($user["manager_id"][0] ?: NULL): NULL;
                }
                
                // check update or create
                $db->beginTransaction();
                $usercheck =  SQL_Query("SELECT * FROM users WHERE staffcode = '".$user["staffcode"]."'", $db);
                
                if($usercheck != []){
                    // update user
                    $userprofile = $usercheck[0];
                    try {
                        foreach ($userprofile as $field => $value) {
                            if(isset($user[$field]) && $user[$field] != $value){
                                writelog(LOG,"change user id: ".$userprofile["id"]." -- field: ". $field. " -- from ". $value . " -- to ". $user[$field]);
                                if($field == "birthdate" || $field == "manager_id"  )
                                    $sql  = "UPDATE users SET $field = ".$user[$field]." WHERE id =". $userprofile["id"];
                                else $sql  = "UPDATE users SET $field = '".$user[$field]."' WHERE id =". $userprofile["id"];
                                
                                try {
                                    $query =  $db->prepare($sql);
                                    $query->execute();
                                } catch (\Throwable $th) {
                                    $sql  = 'UPDATE users SET '.$field.' = "'.$user[$field].'" WHERE id ='. $userprofile["id"];
                                    $query =  $db->prepare($sql);
                                    if(!$query->execute())
                                    echo $th->getMessage();
                                    echo "</br>";
                                }
                                
                            }
                        }
                        $db->commit();
                    } catch (\Throwable $th) {
                        echo $th->getMessage();
                        writelog(LOG,$th->getMessage());
                        writelog(LOG,"error update:".json_encode($user));
                        $db->rollBack();
                    }
                }else{
                    // create user
                    try {
                        //dont create user for account doesnt has bu_id
                        if($bu != "true"){
                            $query =  $db->prepare("INSERT INTO users (status, role, center, manager_id, firstname, lastname, nickname, midname, email, mobile, birthdate, gender, nationality, idcard, taxcode, staffcode, country, address) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                            $query->execute(array_values($user));
                            writelog(LOG,"create user staffcode : ".$user["staffcode"]);
                        }
                        else{
                            writelog(LOG,"create user error bu code profile id :".$profile->id."--user profile".json_encode($user));
                        }
                        $db->commit();
                    } 
                    catch (\Throwable $th) {
                        echo $th->getMessage();
                        writelog(LOG,$th->getMessage());
                        writelog(LOG,"error create:".json_encode($user));
                        $db->rollBack();
                    }
                }
            }
        } 
    }
}

function getRole($jobtitle)
{
    $role = $jobtitle[1] ?? "";
    $start = strpos($role,'(');
    $end   = strpos($role,')');

    if($start && $end){
        $role = substr($role,$start + 1, $end - $start - 1);
        $arrayrole = explode("/",$role);
        if($arrayrole[1] == "TE" || $arrayrole[1] == "VNT" || $arrayrole[1] == "ST" || $arrayrole[1] == "NT") return "teacher";
        else if($arrayrole[1] == "TA") return "ta";
        else return $role;
    }else return $role;
}


function getCenter($cost,$dept,$centers)
{
    if($cost != "" && $dept != "" && isset($centers[$cost."_".$dept])) return $centers[$cost."_".$dept];
    return "NULL";
}





