<?php

class jPagination{
	
	public $per_page = 25;

	public function __construct(){
      
	}
	
	public function display($page_number,$list_tot,$per_page,$offset,$params){
		
		$orig_offset = $offset;
		
		$first_page = $_SERVER['PHP_SELF'].'?page=1&offset=0'.$params;
		
		if($list_tot%$per_page==0){
			$lp_page = ($list_tot/$per_page);
			$lp_offset = ($lp_page-1)*$per_page;
		}else{
			$lp_page = ceil($list_tot/$per_page);
			$lp_offset = ($lp_page-1)*$per_page;
		}
		$last_page = $_SERVER['PHP_SELF'].'?page='.$lp_page.'&offset='.$lp_offset.''.$params;
		
		$prev_arrow = $_SERVER['PHP_SELF'].'?page='.($page_number-1).'&offset='.($offset-$per_page).''.$params;
		$next_arrow = $_SERVER['PHP_SELF'].'?page='.($page_number+1).'&offset='.($offset+$per_page).''.$params;
		
		// calculate prev page
		$prev_page = $page_number-5;
		
		if($prev_page>=1){
			$prev_page2 = $prev_page;
			$prev_offset = $offset-($per_page*5);
		}else{
			$prev_page2 = 1;
			$prev_offset = 0;
		}
		
		// calculate next page
		if($list_tot%$per_page==0){
			$tot_pages = ($list_tot/$per_page);
			$total_next_page = 	($tot_pages-$page_number);
			$total_next_page2 = ($total_next_page>5)?$page_number+5:$page_number+$total_next_page;
		}else{
			$tot_pages = ceil($list_tot/$per_page);
			$total_next_page = 	($tot_pages-$page_number);
			$total_next_page2 = ($total_next_page>5)?$page_number+5:$page_number+$total_next_page;
		}
		
		//echo $page_number.' - '.$tot_pages.' - '.$total_next_page.' - '.$total_next_page2.' - '.($list_tot%$per_page);
		
		$str = '
		<link rel="stylesheet" href="/css/jpagination.css">
		<div class="container" style="width: auto;"> 
		  <ul class="pagination">
			<li><a href="'.$first_page.'"><<</a></li>';
			
			if($page_number>1){
				$str .= '<li><a href="'.$prev_arrow.'"><</a></li>';
			}			
			// prev links
			for($i=$prev_page2;$i<$page_number;$i++){ 
				$str .= '<li><a href="'.$_SERVER['PHP_SELF'].'?page='.$prev_page2.'&offset='.$prev_offset.''.$params.'">'.$prev_page2.'</a></li>';
				$prev_offset += $per_page;
				$prev_page2++;
			}
			// current/active
			$str .= '<li class="active"><a href="#">'.$page_number.'</a></li>';
			// next links
			for($i=($page_number+1);$i<=$total_next_page2;$i++){ 
				$offset += $per_page;
				$str .= '<li><a href="'.$_SERVER['PHP_SELF'].'?page='.$i.'&offset='.$offset.''.$params.'">'.$i.'</a></li>';	
			}
			
			if($page_number<$tot_pages){
				$str .= '<li><a href="'.$next_arrow.'">></a></li>';	
			}			
			
			$str .= '
			<li><a href="'.$last_page.'">>></a></li>
			<div style="margin-top: 48px;">
				<span class="pagination_range">'.($orig_offset+1).' - '.(($page_number==$tot_pages)?$list_tot:$orig_offset+$per_page).' of</span> '.$list_tot.' items 
			</div>
		  </ul>
		</div>';
		
		return $str;
		
	}

}

?>