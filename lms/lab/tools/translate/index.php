<?php

chdir("../..");

include ".\application\lib\utils.php";

$files   = [];


$found = Storage_Files_Collect(".\application\modules", ["dat", "cfg"], ["recurse"]);
$files = array_merge($files, $found);

$found = Storage_Files_Collect(".\partners\default", ["dat", "cfg"]);
$files = array_merge($files, $found);

foreach($files as &$file) 
{
 $item         = [];
 $item["name"] = str_replace(".\\", "", $file);
 $item["data"] = parse_ini_file($file, true);

 $file         = $item;
}

Client_PublishVar("files", $files);

?>

<style>

.title-file
{
 font-family:    segoe ui;
 font-size:      24px;
 font-weight:    bold;
 padding:        8px;
 text-transform: uppercase;
}



.title-section
{
 font-family:    segoe ui;
 font-size:      16px;
 font-weight:    bold;
 padding:        6px;
 text-transform: capitalize;
}



.box
{
 display:          flex;
 flex-direction:   column;
 padding:          8px;
 gap:              8px;
 xwidth:            576px;
 border:           1px solid silver;
 box-shadow:       0px 11px 15px 2px rgba(0,0,0,0.21);
 background-color: #F1E4D6;
}



.edit
{  
 font-family:     segoe ui;
 width:           100%;
 display:         flex;
 flex-direction:  row;
 padding:         4px;
 gap:             8px;
 justify-content: flex-start;
 align-items:     center;
}



.input-complete
{
 font-family:     segoe ui;
 width:           250px;
 padding:         4px;
 border:          1px solid silver;
}



.input-missing    
{
 font-family:      segoe ui;
 width:            250px;
 padding:          4px;
 border:           1px solid silver;
 background-color: #FFB8C8;
 color:            white;
}

</style>


<body style = "display:flex; flex-direction:row; flex-wrap:wrap; overflow:hidden auto; padding:16px; gap:16px; background-color:#FEE3C6">
</body>


<script src = "../.././application/lib/utils.js">         </script>
<script src = "../.././application/modules/core/core.js"> </script>

<script>


function Input_Class(input)
{
 if(input.value) input.className = "input-complete"; else input.className = "input-missing";
}



function Input_Update(event)
{
 var input = event.currentTarget;
 
 Input_Class(input);
 
 var filename = input.getAttribute("filename");
 var section  = input.getAttribute("section");
 var field    = input.getAttribute("field");
 var value    = input.value;
 
 Core_Api("Ini_Key_Write", {filename, section, field, value});

 console.log(filename, section, field);
}



function Clean_Text(text)
{
 text = text.replace("application/modules/", "");
 text = text.replace("partners/", "");
 text = text.replace(".dat", "");
 
 return text;
}



var translate = {};
var languages = ["en", "vn"];

// SCAN ALL FILES
for(var file of files)
{
 var filename = file["name"];
 var data     = file["data"];
 
 // SCAN SECTIONS IN FILE
 for(var section in data)
 {
  // IF A SECTION HAS "EN" THEN IT COULD REQUIRE TRANSLATION
  if(data[section]["en"])
  {
   data[section]["section"] = section;
   
   if(!translate[filename]) translate[filename] = [];
   translate[filename].push(data[section]);
  }
 }
}


for(var filename in translate)
{  
 var box        = document.createElement("div");
 box.className  = "box";
 
 // FILE NAME
 var file       = document.createElement("div");
 file.className = "title-file";
 file.innerHTML = Clean_Text(filename);
 box.appendChild(file);
 
 
 // SECTIONS
 var sections = translate[filename];
 for(var section of sections)
 {
  var header       = document.createElement("div");
  header.className = "title-section";
  header.innerHTML = section["section"]; ;
  box.appendChild(header);
  
  var edit       = document.createElement("div");
  edit.className = "edit";
  
  for(var language of languages)
  {
   var input         = document.createElement("input");
   input.placeholder = language;
   input.value       = section[language] || ""; 
   
   Input_Class(input);
   input.onchange = Input_Update;
   
   input.setAttribute("filename", filename);
   input.setAttribute("section",  section["section"]);
   input.setAttribute("field",    language);
   
   edit.appendChild(input);
  }
  
  box.appendChild(edit);
 }
 
 document.body.appendChild(box);
}




</script>