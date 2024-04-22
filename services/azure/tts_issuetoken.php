<?php

include_once "lib/utils.php";

$config     = parse_ini_file("cfg/config.ini", true);  
$parameters = json_decode(file_get_contents('php://input'), true);

// CALL SERVICE
$url      = $config["endpoint"]["issuetoken"];
$ctype    = "";
$mtype    = "";
$key      = "Ocp-Apim-Subscription-Key: ". $config["credentials"]["key1"];
$data     = "";
 
$response = CURL_Service($url, "POST", $data, $ctype, $mtype, $key);

echo $response;


?>
