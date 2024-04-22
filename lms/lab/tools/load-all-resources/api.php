<?PHP
    chdir("../..");
    include "application\lib\utils.php";
    define("defaultTypes", ["png","jpg","mp4","mp3","pdf","txt", "svg"]);

    function getAllFiles($path, $fileTypes = defaultTypes) {
        $files   = Storage_Files_Collect($path, $fileTypes, ["uproot", "recurse"]) ?? [];
        return $files;
    };

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $folder = $_GET["folder"];
        echo json_encode(array("data" => getAllFiles($folder)));
    } 
?>

