<?php

include 'phpqrcode/qrlib.php';

QRcode::png('{"url":"http:\/\/t-a-g.dk\/getGame.php","gameID":"test","teamID":2}', 'qr_test.png', QR_ECLEVEL_L, 10);
?>
