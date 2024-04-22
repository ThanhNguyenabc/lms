<?php
chdir("../..");
include ".\application\lib\utils.php";
$partner = $_REQUEST["partner"] ?? "default";
$config  = parse_ini_File("partners/$partner/system.cfg", true);
$db      = SQL_Connect("localhost", $config["database"]["username"], $config["database"]["password"], $config["database"]["schema"], "mysql");
//echo('<pre>');print_r($_SERVER);die;
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


$db->beginTransaction();
$warehouse_codes =  SQL_Query("SELECT warehouse_code FROM centers WHERE dept_code='D0.09' order by id", $db);
//echo('<pre>');print_r($warehouse_codes);die;
$currentindex = $_GET['centerindex']??0;
$lastindex = $currentindex + 5;
$totalcenters = count($warehouse_codes);
$items = SQL_Query("SELECT item_code FROM inventory_items WHERE id <=50",$db);
//echo('<pre>');print_r($items);die;
$url = 'https://'.$_SERVER['SERVER_NAME'].'/lms/lab/api.php?f=Inventory_Create_Update&loginkey=D365inventorykey';
$method = 'POST';
$headerContentType ="Content-Type: application/json; charset=utf-8";
$outputFormat  = "";
$authkey  = "";
for($currentindex; $currentindex < $lastindex; $currentindex ++){
	$warehouse = $warehouse_codes[$currentindex];
	foreach($items as $index=>$item){
		$total = random_int(10, 500);
		$data = '{
				    "data": [
				          {
				            "item_number": "'.$item['item_code'].'",
				            "warehouse": "'.$warehouse['warehouse_code'].'",
				            "physical_inventory": "'.$total.'",
				            "physical_reserved": "0",
				            "physical_available": "'.$total.'",
				            "ordered_in_total": "0",
				            "on_order": "0",
				            "ordered_reserved": "0",
				            "available_for_reservation": "0",
				            "total_available": "'.$total.'"
				        }
				    ]
				}';
		$result = CURL_Service($url, $method, $data, $headerContentType, $outputFormat, $authkey);
		echo('<br>'.$result);
	}
	if($currentindex>=$totalcenters){
		exit('Done');
	}
}
header('Location: https://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'?centerindex='.$currentindex);
?>