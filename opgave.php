<?php
require_once 'Mysql_opgave.php';

class opgave {

    function gemmer($text, $filename, $name) {
        $file = fopen('Opgaver/' . $name . '/' . $filename . '.txt', "w") or die("Unable to open file!");
        $txt = $text;
        fwrite($file, $txt);
        fclose($file);
    }
    
    function upload_image($game_name , $file_name, $new_name, $team){
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
            if (file_exists($game_name . "/" . $_FILES[$file_name]["name"])) {
              echo $_FILES[$file_name]["name"] . " already exists. <br> ";
            } else {
              move_uploaded_file($_FILES[$file_name]["tmp_name"], $game_name .'/' . basename( $_FILES[$file_name]["name"]));
              rename($game_name . "/" . $_FILES[$file_name]["name"], $game_name .'/'. $new_name . '.' . $ext);
              }
          }
        } else {
          echo "No image was uploaded for " . $team ."<br>";
        }
    }

    function opgave_gemmer() {
        
        $Assignment = new Mysql_assignment ();
        
        if (isset($_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4'], $_POST['check'], $_POST['assignmentName'])) {           
        	$name = $_POST['assignmentName'];
            $mult_text = array($_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4']);
            $mult_cor = $_POST['check'];
            $typeOut = $_POST['formOut'];
            $typeIn = $_POST['formIn'];

            if ((($typeOut === "text" || $typeOut === "textImage")&&(empty($_POST['questionOut'])))||
                   (($typeIn === "text" || $typeIn === "textImage")&&(empty($_POST['questionOut'])))) {
                echo "one or more fields where not filled out<br>";
            } else {

            	$check = $Assignment->create_assignment($name);

                if ($check === 0) {
                    if (!file_exists(getcwd() . '/Opgaver/')) {
                        mkdir(getcwd() . '/Opgaver');
                    }
                    if (!file_exists(getcwd() . '/Opgaver/' . $name)) {
                        mkdir(getcwd() . '/Opgaver/' . $name, 0777, true);
                    } else {
                        die("Opgave navn er allerede taget");
                    }
                    if ($typeOut === "text" || $typeOut === "textImage") {
                        if (isset($_POST['questionOut'])) {
                            $out = $_POST['questionOut'];
                            $this->gemmer($out, 'textOut', $name);
                        }
                    }
                    if ($typeOut === "textImage" || $typeOut === "image") {
                        $this->upload_image('Opgaver/' . $name, 'file1', 'imageOut', "Field team");
                    }
                    if ($typeIn === "textImage" || $typeIn === "text") {
                        if (isset($_POST['questionOut'])) {
                            $in = $_POST['questionIn'];
                            $this->gemmer($in, 'textIn', $name);
                    	}
                	}
                	if ($typeIn === "textImage" || $typeIn === "image") {
                    $this->upload_image('Opgaver/' . $name, 'file2', 'imageIn', "Base Team");
                	}
                	for ($i = 0; $i < 5; $i++) {
                    	$i2 = $i + 1;
                    	$this->gemmer($mult_text[$i], 'multiple' . $i2, $name);
                	}
                	$this->gemmer($mult_cor, 'multipleNr', $name);
                	$this->gemmer($typeOut, 'typeOut', $name);
                	$this->gemmer($typeIn, 'typeIn', $name);

                	echo 'The assignment has been uploaded successfully';
            
            	} else {
            		echo 'A game with that name already exist';
        		}
        	}
        }else {
        	echo 'one or more fields were not filled out <br>';
        }
    }
    
    function deleteAssignment() {
        $assign = $_POST['selectDelete'];
        $path = getcwd() . "/Opgaver/" . $assign;
   
        $this->Delete($path);
        $Assignment = new Mysql_assignment ();
        $Assignment->delete_assignment ($assign);
     
    }

    function Delete($path) {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $this->Delete(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
        
    }

    function populate() {
        $directory = getcwd()."/Opgaver/";

//get all files in specified directory
        $files = array_diff(scandir($directory), array('.', '..'));

//print each file name
        echo "<select name=\"selectDelete\">";
        foreach ($files as $file) {
            //check to see if the file is a folder/directory
            echo "<option value=\"" . $file . "\">" . $file . "</option>";
        }
        echo "</select>";
    }

}
