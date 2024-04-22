<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
// custom data input become type : 
// [
//     "outcome1" => [
//         "info" => [
//             "vn" => "value1"
//         ],
//         "lv1" => [
//             "vn" => "value2"
//         ]
//     ]
// ]
function customData($data)
{
    $result = [];
    foreach ($data as $value) {
        $valueArray = array_values((array)$value);
        $outcomeId = strtolower($valueArray[0]);
        $result[$outcomeId] = [];
        $result[$outcomeId]["info"]["vn"] = $valueArray[5];
        $listlv = preg_split('/\n/',$valueArray[7]) ;
        foreach ($listlv as $key => $value) {
            $result[$outcomeId]["lv ".($key+1)]["vn"] = $value;
        }
    }
    return $result;
}
function compareData($olddata, $newdata)
{
    $result = $olddata;
    foreach ($newdata as $key => $value) {
        $result[$key]["vn"] = $value["vn"];
    }
    
    return $result;
}
function update_ini_file($data, $filepath)
{
    $content = "";

    $parsed_ini = parse_ini_file($filepath, true);

    //write it into file
    if ($parsed_ini) {
        $data = compareData($parsed_ini, $data);
        if (!$handle = fopen($filepath, 'w')) {
            return false;
        }
    } else if (!$handle = fopen($filepath, 'a')) {
        return false;
    }

    foreach ($data as $section => $values) {
        $content .= "[" . $section . "]\n";
        foreach ($values as $key => $value) {
            $content .= $key . " = \"" . $value . "\"\n";
        }
        $content .= "\n";
    }


    $success = fwrite($handle, $content);
    fclose($handle);
    return $success;
}

if (isset($_POST['submit'])) {
    $data = json_decode($_POST['data']);
    $data = customData($data);
    foreach ($data as $key => $outcome) {
        if (file_exists("content/outcomes/$key")) {
        } else {
            // mkdir("content/lessons/" . $key);
            echo "<p style='color:red'>".$key."</p>"."<br>";
        }
        $status = update_ini_file($outcome, "content/outcomes/$key/info.dat");
        echo $key . "<br>";
    }

    echo "Action Successfuly";
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
    <p>Choose file outcome info </p>
    <input id="fileInput" type="file" name="file" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
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
        //console.log(toJson(workbook));
        document.getElementById("submit").click();
    }

    function toJson(workbook) {
        var json
        workbook.SheetNames.forEach(function(sheetName) {
            json = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
                defval: ""
            });
        });
        //console.log(json)
        return JSON.stringify(json);
    }
</script>

</html>