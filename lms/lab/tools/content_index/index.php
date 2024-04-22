<?php

chdir("../..");

include ".\application\lib\utils.php";

//$lessons  = Storage_Files_Collect(".\content\lessons",  ["dat"], ["recurse"]);


foreach(["outcomes", "vocabulary"] as $content)
{
 echo "Cataloguing $content<br>";
 
 // CATALOGS
 $catalog              = [];
 $catalog["by tag"]    = [];
 $catalog["by parent"] = [];
 $catalog["by level"]  = [];
 
 // LANGUAGE
 $language = [];
 
 
 // COLLECT ITEMS 
 $items = Storage_Files_Collect("./content/$content", ["dat"], ["recurse"]);
 foreach($items as $item)
 {
  $id    = Storage_Path_GetFilename(Storage_Path_GetFolder(Storage_Path_Sanitize($item)));
  $file  = parse_ini_file($item, true);
 

  // META TAGS
  if(isset($file["meta"]["tags"]))
  {
   $tags  = explode(",", $file["meta"]["tags"]);
   foreach($tags as &$tag) $tag = mb_strtolower(trim($tag));
  
   foreach($tags as $tag)
   {
    if(!isset($catalog["by tag"][$tag])) $catalog["by tag"][$tag] = [];
    array_push($catalog["by tag"][$tag], $id);
   }
  }
 
 
  // PARENT
  if(isset($file["cefr"]["parent"]))
  {
   $parent = $file["cefr"]["parent"];
  
   if(!isset($catalog["by parent"][$parent])) $catalog["by parent"][$parent] = [];
   array_push($catalog["by parent"][$parent], $id);
  }
  
  
  // CEFR LEVEL
  if(isset($file["cefr"]["level"]))
  {
   $level   = $file["cefr"]["level"];
  
   if(!isset($catalog["by level"][$level])) $catalog["by level"][$level] = [];
   array_push($catalog["by level"][$level], $id);
  }
  
  
  // LANGUAGE
  if($content == "lessons") $locales = $file["title"] ?? []; else $source = $file["info"] ?? [];
 
  $locales = array_keys($source);
  foreach($locales as $locale)
  {
   if(!isset($language[$locale])) $language[$locale] = [];
   
   $language[$locale][$id] = $source[$locale];
  }
  
 }     
 
 
 // DEDUPLICATE CATALOGS
 foreach(["by tag", "by parent", "by level"] as $category)
 {
  $keys = array_keys($catalog[$category]);
  
  foreach($keys as $key) $catalog[$category][$key] = array_unique($catalog[$category][$key]);
 }
 
 
 // MAP CATALOGS LEVELS BY PARENTS
 $levels  = array_keys($catalog["by level"]);
 $parents = array_keys($catalog["by parent"]);
 $index   = [];
 foreach($levels as $level)
 {
  $index[$level] = [];
  
  foreach($parents as $parent)
  {
   if(in_array($parent, $catalog["by level"][$level]))
   {
	array_push($index[$level], $parent);
   }
  }
 }
 
 $catalog["index"] = $index;
 
 
 
 // SAVE CATALOGS
 $json = json_encode($catalog);
 file_put_contents("content/index/$content-catalog.dat", $json);
 
 
 // SAVE LANGUAGE
 $locales = array_keys($language);
 foreach($locales as $locale)
 {
  $json = json_encode($language[$locale]);
  file_put_contents("content/index/$content-$locale.dat" , $json);
 }
  
 
 // DONE FOR THIS CONTENT CATEGORY
 echo "$content Indexed..<br><br>";
}



echo "done";


?>