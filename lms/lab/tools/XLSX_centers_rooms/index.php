<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
function checkCenterOrRoom($data)
{
    $check = 0; // is Center
    foreach ($data as $key => $value) {
        if($key == "Room") $check = 1; // is Room
    }
    return $check;
}
function customData($data,$mode){
    $result = [];
    if($mode == 'center'){
        foreach ($data as $ckey => $center) {
            $center = (array) $center;
            $result[$center["Site"]]['name']    = $center["Name"];
            $result[$center["Site"]]['type']    = $center["Type"] ?? 'internal';
            $result[$center["Site"]]['cluster'] = $center["Cluster"] ?? '';
            $result[$center["Site"]]['address'] = $center["Address"] ?? '';
        }
    }else{
        foreach ($data as $rkey => $room) {
            $room = (array) $room;
            $result[$room["Centre"]][$room["Room"]] = ["info" => ""];
        }
    }
    return $result;
}
function compareData($olddata,$newdata){
    $result = $olddata;
    $checkCompare = 0;
    foreach ($newdata as $key => $value) {
        if(array_key_exists($key,$olddata)){
            $checkCompare = 1;
        }
        $result[$key] = $value;
    }
    if($checkCompare == 1) return $result;
    else return $checkCompare;
}
function update_ini_file($data, $filepath) { 
    $content = ""; 
    
    $parsed_ini = parse_ini_file($filepath, true);

    //write it into file
    if($parsed_ini && compareData($parsed_ini,$data))
    {
        $data = compareData($parsed_ini,$data);
        if (!$handle = fopen($filepath, 'w')) {
            return false; 
        }
    } 
    else if (!$handle = fopen($filepath, 'a')) { 
        return false; 
    }

    foreach($data as $section=>$values){
        $content .= "[".$section."]\n"; 
        foreach($values as $key=>$value){
            $content .= $key." = \"".$value."\"\n"; 
        }
        $content .= "\n";
    }
    
    
    $success = fwrite($handle, $content);
    fclose($handle); 
    return $success; 
}
function createRoom($data){
    foreach ($data as $center => $listroom) {
        $path = './partners/default/centers/'.$center.'/rooms.cfg';
        $status =  update_ini_file($listroom,$path);
    }
}
function updateCenter($data){
    $path = './partners/default/centers.cfg';
    if(file_exists($path)){
        $status =  update_ini_file($data,$path);
    }
    foreach ($data as $key => $value) {
        if(!is_dir("./partners/default/centers/" . $key))
        mkdir("./partners/default/centers/" . $key);
    }
}
if (isset($_POST['submit'])) {
    //mkdir("./partners/default/centers/" . 'test1');
    
    $data = json_decode($_POST['data']);
    if(checkCenterOrRoom($data[0])){
        $data = customData($data,'room');
        createRoom($data);
    }else{
        $data = customData($data,'center');
        updateCenter($data);
    }
    echo "<script type = 'text/javascript'>alert('Done!');</script>";

}
?>
<!DOCTYPE html>
<html>

<head>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <style>
        #createForm {
            display: none;
        }
    </style>
</head>

<body onload="init()">
<p>Choose file centre list or file room list </p>
    <input id="fileInput" type="file" name="file" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>
    <pre id="fileContent"></pre>
    <form action="" method="post" id="createForm">
        <input type="hidden" name="data" id="data">
        <input type="submit" name="submit" value="create" id="submit" />
    </form>
</body>
<script>
    function init() {
        document.getElementById('fileInput').addEventListener('change', handleFileSelect, false);
    }

    async function handleFileSelect(e) {
        const file = e.target.files[0];
        const data = await file.arrayBuffer();
        /* data is an ArrayBuffer */

        const workbook = XLSX.read(data);
       
        document.getElementById('data').value = toJson(workbook);
        document.getElementById("submit").click();
    }

    function toJson(workbook) {
        var json
        workbook.SheetNames.forEach(function(sheetName) {
            json = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName]);
        });
        console.log(json)
        return JSON.stringify(json);
    }
</script>

</html>