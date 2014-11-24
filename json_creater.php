<?php

// array('text' => 'abc', 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5)
// $path = path + filnavn (eks: sirius/Opgaver/test1) hvilket giver en json fil test1.json
class json {

    function createJson($array, $path) {
		//header('Content-Type: application/json');
        $result = json_encode($array);
        $file = $file = fopen($path . '.json', "w") or die("Unable to open file!");
        fwrite($file, $result);
        fclose($file);
    }
}

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

