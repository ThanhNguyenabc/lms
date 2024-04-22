<?PHP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

  

function String_ReplaceVariables($string, $variables, $delimiter)
{
 if(!$delimiter) $delimiter = "$";
 
 $keys = array_keys($variables ?? []);
 
 foreach($keys as $key)
 {
  $value  = $variables[$key];
  
  $string = str_replace($delimiter . $key . $delimiter, $value, $string);
 }
 
 return $string;
}



// GET DATA
if($_REQUEST["direct"])
{
 echo "direct";
 $to       = $_REQUEST["to"];
 $subject  = $_REQUEST["subject"];
 $template = $_REQUEST["template"];
 $data     = $_REQUEST["data"];
}
else
{
 $to       = json_decode($_REQUEST["to"]);
 $subject  = json_decode($_REQUEST["subject"]);
 $template = json_decode($_REQUEST["template"]);
 $data     = json_decode($_REQUEST["data"], true);
}



// GET EMAIL TEMPLATE AND FILL IT
$html = file_get_contents("templates/$template.html");
$html = String_ReplaceVariables($html, $data, "$");
 
 
// CONFIGURE MAILER
$mail = new PHPMailer;

$config = parse_ini_file('mailer.ini', true);

// SMTP
$mail->isSMTP();
$mail->mailer = $config['mail']['mailer'];  
$mail->SMTPAuth   = $config['mail']['auth'];                               
//$mail->SMTPSecure = 'ssl';  
$mail->SMTPSecure = $config['mail']['Secure'];  
$mail->SMTPDebug   = $config['mail']['debug'];               

// SENDER CREDENTIALS                             
$mail->Host       = $config['mail']['host'];                                                
//$mail->Port       = 465;
$mail->Port       = $config['mail']['port'];
$mail->Username   = $config['mail']['user'];          
//$mail->Password   = '137137137';
$mail->Password   = $config['mail']['pass'];

// EMAIL CONFIG
//$mail->isHTML(false);
$mail->isHTML(true);
//$mail->WordWrap = 50;           
$mail->setFrom("prodtech@ilavietnam.edu.vn", "Prodtech");   
$mail->addAddress($to, $to);
$mail->Subject = $subject;
$mail->Body    = $html;
                

// SEND 
if(!$mail->send()) 
{
 echo 'Message could not be sent.';
 echo 'Mailer Error: ' . $mail->ErrorInfo;
 exit;
}
 
echo 'Message has been sent';

?>