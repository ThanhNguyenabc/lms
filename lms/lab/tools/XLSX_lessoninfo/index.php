<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
// custom data input become type : 
// [
//     "lesson1" => [
//         "info1" => [
//             "title1" => "value1"
//         ],
//         "info2" => [
//             "title2" => "value2"
//         ]
//     ]
// ]
function customData($data)
{
    $result = [];
    foreach ($data as $value) {
        $valueArray = array_values((array)$value);
        $result[$valueArray[0]] = [];
        $result[$valueArray[0]]["info"]["type"] = "standard";
        $result[$valueArray[0]]["title"]["en"] = $valueArray[2];
        $result[$valueArray[0]]["title"]["vn"] = $valueArray[3];
        $result[$valueArray[0]]["desc-teacher"]["en"] = $valueArray[6];
        $result[$valueArray[0]]["desc-teacher"]["vn"] = $valueArray[7];
        $result[$valueArray[0]]["desc-student"]["en"] = $valueArray[8];
        $result[$valueArray[0]]["desc-student"]["vn"] = $valueArray[9];
        $result[$valueArray[0]]["cefr"]["level"] = strtolower($valueArray[1]);
        $result[$valueArray[0]]["meta"]["tags"] = $valueArray[10];
        //$outcome
        $outcomes = explode(",", $valueArray[4]);
        foreach ($outcomes as $okey => $outcome) {
            $result[$valueArray[0]]["outcomes"]["outcome 0" . $okey] = strtolower(trim($outcome));
        }
        $result[$valueArray[0]]["project"]["en"] = "";
        $result[$valueArray[0]]["project"]["vn"] = "";
        $result[$valueArray[0]]["project"]["file"] = "";
        // vocab
        $vocab = explode(",", $valueArray[5]);
        foreach ($vocab as $vkey => $vocabulary) {
            if ($vkey < 10)
                $result[$valueArray[0]]["vocabulary"]["term 0" . $vkey] = trim($vocabulary);
            else $result[$valueArray[0]]["vocabulary"]["term " . $vkey] = trim($vocabulary);
        }
        // skill
        $skills = explode(",", $valueArray[12]);
        foreach ($skills as $skey => $skill) {
            if ($skey < 10)
                $result[$valueArray[0]]["skills"]["skill 0" . $skey] = trim($skill);
            else $result[$valueArray[0]]["skills"]["skill " . $skey] = trim($skill);
        }
        // authors
        $result[$valueArray[0]]["authors"]["lesson"] = "";
        $result[$valueArray[0]]["authors"]["presentation"] = "";
        $result[$valueArray[0]]["authors"]["homework"] = "";
        
        $result[$valueArray[0]]["source"]["0"] = strtoupper($valueArray[0]);
    }
    return $result;
}
function compareData($olddata, $newdata)
{
    $result = $olddata;
    // foreach ($newdata as $key => $value) {
    //     if (array_key_exists($key, $olddata)) {
    //         if($key == "cefr"){
    //             $result["cefr"]["level"] = $value["level"];
    //         }else 
    //         if ($key != "authors")
    //             $result[$key] = $value;
    //     } else {
    //         $result[$key] = $value;
    //     }
    // }
    $result["skills"] = $newdata["skills"];
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
    foreach ($data as $key => $lesson) {
        if (file_exists("content/lessons/$key")) {
        } else {
            mkdir("content/lessons/" . $key);
        }
        $status = update_ini_file($lesson, "content/lessons/$key/info.dat");
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
    <p>Choose file lesson info </p>
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