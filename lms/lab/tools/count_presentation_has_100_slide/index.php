<?php

chdir("../..");
$folder    = 'content/lessons';

$data = scandir($folder);

foreach ($data as $item) {
    if (($item != '.') && ($item != '..')) {
        $presentationfolder = $folder."/".$item."/presentation";
        if(file_exists($presentationfolder)){
            $files = scandir($presentationfolder);
            $count = 0;
            $countfilename = 0;
            foreach ($files as $key => $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $filename = pathinfo($file, PATHINFO_FILENAME);
                if(in_array($ext, ["dat"])){
                   $count += 1;
                   if($filename >= 100){
                    $countfilename ++;
                   } 
                }  
            }
            if($count > 100) echo "<div>".$item." : ".$count." slides</div>";
            else if($countfilename) echo "<div>".$item." : ".$countfilename." slides > 100 </div>";
        }
    }
    
}
echo "finish";