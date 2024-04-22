<?php
// Read XLSX file list lesson id has test info . Write new config in grades.cfg
error_reporting(E_ERROR | E_PARSE);
chdir("../..");

function update_ini_file($data, $filepath)
{
    $content = "";

    $parsed_ini = parse_ini_file($filepath, true);

    //write it into file
    if ($parsed_ini) {
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
    $programConfig = parse_ini_file("partners/default/programs.cfg",true);
    $check = [];
    $data = json_decode($_POST['data']);
    foreach ($programConfig as $key => $value) {
      foreach ($value as $keyv => $srting) {
        if($keyv != "name" && $keyv != "program" && $keyv != "levels")
        {
          $array = explode(",",$srting);
          $result = [];
          foreach ($data as $keyO => $lessonObject) {
            $lessonObject = (array)$lessonObject;
            if(in_array(strtolower($lessonObject["Lesson ID"]),$array) || in_array(strtoupper($lessonObject["Lesson ID"]),$array))
            {
              $taskName = trim(json_encode($lessonObject["Test name"]),'"');
              $taskName = str_replace('\\\\n'," ",json_encode($taskName));
              $taskName = trim($taskName,'"');
              $result[] = strtolower($lessonObject["Lesson ID"])."__". $taskName;
            }
          }
          //var_dump([$key,$keyv,implode(",",$result)]);
          $programConfig[$key][$keyv] = implode(";",$result) ;

        }
      }
    }
    //var_dump($programConfig);
    update_ini_file($programConfig,"partners/default/grades.cfg");
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
        document.getElementById("submit").click();
    }

    function toJson(workbook) {
        var json
        workbook.SheetNames.forEach(function(sheetName) {
            json = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
                defval: ""
            });
        });
        return JSON.stringify(json);
    }
</script>

</html>