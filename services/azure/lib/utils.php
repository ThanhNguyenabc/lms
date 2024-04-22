<?php

function CURL_Service($url, $method, $data, $headerContentType, $outputFormat, $authkey)
{
 $curl = curl_init();

 $params = 
 array(
   CURLOPT_URL => $url,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_ENCODING => '',
   CURLOPT_MAXREDIRS => 10,
   CURLOPT_TIMEOUT => 0,
   CURLOPT_FOLLOWLOCATION => true,
   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
   CURLOPT_SSL_VERIFYHOST => false,
   CURLOPT_SSL_VERIFYPEER => false,
   CURLOPT_CUSTOMREQUEST => $method,
   CURLOPT_POSTFIELDS => $data,
   CURLOPT_HTTPHEADER => 
   array(
	 $headerContentType, 
	 'Content-Length:'.strlen($data),
	 'User-Agent: curl',
	 $outputFormat,
	 $authkey
	)
   );
 curl_setopt_array($curl, $params);

 $response = curl_exec($curl);

 curl_close($curl);
 
 return $response;
}





/**
 * Send_Big_File function will read and respone partial content, this will helpful for the big audio/video file and client don't need to wait until the file loaded done for playing
 * @param string $file     file path and name
 * @param string $mimeType file type
 */
function Send_Big_File($file, $mimeType='application/octet-stream')
{ 
  if(!file_exists($file))
  { header ("HTTP/1.0 404 Not Found");
    return;
  }

  $size=filesize($file);
  $time=date('r',filemtime($file));

  $fm=@fopen($file,'rb');
  if(!$fm)
  { header ("HTTP/1.0 505 Internal server error");
    return;
  }

  $begin=0;
  $end=$size;
  if(isset($_SERVER['HTTP_RANGE']))
  { if(preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches))
    { $begin=intval($matches[0]);
      if(!empty($matches[1]))
        $end=intval($matches[1]);
    }
  }

  $path_parts = pathinfo($file);
  if($begin>0||$end<$size)
    header('HTTP/1.0 206 Partial Content');
  else
    header('HTTP/1.0 200 OK'); 

  header("Content-Type: $mimeType");
  header('Cache-Control: public, must-revalidate, max-age=0');
  header('Pragma: no-cache'); 
  header('Accept-Ranges: bytes');
  header('Content-Length:'.($end-$begin));
  header("Content-Range: bytes $begin-$end/$size");
  header("Content-Disposition: inline; filename=".$path_parts['basename']);
  header("Content-Transfer-Encoding: binary\n");
  header("Last-Modified: $time");
  header('Connection: close'); 

  $cur=$begin;
  fseek($fm,$begin,0);

  while(!feof($fm)&&$cur<$end&&(connection_status()==0))
  { print fread($fm,min(1024*16,$end-$cur));
    $cur+=1024*16;
  }
}






/**
 * Send_File function will read and respone all content
 * @param string $file     file path and name
 * @param string $mimeType file type
 */
function Send_File($file, $mimeType='application/octet-stream')
{ 
  if (file_exists($file)) {
    $path_parts = pathinfo($file);
    header("Content-Transfer-Encoding: binary"); 
    header("Content-Type: audio/mpeg");
    header('Content-length: ' . filesize($file));
    header('Content-Disposition: inline; filename="' . $path_parts['basename'] . '"');
    header('X-Pad: avoid browser bug');
    header('Cache-Control: no-cache');


    readfile($file);
    exit;
  }
  else {
    header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
    echo "no file";
  }
}

?>