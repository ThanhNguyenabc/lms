<?php

// include your composer dependencies
require_once 'lib/vendor/autoload.php';

/************************************************
 * The redirect URI is to the current page, e.g:
 * http://localhost:8080/googledrivefiles/index.php
 ************************************************/
 
$httpType = (($_SERVER['HTTPS'] == 'on') || ($_SERVER['SERVER_PORT']==443))? 'https':'http';
define('REDIRECT_URI', $httpType.'://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
define('PARENTFOLDER', '1zGBw-YFhYH3e3vatuugA1rrEiJF5tYYb');
define('APPLICATION_NAME', 'Drive API PHP');
define('CREDENTIALS_PATH', __DIR__.'/admin-directory.json');
define('CLIENT_SECRET_PATH', __DIR__.'/client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Drive::DRIVE,Google_Service_Drive::DRIVE_FILE)
));



/**
 * [Google_Client_Create description]
 * @return [google client object] [description]
 */
function Google_Client_Create()
{
 $client = new Google_Client();

 $client->setApplicationName(APPLICATION_NAME);
 $client->setScopes(SCOPES);
 $client->setAuthConfigFile(CLIENT_SECRET_PATH);
 $client->setAccessType('offline');
 $client->setRedirectUri(REDIRECT_URI);

 // Load previously authorized credentials from a file.
 $credentialsPath = Google_Directory_Expand(CREDENTIALS_PATH);
 if(file_exists($credentialsPath)) 
 {
  $myfile      = fopen(CREDENTIALS_PATH, "r");
  $accessToken = json_decode(fgets($myfile),true);
  fclose($myfile);
		
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if($client->isAccessTokenExpired())   
  {					
   $token = json_decode(json_encode($client->getAccessToken()));
   $client->refreshToken($token->refresh_token);
 
   $myfile = fopen(CREDENTIALS_PATH, "w");
 
   fwrite($myfile, json_encode($client->getAccessToken()));
   fclose($myfile);
 
   $myfile1     = fopen(CREDENTIALS_PATH, "r");
   $accessToken = json_decode(fgets($myfile1),true);
   fclose($myfile1);
			
   $client->setAccessToken($accessToken);
  }
 } 
 else 
 {
  // Request authorization from the user.
  $authUrl = $client->createAuthUrl(); 

  header('Location: ' .$authUrl);

  if(isset($_GET['code'])) 
  {
   $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);
   if(!file_exists(dirname($credentialsPath))) 
   {
    mkdir(dirname($credentialsPath), 0700, true);
   }
 
   $myfile = fopen(CREDENTIALS_PATH, "x+");
   fwrite($myfile, json_encode($client->getAccessToken()));
   fclose($myfile);
 
   header('Location: ' .REDIRECT_URI);
  }
 }
 
 return $client;
}
	


/**
 * [Google_Directory_Expand description]
 * @param  [string] $path [path to file]
 * @return [string]       [real path to file]
 */
function Google_Directory_Expand($path) 
{
 $homeDirectory = getenv('HOME');

 if (empty($homeDirectory)) 
 {
  $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
 }
 
 return str_replace('~', realpath($homeDirectory), $path);	
}



/**
 * [Google_File_SetPermissions set access]
 * @param [Drive] $driveservice [the Google\Service\Drive object]
 * @param [string] $fileId       [the id of file]
 */
function Google_File_SetPermissions($driveservice, $fileId)
{
 $fileBody = new Google_Service_Drive_Permission();
 $fileBody->setRole('writer');
 $fileBody->setType('anyone');
 $fileBody->setAllowFileDiscovery(false);

 $parameters = array();
 $parameters['sendNotificationEmail'] = false;

 $result = $driveservice->permissions->create($fileId, $fileBody,$parameters);
 
 return true;		
}




/**
 * [Google_File_Exists check if file is existed]
 * @param  [Drive] $driveservice [the Google\Service\Drive object]
 * @param  [string] $filename    [the name of file want check if it is existed]
 * @param  [string] $mimeType    [the type of file want check if it is existed]
 * @param  [string] $parentId    [the id of parent folder]
 * @return [File object/integer] [return a file object if the file is existed or zero (0) if file isn't existed]
 */
function Google_File_Exists($driveservice, $filename, $mimeType, $parentId = null)
{ 
 $result = array();
	
 try 
 {
  $parameters             = array();
  $parameters['q']        = "name='".$filename."' and '".$parentId."' in parents and mimeType='".$mimeType."'";
  $parameters['pageSize'] = 1;
  
  $files  = $driveservice->files->listFiles($parameters);
  $result = $files->getFiles();
 }
 catch(Exception $e) 
 {
  print "An error occurred: " . $e->getMessage();
  $pageToken = NULL;
  die;
 } 
 
 if(isset($result[0]))
 {
  return $result[0]; 
 }

 return 0;
}



/**
 * [Google_File_Create create a google file]
 * @param  [string] $fileType [type of file want to create include 'docs', 'sheets' and 'slides', the default is 'slides']
 * @param  [string] $fileName [the name of file want to create, the default is 'testing file']
 * @return [File object]           [the object have 3 info fileid, filename and filelink]
 */
function Google_File_Create($fileType = null, $fileName = null)
{
 $client   = Google_Client_Create();
 
 $service  = new Google\Service\Drive($client);
 $file     = new Google\Service\Drive\DriveFile();
 
 $fileType = (!empty($fileType)) ? $fileType:('slides');
 $fileName = (!empty($fileName)) ? $fileName:'testing file';
 
 switch($fileType) 
 {
  case "docs":
	$file->setMimeType('application/vnd.google-apps.document');
	$namespace = "document";
  break;
	
  case "sheets":
	$file->setMimeType('application/vnd.google-apps.spreadsheet');
	$namespace = "spreadsheets";
  break;
	
  case "slides":
 	$file->setMimeType('application/vnd.google-apps.presentation');
	$namespace = "presentation";
  break;
 }
 		
 $result = Google_File_Exists($service,$fileName,$file->getMimeType(),PARENTFOLDER);
 
 if(empty($result))
 {
  $file->setName($fileName);
  $file->setParents(array(PARENTFOLDER));
  
  $parameters           = array();
  $parameters['fields'] = 'id,name,mimeType';
		
  $result = $service->files->create($file, $parameters);  
 }
	
 /*Put this code out site if condition to make sure the file has share permission correctly*/
 Google_File_SetPermissions($service, $result->id);

 $fileObj = new stdclass();

 $fileObj->fileid   = $result->id;
 $fileObj->filename = $result->name;
 $fileObj->filelink = "https://docs.google.com/$namespace/d/".$result->id;
	
 return $fileObj;
}


?>