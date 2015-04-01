<?php
require_once 'opgave.php';
$opgave = New opgave();

if (!isset($_SESSION['assignNr'])) {
    $_SESSION['assignNr'] = 2;
}
if (isset($_POST['submitPre'])) {
    $opgave->chosePrev ('chose');    
}
if (isset($_POST['submitPrev'])) {
    $opgave->chosePrev ('prev');
}
if (isset($_POST['submitNext'])) {
    $opgave->chosePrev ('next');
}
?>
<script>
function change_page(page_name) {
        window.location.href = (page_name + ".php");
}
</script>

<html lang="da">
    <head>

        <link rel="stylesheet" type="text/css" href="opgave.css">
        <title>Opsætning - opret spil</title>

        <meta name="keywords" content="Lektiehjælp, Uddanelse, Matematik, Fysik, Kemi, Matkon, Service" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">

    </head>

    <body>
        <div style="width:100%; padding-bottom:5px">
        <button id="back" type="button" onclick=change_page('consultant_panel')>Consultant menu</button>
        <form method="post" style="display:inline">
            <input type="submit" value="Log out" style="float:right" name="logout" /><br>
        </form>
    </div>
        
        <div class="preview">

            <div class="previewChose">

                <form method="post" id="preview">
                    <p>Chose an assignment to view</p>
                    <p><?php $opgave->populate2('selectPreview'); ?>
                    </p>
                </form>
            </div>
            <div class="previewOut">
                <div class="previewOutTitle"> <?php $opgave->previewer('', 'name','no'); ?></div>
                <div class="previewOutPic"><?php $opgave->previewer('out', 'img','no'); ?></div>
                <div class="previewOutTxt"><?php $opgave->previewer('out', 'text','no'); ?></div>
            </div>
            <div class="previewIn">

                <div class="previewInTitle"> <?php $opgave->previewer('', 'name','no'); ?></div>
                <div class="previewInPic"><?php $opgave->previewer('in', 'img','no'); ?></div>
                <div class="previewInTxt">
                    <?php $opgave->previewer('in', 'text','no'); ?>
                    <?php $opgave->previewer('', 'ans','no'); ?>
                </div>
            </div>
            
        </div>

    </body>
</html>


