<?php
chdir("../..");
error_reporting(E_ERROR | E_PARSE);
function scandocuments(){
    $folder    = 'content/lessons';
    $data = scandir($folder);
    foreach ($data as $itemkey => $item) {
        if (($item != '.') && ($item != '..')) {
            $documentsfolder = "$folder/$item/documents";
            if(file_exists($documentsfolder))
            {
                $documents = scandir($documentsfolder);
                $documents = array_map('strtolower',$documents);
                $ini = "$folder/$item/info.dat";
                $info = parse_ini_file($ini, true) ?? [];
                //check documents
                $inidocuments = $info["documents"];
                // get list document 
                $listdocument = [];
                foreach ($info as $key => $value) {
                    preg_match('/document\s\d\d/', $key, $matches, PREG_OFFSET_CAPTURE);
                    if($matches) $listdocument[] = $value;
                }
                $checklistdocument = false;
                for($i = 0; $i < count($inidocuments); $i++){
                    if($i < 10) $itemdoc = "document 0".$i;
                    else $itemdoc = "document ".$i;
                    $document = $info[$itemdoc] ?? [];
                    //check documents

                    if(!in_array(strtolower($inidocuments[$i]),$documents))
                    {
                        if(count($inidocuments) != count($documents) - 2) {
                           echo "<div>".$item." -- lesson is incomplete-- missing --".$inidocuments[$i]."</div>";  
                        } 
                        else{
                            if($document && in_array(strtolower($document["file"]),$documents))
                            {
                                echo "<div style='color:green'>".$item." -- ".$inidocuments[$i]. " -- can update from document item</div>"; 
                            }
                            else{
                                $checklistdocument = true;
                                //echo "<div style= 'color:red'>".$item." -- ".$inidocuments[$i]. " -- can not found document --need check by hand</div>"; 
                            }
                        }
                    }        
                }
                // check list document
                if($checklistdocument){
                    foreach ($listdocument as $key => $value) {
                        if(!in_array(strtolower($value["file"]),$documents)) 
                         echo "<div style= 'color:red'>".$item." -- ".$value["file"]. " -- can not found document --need check by hand</div>"; 
                    }
                }
            }
        }
    }
    echo "finsish";
}

scandocuments();