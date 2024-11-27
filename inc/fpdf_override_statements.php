<?php

class jPDF extends FPDF{

	public $path;
	public $country_id;
	public $agency_id;

	function Header(){

		
		
		if( $this->PageNo() == 1 ){
			
			
			if( $this->country_id == 1 ){ // AU
				$image = 'documents/statements_header_au.png';
			}else if( $this->country_id == 2 ){ // NZ
				$image = 'documents/statements_header_nz.png';
			}
			
			$this->Image($this->path . $image ,135,10,65);
			
			
			// get agency details
			$jparams = array(
				'agency_id' => $this->agency_id,
				'join_table' => 'country'
			);
			$agency_sql = Sats_Crm_Class::getAgency($jparams);
			$agency = mysql_fetch_array($agency_sql);
			$a_name = $agency['agency_name'];
			$a_address1 = "{$agency['address_1']} {$agency['address_2']}";
			$a_address2 = "{$agency['address_3']} {$agency['state']} {$agency['postcode']}";
			$a_country_name = $agency['country'];
			$country_id = $agency['country_id'];
			$fg_id = $agency['franchise_groups_id'];
			
			
			$url = $_SERVER['SERVER_NAME'];
			if($_SESSION['country_default']==1){ // AU
			
				if( strpos($url,"crmdev")===false ){ // live 
					$compass_fg_id = 39;
				}else{ // dev 
					$compass_fg_id = 34;
				}
				
			}
			
			
			// set default values
			$header_width = 0;
			$header_height = 6;
			$header_border = 0;
			$header_new_line = 1;
			$header_align = 'T';

			$x = 30;
			$y = 58;
			$this->SetXY($x,$y);
			$font_size = 10;
			$this->SetFont('Arial','B',$font_size);
			$this->Cell($header_width, 4, $a_name, $header_border, 1, 'L');
			$y = $this->GetY();

			// agency address
			$this->SetXY($x,$y);
			$this->SetFont('Arial',null,$font_size);
			$agency_text = "{$a_address1}\n{$a_address2}";
			$this->MultiCell($header_width, 4, $agency_text, $header_border,'L');


			// statement
			$x = $header_width+25;
			$this->SetFont('Arial','BI',14);
			$this->SetTextColor(180,32,37);
			$this->Cell(138, 7, 'STATEMENT', 0,null,'R');
			$this->SetTextColor(0,0,0);
			$y = $this->GetY();



			// Current as of 
			$to_date = ( $this->to_date != '' )?$this->to_date:date('d/m/Y');
			$this->SetFont('Arial','B',12);
			$this->Cell($header_width, 7, 'Current as of '.$to_date, 0,null,'R');
			$y = $this->GetY();

			$this->Ln();			
			
		}
		
		
		// table header
		$cell_height = 5;
		$font_size = 8;

		$col1 = 17;
		$col3 = 96; 
		$col5 = 15; 
		
		// grey
		$this->SetFillColor(238,238,238);
		$this->SetFont('Arial','B',$font_size);
		$this->Cell($col1,$cell_height,'Date',1,null,null,true);
		$this->Cell($col1,$cell_height,'Invoice',1,null,null,true);
		if( $fg_id == $compass_fg_id ){ // compass only
			$this->Cell($col1,$cell_height,'Index No.',1,null,null,true);
			$this->Cell($col3-18,$cell_height,'Property',1,null,null,true);
		}else{
			$this->Cell($col3,$cell_height,'Property',1,null,null,true);
		}			
		$this->Cell($col5,$cell_height,'Charges',1,null,null,true);
		$this->Cell($col5,$cell_height,'Payments',1,null,null,true);
		$this->Cell($col5,$cell_height,'Credits',1,null,null,true);
		$this->Cell($col5,$cell_height,'Balance',1,null,null,true);
		$this->Ln();
		

	}
	
	function Footer(){
		
		// Go to 1.5 cm from bottom
		$this->SetY(-31);
		// Select Arial italic 8
		$this->SetFont('Arial','I',8);
		// Print current and total page numbers
		$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		
		if( $this->country_id == 1 ){ // AU
			$image = 'documents/statements_footer_au.png';
		}else if( $this->country_id == 2 ){ // NZ
			$image = 'documents/statements_footer_nz.png';
		}
		
		$this->Image($this->path . $image,2,273,208);
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