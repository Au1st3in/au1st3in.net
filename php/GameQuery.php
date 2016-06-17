<?php
	require_once("php/GameQ/Autoloader.php");

	$servers = array(
		array(
			'type' => 'armedassault3',
			'host' => $externalIP . ":" . $arma3Port,
		)
	);

	// Call the class, and add your servers.
	$gq = \GameQ\GameQ::factory();
	$gq->addServers($servers);

	// You can optionally specify some settings
	$gq->setOption('timeout', 3);

	// Send requests, and parse the data
	$results = $gq->process();
?>
