<?php
	//$SteamAPIKey = '';
	require('gmod/config.php');

	$externalIP = gethostbyname($_SERVER['SERVER_NAME']);
	$internalIP = $_SERVER["REMOTE_ADDR"];

	$serverDNS = "www.au1st3in.net";
	$teamspeakDNS = "ts.au1st3in.net";
	$teamspeakPort = "9987";
	$compName = '';
	$dayzPort = "";
	$arma3Port = "2302";
	$arma3RconPort = $arma3Port;
	$arma2oaPort = "";

	$mainPath = "";
	$controlPath = "";
	$controlPHP = 'control';
	$dayzccPath ="";

	$arma3Path = "";
	$arma3Exe = '';
	$arma3Mods = '';
	$arma3ServerMods = '';
	$arma3ConfigPath = '';
	$arma3BasicPath = '';
	$arma3ProfilesPath = '';
	$arma3Launch = '';
	$arma3RconPass = '';

	$arma2oaPath = "";
	$arma2oaExe = '';
	$arma2oaMods = '';
	$arma2oaConfigPath = '';
	$arma2oaBasicPath = '';
	$arma2oaProfilesPath = '';
	$arma2oaLaunch = '';
	$arma2oaRconPass = '';

	$actionQuery = 'action';
	$actionPath = "php";
	$actionPHP = '';
	$startCase = 0;
	$stopCase = 1;
	$killCase = 2;
	$restartCase = 3;
	$rebootCase = 4;

	$whitelist = array(
		'76561198026915793',
		'76561198079487646',
		'76561198119341696'
	);

?>
