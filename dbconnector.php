<?php
	class DbConnector{
		protected $connection;
		protected $user = "root";
		protected $pass = "teligent";
		protected $db   = "sync";

		public function connect() {
			syslog(LOG_DEBUG, "Connecting to DB...");
			$this->connection = mysql_connect("localhost", $this->user, $this->pass);
			if( $this->connection==FALSE )
				Throw new Exception("Unable connect to DB - " . mysql_error()); 
			if( !mysql_select_db($this->db, $this->connection) )
				Throw new Exception("Unable select DB - " . mysql_error()); 
		}

		public function begin() {
			$result = mysql_query("BEGIN;");
			if( !$result )
				Throw new Exception("Unable start transaction - " . mysql_error());
		}

		public function commit() {
			$result = mysql_query("COMMIT;");
			if( !$result )
				Throw new Exception("Unable commit transaction - " . mysql_error());
		}

		public function rollback() {
			$result = mysql_query("ROLLBACK;");
			if( !$result )
				Throw new Exception("Unable rollback transaction - " . mysql_error());
		}

		public function update($item) {
			$id = $this->uuid2hex($item->uuid);
			if( $this->shouldUpdate($id, $item->updated) ) {
				syslog(LOG_INFO, "Will update '$item->uuid:$item->title'");

				$keys = "";
				$values = "";
				$array = $item->toArray();
				foreach ($array as $key => $value) {
 					if( $key=="updated") {
						// skip it	
						continue;
					}
					
					if( $keys!="" ) {
						$keys .= ",";
						$values .= ",";
					}

					if( $key=="uuid" ) {
						$keys .= "id";
						$values .= $id;
					} else {
						$keys .= mysql_real_escape_string($key);
						$values .= "'".mysql_real_escape_string($value)."'";
					}
				}
				$query = sprintf("REPLACE INTO Items(%s) VALUES(%s)", $keys, $values);
				$result = mysql_query($query);
				if( !$result )
					Throw new Exception("Unable update item '$query' - " . mysql_error());

				$this->updateLog($id, $item->updated, "updated");
			}
		}

		public function delete($item) {
			$id = $this->uuid2hex($item->uuid);
			if( $this->shouldUpdate($id, $item->updated) ) {
				syslog(LOG_INFO, "Will delete '$item->uuid:$item->title'");

			$query = "DELETE FROM Items WHERE id=".mysql_real_escape_string($id);
			$result = mysql_query($query);
			if( !$result )
				Throw new Exception("Unable delete item '$query' - " . mysql_error());

				$this->updateLog($id, $item->updated, "deleted");
			}
		}

		public function getItems($lastUpdate) {
			$items = array();

			$query = "SELECT hex(item) as itemId, UNIX_TIMESTAMP(updated) as updated,"
				." UNIX_TIMESTAMP(localUpdated) as localUpdated, status, Items.* FROM Logs LEFT JOIN Items ON Logs.item=Items.id WHERE localUpdated>=FROM_UNIXTIME(".mysql_real_escape_string($lastUpdate).")";
			$result = mysql_query($query);
			if( !$result )
				Throw new Exception("Unable perform select '$query' - " . mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$row["uuid"] = $this->hex2uuid($row["itemId"]);
				$item = new Item($row);
				$array = array();
				if( $row["status"]=="updated")
			    	$array = $item->toArray();
			    else
			    	$array["uuid"] = $row["uuid"];

			    $array["updated"]=$row["updated"];
			    $array["status"]=$row["status"];
			    $array["localUpdated"]=$row["localUpdated"];

			    array_push($items, $array);
			}			
			
			return $items;
		}

		function shouldUpdate($id, $updated) {
			$query = "SELECT updated FROM Logs WHERE item=".mysql_real_escape_string($id)
				." AND updated>=FROM_UNIXTIME(".mysql_real_escape_string($updated).") LIMIT 1";
			$result = mysql_query($query);
			if( !$result )
				Throw new Exception("Unable perform select '$query' - " . mysql_error());
			if( mysql_num_rows($result)>0  )
					return FALSE;
			
			return TRUE;
		}

		function updateLog($id, $updated, $status, $localUpdated) {
			$query = "REPLACE INTO Logs(item, updated, status) VALUES("
				.mysql_real_escape_string($id).",FROM_UNIXTIME(".mysql_real_escape_string($updated)."),'".$status."')";
			$result = mysql_query($query);
			if( !$result )
				Throw new Exception("Unable update Logs '$query' - " . mysql_error());
		}

		function uuid2hex($id) {
			$result = "0x";
			$result .= substr($id, 0, 8);
			$result .= substr($id, 9, 4);
			$result .= substr($id, 14, 4);
			$result .= substr($id, 19, 4);
			$result .= substr($id, 24, 12);
			// syslog(LOG_INFO, "Convert ".$id." to ".$result);

			return $result;

		}
		function hex2uuid($id) {
			$result = "";
			$result .= substr($id, 0, 8);
			$result .= "-";
			$result .= substr($id, 8, 4);
			$result .= "-";
			$result .= substr($id, 12, 4);
			$result .= "-";
			$result .= substr($id, 16, 4);
			$result .= "-";
			$result .= substr($id, 20, 12);
			// syslog(LOG_INFO, "Convert ".$id." to ".$result);

			return $result;
		}
	}
?>