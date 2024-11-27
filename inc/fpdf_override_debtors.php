<?php

class jPDF extends FPDF{

	public $path;
	public $country_id;

	function Header(){
		
		
		
		if( $this->PageNo() == 1 ){
			
		
			if( $this->country_id == 1 ){ // AU
				$image = 'documents/statements_header_au.png';
			}else if( $this->country_id == 2 ){ // NZ
				$image = 'documents/statements_header_nz.png';
			}
			
			$this->Image($this->path . $image ,135,10,65);

			
			// set default values
			$header_width = 0;
			$header_height = 6;
			$header_border = 0;
			$header_new_line = 1;
			$header_align = 'T';
			
			
			$x = 30;
			$y = 58;
			$this->SetXY(10,$y);
			$font_size = 10;
			$this->SetFont('Arial','BI',14);
			$this->SetTextColor(180,32,37);
			$this->Cell($header_width, 4, 'DEBTORS REPORT', $header_border, 1, 'L');
			$this->SetTextColor(0,0,0);


			// Current as of 
			$this->SetFont('Arial','B',12);
			$this->Cell($header_width, $header_height, 'Current as of '.date('d/m/Y'), $header_border, 1, 'L');
			$y = $this->GetY();
			
			$this->Ln();
		
		}
		
		
		
		// table header
		$col1 = 80;
		$col2 = 22; 
		$cell_height = 5;
		$font_size = 8;
		
		// grey
		$this->SetFillColor(238,238,238);
		$this->SetFont('Arial','B',$font_size);
		


		$this->Cell($col1,$cell_height,"Agency",1,null,'L',true);
		$this->Cell($col2,$cell_height,"0-30 days",1,null,'L',true);
		$this->Cell($col2,$cell_height,"31-60 days",1,null,'L',true);
		$this->Cell($col2,$cell_height,"61-90 days",1,null,'L',true);
		$this->Cell($col2,$cell_height,"91+ days",1,null,'L',true);
		$this->Cell($col2,$cell_height,"Total Due",1,null,'L',true);
		
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