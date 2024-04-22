<?php
chdir("../..");
error_reporting(E_ERROR | E_PARSE);
include ".\application\lib\utils.php";
$folder = "content/lessons";
$data = scandir($folder);
$count = 0;
$vocabulary = scandir("../contdev/content/vocabulary");
$vocabulary = array_map("strtolower",$vocabulary);
$vocaexports = [];
foreach ($data as $key => $lesson) {
    if($lesson != "." && $lesson != "..")
    {   //check lesson 
        preg_match('/^[0-9]/', $lesson, $matches, PREG_OFFSET_CAPTURE);
        if($matches)
        { 
            $infofile = $folder."/".$lesson."/info.dat";
            if(file_exists($infofile)){
                $info = parse_ini_file($infofile,true);
                $vocab = $info["vocabulary"] ?? [];
                foreach ($vocab as $key => $value) {
                    if(!in_array(strtolower($value), $vocabulary) && !in_array($value, $vocaexports))
                    {
                        $vocaexports[] = $value;
                    }
                }
            }
        }
    }
}

header("Content-Disposition: attachment; filename=\"vocabularies.csv\"");
header("Content-Type: application/vnd.ms-excel;");
header("Pragma: no-cache");
header("Expires: 0");
$out = fopen("php://output", 'w');
foreach ($vocaexports as $data)
{
    fputcsv($out, [$data],"\t");
}
fclose($out);