<html><head>
<link rel="shortcut icon" href="../ts/img/teamspeak.ico">
<title>TSStatus</title>
<link rel="stylesheet" type="text/css" href="../ts/tsstatus.min.css" />
</head><body><br><br><br><br>
<?php $firefox = strpos($_SERVER["HTTP_USER_AGENT"], 'Firefox') ? true : false; if(!$firefox){?><br><?php } ?>
<?php
require_once("../ts/tsstatus.php");
$tsstatus = new TSStatus("www.au1st3in.net", 10011);
$tsstatus->useServerPort(9987);
$tsstatus->imagePath = "../ts/img/";
$tsstatus->timeout = 2;
$tsstatus->setLoginPassword("LOGIN", "PASSWORD");
$tsstatus->setCache(2);
$tsstatus->hideEmptyChannels = true;
$tsstatus->hideParentChannels = false;
$tsstatus->showNicknameBox = false;
$tsstatus->showPasswordBox = false;
echo $tsstatus->render();
?>
</body></html>
