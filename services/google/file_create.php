<?php

include_once "google.php";


$parameters = json_decode(file_get_contents('php://input'), true);

$filetype   = $parameters["type"] ?? $_REQUEST["type"];
$filename   = $parameters["name"] ?? $_REQUEST["name"];

$file     = Google_File_Create($filetype, $filename);

echo(json_encode($file->filelink));