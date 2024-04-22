<?php
chdir("../..");
error_reporting(E_ERROR | E_PARSE);
$html = <<<HTML
   <html>
      <body>
         <form action="#" id="form" method="POST">
            <button name="run" value="1">
               Run
            </button>
         </form>
         
      </body>
   </html>
HTML;
function savefilemp3()
{

   $folder    = 'content/vocabulary';
   $data = scandir($folder);
   $count = 0;
   foreach ($data as $item) {
      if (($item != '.') && ($item != '..')) {
         $audio = "$folder/$item/audio.mp3";
         if (!file_exists($audio) && $count <= 20) {
            $info = parse_ini_file("$folder/$item/info.dat", true);
            $audioName = $info["info"]["en"];

            $data = json_encode(['voice' => 'en-US-JaneNeural', 'text' => $audioName]);

            $url = "https://dev.ila.edu.vn/lms/lab/services/azure/tts_speak.php";
            $handle = curl_init();
            $array = array(
               CURLOPT_URL => $url,
               CURLOPT_RETURNTRANSFER => true,
               CURLOPT_ENCODING => '',
               CURLOPT_MAXREDIRS => 10,
               CURLOPT_TIMEOUT => 0,
               CURLOPT_FOLLOWLOCATION => true,
               CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
               CURLOPT_SSL_VERIFYHOST => false,
               CURLOPT_SSL_VERIFYPEER => false,
               CURLOPT_CUSTOMREQUEST => "POST",
               CURLOPT_POSTFIELDS => $data,
               CURLOPT_HTTPHEADER =>
               array(
                  'Content-Type: application/json',
                  'Content-Length:' . strlen($data),
                  'User-Agent: curl'
               )
            );
            curl_setopt_array($handle, $array);
            $output = curl_exec($handle);
            file_put_contents($audio, $output);
            $count++;
         }
      }
   }
   $script = <<<SCRIPT
   <html>
      <body>
      <p> Please wait a few minutes, will has notify "done" when finish.</p>
         <form action="#" id="form" method="POST">
            <input type="hidden" name="run" value="1"/>
         </form>
         <script>
            function run(){
               document.getElementById("form").submit();
            }
            var myVar = setTimeout(run, 12000);
         </script>
      </body>
   </html>     
   SCRIPT;
   if($count > 20) echo $script;// not done
   else echo "done"; //done
}

if(isset($_POST["run"])){
   //echo $_POST["run"];
   savefilemp3();
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   echo $html;
}
