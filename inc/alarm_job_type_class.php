<?php

class Alarm_Job_Type{
	
	public function get_alarm_job_type(){
		return mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` = 1
		");
	}
	
}

?>