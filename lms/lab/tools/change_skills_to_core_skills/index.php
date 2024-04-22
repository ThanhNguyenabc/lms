<?php
error_reporting(E_ERROR | E_PARSE);
chdir("../..");
function changeData($data)
{
    $result = [];
    foreach ($data as $key => $value) {
        if($key == "skills")
        {
             $result["core skills"] = $data["skills"];
        }
        else $result[$key] = $data[$key];
    }
    return $result;
}
function update_ini_file($filepath)
{
    $content = "";

    $parsed_ini = parse_ini_file($filepath, true);

    //write it into file
    if ($parsed_ini) {
        $data = changeData($parsed_ini);
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
function checkskillsinfo($filepath){
    $parsed_ini = parse_ini_file($filepath, true);
    if($parsed_ini && array_key_exists("skills",$parsed_ini)) return 1;
    return 0;
}
function change_info_skills()
{
   $folder    = 'content/lessons';
   $data = scandir($folder);

   foreach ($data as $item) {
      if (($item != '.') && ($item != '..')) {
         $info = "$folder/$item/info.dat";
         if (file_exists($info)) {
            if($_POST["lesson_type"] == "all" || strpos($item, $_POST["lesson_type"]) !== false)
            {
                if(checkskillsinfo($info))
                {
                    echo $item . "<br/>";
                    update_ini_file($info);
                }
                
            }
         }
      }
   }
   echo "Success";
}

if($_POST["lesson_type"]){
    change_info_skills();
}

?>
<html>
    <body>
        <p>Choose type lesson to change data info </p>
        <form method='POST' action='#'>
            <select name="lesson_type">
                <option value="all">All</option>
                <option value="SJ_PR">SJ_PR</option>
                <option value="SJ_A1">SJ_A1</option>
                <option value="SJ_A2">SJ_A2</option>
                <option value="SJ_B1">SJ_B1</option>
            </select>
            <button type="submit">Change</button>
        </form>
    </body>
</html>