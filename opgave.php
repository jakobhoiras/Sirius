<?php

require_once 'Mysql_opgave.php';
require_once 'zipper.php';

class opgave {

    function gemmer($text, $filename, $name) {
        $file = fopen('Opgaver/' . $name . '/' . $filename . '.json', "w") or die("Unable to open file!");
        $txt = $text;
        fwrite($file, $txt);
        fclose($file);
    }

    function upload_image($game_name, $file_name, $new_name, $team) {
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES[$file_name]["name"]);
        $extension = end($temp);
        $path = $_FILES[$file_name]['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        if ((($_FILES[$file_name]["type"] == "image/gif") || ($_FILES[$file_name]["type"] == "image/jpeg") || ($_FILES[$file_name]["type"] == "image/jpg") || ($_FILES[$file_name]["type"] == "image/pjpeg") || ($_FILES[$file_name]["type"] == "image/x-png") || ($_FILES[$file_name]["type"] == "image/png")) && ($_FILES[$file_name]["size"] < 500000) && in_array($extension, $allowedExts)) {
            if ($_FILES[$file_name]["error"] > 0) {
                echo "Return Code: " . $_FILES[$file_name]["error"] . "<br>";
            } else {
                if (file_exists($game_name . "/" . $_FILES[$file_name]["name"])) {
                    echo $_FILES[$file_name]["name"] . " already exists. <br> ";
                } else {
                    move_uploaded_file($_FILES[$file_name]["tmp_name"], $game_name . '/' . basename($_FILES[$file_name]["name"]));
                    rename($game_name . "/" . $_FILES[$file_name]["name"], $game_name . '/' . $new_name . '.' . $ext);
                }
            }
        } else {
            echo "No image was uploaded for " . $team . "<br>";
        }
        return $ext;
    }

    function opgave_gemmer() {

        $Assignment = new Mysql_assignment ();

        if (isset($_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4'], $_POST['check'], $_POST['assignmentName'])) {
            $name = $_POST['assignmentName'];
            $mult_text = array($_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4']);
            $mult_cor = $_POST['check'];
            $typeOut = $_POST['formOut'];
            $typeIn = $_POST['formIn'];
            $options = array();



            if ((($typeOut === "text" || $typeOut === "textImage") && (empty($_POST['questionOut']))) ||
                    (($typeIn === "text" || $typeIn === "textImage") && (empty($_POST['questionIn'])))) {
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

                    $in = "";
                    $out = "";
                    $imgIn = "";
                    $imgOut = "";
                    if ($typeOut === "text" || $typeOut === "textImage") {
                        if (isset($_POST['questionOut'])) {
                            $out = $_POST['questionOut'];
                        }
                    }
                    if ($typeOut === "textImage" || $typeOut === "image") {
                        $ext = $this->upload_image('Opgaver/' . $name, 'file1', 'imageOut', "Field team");
                        $imgOut = 'imageOut.' . $ext;
                    }
                    if ($typeIn === "textImage" || $typeIn === "text") {
                        if (isset($_POST['questionIn'])) {
                            $in = $_POST['questionIn'];
                        }
                    }
                    if ($typeIn === "textImage" || $typeIn === "image") {
                        $ext = $this->upload_image('Opgaver/' . $name, 'file2', 'imageIn', "Base Team");
                        $imgIn = 'imageIn.' . $ext;
                    }

                    for ($i = 1; $i <= sizeof($mult_text); $i++) {

                        $correct = false;
                        if (($mult_cor . "") === $i . "") {
                            $correct = true;
                        }
                        $optArray = array(
                            "text" => $mult_text[$i - 1],
                            "image" => "",
                            "correct" => $correct
                        );
                        array_push($options, $optArray);
                    }

                    $ansArray = array(
                        "options" => $options,
                        "hintImage" => $imgIn,
                        "hintText" => $in
                    );
                    $qstArray = array(
                        "text" => $out,
                        "image" => $imgOut
                    );
                    $array = array(
                        "question" => $qstArray,
                        "answer" => $ansArray
                    );

                    $finArray = json_encode($array);

                    $this->gemmer($finArray, 'question', $name);

                    $_SESSION['pv'] = $name;

                    echo 'The assignment has been uploaded successfully';
                } else {
                    echo 'A game with that name already exist';
                }
            }
        } else {
            echo 'one or more fields were not filled out <br>';
        }
    }

    function deleteAssignment() {
        $assign = $_POST['selectDelete'];
        $path = getcwd() . "/Opgaver/" . $assign;

        $this->Delete($path);
        $Assignment = new Mysql_assignment ();
        $Assignment->delete_assignment($assign);
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

    function Zip() {

        $assign = $_POST['selectZip'];
        $path = getcwd() . "/Opgaver/" . $assign;

        $zipper = new zipper ();
        $zipper->zip($path, $path . ".zip");
    }

    function populate($postName) {
        $directory = getcwd() . "/Opgaver/";

//get all files in specified directory
        $files = array_diff(scandir($directory), array('.', '..'));

//print each file name
        echo "<select name=\"" . $postName . "\">";
        foreach ($files as $file) {
//check to see if the file is a folder/directory
            echo "<option value=\"" . $file . "\">" . $file . "</option>";
        }
        echo "</select>";
    }

    function populate2($postName) {
        $directory = getcwd() . "/Games/" . $_SESSION['cg'] . "/questionfile/";

//get all files in specified directory
        $files = array_diff(scandir($directory), array('.', '..'));

//print each file name
        $first = $files[2];
        if (!isset($_SESSION['pv'])) {
            $_SESSION['pv'] = $first;
        }
        $last = $files[sizeof($files) + 1];
        echo "<select name=\"" . $postName . "\">";
        foreach ($files as $file) {
            $selected = "";
            if ($file === $_SESSION['pv']) {
                $selected = "selected=\"selected\"";
            }
            echo "<option ".$selected." value=\"" . $file . "\">" . $file . "</option>";
        }
        echo "</select><input type=\"submit\" name=\"submitPre\" id=\"submitPre\" value=\"Preview\"></p><p>";                
        if ($_SESSION['pv'] !== $first) {
            echo "<input type=\"submit\" name=\"submitPrev\" id=\"submitPrev\" value=\"previous\">";
        }
        if ($_SESSION['pv'] !== $last) {
            echo "<input type=\"submit\" name=\"submitNext\" id=\"submitNext\" value=\"next\">";
        }
    }

    function previewer($team, $type, $pre) {
        if (isset($_SESSION['pv'])) {
            $assign = $_SESSION['pv'];
            if ($pre === 'yes') {
                $address = getcwd() . "/Opgaver/" . $assign . "/question.json";
            } else {
                $address = getcwd() . "/Games/" . $_SESSION['cg'] . "/questionfile/" . $assign . "/question.json";
            }
            $json = file_get_contents($address);
            $json_output = json_decode($json, true);

            if ($type === "img") {
                if ($team === "in") {
                    $img = $json_output['answer']['hintImage'];
                    if ($img !== "") {
                        echo "<img src = \"Opgaver/" . $assign . "/" . $img . "\"class=\"centerImg\">";
                    }
                }
                if ($team === "out") {
                    $img = $json_output['question']['image'];
                    if ($img !== "") {
                        echo "<img src = \"Opgaver/" . $assign . "/" . $img . "\"class=\"centerImg2\">";
                    }
                }
            }
            if ($type === "text") {
                if ($team === "in") {
                    $text = $json_output['answer']['hintText'];
                }
                if ($team === "out") {
                    $text = $json_output['question']['text'];
                }
                if ($text !== "") {
                    echo $text;
                }
            }
            if ($type === "ans") {
                for ($i = 0; $i < 5; $i++) {
                    $answer = $json_output['answer']['options'][$i]['text'];
                    $true = $json_output['answer']['options'][$i]['correct'];
                    $correct = "previewAnswer";
                    if ($true === true) {
                        $correct = "previewAnswer2";
                    }

                    echo "<hr/><div class=\"" . $correct . "\">" . $answer . "</div><br/>";
                }
            }
            if ($type === "name") {
                echo $assign;
            }
        }
    }

    function chosePrev($direction) {

        $directory = getcwd() . "/Games/" . $_SESSION['cg'] . "/questionfile/";
        $files = array_diff(scandir($directory), array('.', '..'));

        if ($direction === 'prev') {
            $_SESSION['assignNr'] = $_SESSION['assignNr'] - 1;
        }
        if ($direction === 'next') {
            $_SESSION['assignNr'] = $_SESSION['assignNr'] + 1;
        }
        if ($direction === 'next' or $direction === 'prev') {

            foreach ($files as $key => $file) {
                if ($key === $_SESSION['assignNr']) {
                    $_SESSION['pv'] = $file;
                }
            }
        }
        if ($direction === 'chose') {
            $_SESSION['pv'] = $_POST['selectPreview'];
            foreach ($files as $key => $file) {
                if ($file === $_SESSION['pv']) {
                    $_SESSION['assignNr'] = $key;
                }
            }
        }
    }

}
