<?php
chdir("../..");
include ".\application\lib\utils.php";
define("PROFILES","tools/HRM_Sync/profiles.dat");
define("LOG","tools/HRM_Sync/".date("Ymd")."log.dat");
define("NOW",date('Y-m-d'));
$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");

//Connecting to Redis server on localhost 
$redis = new Redis(); 
$redis->connect('172.17.94.103', 6379); 
$redis->auth(['ilaodoo','redisOdoo2023']);
echo "Connection to server sucessfully";  
echo "<br/>";

$profilekeys = $redis->keys("employee:profile:*");
$profiles = $redis->mGet($profilekeys);
function jsondecode($string){
    $array = json_decode($string);
    if(date('Y-m-d',strtotime($array->write_date)) === NOW)
    return $array;
}
$profiles = array_map("jsondecode",$profiles);
$profiles = array_values(array_filter($profiles,fn($value) => !is_null($value) && $value !== '')) ;
JSON_Write(PROFILES,$profiles);
?>