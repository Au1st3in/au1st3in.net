<?php
	//$SteamAPIKey = '';
	require('gmod/config.php');

	$serverIP = $_SERVER["REMOTE_ADDR"];
	$serverDNS = "www.au1st3in.net";
	$teamspeakDNS = "ts.au1st3in.net";
	$teamspeakPort = "9987";
	$arma3Port = "2302";

	if(strpos($serverIP, '192.168') !== false){
		$serverIP = "192.168.1.3";
	}
	
	$arma3 = $serverIP . ":" . $arma3Port;

	$servers = array(
		array(
			'id' => 'ExileZ Esseker',
			'type' => 'armedassault3',
			'host' => $arma3,
		)
	);

	$whitelist = array(
		'76561198026915793',
		'76561198079487646'
	);

?>
