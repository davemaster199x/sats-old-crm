<?php

class Agency_Alarms{

	public $agency_id;

	public function __construct($agency_id){
		$this->agency_id = $agency_id;
	}

	public function get_agency_alarms(){
		return mysql_query("
			SELECT *
			FROM `agency_alarms` AS aa
			LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
			WHERE aa.`agency_id` = {$this->agency_id}
		");
	}

}

?>