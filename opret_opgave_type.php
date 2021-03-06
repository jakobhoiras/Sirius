<?php
require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Both();
$membership->check_Active();
//$mysql = new Mysql_spil();
//if( $_POST && !empty($_POST['opret_opgave']) ) {
//    $response = $mysql->opret_opgave($_POST['opret_opgave'], $_SESSION['spil_navn']);
//    header('location: opgave_GUI.php');
//}

if ($_POST && !empty($_POST['logout'])) {
    $membership->log_User_Out();
    header('location: login.php');
}

require_once 'opgave.php';
$opgave = New opgave();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">

<html lang="da">
    <head>

        <link rel="stylesheet" type="text/css" href="opgave.css">
        <title>Opsætning - opret spil</title>

        <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
        <script src="opgave.js"></script>



    </head>

    <body>

        <form method="post" action="">
            <?php
            if ($_SESSION['status'] == 'authorized_admin') {
                echo '<input type="button" value="Start menu" onclick=change_page("start_admin") />';
            } else {
                echo '<input type="button" value="Start menu" onclick=change_page("start_user") />';
            }

            if (isset($_POST['Submit1'])) {
                $opgave->opgave_gemmer();
            }
            if (isset($_POST['submitDel'])) {
                $opgave->deleteAssignment();
            }
            if (isset($_POST['submitPre'])) {
                $_SESSION['pv'] = $_POST['selectPreview'];
            }
            ?>
            <form method="post">
                <input type="submit" value="Log out" style="float:right" name="logout" /><br>
            </form>
        </form>

        <div class="entire_field">
            <div class="input_felt">
                <form Name ="form1" Method ="POST" enctype="multipart/form-data" onsubmit= "return confirm('are you sure you wish to upload this assignment?')">
                    <p>Name of assignment:<br>
                        <input type="text" name="assignmentName"> </p>
                    <div name="inputOutDiv" class="felt">
                        <h1> Field Team </h1>
                        <p>Chose layout: <select onchange="optionCheck('Out')" id="formOut" name="formOut">
                                <option value="textImage">Text + Image</option>
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                            </select></p>


                        <div id="imageOut"><p> Chose a picture:<br>
                                <input type="file" name='file1' id="files1" onchange="checkFile(1)" /><br>
                            <output id="toolarge1"></output></p> </div>
                        <div id="textOut"><p> Description:<br>
                                <textarea name='questionOut' rows='4' cols='50'class='textWindow' value = 'hey'></textarea></p> </div>

                    </div>

                    <div name="inputOutDiv" class="felt">
                        <h1> Base Team </h1>
                        Chose layout: <select onchange="optionCheck('In')" id="formIn" name="formIn">
                            <option value='textImage'>Text + Image</option>
                            <option value="text">Text</option>
                            <option value="image">Image</option>
                        </select><br>

                        <div id="imageIn"><p> Chose a picture:<br>
                                <input type="file" name='file2' id="files2" onchange="checkFile(2)"/><br>
                            <output id="toolarge2"></output></p> </div>

                        <div id="textIn"><p> Description:<br>
                                <textarea name='questionIn' rows='4' cols='50' class='textWindow'></textarea></p> </div>
                        <p>Multiple-choice:<br>
                            <small>Please choose the name to be displayed at each possible answer<br>
                                (and please choose which one is the correct answer)</small></p>
                        <input type='text' name='mult0' /><input type='radio' name='check' value='1'/><br>
                        <input type='text' name='mult1' /><input type='radio' name='check' value='2'/><br>
                        <input type='text' name='mult2' /><input type='radio' name='check' value='3'/><br>
                        <input type='text' name='mult3' /><input type='radio' name='check' value='4'/><br>
                        <input type='text' name='mult4' /><input type='radio' name='check' value='5'/><br>



                    </div>
                    <p><input TYPE = "Submit" Name = "Submit1" VALUE = "Upload"></p>

                </form>

                <form method="post" id="deletion" onsubmit= "return confirm('are you sure you wish to Delete this assignment?')">

                    <div class="felt">
                        <h2>Chose an assignment for deletion</h2>
                        <p><?php
                            $opgave->populate('selectDelete');
                            ?></p>
                        <input type="submit" name="submitDel" id="submitDel" value="Delete">
                    </div>


                </form>
            </div>






            <div class="preview">

                <h1> Preview </h1>
                <div class="previewOut">
                    <div class="previewOutTitle"> <?php $opgave->previewer('', 'name','yes'); ?></div>
                    <div class="previewOutPic"><?php $opgave->previewer('out', 'img','yes'); ?></div>
                    <div class="previewOutTxt"><?php $opgave->previewer('out', 'text','yes'); ?></div>
                </div>
                <div class="previewIn">

                    <div class="previewInTitle"> <?php $opgave->previewer('', 'name','yes'); ?></div>
                    <div class="previewInPic"><?php $opgave->previewer('in', 'img','yes'); ?></div>
                    <div class="previewInTxt">
                        <?php $opgave->previewer('in', 'text','yes'); ?>
                        <?php $opgave->previewer('', 'ans','yes'); ?>
                    </div>
                </div>
                <div class="previewChose">

                    <form method="post" id="preview">
                        <p>Chose an assignment to preview</p>
                        <p><?php $opgave->populate('selectPreview'); ?></p>
                        <p><input type="submit" name="submitPre" id="submitPre" value="Preview"></p>
                    </form>
                </div>
            </div>
        </div>

        <!--<div class="input_felt">
            <div  id="udehold-output" class="felt3">
                <div class='header-ude'><font size="4">
                        <php
                        if (isset($_POST['preview-ude']) && isset($_POST['header-ude']) && $_POST['header-ude'] != "") {
                            echo $_POST['header-ude'];
                        } else {
                            echo "Opgavenavn";
                        }
                        ?>
                    </font>
                </div>
                <div class='pic-ude'><img src="Opgaver\ude.jpg" class="center"></div>

                <div class='text-ude'>
                    <font size="2"><php
                        if (isset($_POST['question_out']) && isset($_POST['preview-ude'])) {
                            echo $_POST['question_out'];
                        }
                        ?></font>
                </div>
            </div>
        </div> -->           



    </body>
</html>	

<!--	 -->
