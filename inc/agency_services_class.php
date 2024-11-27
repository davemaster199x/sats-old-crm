<?php

class Agency_Services{

	public $agency_id;

	public function __construct($agency_id){
		$this->agency_id = $agency_id;
	}

	public function get_agency_services(){
		return mysql_query("
			SELECT *
			FROM `agency_services` AS a_s
			LEFT JOIN `alarm_job_type` AS ajt ON a_s.`service_id` = ajt.`id`
			WHERE a_s.`agency_id` = {$this->agency_id}
		");
	}

}

?>