<?php

include_once "lib/utils.php";

$config     = parse_ini_file("cfg/config.ini", true);  
$parameters = json_decode(file_get_contents('php://input'), true);


$url    = $config["endpoint"]["voicelist"];
$ctype  = "Content-Type: application/json; charset=utf-8";
$mtype  = "";
$key    = "Ocp-Apim-Subscription-Key: ". $config["credentials"]["key1"];
$data   = "";

$response = CURL_Service($url, "GET", $data, $ctype, $mtype, $key);
$voices   = json_decode($response, true);

echo $url;
die;
	
// FILTER BY LOCALE(S)
$locale = $parameters["locale"] ?? $_REQUEST["locale"] ?? false;
if($locale)
{
 $locale = strtolower($locale);
 $filtered = [];
 
 foreach($voices as $voice)
 {
  if(str_starts_with(strtolower($voice["Locale"]), $locale)) 
  {
   array_push($filtered, $voice);
  }
 }
 
 $voices = $filtered;
}
	
	
	
// FILTER BY GENDER
$gender = $parameters["gender"] ?? $_REQUEST["gender"] ?? false;
if($gender)
{
 $gender   = strtolower($gender);
 $filtered = [];
 
 foreach($voices as $voice)
 {
  if(strtolower($voice["gender"]) == $gender) array_push($filtered, $voice);
 }
 
 $voices  = $filtered;
}
	
	
	
// FILTER BY TYPE
$type = $parameters["type"] ?? $_REQUEST["type"] ?? false;
if($type)
{
 $type     = strtolower($type);
 $filtered = [];
 
 foreach($voices as $voice)
 {
  if(strtolower($voice["VoiceType"]) == $type) array_push($filtered, $voice);
 }
 
 $voices  = $filtered;
}



// FILTER BY STYLE
$style = $parameters["style"] ?? $_REQUEST["style"] ?? false;
if($style)
{
 $style    = strtolower($style);
 $filtered = [];
 
 foreach($voices as $voice)
 {
  if(in_array($style, array_map("strtolower", $voice["StyleList"] ?? [])))
  {
   array_push($filtered, $voice);
  }
 }
 
 $voices  = $filtered;
}	
	
	
// SPECIAL SIMPLIFIED OUTPUT
$output = $parameters["output"] ?? $_REQUEST["output"] ?? false;
if($output == "simple")
{  
 $list = [];
 
 foreach($voices as $voice)
 { 
  $styles = $voice["StyleList"] ?? [];
  $styles = implode(",", $styles);
  
  array_push($list, $voice["ShortName"] . ";" . $styles);
 }
 
 $list = implode("\r\n", $list);
 echo $list;
 
 die;
}
	
	
echo json_encode($voices);
	

?>
