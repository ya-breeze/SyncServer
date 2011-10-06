<?php
	require 'item.php';
	require 'dbconnector.php';

	try{
		syslog(LOG_INFO, "Updating server DB...");
		$handle = @file_get_contents('php://input');
		$decoded = json_decode($handle, true);
		if( $decoded==NULL ) {
			echo "wrong input JSON\n";
			return;
		}

		$mysql = new DbConnector();
		$mysql->connect();

		$items = $decoded["items"];
		// echo var_dump($items);
		// echo ">" . $decoded["items"][0]["title"] . "<\n";
		$mysql->begin();
		foreach($items as $itemJson) {
			$status = $itemJson['status'];
			$item = new Item($itemJson);
		    // syslog(LOG_INFO, "Item: '$item->title($item->uuid)' - $status");
		    if( strtolower($status)=="updated")
		    	$mysql->update($item);
		    elseif( strtolower($status)=="deleted")
		    	$mysql->delete($item);
		    else
		    	Throw new Exception("Unknown status - '$status'");
		}
		$mysql->commit();
		syslog(LOG_INFO, "Success");
	} catch (Exception $e) {
		syslog(LOG_INFO, "Error while syncronizing: $e");
		$mysql->rollback();
	}
	syslog(LOG_INFO, "Finish");
?>