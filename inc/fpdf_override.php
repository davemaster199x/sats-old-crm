<?php

class jPDF extends FPDF{

	public $path;
	public $country_id;

	function Header()
	{
		//$this->Image($this->path . 'documents/cert_corner_img.png',110,0,100);
		$this->Image($this->path . 'documents/inv_cert_pdf_header.png',150,10,50);
	}
	
	function Footer()
	{
		// get country data
		$c_sql = $this->getCountryData($this->country_id);
		$c = mysql_fetch_array($c_sql);
		$letterhead_footer = $c['letterhead_footer'];
		//$this->Image($this->path . 'documents/'.$letterhead_footer,0,263,210);
		
		if( $this->country_id == 1 ){ // AU
			$image = 'documents/inv_cert_pdf_footer_au.png';
		}else if( $this->country_id == 2 ){ // NZ
			$image = 'documents/inv_cert_pdf_footer_nz.png';
		}
		
		$this->Image($this->path . $image,0,273,210);
	}
	
	function setPath($path){
		$this->path = $path;
	}
	
	function getCountryData($country_id){
		return mysql_query("
			SELECT *
			FROM `countries`
			WHERE `country_id` = {$country_id}
		");
	}
	
	function setCountryData($country_id){
		$this->country_id = $country_id;
	}
	
}


?>