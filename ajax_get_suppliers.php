<?php

include('inc/init_for_ajax.php');

$crm = new Sats_Crm_Class();

$suppliers_id = mysql_real_escape_string($_REQUEST['suppliers_id']);
$country_id = mysql_real_escape_string($_REQUEST['country_id']);

$sup_arr = array();

$params = array(
	'country_id' => $country_id,
	'suppliers_id' => $suppliers_id
);
$sup_sql = $crm->getSupplier($params);

while( $sup = mysql_fetch_array($sup_sql) ){
	
	$sup_arr['address'] = $sup['address'];
	$sup_arr['email'] = $sup['email'];
	$sup_arr['company_name'] = $sup['company_name'];
	
	// get tech stock via supplier
	$params = array(
		'country_id' => $_SESSION['country_default'],
		'suppliers_id' => $suppliers_id,
		'status' => 1,
		'sort_list' => array(
			'order_by' => 's.`item`',
			'sort' => 'ASC'
		)
	);
	$ts_sql = $crm->getStock($params);
	if( mysql_num_rows($ts_sql)>0 ){
		
		$ts_list = '
			<table class="tbl-sd" style="width:auto; margin-bottom: 5px;">
					<tr class="toprow" style="text-align: left;">
						<th>Code</td>
						<th>Item</th>
						<th>Price</th>
						<th>Qty</th>
						<th>Total</th>
					</tr>
		';	
			
			while( $ts = mysql_fetch_array($ts_sql) ){
				
				$qty = 0;
				
				$ts_list .= '
				<tr style="background-color:#eeeeee;" class="fadeOutText">
					<td>
						<input type="hidden" name="stocks_id[]" value="'.$ts['stocks_id'].'" />
						<input type="text" class="addinput code" name="code[]" style="width: 100px;" readonly="readonly" value="'.$ts['code'].'" />
					</td>
					<td><input type="text" class="addinput item" name="item[]" style="width: 150px;" readonly="readonly" value="'.$ts['item'].'" /></td>
					<td>
						<input type="text" class="addinput price_lbl" style="width: 75px;" readonly="readonly" value="$'.$ts['price'].'" />
						<input type="hidden" name="price[]" class="price" value="'.$ts['price'].'" />
					</td>
					<td><input type="text" class="addinput qty" name="qty[]" style="width: 50px;" value="'.$qty.'" /></td>
					<td>
						<input type="text" class="addinput total_lbl" style="width: 100px;" readonly="readonly" value="$'.($ts['price']*$qty).'" />
						<input type="hidden" name="total[]" class="total" value="'.($ts['price']*$qty).'" />
					</td>
				</tr>';

				
			}
			
			$ts_list .= '
			</table>
		';
		
		$sup_arr['tech_stock_list'] = $ts_list;
		
	}  
	
	
	
}

echo json_encode($sup_arr);

?>