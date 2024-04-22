<?php

include_once "lib/utils.php";

$config     = parse_ini_file("cfg/config.ini", true);  
$parameters = json_decode(file_get_contents('php://input'), true);

// SET SOME DEFAULTS
$parameters["lang"] = $parameters["lang"] ?? $_REQUEST["lang"] ?? "en-us";
$parameters["style"] = $parameters["style"] ?? $_REQUEST["style"] ?? "default";
$parameters["pitch"] = $parameters["pitch"] ?? $_REQUEST["pitch"] ?? "default";
$parameters["rate"]  = $parameters["rate"]  ?? $_REQUEST["rate"]  ?? "default";

// COMPILE XML DATA
$data = file_get_contents("tts.xml");
foreach(["lang", "voice", "style", "pitch", "rate", "text"] as $param)
{
 $value = $parameters[$param] ?? $_REQUEST[$param] ?? "";
 $data  = str_replace("{" . $param ."}", $value, $data);
}


// CALL SERVICE
$url      = $config["endpoint"]["texttospeech"];
$ctype    = "Content-Type: application/ssml+xml";
$mtype    = "X-Microsoft-OutputFormat: audio-48khz-96kbitrate-mono-mp3";
$key      = "Ocp-Apim-Subscription-Key: ". $config["credentials"]["key1"];
 
$response = CURL_Service($url, "POST", $data, $ctype, $mtype, $key);



// CREATE FILE
$filename = tempnam("temp", "");
$myfile   = fopen($filename, "w");
fwrite($myfile, $response);
fclose($myfile);
 
Send_File($filename,"audio/mpeg");


?>
