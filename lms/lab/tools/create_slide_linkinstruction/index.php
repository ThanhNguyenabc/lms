<?php
chdir("../..");
include ".\application\lib\utils.php";
// GET LIST LESSON NEED CREATE SLIDE
$config =  parse_ini_file("partners/default/programs.cfg",true);
$listlesson = [];
foreach ($config as $key => $value) {
  // GET LIST LESSON HYBRID
    if(isset($value["type"]) && $value["type"] == "hybrid")
    {
        $levels = $value["levels"];
        $levels = explode(",",$levels);
        foreach ($levels as $key => $level) {
            $lessons = explode(",",$value[$level]);
            $listlesson = array_merge($listlesson,$lessons);
        }
    }
}

$folder = "content/lessons";
$lessons = scandir($folder);
foreach ($lessons as $key => $lesson) {
    if (($lesson != '.') && ($lesson != '..') && in_array($lesson,$listlesson)) {
        echo $lesson . "<br/>";
        $path = "$folder/$lesson/presentation";
        if(file_exists($path))
        $files = Storage_Files_Collect($path, ["dat"]);
        else mkdir($path);
        //CHECK HAS SLIDE LINK IN INSTRUCTION
        $hasfile = 0;
        $firstfile = "";
        if($files)
        {
            $firstfile = Storage_Path_GetFilename($files[0]);
        }
       
        if($firstfile == "00.dat"){
            if(file_exists("$path/$firstfile"))
            $slideinfo = Ini_File_Read("$path/$firstfile");
            if($slideinfo){
                if($slideinfo["header"]["name"] == "Link in instructions")
                { 
                    $hasfile = 1;
                } 
            }
            else{
                $slideinfo = file_get_contents("$path/00.dat");
                $namestartposition = strpos($slideinfo,"\"");
                $nameendposition = strpos($slideinfo,"\"",$namestartposition + 1);
                $name = substr($slideinfo,$namestartposition + 1, $nameendposition - $namestartposition -1);
                if($name == "Link in instructions")
                { 
                    $hasfile = 1;
                } 
            }
        }
        
        // GET LINK IN INFO FILE
        if(file_exists("$folder/$lesson/info.dat"))
        {
            $lessoninfo = parse_ini_file("$folder/$lesson/info.dat", true);
            if($lessoninfo)
            {
                $keys = array_keys($lessoninfo);
                $keymatches = preg_grep('/link\s\d\d/',$keys);
                $ableread = true;
            }
            else 
            {
                $lessoninfo = file_get_contents("$folder/$lesson/info.dat", true);
                preg_match_all('/link\s\d\d/',$lessoninfo,$keymatches);
                $ableread = false;
                echo "read file string";
            }
            if($keymatches){
                $slides = [];
                
                // CREATE NEW SLIDE
                $string = "Please click links below to access the resourses:\n";
                
                $countlink = 0;
                if($ableread)
                    foreach ($keymatches as $key => $value) {
                        $linkname = $lessoninfo[$value]["en"];
                        $linkurl = $lessoninfo[$value]["url"];
                        if($linkurl)
                        {
                            $string .= "<a href='$linkurl' target='_blank'>$linkname</a>";
                            $countlink = 1;
                        }
                    }
                else{
                    foreach ($keymatches[0] as $key => $value) {
                        $startposition = strpos($lessoninfo,$value);
                       
                        $linknamestartposition = strpos($lessoninfo,"\"",$startposition);
                        $linknameendposition = strpos($lessoninfo,"\"",$linknamestartposition + 1);
                        $linkname = substr($lessoninfo,$linknamestartposition + 1,$linknameendposition - $linknamestartposition - 1);
                        
                        $urlstartposition = strpos($lessoninfo,"url",$startposition);
                        $linkurlstartposition = strpos($lessoninfo,"\"",$urlstartposition);
                        $linkurlendposition = strpos($lessoninfo,"\"",$linkurlstartposition + 1);
                        $linkurl = substr($lessoninfo,$linkurlstartposition + 1, $linkurlendposition - $linkurlstartposition - 1);
                        
                        if($linkurl)
                        {
                            $string .= "<a href='$linkurl' target='_blank'>$linkname</a>";
                            $countlink = 1;
                        }
                    }
                }
                if(!$countlink) $string = "No link in instruction";

                $newslide["id"] = "00";
                $newslide["info"] = file_get_contents("tools/create_slide_linkinstruction/00.dat");
                $newslide["info"] = String_Variables_Apply($newslide["info"], ["teachertext" => $string]);
                array_push($slides,$newslide);

                if(!$hasfile){
                    // GET ALL SLIDES
                    foreach($files as $file)
                    {
                        $filename = Storage_Path_GetFilename($file);	  
                        

                        if($filename != "info.dat")
                        {
                            $slide["info"]      = file_get_contents("$path/$filename");
                            $slide["id"] = (int)Storage_Path_RemoveExtension(Storage_Path_GetFilename("$path/$filename"));
                            if($slide["id"] < 9) $slide["id"] = "0" .($slide["id"] + 1);
                            else $slide["id"] = ($slide["id"] + 1) ."";
                            array_push($slides, $slide);
                        } 
                        unlink("$path/$filename");
                    }
                    // WRITE NEW LIST SLIDE
                    foreach ($slides as $key => $slide) {
                        file_put_contents("$path/".$slide["id"].".dat",$slide["info"]) ;
                    }
                }else{
                    // UPDATE FILE 00.dat
                    if(file_exists("$path/00.dat"))
                    file_put_contents("$path/00.dat",$newslide["info"]) ;

                    // UPDATE BACKGROUND IMAGE
                    $imagePath = "resources/images/cover-presentation.jpg";
                    if(isset($_REQUEST["updateImage"]) && $_REQUEST["updateImage"] && !file_exists($path . "/" . Storage_Path_GetFilename($imagePath)))
                      copy($imagePath,$path . "/" . Storage_Path_GetFilename($imagePath));
                }
            }else{
                if($hasfile){
                    if(file_exists("$path/00.dat"))
                    unlink("$path/00.dat");
                }
            }
        } 
    }    
}
echo "finish";
?>