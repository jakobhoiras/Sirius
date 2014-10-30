<?php

class opgave {

    function gemmer($text, $filename, $name) {


        $file = fopen('Opgaver/' . $name . '/' . $filename . '.txt', "w") or die("Unable to open file!");
        $txt = $text;
        fwrite($file, $txt);
        fclose($file);
    }

    function opgave_gemmer() {
        if (isset($_POST['question_out'], $_POST['question_in'], $_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4'], $_POST['check'], $_POST['opgave_name'])) {

            $name = $_POST['opgave_name'];
            $out = $_POST['question_out'];
            $in = $_POST['question_in'];
            $mult_text = array($_POST['mult0'], $_POST['mult1'], $_POST['mult2'], $_POST['mult3'], $_POST['mult4']);
            $mult_cor = $_POST['check'];
            
            if (!file_exists(getcwd() . '/Opgaver/')) {
                mkdir(getcwd() . '/Opgaver');
            }
            if (!file_exists(getcwd() . '/Opgaver/' . $name)) {
                mkdir(getcwd() . '/Opgaver/' . $name, 0777, true);
            } else {
                die("Opgave navn er allerede taget");
            }

            $this->gemmer($out, $name . '_udehold', $name);
            $this->gemmer($in, $name . '_indehold', $name);
            for ($i = 0; $i < 5; $i++) {
                $this->gemmer($mult_text[$i], $name . '_mult' . $i, $name);
            }
            $this->gemmer($mult_cor, $name . '_mult_korrektNr', $name);
        } else {
            echo 'one or more fields were not filled out <br>';
        }
    }

    function select_opgave() {
        $opgaver = scandir(getcwd() . '/Opgaver');
        $opgaver2 = array_diff($opgaver, array('.', '..'));

        echo '<select name="vælg opgave" value="">';
        foreach ($opgaver2 as $val) {
            echo '<option value="' . $val . '">' . $val . '</option>';
        }
        echo '</select>';
    }

}
