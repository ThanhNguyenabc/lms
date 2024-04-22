<?php
chdir("../..");
define("page", <<<HTML
<html>
   <body>
      <p>Click to generate list vocabulary miss image in folder when update new image from contdev</p>
      <form action="#" method="POST">
            <button name="submit" value="compare">Compare</button>
      </form>
      <br/>
      <p>Click to update new image from contdev to vocabulary folder </p>
      <form action="#" method="POST">
            <button name="submit" value="merge">Merge</button>
      </form>
      <br/>
     
   </body>
</html>
HTML);
function changeNameVocab(){
    $folderVocab = 'content/vocabulary';
    $listVocab = scandir($folderVocab);
    foreach ($listVocab as $key => $vocab) {
        if(($vocab != '.') && ($vocab != '..') && !file_exists("content/vocabulary/$vocab/picture.png")){
            //check image
            $invocab = scandir("content/vocabulary/$vocab");
            foreach ($invocab as $key => $filename) {
                if(($filename != '.') && ($filename != '..')){
                    if(strpos($filename, "png") !== false || strpos($filename, "jpg") !== false || strpos($filename, "jpeg") !== false){
                        rename("content/vocabulary/$vocab/$filename", "content/vocabulary/$vocab/picture.png");
                        echo "<div>$invocab/$filename</div><br/>";
                    }
                }
            }
        }
    }
}

function compareVocab(){
    $folderVocab = 'content/vocabulary';
    $listVocab = scandir($folderVocab);
    $listContdevVocab1 = scandir("content/hybrid_vocab_images/1");
    $listContdevVocab2 = scandir("content/hybrid_vocab_images/2");
    $listContdevVocab3 = scandir("content/hybrid_vocab_images/3");
    $listContdevVocab4 = scandir("content/hybrid_vocab_images/4");

    $listContdevVocab = array_merge($listContdevVocab1,$listContdevVocab2,$listContdevVocab3,$listContdevVocab4);
    echo "<div style='display:flex;height:18%'>";
    echo "<div style='width:50%;height:100%;overflow:auto;'>";
    echo "<div style='background-color:#00FA9A;font-size:30px;width:auto'>Đã có picture</div><div style='background-color:#AFEEEE;font-size:30px'>Chưa có picture nhưng có trong contdev</div>";
    echo "<div style='background-color:#F08080;font-size:30px'>Chưa có picture và chưa có trong contdev</div><br/>";
    echo "</div>";
    echo "<div style = 'width:50%;height:100%;overflow:auto;'>";
    echo "<h3>Chưa thể compare</h3>";
    echo "</div></div>";
    echo "<div style='display:flex;height:80%'>";
    echo "<div style='width:50%;height:100%;overflow:auto;'>";
    $arraycheck = [];
    for ($i=0; $i < count($listContdevVocab); $i++) { 
        $arraycheck[$i] = 0;
    }
    $count = 0;
    foreach ($listVocab as $key => $vocab) {
        if(($vocab != '.') && ($vocab != '..'))
        if(file_exists("content/vocabulary/$vocab/picture.png")){
            echo "<div style='background-color:#00FA9A'>$vocab</div>";
        }else{
            if(in_array("$vocab.png",$listContdevVocab)){
                echo "<div style='background-color:#AFEEEE'>$vocab</div>";
                $folder = array_search("$vocab.png",$listContdevVocab);
                $arraycheck[$folder] = 1;
            }
            else{
                $count++;
                echo "<div style='background-color:#F08080'>$vocab</div>";
            }
        }
    }
    echo "</div>";
    echo "<div style = 'width:50%;height:100%;overflow:auto;'>";
    $count1 = 0;
    foreach ($arraycheck as $key => $value) {
        if($value == 0 && $listContdevVocab[$key] != "." && $listContdevVocab[$key] != "..")
        {
            $count1 ++;
            echo "<p>$listContdevVocab[$key]</p>";
        }
    }
    
    echo "</div>";
    echo "</div>";
    echo "<div><div style='background-color:#F08080'>$count</div><div>$count1</div></div>";
}

function mergeVocab(){
    $folderVocab = 'content/vocabulary';
    $listVocab = scandir($folderVocab);
    $listContdevVocab1 = scandir("content/hybrid_vocab_images/1");
    $listContdevVocab2 = scandir("content/hybrid_vocab_images/2");
    $listContdevVocab3 = scandir("content/hybrid_vocab_images/3");
    $listContdevVocab4 = scandir("content/hybrid_vocab_images/4");

    $listContdevVocab = array_merge($listContdevVocab1,$listContdevVocab2,$listContdevVocab3,$listContdevVocab4);

    foreach ($listVocab as $key => $vocab) {
        if(($vocab != '.') && ($vocab != '..'))
        if(file_exists("content/vocabulary/$vocab/picture.png")){
        }else{
            if(in_array("$vocab.png",$listContdevVocab)){
                echo "<div style='background-color:#AFEEEE'>$vocab</div><br/>";
                $vkey = array_search("$vocab.png",$listContdevVocab);
                $image = $listContdevVocab[$vkey];
                if(file_exists("content/hybrid_vocab_images/1/$image"))
                    copy("content/hybrid_vocab_images/1/$image","content/vocabulary/$vocab/picture.png");
                else
                if(file_exists("content/hybrid_vocab_images/2/$image"))
                    copy("content/hybrid_vocab_images/2/$image","content/vocabulary/$vocab/picture.png");
                else
                if(file_exists("content/hybrid_vocab_images/3/$image"))
                    copy("content/hybrid_vocab_images/3/$image","content/vocabulary/$vocab/picture.png");
                else
                if(file_exists("content/hybrid_vocab_images/4/$image"))
                    copy("content/hybrid_vocab_images/4/$image","content/vocabulary/$vocab/picture.png");
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo page;
}
if(isset($_POST["submit"])){
    switch ($_POST["submit"]) {
        case 'changename':
            changeNameVocab();
            break;
        case 'merge':
            mergeVocab();
            break;
        default:
            compareVocab();
            break;
    }
}

?>