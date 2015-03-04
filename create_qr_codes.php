<?php

include 'phpqrcode/qrlib.php';

function create_qr_codes(){
    $path = getcwd() . "/Games/" . $_SESSION['cg'] . "/json/";
    $files = array_diff(scandir($path),array('.','..'));
    if(array_search('qr_codes', scandir($path))==FALSE){
        mkdir($path . 'qr_codes');
    }
    for ($i=2; $i<sizeof($files)+2;$i++){
        if (substr($files[$i],0,4)=='iden'){
            $json_file = fopen($path . $files[$i], "r") or die("Unable to open file!");
            $txt = fgets($json_file);
            fclose($json_file);
            QRcode::png($txt, $path . 'qr_codes/qr_code_team_' . filter_var($files[$i], FILTER_SANITIZE_NUMBER_INT) . '.png', QR_ECLEVEL_L, 10);
        }
    }
    chdir($path);
    shell_exec('zip -r qr_codes.zip qr_codes');
    return true;
}

?>
