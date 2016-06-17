<?php
	//$SteamAPIKey = '';
	require('gmod/config.php');

	$externalIP = gethostbyname($_SERVER['SERVER_NAME']);
	$internalIP = $_SERVER["REMOTE_ADDR"];

	$serverDNS = "www.au1st3in.net";
	$teamspeakDNS = "ts.au1st3in.net";
	$teamspeakPort = "9987";
	$arma3Port = "2302";

	$servers = array(
		array(
			'id' => 'ExileZ Esseker',
			'type' => 'armedassault3',
			'host' => $externalIP . ":" . $arma3Port,
		)
	);

	$whitelist = array(
		'76561198026915793',
		'76561198079487646'
	);

?>
