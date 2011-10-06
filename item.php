<?php
	class Item{
		public $uuid;
		public $body;
		public $updated;

		public function Item($json) {
			$this->uuid = $json['uuid'];
			$this->body = $json['body'];
			$this->updated = $json['updated'];
		}

		public function toArray() {
			$array = array(
				"uuid"=>$this->uuid,
				"body"=>$this->body,
				"updated"=>$this->updated,
				);
			return $array;
		}
	}
?>