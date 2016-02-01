<html>
<head>
<link rel="shortcut icon" href="../teamspeak/teamspeak.ico">
<title>TSStatus</title>
<link rel="stylesheet" type="text/css" href="../teamspeak/tsstatus.min.css" />
</head>
<body>
<p><br><br><br><br></p>
<?php
require_once("../teamspeak/tsstatus.php");
$tsstatus = new TSStatus("www.au1st3in.net", 10011);
$tsstatus->useServerPort(9987);
$tsstatus->imagePath = "../teamspeak/img/";
$tsstatus->timeout = 2;
$tsstatus->setLoginPassword("LOGIN", "PASSWORD");
$tsstatus->setCache(2);
$tsstatus->hideEmptyChannels = true;
$tsstatus->hideParentChannels = true;
$tsstatus->showNicknameBox = false;
$tsstatus->showPasswordBox = false;
echo $tsstatus->render();
?>
</body>
</html>
