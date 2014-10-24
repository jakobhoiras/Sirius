<?php
class Upload {
    function upload_image($game_name , $file_name, $new_name){
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES[$file_name]["name"]);
        $extension = end($temp);
        $path = $_FILES[$file_name]['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ((($_FILES[$file_name]["type"] == "image/gif")
        || ($_FILES[$file_name]["type"] == "image/jpeg")
        || ($_FILES[$file_name]["type"] == "image/jpg")
        || ($_FILES[$file_name]["type"] == "image/pjpeg")
        || ($_FILES[$file_name]["type"] == "image/x-png")
        || ($_FILES[$file_name]["type"] == "image/png"))
        && ($_FILES[$file_name]["size"] < 500000)
        && in_array($extension, $allowedExts)) {
          if ($_FILES[$file_name]["error"] > 0) {
            echo "Return Code: " . $_FILES[$file_name]["error"] . "<br>";
          } else {
            echo "Upload: " . $_FILES[$file_name]["name"] . "<br>";
            echo "Type: " . $_FILES[$file_name]["type"] . "<br>";
            echo "Size: " . round($_FILES[$file_name]["size"] / 1024) . " kB<br>";
            echo "Temp file: " . $_FILES[$file_name]["tmp_name"] . "<br>";
            if (file_exists($game_name . "/" . $_FILES[$file_name]["name"])) {
              echo $_FILES[$file_name]["name"] . " already exists. <br> ";
            } else {
              move_uploaded_file($_FILES[$file_name]["tmp_name"], $game_name .'/' . basename( $_FILES[$file_name]["name"]));
              rename($game_name . "/" . $_FILES[$file_name]["name"], $game_name .'/'. $new_name . '.' . $ext);
              echo "Stored in: " . $game_name . "/" . $_FILES[$file_name]["name"] . '<br>';
             
            }
          }
        } else {
          echo "Invalid file<br>";
        }
    }
}
?>
