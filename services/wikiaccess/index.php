<?php
session_start();
$user = $_SESSION['user']??null;
$url=$_GET['url'];
$host = 'https://ilawiki.ilavietnam.com/tiki_login_google.php'; 
if($user){
    header('Location: ' .$host.'?user='.$user['email'].'&pass='.md5($user['password']).'&crossUser=existed&url='. $url);
?>
<?php 
}
else{
	echo('Please login');
}
