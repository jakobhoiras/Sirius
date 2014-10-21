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

<!DOCTYPE html>

<html lang="da">
    <head>

        <style>
            .textwindow {
                resize:none;
            }
        </style>
        <title>Opsætning - opret spil</title>

        <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

        <link rel="stylesheet" type="text/css" href="styleGrøn.css" />



    </head>

    <body>

        <form method="post" action="">
            <select name="opret_opgave">
                <option value="volvo">Volvo</option>
                <option value="saab">Saab</option>
                <option value="opel">Opel</option>
                <option value="audi">Audi</option>
            </select>  
            <br>
        </form>

        <form Name ="form1" Method ="POST" enctype="multipart/form-data" onsubmit= "return check_submit()">
            Opgavens navn:<input type='text' name='opgave_name' /><br>
            <input type="file" name='file1' id="files1" /><br>
            <output id="toolarge1"></output><br>
            <input type="file" name='file2' id="files2" /><br>
            <output id="toolarge2"></output><br>

            <p>Tekst udehold:</p>
            <textarea name='question_out' rows='4' cols='50' class='textwindow'> </textarea><br>
            <p>Tekst indehold:</p>
            <textarea name='question_in' rows='4' cols='50' class='textwindow'> </textarea><br>
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
        <script>
            //Checker om en valgt fil er over en hvis størrelse, og kommer med en advarsel
            //hvis den er.
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
        require_once 'uploader.php';
        require_once 'opgave.php';

        if (isset($_POST['Submit1'])) {

            $opgave = New opgave();
            $opgave->opgave_gemmer();
            $upload = New Upload();
            $upload->upload_image('uploads', 'file1');
            $upload->upload_image('uploads', 'file2');
        }
        ?>

    </body>
</html>	

<!--	 -->
