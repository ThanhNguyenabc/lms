<?php
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
  // create or update AdminTaskConfig file
  $configPath = "partners/default/admin-task.cfg";
  if (!file_exists($configPath)) {
    touch($configPath);
  }
  $config = parse_ini_file($configPath, true);
  foreach (json_decode($_POST["data"])  as $key => $value) {
    $value = (array)$value;
    $lesson = strtolower($value["Lesson ID"]);

    $info = $value["Info.dat"];
    $firstPosition = strpos($info,"[");
    $lastPosition = strpos($info,"]");
    $key = substr($info,$firstPosition + 1,$lastPosition - $firstPosition - 1);

    $countTask = round(substr_count($info,'"') / 2) ;
    //GET TASK
    $index = 1;
    $position = 0;
    while ($index <= $countTask) {
      $firstPositionTask = strpos($info,'"',$position);
      $lastPositionTask = strpos($info,'"',$firstPositionTask + 1);
      $task = substr($info,$firstPositionTask + 1,$lastPositionTask - $firstPositionTask - 1);
      if(isset($config[$key]) && isset($config[$key][$task]))
      {
        $lessons = explode(",",$config[$key][$task] ) ;
        if(!in_array($lesson,$lessons)) $lessons[] = $lesson;
        $lessons = implode(",",$lessons);
        $config[$key][$task] = $lessons;
      }
      else $config[$key][$task] = $lesson;
      $index +=1;
      $position = $lastPositionTask + 1;
    }
  }
  update_ini_file($config, $configPath);
  echo "Update AdminTask Config Finished";
}


if (isset($_POST['submitLanguage'])) {
  // create or update AdminTaskConfig language file
  $configPath = "partners/default/admin-task-language.dat";
  if (!file_exists($configPath)) {
    touch($configPath);
  }

  $config = parse_ini_file($configPath, true);
  foreach (json_decode($_POST["datalanguage"])  as $key => $value) {
    $value = (array)$value;
    $key = trim($value["TASK"],'"');
    $en = trim($value["ENGLISH MESSAGE"],'"');
    $vn = trim($value["VIETNAMESE MESSAGE"],'"');
    $config[$key]["en"] = $en;
    $config[$key]["vn"] = $vn;
  }
  update_ini_file($config, $configPath);
  echo "Update AdminTask Config Language Finished";
}


?>
<!DOCTYPE html>
<html>

<head>
  <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

</head>

<body onload="init()">
  <p>Choose file admin Task config </p>

  <form action="" method="post" id="createForm">
    <input id="fileInput" type="file" name="file" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
    <pre id="fileContent"></pre>
    <input type="hidden" name="data" id="data">
    <input type="submit" name="submit" value="Task" id="submit" style="display:none"/>
  </form>
  <br />
  <p>Choose file admin Task <span style="color:blue"> language </span> config </p>
  <form action="" method="post" id="createForm1">
    <input id="fileInput1" type="file" name="file1" accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
    <pre id="fileContent1"></pre>
    <input type="hidden" name="datalanguage" id="datalanguage">
    <input type="submit" name="submitLanguage" value="language" id="submitLanguage" style="display:none" />
  </form>
</body>
<script>
  function init() {
    document.getElementById('fileInput').addEventListener('change', handleFileSelect, false);
    document.getElementById('fileInput1').addEventListener('change', handleFileSelect1, false);
  }

  async function handleFileSelect(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    /* data is an ArrayBuffer */

    const workbook = XLSX.read(data);

    document.getElementById('data').value = toJson(workbook);
    document.getElementById("submit").click();
  }

  async function handleFileSelect1(e) {
    const file = e.target.files[0];
    const data = await file.arrayBuffer();
    /* data is an ArrayBuffer */

    const workbook = XLSX.read(data);

    document.getElementById('datalanguage').value = toJson(workbook);
    document.getElementById("submitLanguage").click();
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