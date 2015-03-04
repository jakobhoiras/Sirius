<?php

require_once 'Membership.php';
$membership = New Membership();
$membership->confirm_Both();
$membership->check_Active();

$mysql = new Mysql_spil();
$res = $mysql -> get_overview_settings();
$screens = $res[0][0];

?>

<script>

function change_page(page_name) {
   if (page_name == 'spil_overblik'){
        window.location.href = ("http://localhost/sirius/" + page_name + ".php?cg=" + <?php echo json_encode($_SESSION['cg']) ?>);
    } else{
        window.location.href = ("http://localhost/sirius/" + page_name + ".php");
    }
}

</script>

<html>
    <head lang="da">
        <meta http-equiv="Content-Type" content="text/html; charset=utf8">
        <title>
            Overview screen
        </title>
    </head>
    <body>
        <div style="width:100%; padding-bottom:5px">
            <?php
                if ($_SESSION['status'] == 'authorized_admin'){
                    echo '<button id="back" type="button" onclick=change_page("spil_overblik") >Game menu</button>';
                } else{
                    echo '<button id="back" type="button" onclick=change_page("start_user") >Start menu</button>';
                }
            ?>
        <div style="width:500px; margin-left:auto; margin-right:auto">
            <?php for ($i=1; $i<$screens+1; $i++){echo "<a href=\"teams_loc_overview.php?screen=$i\">Screen $i</a><br/>";} ?>
        </div>
    </body>
</html>
