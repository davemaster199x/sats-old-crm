<?php

$title = "Purchase Order";

include('inc/init.php');
include('inc/header_html.php');
include('inc/menu.php');

$crm = new Sats_Crm_Class();



$from = ($_REQUEST['from']!='')?mysql_real_escape_string($_REQUEST['from']):date('01/m/Y');
$to = ($_REQUEST['to']!='')?mysql_real_escape_string($_REQUEST['to']):date('d/m/Y');
// format date to db ready Y-m-d format
$from2 = $crm->formatDate($from);
$to2 = $crm->formatDate($to);
$supplier = mysql_real_escape_string($_REQUEST['supplier']);


// pagination script
$offset = ($_REQUEST['offset']!="")?$_REQUEST['offset']:0;
$limit = 100;
$this_page = $_SERVER['PHP_SELF'];

$params = "&from={$from}&to={$to}";

$next_link = "{$this_page}?offset=".($offset+$limit).$params;
$prev_link = "{$this_page}?offset=".($offset-$limit).$params;

?>

<style>
.jalign_left{
	text-align:left;
}
.txt_hid, .btn_update{
	display:none;
}
.date_filter_div{
	float: left;
    margin: 0 1px 0 5px;
}
.date_filter_div input{
	margin: 0 5px;
	width: 87px;
}
</style>
<div id="mainContent">    

	<div class="sats-middle-cont">
  
		<div class="sats-breadcrumb">
		  <ul>
			<li><a title="Home" href="/main.php"><img alt="" src="images/home_icon.png"></a></li>
			<li class="other first"><a title="<?php echo $title; ?>" href="/purchase_order.php"><strong><?php echo $title; ?></strong></a></li>
		  </ul>
		</div>	
		<div id="time"><?php echo date("l jS F Y"); ?></div>
	
	
		 <?php
		if($_GET['success']==1){ ?>
			<div class="success">Submission Successful</div>
		<?php
		}
		?>

		<div class="aviw_drop-h aviw_drop-vp" style="border: 1px solid #ccc; border-bottom: none;">	
			<form method="post">
				<div class="fl-left">
					<div style="float: left; margin-top: 5px;">Date:</div>
					<div class="date_filter_div">
						<input type="text" name="from" class="addinput datepicker" value="<?php echo $from; ?>" /> 
						<input type="text" name="to" class="addinput datepicker" value="<?php echo $to; ?>" />
					</div>
				</div>
				<div class="fl-left">
					<div style="float: left; margin-top: 5px;">Supplier:</div>
					<select name="supplier" id="supplier" style="margin-left: 10px; width: 107px;">
						<option value="">---- Select ---</option>	
						<?php
						$jparams = array(
							'country_id' => $_SESSION['country_default'],
							'sort_list' => array(
								'order_by' => '`company_name`',
								'sort' => 'ASC'
							)
						);
						$sup_sql = $crm->getSupplier($jparams);
						while( $sup = mysql_fetch_array($sup_sql) ){ ?>
							<option value="<?php echo $sup['suppliers_id']; ?>" <?php echo ( $sup['suppliers_id']==$supplier )?'selected="selected"':''; ?>><?php echo $sup['company_name']; ?></option>
						<?php	
						}
						?>
					</select>
				</div>				
				<div class="fl-left" style="float:left;">
					<input type="submit" class="submitbtnImg" value="Go" name="btn_search">
				</div>
			</form>			
		</div>
		
		<table border=0 cellspacing=0 cellpadding=5 width=100% class="tbl-sd" style="margin-top: 0px;">
			<tr class="toprow jalign_left">
				<th>Date</th>
				<th>PO No.</th>
				<th>Supplier</th>
				<th>Agency</th>
				<th>Problem</th>
				<th>Deliver to</th>				
				<th>Order Total</th>
			</tr>
			<?php
			$jparams = array(
				'country_id' => $_SESSION['country_default'],
				'filterDate' => array(
					'from' => $from2,
					'to' => $to2
				),
				'supplier' => $supplier,
				'paginate' => array(
					'offset' => $offset,
					'limit' => $limit
				),
				'sort_list' => array(
					'order_by' => '`date`',
					'sort' => 'DESC'
				)
			);
			$po_sql = $crm->getPurchaseOrder($jparams);
			$jparams = array(
				'country_id' => $_SESSION['country_default'],
				'supplier' => $supplier,
				'filterDate' => array(
					'from' => $from2,
					'to' => $to2
				)
			);
			$ptotal = mysql_num_rows($crm->getPurchaseOrder($jparams));
			if( $ptotal>0 ){
				$ctr = 0;
				while($po = mysql_fetch_array($po_sql)){ ?>
					<tr class="body_tr jalign_left" <?php echo ( $ctr%2 == 0 )?'':'style="background-color:#ececec"'; ?>>
						<td>
							<span class="txt_lbl">
								<a href="purchase_order_details.php?id=<?php echo $po['purchase_order_id']; ?>">
									<?php echo date('d/m/Y',strtotime($po['date'])); ?>
								</a>
							</span>
							<input type="hidden" name="kms_id" class="kms_id" value="<?php echo $po['kms_id']; ?>" />
						</td>
						<td>
							<span class="txt_lbl"><?php echo $po['purchase_order_num']; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $po['company_name']; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo $po['agency_name']; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo ( $po['suppliers_id'] == $crm->getDynamicHandyManID() )?$po['item_note']:''; ?></span>
						</td>
						<td>
							<span class="txt_lbl"><?php echo "{$po['dt_fname']} ".( ($po['dt_lname']!="")?strtoupper(substr($po['dt_lname'],0,1)).'.':'' ); ?></span>
						</td>						
						<td>
							<?php
							
							if( $po['suppliers_id'] == $crm->getDynamicHandyManID() ){ // if supplier is handyman
								
								$poi_tot = $po['invoice_total'];	
								
							}else{
								
								$jparams = array(
									'purchase_order_id' => $po['purchase_order_id'],
									'getTotal' => 1
								);
								$poi_sql = $crm->getPurchaseOrderItem($jparams);
								$poi = mysql_fetch_array($poi_sql);
								$poi_tot = $poi['poi_total'];
								
							}
							
								
							?>
							<span class="txt_lbl"><?php echo ($poi_tot>0)?'$'.number_format($poi_tot,2):''; ?></span>
						</td>							
					</tr>
				<?php
				$ctr++;
				}
				?>
				<tr>
					<td colspan="6" align="left"><strong>TOTAL</strong></td>
					<?php
						// get non handyman supplier total
						$jparams = array(
							'country_id' => $_SESSION['country_default'],
							'supplier' => $supplier,
							'filterDate' => array(
								'from' => $from2,
								'to' => $to2
							),							
							'getTotal' => 1
						);
						$poi_sql = $crm->getPurchaseOrderItem($jparams);
						$poi = mysql_fetch_array($poi_sql);
						$supp_tot = $poi['poi_total'];
						
						// get handyman supplier total
						$jparams = array(
							'country_id' => $_SESSION['country_default'],
							'supplier' => $crm->getDynamicHandyManID(),
							'filterDate' => array(
								'from' => $from2,
								'to' => $to2
							)
						);
						$poi_sql = $crm->getPurchaseOrder($jparams);
						$poi = mysql_fetch_array($poi_sql);
						$non_supp_tot = $poi['invoice_total'];
					?>
					<td align="left"><strong><?php echo ($poi_tot>0)?'$'.number_format(($supp_tot+$non_supp_tot),2):''; ?></strong></td>				
				</tr>
			<?php	
			}else{ ?>
				<tr><td colspan="100%">Empty</td></tr>
			<?php	
			}
			?>
					
		</table>
	
		<?php

		// Initiate pagination class
		$jp = new jPagination();

		$per_page = $limit;
		$page = ($_GET['page']!="")?$_GET['page']:1;
		$offset = ($_GET['offset']!="")?$_GET['offset']:0;	

		echo $jp->display($page,$ptotal,$per_page,$offset,$params);

		?>
		
		
		
		
    
	</div>
	
</div>

<br class="clearfloat" />


</body>
</html>
