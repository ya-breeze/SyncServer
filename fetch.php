<?php
	require 'item.php';
	require 'dbconnector.php';

	try{
		syslog(LOG_INFO, "Fetching from server...");
		$lastUpdate = 0;
		if(isset($_GET["lastUpdated"]))
		    $lastUpdate = $_GET["lastUpdated"];
		$mysql = new DbConnector();
		$mysql->connect();

		$result = array();
		$mysql->begin();
		$items = $mysql->getItems($lastUpdate);
		$mysql->commit();

		$result = array("items"=>$items);
		echo json_encode($result);
		echo "\n";

		syslog(LOG_INFO, "Success");
	} catch (Exception $e) {
		syslog(LOG_INFO, "Error while syncronizing: $e");
		$mysql->rollback();
	}
	syslog(LOG_INFO, "Finish");
?>