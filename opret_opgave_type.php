<!--?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Admin();
$membership->check_Active();
$mysql = new Mysql_spil();

if( $_POST && !empty($_POST['opret_opgave']) ) {
    $response = $mysql->opret_opgave($_POST['opret_opgave'], $_SESSION['spil_navn']);
    header('location: opgave_GUI.php');
}

?-->
<?php
require_once 'opgave.php';
require_once 'uploader.php';
$opgave = New opgave();
$upload = New Upload();
?>

<!DOCTYPE html>

<html lang="da">
    <head>

        <style>
            .textwindow {
                resize:none;
                width: 380px;
            }
            .input_felt{
                width: 410px;
                float: left;
                border: transparent;
            }
            .felt {
                width: 400px;
                height: 250px;
                border: solid;
                float: left;
            }
            .felt2 {
                width: 400px;
                border: solid;
                float: left;
            }
        </style>
        <title>Opsætning - opret spil</title>

        <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

        <link rel="stylesheet" type="text/css" href="styleGrøn.css" />



    </head>

    <body>
        <form method="post" action="">
            <input type="button" value="Back to start" onclick="change_page('start')"/><br>
                       

        </form>
        
       
                <form Name ="form1" Method ="POST" enctype="multipart/form-data" onsubmit= "return check_submit()">
                    <p>Opgavens navn:<p>
                <input type='text' name='opgave_name' /><br>    
<div class="input_felt">
            <div class="felt">
                    <h1> Udeholdet </h1>

                    Billede til udeholdet:<br>
                    <input type="file" name='file1' id="files1" /><br>
                    <output id="toolarge1"></output><br>

                    Opgavens beskrivelse:<br>
                    <textarea name='question_out' rows='4' cols='50' class='textwindow' value = 'hey'></textarea><br>
                    </div>
                    <div class="felt2">
                        <h1> Indeholdet </h1>

                        Billede til udeholdet:<br>
                        <input type="file" name='file2' id="files2" /><br>
                        <output id="toolarge2"></output><br>

                        Opgavens beskrivelse:<br>
                        <textarea name='question_in' rows='4' cols='50' class='textwindow'></textarea><br>
                        <p>Multiple-choice:<br>
                            <small>Please choose the name to be displayed at each possible answer<br>
                                (and please choose which one is the correct answer)</small></p>
                        <input type='text' name='mult0' /><input type='radio' name='check' value='0'/><br>
                        <input type='text' name='mult1' /><input type='radio' name='check' value='1'/><br>
                        <input type='text' name='mult2' /><input type='radio' name='check' value='2'/><br>
                        <input type='text' name='mult3' /><input type='radio' name='check' value='3'/><br>
                        <input type='text' name='mult4' /><input type='radio' name='check' value='4'/><br>

                        <input TYPE = "Submit" Name = "Submit1" VALUE = "Upload">
                        </form>
                    </div>
            </div>
                    <div class="input_felt">
            <div class="felt">
                <img src="Opgaver/ude.jpg" alt="Her kommer billedet til at være" style="width:230px;height:150px"><br>
                <font size="2"><?php
                if (isset($_POST['question_out'])) {
                    echo $_POST['question_out'];
                }
                ?></font>
            </div>
                    </div>
            <script>
                //Checker om en valgt fil er over en hvis størrelse, og kommer med en advarsel
                //hvis den er.
                function change_page(page_name) {
                    window.location.href = ("http://localhost/sirius/" + page_name + ".php");
                }

                function large_file(fileNr, check) {
                    var control = document.getElementById("files" + fileNr);

                    function error_printer() {
                        var file = control.files;
                        var size = Math.round((file[0].size / 1048576) * 100) / 100;
                        if (size > 0) {
                            var str = 'File is too large: ' + size + 'mb' + ' - we only support 0mb';
                            var result = str.fontcolor("red");
                            document.getElementById('toolarge' + fileNr).innerHTML = result;
                        }
                    }
                    control.addEventListener("change", error_printer, false);
                    if (check == 1) {
                        var file2 = control.files;
                        var size2 = Math.round((file2[0].size / 1048576) * 100) / 100;
                        return size2;
                    }
                }

                large_file(2, 0);
                large_file(1, 0);

                function check_submit() {
                    if (large_file(2, 1) > 0) {
                        alert("Atleast one of the files is too large");
                        return false;
                    }
                    if (large_file(1, 1) > 0) {
                        alert("Atleast one of the files is too large");
                        return false;
                    }
                }
            </script>
            <?php
            if (isset($_POST['Submit1'])) {

                $opgave->opgave_gemmer();
                $upload->upload_image('Opgaver', 'file1', 'ude');
                $upload->upload_image('Opgaver', 'file2', 'inde');
            }
            ?>

    </body>
</html>	

<!--	 -->
