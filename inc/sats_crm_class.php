<?php

include ('DatabaseUtils.php');

class Sats_Crm_Class {

    // table suppliers
    function getSupplier($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `status` = 1 ";

        if ($params['country_id'] != "") {
            $filter_arr[] = " `country_id` = {$params['country_id']} ";
        }

        if ($params['suppliers_id'] != "") {
            $filter_arr[] = " `suppliers_id` = {$params['suppliers_id']} ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `suppliers`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table staff_accounts
    function getStaffAccount($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = "WHERE `active` = 1";
        $filter_arr[] = "AND `Deleted` = 0";

        if ($params['staff_id'] != "") {
            $filter_arr[] = "AND sa.`StaffID` = {$params['staff_id']}";
        }

        if ($params['class_id'] != "") {
            $filter_arr[] = "AND sa.`ClassID` = {$params['class_id']}";
        }

        if ($params['tech_id'] != "") {
            $filter_arr[] = "AND sa.`TechID` = {$params['tech_id']}";
        }

        if ($params['custom_query'] != "") {
            $filter_arr[] = "{$params['custom_query']}";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `staff_accounts` AS sa
			INNER JOIN `country_access` AS ca ON (
				sa.`StaffID` = ca.`staff_accounts_id`
				AND ca.`country_id` ={$_SESSION['country_default']}
			)
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    function getStaffAccountsData($params) {


        // select
        $sel_str = '';
        if ($params['custom_select'] != '') {
            $sel_str = $params['custom_select'];
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(sa.`StaffID`) AS jcount ";
        } else {
            $sel_str = "*";
        }

        // filters
        $filter_arr = array();
        if ($params['staff_id'] != "") {
            $filter_arr[] = "AND sa.`StaffID` = {$params['staff_id']}";
        }
        if ($params['class_id'] != "") {
            $filter_arr[] = "AND sa.`ClassID` = {$params['class_id']}";
        }
        if (is_numeric($params['active'])) {
            $filter_arr[] = "AND sa.`active` = {$params['active']}";
        }
        if (is_numeric($params['deleted'])) {
            $filter_arr[] = "AND sa.`Deleted` = {$params['deleted']}";
        }
        if (is_numeric($params['assigned_cc'])) {
            $filter_arr[] = "AND sa.`other_call_centre` = {$params['assigned_cc']}";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }

        //custom where
        if ($params['custom_where'] != '') {
            $custom_where_str = $params['custom_where'];
        }

        // group by
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }

        // sort
        if ($params['sort_query'] != '') {
            $sort_query = "ORDER BY {$params['sort_query']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        // join table
        $join_table_arr = [];
        if ($params['join_table'] != '') {

            if ($params['join_table'] == 'cc') {
                $join_table_arr[] = 'LEFT JOIN `staff_accounts` AS cc ON sa.`other_call_centre` = cc.`StaffID`';
            }
        }

        // combine all filters
        $join_table_str = '';
        if (count($join_table_arr) > 0) {
            $join_table_str = implode(" ", $join_table_arr);
        }

        $sql = "
			SELECT {$sel_str}
			FROM `staff_accounts` AS sa
			LEFT JOIN `staff_classes` AS sc ON sa.`ClassID` = sc.`ClassID`
			{$join_table_str}
			INNER JOIN `country_access` AS ca ON (
				sa.`StaffID` = ca.`staff_accounts_id`
				AND ca.`country_id` ={$_SESSION['country_default']}
			)
			WHERE sa.`StaffID` > 0
			{$filter_str}
			{$custom_where_str}
			{$group_by_str}
			{$sort_query}
			{$pag_str}
		";


        if ($params['display_echo'] == 1) {
            echo $sql;
        }


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // table stocks
    function getStock($params) {

        // filters
        $filter_arr = array();

        //$filter_arr[] = " s.`status` = 1 ";

        if ($params['country_id'] != "") {
            $filter_arr[] = " s.`country_id` = {$params['country_id']} ";
        }

        if ($params['suppliers_id'] != "") {
            $filter_arr[] = " s.`suppliers_id` = {$params['suppliers_id']} ";
        }

        if ($params['status'] != "") {
            $filter_arr[] = " s.`status` = {$params['status']} ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *, s.status AS s_status
			FROM `stocks` AS s
			LEFT JOIN `suppliers` AS sup ON s.`suppliers_id` = sup.`suppliers_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table purchase_order
    function getPurchaseOrder($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " po.`active` = 1 ";
        $filter_arr[] = " po.`deleted` = 0 ";

        if ($params['supplier'] != "") {
            $filter_arr[] = " po.`suppliers_id` = {$params['supplier']} ";
        }

        if ($params['purchase_order_id'] != "") {
            $filter_arr[] = " po.`purchase_order_id` = {$params['purchase_order_id']} ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = " po.`country_id` = {$params['country_id']} ";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " po.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
            }
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *,
				sup.`address` AS sup_address,
				sup.`email` AS sup_email,

				sa.`FirstName` AS dt_fname,
				sa.`LastName` AS dt_lname,
				sa.`address` AS dt_address,
				sa.`Email` AS dt_email,

				sa2.`FirstName` AS ob_fname,
				sa2.`LastName` AS ob_lname,
				sa2.`Email` AS ob_email
			FROM `purchase_order` AS po
			LEFT JOIN `agency` AS a ON po.`agency_id` = a.`agency_id`
			LEFT JOIN `suppliers` AS sup ON po.`suppliers_id` = sup.`suppliers_id`
			LEFT JOIN `staff_accounts` AS sa ON po.`deliver_to` = sa.`StaffID`
			LEFT JOIN `staff_accounts` AS sa2 ON po.`ordered_by` = sa2.`StaffID`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table purchase_order_item
    function getPurchaseOrderItem($params) {


        if ($params['getTotal'] == 1) {
            $sel_str = " SUM(poi.`total`) AS poi_total ";
        } else {
            $sel_str = " * ";
        }



        // filters
        $filter_arr = array();

        $filter_arr[] = " poi.`active` = 1 ";
        $filter_arr[] = " poi.`deleted` = 0 ";

        if ($params['purchase_order_id'] != "") {
            $filter_arr[] = " poi.`purchase_order_id` = {$params['purchase_order_id']} ";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " po.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
            }
        }

        if ($params['supplier'] != "") {
            $filter_arr[] = " po.`suppliers_id` = {$params['supplier']} ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = " po.`country_id` = {$params['country_id']} ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `purchase_order_item` AS poi
			LEFT JOIN `purchase_order` AS po ON poi.`purchase_order_id` = po.`purchase_order_id`
			LEFT JOIN `stocks` AS s ON poi.`stocks_id` = s.`stocks_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    function sendPurchaseOrderEmail($params) {

        //echo "<pre>";
        //print_r($params);
        //echo "</pre>";
        // data
        $country_id = $params['country_id'];
        $purchase_order_num = mysql_real_escape_string($params['post_data']['purchase_order_num']);
        $date = mysql_real_escape_string($params['post_data']['date']);
        $date2 = Sats_Crm_Class::formatDate($date);

        $suppliers_id = mysql_real_escape_string($params['post_data']['supplier']);
        $supplier_name = mysql_real_escape_string($params['post_data']['supplier_name']);
        $supplier_address = mysql_real_escape_string($params['post_data']['supplier_address']);
        $supplier_email = mysql_real_escape_string($params['post_data']['supplier_email']);

        $code_arr = $params['post_data']['code'];
        $item_arr = $params['post_data']['item'];
        $price_arr = $params['post_data']['price'];
        $qty_arr = $params['post_data']['qty'];
        $total_arr = $params['post_data']['total'];
        $item_note = mysql_real_escape_string($params['post_data']['item_note']);

        $deliver_to = mysql_real_escape_string($params['post_data']['deliver_to']);
        $deliver_to_name = mysql_real_escape_string($params['post_data']['deliver_to_name']);
        $delivery_address = mysql_real_escape_string($params['post_data']['delivery_address']);
        $reciever_email = mysql_real_escape_string($params['post_data']['reciever_email']);

        $comments = mysql_real_escape_string($params['post_data']['comments']);

        $ordered_by = mysql_real_escape_string($params['post_data']['ordered_by']);
        $ordered_by_name = mysql_real_escape_string($params['post_data']['ordered_by_name']);
        $ordered_by_full_name = mysql_real_escape_string($params['post_data']['ordered_by_full_name']);
        $order_by_email = mysql_real_escape_string($params['post_data']['order_by_email']);

        // Get base template
        $template = getBaseEmailTemplate();

        $subject = $params['subject'];

        // multiple recipients
        // test emails
        //$to_email .= 'vaultdweller123@gmail.com' . ', '; // note the comma
        //$to_email .= 'pokemaniacs123@yahoo.com' . ', '; // note the comma
        //$to_email .= 'danielk@sats.com.au';

        $to_email_arr = [];

        if (filter_var($supplier_email, FILTER_VALIDATE_EMAIL)) {
            $to_email_arr[] = $supplier_email;
        }
        if (filter_var($reciever_email, FILTER_VALIDATE_EMAIL)) {
            $to_email_arr[] = $reciever_email;
        }
        if (filter_var($order_by_email, FILTER_VALIDATE_EMAIL)) {
            $to_email_arr[] = $order_by_email;
        }


        $to_email = implode(",", $to_email_arr);

        /*
          $to_email .= $supplier_email . ', '; // note the comma
          $to_email .= $reciever_email . ', '; // note the comma
          $to_email .= $order_by_email;
         */



        // Set template title
        $template = str_replace("#title", $subject, $template);

        // get country
        $cntry_sql = getCountryViaCountryId($country_id);
        $cntry = mysql_fetch_array($cntry_sql);
        // replace email signature image
        $template = str_replace("cron_email_footer.png", $cntry['email_signature'], $template);
        // replace trading name
        $template = str_replace("SATS Trading Pty Ltd", $cntry['trading_name'], $template);

        $email_body .= '<p><strong>' . $cntry['trading_name'] . '</strong><br />';
        $email_body .= '<strong>PO Box ' . $cntry['company_address'] . '</strong><br />';
        $email_body .= '<strong>A.B.N ' . $cntry['abn'] . '</strong></p>';

        $email_body .= '
			<table style="width:100%;">
				<tr>
					<td><strong>Purchase Order No.:</strong></td>
					<td>' . $purchase_order_num . '</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Date:</strong></td>
					<td>' . Sats_Crm_Class::formatDate($date2, 'd/m/Y') . '</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Supplier Name:</strong></td>
					<td>' . $supplier_name . '</td>
				</tr>

				';

        $email_body .= '<tr>
					<td><strong>Supplier Address:</strong></td>
					<td>' . $supplier_address . '</td>
				</tr>
				<tr>
					<td><strong>Supplier Email:</strong></td>
					<td>' . $supplier_email . '</td>
				</tr>';

        // item
        if (count($code_arr) > 0) {
            $email_body .= '
					<tr>
						<td colspan="2">
							<table id="tbl_item" style="border-collapse: collapse; float: left;">
								<tr style="background-color: #eeeeee;">
									<td style="border: 1px solid; padding: 5px;">Code</td>
									<td style="border: 1px solid; padding: 5px;">Item</td>
									<td style="border: 1px solid; padding: 5px;">Price</td>
									<td style="border: 1px solid; padding: 5px;">Qty</td>
									<td style="border: 1px solid; padding: 5px;">Total</td>
								</tr>';
            foreach ($code_arr as $index => $code) {
                $email_body .= '
									<tr>
										<td style="border: 1px solid; padding: 5px;">' . mysql_real_escape_string($code) . '</td>
										<td style="border: 1px solid; padding: 5px;">' . mysql_real_escape_string($item_arr[$index]) . '</td>
										<td style="border: 1px solid; padding: 5px;">$' . mysql_real_escape_string($price_arr[$index]) . '</td>
										<td style="border: 1px solid; padding: 5px;">' . mysql_real_escape_string($qty_arr[$index]) . '</td>
										<td style="border: 1px solid; padding: 5px;">$' . mysql_real_escape_string($total_arr[$index]) . '</td>
									</tr>
									';
            }
            $email_body .= '
							</table>
						</td>
					</tr>
				';
        }

        $email_body .= '
				<tr>
					<td><strong>Order Notes:</strong></td>
					<td>' . $item_note . '</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Deliver to:</strong></td>
					<td>' . $deliver_to_name . '</td>
				</tr>
				<tr>
					<td><strong>Delivery Address:</strong></td>
					<td>' . $delivery_address . '</td>
				</tr>
				<tr>
					<td><strong>Receiver Email:</strong></td>
					<td>' . $reciever_email . '</td>
				</tr>
				<tr>
					<td><strong>Delivery Comments:</strong></td>
					<td>' . $comments . '</td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;</td>
				</tr>
				<tr>
					<td><strong>Ordered By:</strong></td>
					<td>' . $ordered_by_full_name . '</td>
				</tr>
				<tr>
					<td><strong>Ordered by Email:</strong></td>
					<td>' . $order_by_email . '</td>
				</tr>
			</table>
		';



        # Populate Template
        $template = str_replace("#content", $email_body, $template);

        //echo $template;
        //echo $to_email;
        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= "To: {$to_email}" . "\r\n";
        $headers .= "From: SATS - Smoke Alarm Testing Services <{$cntry['outgoing_email']}>" . "\r\n";



        mail($to_email, $subject, $template, $headers);
    }

    // table tools
    function getTools($params) {

        $sel_str = " SELECT *, t.`purchase_date` AS t_purchase_date, t.`purchase_price` AS t_purchase_price ";

        if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 't.`item`':
                    $sel_str = " SELECT DISTINCT t.`item`, ti.`item_name` ";
                    break;
            }
        }

        // filters
        $filter_arr = array();

        $filter_arr[] = " t.`active` = 1 ";
        $filter_arr[] = " t.`deleted` = 0 ";

        if ($params['country_id'] != "") {
            $filter_arr[] = " t.`country_id` = {$params['country_id']} ";
        }

        if ($params['tools_id'] != "") {
            $filter_arr[] = " t.`tools_id` = {$params['tools_id']} ";
        }

        if ($params['item'] != "") {
            $filter_arr[] = " t.`item` = {$params['item']} ";
        }


        if ($params['assign_to_vehicle'] != "") {
            $filter_arr[] = " t.`assign_to_vehicle` = {$params['assign_to_vehicle']} ";
        }


        if ($params['search_phrase'] != "") {
            $filter_arr[] = " CONCAT ( LOWER(t.`item_id`), LOWER(t.`brand`), LOWER(t.`description`) ) LIKE '%{$params['search_phrase']}%'  ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `tools` AS t
			LEFT JOIN `tool_items` AS ti ON t.`item` = ti.`tool_items_id`
			LEFT JOIN `vehicles` AS v ON t.`assign_to_vehicle` = v.`vehicles_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table ladder_check
    function getLadderCheck($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";


        if ($params['tools_id'] != "") {
            $filter_arr[] = " `tools_id` = {$params['tools_id']} ";
        }

        if ($params['ladder_check_id'] != "") {
            $filter_arr[] = " `ladder_check_id` = {$params['ladder_check_id']} ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `ladder_check`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table ladder_inspection
    function getLadderInspection($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";

        /*
          if($params['tools_id']!=""){
          $filter_arr[] = " t.`tools_id` = {$params['tools_id']} ";
          }
         */

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `ladder_inspection`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table ladder_inspection
    function ladderInspectionSelection($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";


        if ($params['ladder_check_id'] != "") {
            $filter_arr[] = " `ladder_check_id` = {$params['ladder_check_id']} ";
        }

        if ($params['ladder_inspection_id'] != "") {
            $filter_arr[] = " `ladder_inspection_id` = {$params['ladder_inspection_id']} ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `ladder_inspection_selection`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table lockout_kit_checklist
    function getLockOutKitCheckList($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";

        /*
          if($params['tools_id']!=""){
          $filter_arr[] = " t.`tools_id` = {$params['tools_id']} ";
          }
         */

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `lockout_kit_checklist`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table lockout_kit_check
    function getLockoutKitCheck($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";


        if ($params['tools_id'] != "") {
            $filter_arr[] = " `tools_id` = {$params['tools_id']} ";
        }

        if ($params['lockout_kit_check_id'] != "") {
            $filter_arr[] = " `lockout_kit_check_id` = {$params['lockout_kit_check_id']} ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `lockout_kit_check`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table lockout_kit_checklist_selection
    function lockoutKitChecklistSelection($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";


        if ($params['lockout_kit_check_id'] != "") {
            $filter_arr[] = " `lockout_kit_check_id` = {$params['lockout_kit_check_id']} ";
        }

        if ($params['lockout_kit_checklist_id'] != "") {
            $filter_arr[] = " `lockout_kit_checklist_id` = {$params['lockout_kit_checklist_id']} ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `lockout_kit_checklist_selection`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table test_and_tag
    function getTestAndTag($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = " `active` = 1 ";
        $filter_arr[] = " `deleted` = 0 ";


        if ($params['tools_id'] != "") {
            $filter_arr[] = " `tools_id` = {$params['tools_id']} ";
        }

        if ($params['test_and_tag_id'] != "") {
            $filter_arr[] = " `test_and_tag_id` = {$params['test_and_tag_id']} ";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT *
			FROM `test_and_tag`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";
        return mysql_query($sql);
    }

    // table jobs
    // use job data instead
    function getJobs($params) {

        $sel_str = " * ";

        if ($params['custom_select'] != '') {
            $sel_str = $params['custom_select'];
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(j.`id`) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'assigned_tech':
                    $sel_str = " DISTINCT j.`assigned_tech`";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        } else if ($params['sum_completed_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( j.`date`, CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        } else if ($params['sum_job_price'] == "1") {
            $sel_str = " SUM( j.`job_price` ) AS j_price ";
        }

        // filters
        $filter_arr = array();

        $filter_arr[] = " p.`deleted` = 0 ";
        $filter_arr[] = " a.`status` = 'active' ";
        $filter_arr[] = " j.`del_job` =0";


        if ($params['jstatus'] != '') {
            $filter_arr[] = " j.`status` = '{$params['jstatus']}' ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = " a.`country_id` = {$params['country_id']} ";
        }

        if ($params['booked'] == 1) {
            $filter_arr[] = " j.`status` = 'Booked' ";
        }

        if ($params['ts_completed'] == 1) {
            $filter_arr[] = " j.`ts_completed` = 1 ";
        }

        if (is_numeric($params['dk'])) {
            $filter_arr[] = " j.`door_knock` = {$params['dk']} ";
        }

        if ($params['date'] != '') {
            $filter_arr[] = " j.`date` = '{$params['date']}' ";
        }

        if ($params['date_range'] != '') {
            $filter_arr[] = " j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}' ";
        }

        if ($params['exclude_status_for_kpi_report'] == 1) {
            $filter_arr[] = " (
				j.`status` != 'On Hold' AND
				j.`status` != 'Pending' AND
				j.`status` != 'Completed' AND
				j.`status` != 'Cancelled'
			) ";
        }

        if ($params['status_booked_or_completed'] == 1) {
            $filter_arr[] = " (
				j.`status` = 'Booked' OR
				j.`status` = 'Completed'
			) ";
        }

        if ($params['completed_status_for_kpi_report'] == 1) {
            $filter_arr[] = " ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )";
        }

        if ($params['query_for_estimated_income'] == 1) {
            $filter_arr[] = " (
				 ( j.`status` = 'Booked' AND j.`door_knock` !=1 ) OR
				  j.`status` = 'Completed'  OR
				  j.`status` = 'Merged Certificates'
			) ";
        }

        if ($params['exclude_tech_other_supplier'] == 1) {
            $filter_arr[] = " j.`assigned_tech` != 1 ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getJobsData($params) {

        // filters
        $filter_arr = array();
        $extra_field = '';

        // fields that needs join
        $join_tbl_str = '';
        if ($params['ejr_id'] != '') {
            $filter_arr[] = " AND sejr.`escalate_job_reasons_id` = '{$params['ejr_id']}' ";
            $join_tbl_str .= "
			INNER JOIN `selected_escalate_job_reasons` AS sejr ON j.`id` = sejr.`job_id`
			LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
			";
        }

        // maintenance program join
        if ($params['join_maintenance_program'] == 1) {
            $extra_field .= '
				am.`maintenance_id`,
				m.`name` AS m_name,
			';
            $filter_arr[] = " AND am.`maintenance_id` > 0 ";
            $filter_arr[] = " AND am.`status` = 1 ";
            $filter_arr[] = " AND m.`status` = 1 ";
            $join_tbl_str .= "
			INNER JOIN `agency_maintenance` AS am ON a.`agency_id` = am.`agency_id`
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			";
        }

        // maintenance program join
        if ($params['mp_join'] == 1) {
            $extra_field .= '
				am.`maintenance_id`,
				m.`name` AS m_name,
			';
            $join_tbl_str .= "
			LEFT JOIN `agency_maintenance` AS am ON a.`agency_id` = am.`agency_id`
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			";
        }


        // agency api token join
        if ($params['api_token_join'] == 1) {
            $extra_field .= '
				aat.`connection_date`,
			';
            $join_tbl_str .= "
			LEFT JOIN `agency_api_tokens` AS aat ON a.`agency_id` = aat.`agency_id` AND aat.`api_id` = 1
			";
        }


        // most calls remove_deleted_filter is empty, so this is mostly true and will be triggered.
        // used for some part where no deleted and active filter is used
        if ($params['remove_deleted_filter'] != 1) {

            // deleted marker
            if ($params['j_del'] != "") {
                $filter_arr[] = "AND j.`del_job` = {$params['j_del']}";
            } else {
                $filter_arr[] = "AND j.`del_job` = 0";
            }

            if ($params['p_del'] != "") {
                $filter_arr[] = "AND p.`deleted` = {$params['p_del']}";
            } else {
                $filter_arr[] = "AND p.`deleted` = 0";
            }

            if ($params['a_status'] != "") {

                if ($params['a_status'] != 'all') {
                    $filter_arr[] = "AND a.`status` = {$params['a_status']}";
                }
            } else {
                $filter_arr[] = "AND a.`status` = 'active'";
            }
        }


        if ($params['to_be_printed'] != "") {
            $filter_arr[] = " AND j.`to_be_printed` = {$params['to_be_printed']}";
        }


        if ($params['property_managers_id'] != "") {
            $filter_arr[] = " AND p.`pm_id_new` = {$params['property_managers_id']}";
        }

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND j.`id` = {$params['job_id']}";
        }

        if ($params['maintenance_id'] != "") {
            $filter_arr[] = "AND am.`maintenance_id` = {$params['maintenance_id']}";
        }

        if ($params['job_service'] != '') {
            $filter_arr[] = " AND j.`service` = '{$params['job_service']}' ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['job_type'] != '') {
            $filter_arr[] = "AND j.`job_type` = '{$params['job_type']}'";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = "AND p.`postcode` IN ( {$params['postcode_region_id']} )";
        }

        if ($params['a_postcode_region_id'] != "") {
            $filter_arr[] = "AND a.`postcode` IN ( {$params['a_postcode_region_id']} )";
        }

        if ($params['a_state'] != '') {
            $filter_arr[] = "AND a.`state` = '{$params['a_state']}'";
        }

        if ($params['job_status'] != '') {
            $filter_arr[] = "AND j.`status` = '{$params['job_status']}'";
        }

        if ($params['booked'] == 1) {
            $filter_arr[] = "AND j.`status` = 'Booked'";
        }

        if ($params['job_created'] != '') {
            $filter_arr[] = "AND CAST( j.`created` AS DATE ) = '{$params['job_created']}'";
        }

        if ($params['ts_completed'] == 1) {
            $filter_arr[] = "AND j.`ts_completed` = 1";
        }

        if (is_numeric($params['dk'])) {
            $filter_arr[] = "AND j.`door_knock` = {$params['dk']}";
        }

        if ($params['date'] != '') {
            $filter_arr[] = "AND j.`date` = '{$params['date']}'";
        }

        if (is_numeric($params['urgent_job'])) {
            $filter_arr[] = "AND j.`urgent_job` = '{$params['urgent_job']}'";
        }

        if (is_numeric($params['auto_renew'])) {
            $filter_arr[] = "AND a.`auto_renew` ={$params['auto_renew']}";
        }

        if (is_numeric($params['out_of_tech_hours'])) {
            $filter_arr[] = "AND j.`out_of_tech_hours` = {$params['out_of_tech_hours']}";
        }

        if ($params['date_range'] != '') {
            $filter_arr[] = "AND j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}'";
        }

        if ($params['exclude_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND (
				j.`status` != 'On Hold' AND
				j.`status` != 'Pending' AND
				j.`status` != 'Completed' AND
				j.`status` != 'Cancelled'
			)";
        }

        if ($params['status_booked_or_completed'] == 1) {
            $filter_arr[] = "AND (
				j.`status` = 'Booked' OR
				j.`status` = 'Completed'
			)";
        }

        if ($params['completed_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )";
        }

        if ($params['query_for_estimated_income'] == 1) {
            $filter_arr[] = "AND (
				 ( j.`status` = 'Booked' AND j.`door_knock` !=1 ) OR
				  j.`status` = 'Completed'  OR
				  j.`status` = 'Merged Certificates'
			)";
        }

        if ($params['exclude_tech_other_supplier'] == 1) {
            $filter_arr[] = "AND (
                j.`assigned_tech` != 1
                OR j.`assigned_tech` IS NULL
            )";
        }

        if ($params['dha_need_processing'] == 1) {
            $filter_arr[] = "AND j.`dha_need_processing` = 1";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE j.`id` > 0 " . implode(" ", $filter_arr);
        }

        $sel_str = "
			*,
			j.`id` AS jid,
			j.`status` AS jstatus,
			j.`service` AS jservice,
			j.`created` AS jcreated,
			j.`date` AS jdate,
			j.`comments` AS j_comments,

			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
            p.`comments` AS p_comments,
            p.`palace_prop_id`,
            p.`propertyme_prop_id`,
            apd.`api`,
            apd.`api_prop_id`,

			a.`agency_id` AS a_id,
			a.`phone` AS a_phone,
			a.`address_1` AS a_address_1,
			a.`address_2` AS a_address_2,
			a.`address_3` AS a_address_3,
			a.`state` AS a_state,
			a.`postcode` AS a_postcode,
			a.`trust_account_software`,
            a.`tas_connected`,
            a.`palace_supplier_id`,
            a.`palace_diary_id`,
            a.`pme_supplier_id`,

			jr.`name` AS jr_name,

			sa.`FirstName`,
			sa.`LastName`,

			{$extra_field}

			aua.`agency_user_account_id`,
			aua.`fname` AS pm_fname,
			aua.`lname` AS pm_lname,
			aua.`email` AS pm_email,

			ajt.`id` AS ajt_id,
			ajt.`type` AS ajt_type
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`property_managers_id`':
                    $sel_str = " DISTINCT p.`property_managers_id`, pm.`name`, pm.`pm_email` ";
                    break;
                case 'p.`pm_id_new`':
                    $sel_str = " DISTINCT p.`pm_id_new`, aua.`fname`, aua.`lname`, aua.`email` ";
                    break;
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`state` ";
                    break;
                case 'p.`agency_id`':
                    $sel_str = " DISTINCT p.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`agency_id`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`state` ";
                    break;
                case 'sa.`assigned_tech`':
                    $sel_str = " DISTINCT sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
                    break;
                case 'am.`maintenance_id`':
                    $sel_str = " DISTINCT am.`maintenance_id`, m.`name` AS m_name ";
                    break;
                    break;
                case 'tech_id': // need to find where did i passed this
                    $sel_str = " DISTINCT j.`assigned_tech`";
                    break;
                case 'j.`status`':
                    $sel_str = " DISTINCT j.`status` ";
                    break;
                case 'sejr.`escalate_job_reasons_id`':
                    $sel_str = " DISTINCT sejr.`escalate_job_reasons_id`, ejr.`reason` ";
                    $join_tbl_str = "
					INNER JOIN `selected_escalate_job_reasons` AS sejr ON j.`id` = sejr.`job_id`
					LEFT JOIN `escalate_job_reasons` AS ejr ON sejr.`escalate_job_reasons_id` = ejr.`escalate_job_reasons_id`
					";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        } else if ($params['sum_job_price'] == "1") {
            $sel_str = " SUM( j.`job_price` ) AS j_price ";
        } else if ($params['count_jobs_by_agency'] == "1") {
            $sel_str = " COUNT( j.`id` ) AS esc_num_jobs, a.`agency_id`, a.`agency_name`, a.`phone`, a.`state`, a.`save_notes`, a.`escalate_notes`, a.`escalate_notes_ts`, a.`trust_account_software`, a.`tas_connected`, a.`propertyme_agency_id` ";
        } else if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        }









        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "SELECT {$sel_str}
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency_user_accounts` AS aua ON p.`pm_id_new` = aua.`agency_user_account_id`
		LEFT JOIN `api_property_data` AS apd ON p.`property_id` = apd.`crm_prop_id`
		{$join_tbl_str}
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";



        if ($params['display_echo'] == 1) {
            echo $sql;
        }



        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getPropertyData($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND p.`deleted` = 0";
        $filter_arr[] = "AND a.`status` = 'active'";



        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if ($params['property_id'] != "") {
            $filter_arr[] = "AND p.`property_id` = {$params['property_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = "AND p.`postcode` IN ( {$params['postcode_region_id']} )";
        }

        if ($params['postcode_search'] != "") {
            $filter_arr[] = "AND p.`postcode` IN ( {$params['postcode_search']} )";
        }

        if ($params['a_postcode_region_id'] != "") {
            $filter_arr[] = "AND a.`postcode` IN ( {$params['a_postcode_region_id']} )";
        }

        if ($params['a_state'] != '') {
            $filter_arr[] = "AND a.`state` = '{$params['a_state']}'";
        }

        if ($params['p_state'] != '') {
            $filter_arr[] = "AND p.`state` = '{$params['p_state']}'";
        }

        if (is_numeric($params['ps_service'])) {

            if (is_numeric($params['ps_service']) && $params['ps_service'] == 1) {
                $filter_arr[] = "AND ps.`service` = 1";
            } else if (is_numeric($params['ps_service']) && $params['ps_service'] == 0) {
                $filter_arr[] = "AND ( ps.`service` = 0 OR ps.`service` IS NULL )";
            }
        }

        if (is_numeric($params['auto_renew'])) {
            $filter_arr[] = "AND a.`auto_renew` ={$params['auto_renew']}";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // custom query
        if ($params['custom_query'] != '') {
            $filter_arr['custom_query'] = "{$custom_query}";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }



        // additional joins
        $join_table_str = '';
        $join_sel_str = '';
        if ($params['add_join'] != '') {
            if ($params['add_join']['ps_sats_to_service'] == 1) {
                $join_table_str .= " LEFT JOIN `property_services` AS ps ON ( p.`property_id` = ps.`property_id` AND ps.`alarm_job_type_id` =2 ) ";
                $join_sel_str .= " ,ps.`service` ";
            }

            if ($params['add_join']['region_or_district'] == 1) {
                $join_table_str .= " LEFT JOIN `postcode_regions` AS pr ON ( p.`postcode` IN ( pr.`postcode_region_postcodes` ) ) ";
                $join_sel_str .= " ,pr.`postcode_region_name` ";
            }
        }



        $sel_str = "
			p.`property_id`,
			p.`address_1` AS p_address_1,
			p.`address_2` AS p_address_2,
			p.`address_3` AS p_address_3,
			p.`state` AS p_state,
			p.`postcode` AS p_postcode,
			p.`comments` AS p_comments,
			p.`tenant_firstname1`,
			p.`tenant_lastname1`,
			p.`tenant_mob1`,
			p.`tenant_ph1`,
			p.`tenant_email1`,
			p.`tenant_firstname2`,
			p.`tenant_lastname2`,
			p.`tenant_mob2`,
			p.`tenant_ph2`,
			p.`tenant_email2`,
			p.`landlord_firstname`,
			p.`landlord_lastname`,
			p.`landlord_mob`,
			p.`landlord_ph`,
			p.`landlord_email`,
			p.`key_number`,
			p.`tenant_changed`,
			p.`holiday_rental`,
			p.`no_keys`,
			p.`alarm_code`,
			p.`agency_deleted`,
			p.`deleted`,
			p.`no_en`,

			a.`agency_id`,
			a.`agency_name`,
			a.`phone` AS a_phone,
			a.`franchise_groups_id`

			{$join_sel_str}
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`state` ";
                    break;
                case 'p.`agency_id`':
                    $sel_str = " DISTINCT p.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`agency_id`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`agency_name` ";
                    break;
                case 'a.`state`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`state` ";
                    break;
            }
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != '') {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }



        $sql = "
			SELECT {$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$join_table_str}
			WHERE p.`property_id` > 0
			{$filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        //echo "<div style='display:none;'>{$sql}</div>";



        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // table alarms
    function getAlarms($params) {

        $sel_str = " * ";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['sum_alarm_price'] == "1") {
            $sel_str = " SUM( alrm.`alarm_price` ) AS a_price ";
        }

        // filters
        $filter_arr = array();

        $filter_arr[] = " p.`deleted` = 0 ";
        $filter_arr[] = " a.`status` = 'active' ";
        $filter_arr[] = " j.`del_job` =0";


        if ($params['country_id'] != "") {
            $filter_arr[] = " a.`country_id` = {$params['country_id']} ";
        }

        if ($params['date'] != '') {
            $filter_arr[] = " j.`date` = '{$params['date']}' ";
        }

        if ($params['date_range'] != '') {
            $filter_arr[] = " j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}' ";
        }

        if ($params['query_for_estimated_income'] == 1) {
            $filter_arr[] = " (
				 ( j.`status` = 'Booked' AND j.`door_knock` != 1 ) OR
				  j.`status` = 'Completed'  OR
				  j.`status` = 'Merged Certificates'
			) ";
        }

        if ($params['new_alarm'] == 1) {
            $filter_arr[] = " alrm.`new` = 1 ";
        }

        if ($params['ts_discarded'] != '') {
            $filter_arr[] = " alrm.`ts_discarded` = {$params['ts_discarded']} ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM  `alarm` AS alrm
			LEFT JOIN `jobs` AS j ON alrm.`job_id` = j.`id`
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get Expenses last ID number
    function getPurchaseOrderLastIDNumber() {
        return mysql_query("
			SELECT `purchase_order_num`
			FROM `purchase_order`
			ORDER BY `purchase_order_id` DESC
			LIMIT 1
		");
    }

    function getServiceCount($ajt, $postcode) {

        $sql_str = "
			SELECT count( ps.`property_services_id` ) AS jcount
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON p.`property_id` = ps.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE ps.`alarm_job_type_id` ={$ajt}
			AND ps.`service` =1
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND p.`postcode` IN({$postcode})
			AND a.`country_id` = {$_SESSION['country_default']}
		";

        $sql = mysql_query($sql_str);
        $row = mysql_fetch_array($sql);
        return $row['jcount'];
    }

    function getState() {

        return $state = array('NSW', 'ACT', 'VIC', 'SA', 'QLD', 'TAS', 'WA', 'NT');
    }

    // get region
    function getRegion($params) {

        $sel_str = " SELECT * ";

        if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'r.`region_state`':
                    $sel_str = " SELECT DISTINCT r.`region_state` ";
                    break;
            }
        }

        $filter_arr = array();

        $filter_arr[] = " pr.`deleted` = 0 ";
        $filter_arr[] = " pr.`country_id` = {$_SESSION['country_default']} ";

        if ($params['state'] != "") {
            $filter_arr[] = " r.`region_state` = '{$params['state']}' ";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = " pr.`postcode_region_id` IN ({$params['postcode_region_id']}) ";
        }

        if ($params['postcode_region_postcodes'] != "") {
            $filter_arr[] = " pr.`postcode_region_postcodes` LIKE '%{$params['postcode_region_postcodes']}%' ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `postcode_regions` AS pr
			LEFT JOIN `countries` AS c ON pr.`country_id` = c.`country_id`
			LEFT JOIN `regions` AS r ON pr.`region` = r.`regions_id`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        return mysql_query($sql);
    }

    // get incident and report
    function getIncidentAndReport($params) {

        $sel_str = " SELECT * ";

        if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'r.`region_state`':
                    $sel_str = " SELECT DISTINCT r.`region_state` ";
                    break;
            }
        }

        $filter_arr = array();

        $filter_arr[] = " iai.`deleted` = 0 ";
        //$filter_arr[] = " aia.`country_id` = {$_SESSION['country_default']} ";

        if ($params['iai_id'] != "") {
            $filter_arr[] = " iai.`incident_and_injury_id` = '{$params['iai_id']}' ";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = " pr.`postcode_region_id` IN ({$params['postcode_region_id']}) ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `incident_and_injury` AS iai
			LEFT JOIN `staff_accounts` AS sa ON iai.`reported_to` = sa.`StaffID`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        return mysql_query($sql);
    }

    function getIncidentPhotos($iai_id) {
        return mysql_query("
			SELECT *
			FROM `incident_photos`
			WHERE `incident_and_injury_id` = {$iai_id}
		");
    }

    // get Tool Items
    public function getToolItems($params) {

        $sel_str = " SELECT * ";

        $filter_arr = array();

        $filter_arr[] = " ti.`active` = 1 ";
        $filter_arr[] = " ti.`deleted` = 0 ";

        /*
          if($params['state']!=""){
          $filter_arr[] = " r.`region_state` = '{$params['state']}' ";
          }
         */

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `tool_items` AS ti
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        return mysql_query($sql);
    }

    // table leave
    function getLeave($params) {

        // filters
        $filter_arr = array();

        $filter_arr[] = "AND l.`active` = 1";
        $filter_arr[] = "AND l.`deleted` = 0";


        if ($params['country_id'] != "") {
            $filter_arr[] = "AND l.`country_id` = {$params['country_id']}";
        }

        if ($params['leave_id'] != "") {
            $filter_arr[] = "AND l.`leave_id` = {$params['leave_id']}";
        }

        if ($params['needs_approval'] == 1) {
            $filter_arr[] = "AND ( l.`hr_app` IS NULL OR l.`line_manager_app` IS NULL )";
        }

        if ($params['emp_id'] != "") {
            $filter_arr[] = "AND sa_emp.`StaffID` = {$params['emp_id']}";
        }

        if ($params['lm_id'] != "") {
            $filter_arr[] = "AND sa_lm.`StaffID` = {$params['lm_id']}";
        }

        if ($params['l_status'] != "") {
            $filter_arr[] = "AND l.`status` = '{$params['l_status']}'";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE l.`leave_id` > 0 " . implode(" ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				l.*,
				sa_emp.`StaffID` AS emp_staff_id,
				sa_emp.`FirstName` AS emp_fname,
				sa_emp.`LastName` AS emp_lname,
				sa_emp.`Email` AS emp_email,

				sa_lm.`StaffID` AS sa_lm_staff_id,
				sa_lm.`FirstName` AS lm_fname,
				sa_lm.`LastName` AS lm_lname,
				sa_lm.`Email` AS lm_email,

				lma.`FirstName` AS lma_fname,
				lma.`LastName` AS lma_lname,
				hra.`FirstName` AS hra_fname,
				hra.`LastName` AS hra_lname,
				atc.`FirstName` AS atc_fname,
				atc.`LastName` AS atc_lname,
				sn.`FirstName` AS sn_fname,
				sn.`LastName` AS sn_lname
			";
        }

        $sql = "
			SELECT {$sel_str}
			FROM `leave` AS l
			LEFT JOIN `staff_accounts` AS sa_emp ON l.`employee` = sa_emp.`StaffID`
			LEFT JOIN `staff_accounts` AS sa_lm ON l.`line_manager` = sa_lm.`StaffID`
			LEFT JOIN `staff_accounts` AS lma ON l.`line_manager_app_by` = lma.`StaffID`
			LEFT JOIN `staff_accounts` AS hra ON l.`hr_app_by` = hra.`StaffID`
			LEFT JOIN `staff_accounts` AS atc ON l.`added_to_cal_by` = atc.`StaffID`

			LEFT JOIN `staff_accounts` AS sn ON l.`staff_notified_by` = sn.`StaffID`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        return mysql_query($sql);
    }

    // get Expenses
    public function getExpenses($params) {

        $sel_str = "
			SELECT *,
			emp.`StaffID` AS emp_staff_id,
			emp.`FirstName` AS emp_fname,
			emp.`LastName` AS emp_lname,
			eb.`StaffID` AS eb_staff_id,
			eb.`FirstName` AS eb_fname,
			eb.`LastName` AS eb_lname
		";

        $filter_arr = array();

        $filter_arr[] = " exp.`active` = 1 ";
        $filter_arr[] = " exp.`deleted` = 0 ";


        if ($params['employee'] != "") {
            $filter_arr[] = " exp.`employee` = '{$params['employee']}' ";
        }

        if ($params['expense_id'] != "") {
            $filter_arr[] = " exp.`expense_id` = '{$params['expense_id']}' ";
        }

        if ($params['entered_by'] != "") {
            $filter_arr[] = " exp.`entered_by` = '{$params['entered_by']}' ";
        }

        if ($params['exp_sum_id'] != "") {
            $filter_arr[] = " exp.`expense_summary_id` = '{$params['exp_sum_id']}' ";
        }

        // exclude submitted expenses
        if ($params['exc_sub_exp'] == 1) {
            $filter_arr[] = " exp.`expense_summary_id` IS NULL ";
        }

        if ($params['date'] != "") {
            $filter_arr[] = " exp.`date` = '{$params['date']}' ";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " exp.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
            }
        }



        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // sort
        if ($params['sort_list2'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list2'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `expenses` AS exp
			LEFT JOIN `expense_account` AS exp_acc ON exp.`account` = exp_acc.`expense_account_id`
			LEFT JOIN `staff_accounts` AS emp ON exp.`employee` = emp.`StaffID`
			LEFT JOIN `staff_accounts` AS eb ON exp.`entered_by` = eb.`StaffID`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;

        return mysql_query($sql);
    }

    // get Expense Account
    public function getExpenseAccount($params) {

        $sel_str = " SELECT * ";

        $filter_arr = array();

        $filter_arr[] = " exp_acc.`active` = 1 ";
        $filter_arr[] = " exp_acc.`deleted` = 0 ";

        /*
          if($params['employee']!=""){
          $filter_arr[] = " exp.`employee` = '{$params['employee']}' ";
          }
         */


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `expense_account` AS exp_acc
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;

        return mysql_query($sql);
    }

    // get Expense Summary
    public function getExpenseSummary($params) {

        if ($params['distinct'] != "") {

            switch ($params['distinct']) {
                case 'exp_sum.`employee`':
                    $sel_str = " SELECT DISTINCT exp_sum.`employee`, sa.`FirstName` AS sa_fname, sa.`LastName` AS sa_lname ";
                    break;
            }
        } else if ($params['return_sum'] == 1) {

            $sel_str = " SELECT SUM(exp.`amount`) AS jsum ";
        } else if ($params['custom_select'] != '') {

            $sel_str = " {$params['custom_select']} ";
        } else {
            $sel_str = "
			SELECT
				*,
				sa.`FirstName` AS sa_fname,
				sa.`LastName` AS sa_lname,
				sa_who.`FirstName` AS sa_who_fname,
				sa_who.`LastName` AS sa_who_lname,
				lm.`FirstName` AS lm_fname,
				lm.`LastName` AS lm_lname
			";
        }


        // custom join
        if ($params['join_table'] != '') {
            if ($params['join_table'] = 'expenses') {
                $join_str = 'RIGHT JOIN `expenses` AS exp ON exp.`expense_summary_id` = exp_sum.`expense_summary_id`';
            }
        }


        $filter_arr = array();

        $filter_arr[] = " exp_sum.`active` = 1 ";
        $filter_arr[] = " exp_sum.`deleted` = 0 ";


        if ($params['country_id'] != "") {
            $filter_arr[] = " exp_sum.`country_id` = '{$params['country_id']}' ";
        }

        if ($params['exp_sum_id'] != "") {
            $filter_arr[] = " exp_sum.`expense_summary_id` = '{$params['exp_sum_id']}' ";
        }

        if ($params['employee'] != "") {
            $filter_arr[] = " exp_sum.`employee` = '{$params['employee']}' ";
        }

        if ($params['date_reimbursed_is_null'] == 1) {
            $filter_arr[] = " exp_sum.`date_reimbursed` IS NULL ";
        }

        if ($params['line_manager'] != '') {
            $filter_arr[] = " exp_sum.`line_manager` = {$params['line_manager']} ";
        }

        if ($params['exp_sum_status'] != '') {

            if ($params['exp_sum_status'] == -2) {
                // all
            } else if ($params['exp_sum_status'] == -1) {
                $filter_arr[] = " ( exp_sum.`exp_sum_status` IS NULL ) ";
            } else {
                $filter_arr[] = " exp_sum.`exp_sum_status` = {$params['exp_sum_status']} ";
            }
        }


        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " exp_sum.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ";
            }
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `expense_summary` AS exp_sum
			LEFT JOIN `staff_accounts` AS sa ON exp_sum.`employee` = sa.`StaffID`
			LEFT JOIN `staff_accounts` AS sa_who ON exp_sum.`who` = sa_who.`StaffID`
			LEFT JOIN `staff_accounts` AS lm ON exp_sum.`line_manager` = lm.`StaffID`
			{$join_str}
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        return mysql_query($sql);
    }

    public function displaySession() {
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }

    function getToolsLastInspection($params) {

        if ($params['item'] == 1) { // if ladder
            return mysql_query("
				SELECT *
				FROM `ladder_check`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        } else if ($params['item'] == 2) { // if drill
            return mysql_query("
				SELECT *
				FROM `test_and_tag`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        } else if ($params['item'] == 4) { // if drill
            return mysql_query("
				SELECT *
				FROM `lockout_kit_check`
				WHERE `tools_id` = {$params['tools_id']}
				ORDER BY `date` DESC
				LIMIT 1
			");
        }
    }

    function convertEmailToArray($email) {

        unset($jemail);
        $jemail = array();
        $temp = explode("\n", trim($email));
        foreach ($temp as $val) {

            $val2 = preg_replace('/\s+/', '', $val);
            if (filter_var($val2, FILTER_VALIDATE_EMAIL)) {
                $jemail[] = $val2;
            }
        }

        // send email
        return $jemail;
    }

    // format date
    function formatDate($date, $format = 'Y-m-d') {
        return date($format, strtotime(str_replace("/", "-", mysql_real_escape_string($date))));
    }

    function formatStaffName($fname, $lname) {
        return "{$fname}" . ( ($lname != "") ? ' ' . strtoupper(substr($lname, 0, 1)) . '.' : '' );
    }

    function isDateNotEmpty($date) {
        if (
                $date != '' &&
                $date != '0000-00-00' &&
                $date != '0000-00-00 00:00:00' &&
                $date != '1970-01-01'
        ) {
            return true;
        } else {
            return false;
        }
    }

    function getDynamicDomain() {

        if (strpos(URL, "dev") == false) {
            // live
            $domain = 'crm';
        } else {
            //dev
            $domain = 'crmdev';
        }

        return $domain;
    }

    // native PHP email
    function nativeEmail($params) {

        // data
        $to = $params['to'];
        $from = $params['from'];
        $subject = $params['subject'];
        $message = $params['message'];
        $cc = $params['cc'];

        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        /*
          $headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
          $headers .= 'From: Birthday Reminder <birthday@example.com>' . "\r\n";
          $headers .= 'Cc: birthdayarchive@example.com' . "\r\n";
          $headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";
         */
        $headers .= "To: <{$to}>\r\n";
        $headers .= "From: {$from}\r\n";
        $headers .= "Cc: {$cc}\r\n";

        // Mail it
        mail($to, $subject, $message, $headers);
    }

    function getSatsAPIkey() {
        return $api_key = 'AIzaSyBqYJ80rXXfOv5qrbQxXwIpU4H_WHctHHM';
    }

    // Incident photo upload
    function uploadIncidentReportUpload($file) {

        // upload
        if ($file) {


            $country_folder = "/" . strtolower($_SESSION['country_iso']);
            $image_name = "incident" . rand() . '_' . date('YmdHis');

            $folder = "images/incident{$country_folder}";


            // if folder does not exist, make one
            if (!is_dir($folder)) {
                mkdir($folder);
            }

            // IMAGE 1
            $handle = new upload($file);
            if ($handle->uploaded) {

                $handle->file_new_name_body = $image_name;
                $handle->image_resize = true;
                $handle->image_x = 760;
                $handle->image_ratio_y = true;
                $handle->process($_SERVER['DOCUMENT_ROOT'] . $folder);
                if ($handle->processed) {
                    // get file extension
                    $fn = explode("/", $file['type']);
                    $file_ext = ($fn[1] == 'jpeg') ? 'jpg' : $fn[1];
                    $db_ret['photo_of_incident'] = "{$folder}/{$image_name}.{$file_ext}";
                    $handle->clean();
                } else {
                    $error = 'error : ' . $handle->error;
                }
            }

            $db_ret['error'] = $error;

            return $db_ret;
        }
    }

    function masterDynamicUpload($params) {


        // upload
        if ($params['files']) {


            $image_name = "img_{$params['id']}_" . rand() . "_" . date("YmdHis");
            $upload_folder = "images/{$params['upload_folder']}";
            $upload_path = DOC_ROOT . $upload_folder;
            $image_size = ($params['image_size'] != '') ? $params['image_size'] : 760;


            // IMAGE 1
            $handle = new upload($params['files']);
            if ($handle->uploaded) {
                $handle->file_new_name_body = $image_name;
                $handle->image_resize = true;
                $handle->image_convert = 'png';
                $handle->image_x = $image_size;
                $handle->image_ratio_y = true;
                $handle->process($upload_path);
                if ($handle->processed) {
                    //echo 'image resized';
                    $db_ret['image_name'] = "{$image_name}.png";
                    $handle->clean();
                } else {
                    $error = 'error : ' . $handle->error;
                }
            }

            $db_ret['error'] = $error;

            return $db_ret;
        }
    }

    function deleteFile($path_to_file) {
        $source_folder = 'images';
        $del_file = "{$source_folder}/{$path_to_file}";

        if ($del_file != "") {
            // delete file
            unlink($del_file);
        }
    }

    function deleteExpenseFile($path_to_file) {
        //$source_folder = 'uploads';
        $del_file = "{$path_to_file}";
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        $search_str_res = stripos($del_file, $doc_root);

        if ($del_file != "" && $search_str_res == false) {
            // delete file
            unlink($del_file);
        }
    }

    // SAFE TO delete, tested make sure to pass the doc root so its safer
    function genericDeleteFile($path_to_file) {
        //$source_folder = 'uploads';
        $del_file = "{$path_to_file}";
        $doc_root = $_SERVER['DOCUMENT_ROOT'];
        $search_str_res = stripos($del_file, $doc_root);

        if ($del_file != "" && $search_str_res == false) {
            // delete file
            unlink($del_file);
        }
    }

    // re-write array multiple upload into a good structured array
    function reArrayFiles(&$file_post) {

        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);

        for ($i = 0; $i < $file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }

        return $file_ary;
    }

    // get Nature of Incident
    function getNatureOfIncident($nature_of_incident) {
        switch ($nature_of_incident) {
            case 1:
                $nature_of_incident2 = 'Near Miss';
                break;
            case 2:
                $nature_of_incident2 = 'First Aid';
                break;
            case 3:
                $nature_of_incident2 = 'Medical Treatment';
                break;
            case 4:
                $nature_of_incident2 = 'Car accident';
                break;
            case 5:
                $nature_of_incident2 = 'Property damage';
                break;
            case 6:
                $nature_of_incident2 = 'Incident report';
                break;
        }
        return $nature_of_incident2;
    }

    function getIncidentAndReportPdf($params) {

        // instantiate class
        $crm = new Sats_Crm_Class;
        $iai_id = $params['iai_id'];
        // get incident report data
        $jparams = array(
            'iai_id' => $iai_id
        );
        $iai_sql = $crm->getIncidentAndReport($jparams);
        $iai = mysql_fetch_array($iai_sql);

        // get country data
        $cntry_sql = getCountryViaCountryId();
        $cntry = mysql_fetch_array($cntry_sql);

        // start fpdf
        $pdf = new jPDF('P', 'mm', 'A4');
        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($cntry['country_id']);

        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true, 50);
        $pdf->AddPage();

        // set default values
        $header_space = 6.5;
        $header_width = 100;
        $header_height = 10;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = null;

        $cell_width = 64;
        $cell_width2 = 128;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';


        // THE INCIDENT
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'The Incident', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Date of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Time of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('H:i', strtotime($iai['datetime_of_incident'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Nature of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $this->getNatureOfIncident($iai['nature_of_incident']), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Location of incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['location_of_incident'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Describe the incident: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        //$test = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ornare lorem mauris, non varius felis tempor venenatis. Curabitur felis sem, finibus sit amet interdum vitae, consequat at massa. Phasellus eleifend, justo at blandit ornare, nulla odio ultricies augue, sed mollis est urna vitae nisl. Etiam dignissim purus vitae augue hendrerit, non consequat dui varius. Ut pharetra condimentum mollis. Pellentesque sodales in massa ultrices sollicitudin. Cras sodales sapien eu tellus sollicitudin placerat.';
        $pdf->MultiCell($cell_width2, $cell_height, $iai['describe_incident']);

        $pdf->Ln($header_space);

        // INJURED PERSON DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Injured Person Details', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Address: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_address'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Occupation: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_occupation'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Date of birth: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y', strtotime($iai['ip_dob'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Telephone number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_tel_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Employer: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_employer'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Nature of Injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_noi'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Location of Injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['ip_loi'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Onsite treatment: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['ip_onsite_treatment']) && $iai['ip_onsite_treatment'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Further treatment required?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['ip_further_treatment']) && $iai['ip_further_treatment'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        // WITNESS DETAILS
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Witness Details', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_name'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Contact Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['witness_contact'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        // OUTCOME
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Outcome', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Time lost due to injury: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, ( ( is_numeric($iai['loss_time_injury']) && $iai['loss_time_injury'] == 1 ) ? 'Yes' : 'No'), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Who was the incident reported to?: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, $iai['FirstName'] . ' ' . $iai['LastName'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Report Submitted: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width2, $cell_height, date('d/m/Y H:i', strtotime($iai['created_date'])), $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf_filename = 'incident_and_injury_report_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $params['output']);
    }

    // leave
    function getLeavePdf($params) {

        // instantiate class
        $crm = new Sats_Crm_Class;
        $leave_id = $params['leave_id'];
        // get incident report data
        $jparams = array(
            'leave_id' => $leave_id,
            'country_id' => $country_id
        );
        $leave_sql = $crm->getLeave($jparams);
        $leave = mysql_fetch_array($leave_sql);

        // get country data
        $cntry_sql = getCountryViaCountryId();
        $cntry = mysql_fetch_array($cntry_sql);

        // start fpdf
        $pdf = new jPDF('P', 'mm', 'A4');
        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($cntry['country_id']);

        $pdf->SetTopMargin(40);
        $pdf->SetAutoPageBreak(true, 50);
        $pdf->AddPage();

        // set default values
        $header_space = 6.5;
        $header_width = 100;
        $header_height = 10;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = null;

        $cell_width = 64;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';


        // LEAVE REQUEST
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Leave Request', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Date: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['date'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['emp_fname'] . ' ' . $leave['emp_lname'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Type of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $this->getTypesofLeave($leave['type_of_leave']), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'First Day of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['lday_of_work'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Last Day of Leave: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, date('d/m/Y', strtotime($leave['fday_back'])), $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Number of days : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['num_of_days'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Reason for Leave : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->MultiCell($cell_width + 50, $cell_height, $leave['reason_for_leave'], $cell_border, $col2_cell_align);

        $pdf->Ln($header_space);

        // OFFICIAL USE ONLY
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Office Use Only', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell($cell_width, $cell_height, 'Line Manager : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $leave['lm_fname'] . ' ' . $leave['lm_lname'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        // HR Approved
        if (is_numeric($leave['hr_app']) && $leave['hr_app'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['hr_app']) && $leave['hr_app'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'HR Approved : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Line Manager Approved
        if (is_numeric($leave['line_manager_app']) && $leave['line_manager_app'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['line_manager_app']) && $leave['line_manager_app'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Line Manager Approved : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Added to Calendar
        if (is_numeric($leave['added_to_cal']) && $leave['added_to_cal'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['added_to_cal']) && $leave['added_to_cal'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Added to Calendar : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        // Added to MYOB
        if (is_numeric($leave['staff_notified']) && $leave['staff_notified'] == 1) {
            $sel_str = 'Yes';
        } else if (is_numeric($leave['staff_notified']) && $leave['staff_notified'] == 0) {
            $sel_str = 'No';
        } else {
            $sel_str = '';
        }
        $pdf->Cell($cell_width, $cell_height, 'Staff notified in writing : ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $sel_str, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf_filename = 'leave_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $params['output']);
    }

    function getVehicleTools($vehicle_id) {

        return mysql_query("
			SELECT *
			FROM `tools`
			WHERE `assign_to_vehicle` = {$vehicle_id}
			AND `active` = 1
			AND `deleted` = 0
			AND `country_id` = {$_SESSION['country_default']}
		");
    }

    function getVehicleDetailsPdf($params) {

        // instantiate class
        $crm = new Sats_Crm_Class;


        $vehicle_id = $params['vehicle_id'];
        // get incident report data
        $jparams = array(
            'vehicle_id' => $vehicle_id
        );
        $vehicle_sql = $crm->getVehicles($jparams);
        $v = mysql_fetch_array($vehicle_sql);


        // get country data
        $cntry_sql = getCountryViaCountryId();
        $cntry = mysql_fetch_array($cntry_sql);

        // start fpdf
        $pdf = new FPDF('P', 'mm', 'A4');
        //$pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        //$pdf->setCountryData($cntry['country_id']);

        $pdf->SetTopMargin(10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // set default values
        $header_space = 2.5;
        $header_width = 100;
        $header_height = 5;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = null;
        $header_font_family = 'Arial';
        $header_font_style = 'U';
        $header_font_size = 12;

        $cell_width = 50;
        $cell_width2 = 30;
        $cell_height = 6;
        $cell_border = 0;
        $col1_cell_new_line = 0;
        $col2_cell_new_line = 1;
        $col1_cell_align = 'L';
        $col2_cell_align = 'L';
        $cell_font_family = 'Arial';
        $cell_font_style = '';
        $cell_font_size = 9;


        // sats logo
        $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/satslogo.png');

        // image
        if ($v['image'] != '') {
            $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/vehicle/' . $v['image'], 110, 30);
        } else { // if car image not yet present
            $pdf->image($_SERVER['DOCUMENT_ROOT'] . '/images/no_car_image.jpg', 110, 30);
        }

        $pdf->Ln($header_space);

        // Vehicle
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Vehicle', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Plant ID: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['plant_id'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Make: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['make'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Model: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['model'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Year: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['year'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'VIN Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['vin_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Engine Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['engine_number'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        $current_x_pos = $pdf->GetX();
        $current_y_pos = $pdf->GetY();

        $col_1_pos_x = $current_x_pos;
        $col_1_pos_y = $current_y_pos;

        $pd_x_pos = $current_x_pos + 100;
        $pd_y_pos = $current_y_pos + 20;

        $pdf->SetXY($pd_x_pos, $pd_y_pos);

        // Purchase Details
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Purchase Details', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Purchase Date: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $purchase_date = ($v['purchase_date'] != "0000-00-00" && $v['purchase_date'] != "") ? date("d/m/Y", strtotime($v['purchase_date'])) : '';
        $pdf->Cell($cell_width, $cell_height, $purchase_date, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Purchase Price: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['purchase_price'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Warranty Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $warrant_expires = ($v['warranty_expires'] != "0000-00-00" && $v['warranty_expires'] != "") ? date("d/m/Y", strtotime($v['warranty_expires'])) : '';
        $pdf->Cell($cell_width, $cell_height, $warrant_expires, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();


        // Driver
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Driver', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Driver Name: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $driver_name = $v['FirstName'] . ' ' . $v['LastName'];
        $pdf->Cell($cell_width, $cell_height, $driver_name, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();


        // Finance
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Finance', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);
        $current_y_pos = $pdf->GetY();

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Bank:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['finance_bank'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Loan Number:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['finance_loan_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Term (Months):', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['finance_loan_terms'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Monthly $:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['finance_monthly_repayments'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'Start Date:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $finance_start_date = ( $v['finance_start_date'] != "0000-00-00" && $v['finance_start_date'] != "" ) ? date("d/m/Y", strtotime($v['finance_start_date'])) : '';
        $pdf->Cell($cell_width, $cell_height, $finance_start_date, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $current_y_pos = $pdf->GetY();
        $pdf->SetXY($pd_x_pos, $current_y_pos);
        $pdf->Cell($cell_width2, $cell_height, 'End Date:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $finance_end_date = ( $v['finance_end_date'] != "0000-00-00" && $v['finance_end_date'] != "" ) ? date("d/m/Y", strtotime($v['finance_end_date'])) : '';
        $pdf->Cell($cell_width, $cell_height, $finance_end_date, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);


        $pdf->SetXY($col_1_pos_x, $col_1_pos_y);
        // Fuel
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Fuel', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Type: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['fuel_type'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Card Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['fuel_card_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Fuel Card Pin: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['fuel_card_pin'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);



        // eTag
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'eTag', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'eTag Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['etag_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Insurance
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Insurance', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Policy Number: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['ins_pol_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Insurer: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['insurer'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Policy Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $policy_expiry = ( $v['policy_expires'] != "0000-00-00 00:00:00" && $v['policy_expires'] != "" ) ? date("d/m/Y", strtotime($v['policy_expires'])) : '';
        $pdf->Cell($cell_width, $cell_height, $policy_expiry, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Registration
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Registration', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Number Plate: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['number_plate'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $rego_expiry = ($v['rego_expires'] != "0000-00-00 00:00:00") ? date("d/m/Y", strtotime($v['rego_expires'])) : '';
        $pdf->Cell($cell_width, $cell_height, 'Rego Expires: ', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $rego_expiry, $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Cust. Rego #:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['cust_reg_num'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Key Number:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $v['key_number'], $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // KMS
        $kms_sql = mysql_query("
			SELECT *
			FROM `kms`
			WHERE `vehicles_id` = {$v['vehicles_id']}
			ORDER BY `kms_updated` DESC
			LIMIT 0, 1
		");
        $kms = mysql_fetch_array($kms_sql);
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'KMS', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell($cell_width, $cell_height, 'Kms:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $pdf->Cell($cell_width, $cell_height, $kms['kms'], $cell_border, $col2_cell_new_line, $col2_cell_align);
        $pdf->Cell($cell_width, $cell_height, 'Kms Updated:', $cell_border, $col1_cell_new_line, $col1_cell_align);
        $kms_updated_ts = ( $kms['kms_updated'] != "0000-00-00 00:00:00" && $kms['kms_updated'] != "" ) ? date('d/m/Y', strtotime($kms['kms_updated'])) : '';
        $pdf->Cell($cell_width, $cell_height, $kms_updated_ts, $cell_border, $col2_cell_new_line, $col2_cell_align);

        $pdf->Ln($header_space);

        // Tools
        $pdf->SetFont($header_font_family, $header_font_style, $header_font_size);
        $pdf->Cell($header_width, $header_height, 'Tools', $header_border, $header_new_line, $header_align);

        $pdf->Ln($header_space);

        $pdf->SetFont($cell_font_family, $cell_font_style, $cell_font_size);
        $pdf->Cell(60, $header_height, 'Item ID:', 1, 0, $header_align);
        $pdf->Cell(60, $header_height, 'Brand:', 1, 0, $header_align);
        $pdf->Cell(60, $header_height, 'Description:', 1, 0, $header_align);

        $pdf->Ln();

        $tools_sql = $this->getVehicleTools($vehicle_id);
        $pdf->SetFont('Arial', '', 11);
        while ($tool = mysql_fetch_array($tools_sql)) {
            $pdf->Cell(60, $header_height, $tool['item_id'], 1, 0, $header_align);
            $pdf->Cell(60, $header_height, $tool['brand'], 1, 0, $header_align);
            $pdf->Cell(60, $header_height, $tool['description'], 1, 0, $header_align);
            $pdf->Ln();
        }





        $pdf_filename = 'vehicle_details_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $params['output']);
    }

    function getTypesofLeave($tol) {
        switch ($tol) {
            case 1:
                $tol_str = "Annual";
                break;
            case 2:
                $tol_str = "Personal(sick)";
                break;
            case 3:
                $tol_str = "Personal(carer's)";
                break;
            case 4:
                $tol_str = "Compassionate";
                break;
            case 5:
                $tol_str = "Cancel Previous Leave";
                break;
            case -1:
                $tol_str = "Other";
                break;
        }

        return $tol_str;
    }

    // Incident photo upload
    function uploadExpenseRecieptImage($file) {

        // upload
        if ($file) {


            $country_folder = "/" . strtolower($_SESSION['country_iso']);
            $image_name = "expense_receipt" . rand() . '_' . date('YmdHis');

            $folder = "images/expenses_receipt{$country_folder}";


            // if folder does not exist, make one
            if (!is_dir($folder)) {
                mkdir($folder);
            }

            // IMAGE 1
            $handle = new upload($file);
            if ($handle->uploaded) {

                $handle->file_new_name_body = $image_name;
                $handle->image_resize = true;
                $handle->image_x = 760;
                $handle->image_ratio_y = true;
                $handle->process($_SERVER['DOCUMENT_ROOT'] . $folder);
                if ($handle->processed) {
                    // get file extension
                    $fn = explode("/", $file['type']);
                    $file_ext = ($fn[1] == 'jpeg') ? 'jpg' : $fn[1];
                    $db_ret['receipt_image'] = "{$folder}/{$image_name}.{$file_ext}";
                    $handle->clean();
                } else {
                    $error = 'error : ' . $handle->error;
                }
            }

            $db_ret['error'] = $error;

            return $db_ret;
        }
    }

    function ExpensesFileUpload($params) {

        // upload
        if ($params['files']) {
            $array_file = $params['files'];
            if ($params['offset_file_name'] != '') {
                $offset_file_name = $params['offset_file_name'];
            } else {
                $offset_file_name = 'exp';
            }

            $file_name = "{$offset_file_name}_{$params['id']}_" . rand() . "_" . date("YmdHis");
            $upload_folder = "uploads/{$params['upload_folder']}";
            $upload_path = DOC_ROOT . $upload_folder;
            $image_size = ($params['image_size'] != '') ? $params['image_size'] : 760;


            // IMAGE 1
            $handle = new upload($params['files']);
            if ($handle->uploaded) {


                $handle->file_new_name_body = $file_name;

                if ($params['file_type'] == 'image') { // image
                    $handle->image_resize = true;
                    $handle->image_convert = 'png';
                    $handle->image_x = $image_size;
                    $handle->image_ratio_y = true;
                    $ext = end((explode(".", $array_file['name']))); # extra () to prevent notice
                    if ($ext == 'heic') {
                        $extension = $ext;
                    } else {
                        $extension = 'png';
                    }
                } 

                $handle->process($upload_path);

                if ($handle->processed) {

                    if ($params['file_type'] == 'image') { // image
                        $db_ret['file_name'] = "{$file_name}.{$extension}";
                        $db_ret['path_to_file'] = "{$upload_folder}/{$file_name}.{$extension}";
                    } else if ($params['file_type'] == 'pdf') { // pdf
                        $db_ret['file_name'] = "{$file_name}.pdf";
                        $db_ret['path_to_file'] = "{$upload_folder}/{$file_name}.pdf";
                    }

                    $handle->clean();
                } else {
                    $error = 'error : ' . $handle->error;
                }
            }

            $db_ret['error'] = $error;

            return $db_ret;
        }
    }

    // expense summary
    function getExpenseSummaryPdf($params) {

        // instantiate class
        $crm = new Sats_Crm_Class;


        $jparams = array(
            'sort_list' => array(
                'order_by' => 'exp.`date`',
                'sort' => 'DESC'
            ),
            'country_id' => $params['country_id'],
            'exp_sum_id' => $params['exp_sum_id']
        );
        $exp_sql = $crm->getExpenses($jparams);


        // get country data
        $cntry_sql = getCountryViaCountryId();
        $cntry = mysql_fetch_array($cntry_sql);

        // start fpdf
        $pdf = new jPDF('L', 'mm', 'A4');
        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($cntry['country_id']);

        $pdf->SetTopMargin(18);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // set default values
        $header_space = 6.5;
        $header_width = 100;
        $header_height = 7;
        $header_border = 0;
        $header_new_line = 1;
        $header_align = 'T';


        // Expense Summary
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell($header_width, $header_height, 'Expense Claim Form', $header_border, $header_new_line, $header_align);
        $pdf->Ln(5);

        // heading
        $heading2_font_size = 10;
        $heading2_col1 = 33;

        $jparams = array(
            'sort_list' => array(
                'order_by' => 'exp_sum.`date`',
                'sort' => 'DESC'
            ),
            'exp_sum_id' => $params['exp_sum_id'],
            'country_id' => $params['country_id']
        );
        $exp_sum_sql = $crm->getExpenseSummary($jparams);
        $exp_sum = mysql_fetch_array($exp_sum_sql);
        $pdf->SetFont('Arial', '', $heading2_font_size);
        $pdf->Cell($heading2_col1, 6, 'Staff Name: ');
        $pdf->Cell(40, 6, $exp_sum['sa_fname'] . ' ' . $exp_sum['sa_lname']);
        $pdf->Ln();
        $pdf->Cell($heading2_col1, 6, 'Date Submitted: ');
        $pdf->Cell(40, 6, date('d/m/Y', strtotime($exp_sum['date'])));
        $pdf->Ln();
        $pdf->Cell($heading2_col1, 6, 'Line Manager: ');
        $pdf->Cell(40, 6, $exp_sum['lm_fname'] . ' ' . $exp_sum['lm_lname']);
        $pdf->Ln();


        $pdf->Ln(5);


        $cell_width = 27.5;
        $cell_height = 5;
        $font_size = 8;

        $col1 = 17; // Date
        $col1_ins = 22; // Card
        $col2 = 38; // Supplier
        $col3 = 75; // Description
        $col4 = 33; // Account
        $col5 = 20; // Amount
        $col6 = 20; // Net Amt
        $col7 = 20; // GST
        $col8 = 20; // Gross Am


        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($col1, $cell_height, 'Date', 1);
        $pdf->Cell($col1_ins, $cell_height, 'Card', 1);
        $pdf->Cell($col2, $cell_height, 'Supplier', 1);
        $pdf->Cell($col3, $cell_height, 'Description', 1);
        $pdf->Cell($col4, $cell_height, 'Account', 1);
        $pdf->Cell($col5, $cell_height, 'Amount', 1);
        // grey
        $pdf->SetFillColor(192, 192, 192);
        $pdf->Cell($col6, $cell_height, 'Net Amt', 1, null, null, true);
        $pdf->Cell($col7, $cell_height, 'GST', 1, null, null, true);
        $pdf->Cell($col8, $cell_height, 'Gross Amt', 1, null, null, true);
        $pdf->Ln();

        $pdf->SetFont('Arial', '', $font_size);

        $amount_tot = 0;
        $net_amount_tot = 0;
        $gst_tot = 0;
        $amount_reimbursed = 0;

        while ($exp = mysql_fetch_array($exp_sql)) {

            $pdf->Cell($col1, $cell_height, date('d/m/Y', strtotime($exp['date'])), 1);
            $pdf->Cell($col1_ins, $cell_height, $crm->getExpenseCards($exp['card']), 1);
            $pdf->Cell($col2, $cell_height, $exp['supplier'], 1);
            $pdf->Cell($col3, $cell_height, $exp['description'], 1);
            $pdf->Cell($col4, $cell_height, $exp['account_name'], 1);
            $pdf->Cell($col5, $cell_height, '$' . $exp['amount'], 1);
            // get dynamic GST based on country
            $gst = $crm->getDynamicGST($exp['amount'], $params['country_id']);
            $net_amount = $exp['amount'] - $gst;
            $pdf->Cell($col6, $cell_height, '$' . number_format($net_amount, 2), 1, null, null, true);
            $pdf->Cell($col7, $cell_height, '$' . number_format($gst, 2), 1, null, null, true);
            $pdf->Cell($col8, $cell_height, '$' . $exp['amount'], 1, null, null, true);
            $pdf->Ln();

            $amount_tot += $exp['amount'];
            $net_amount_tot += $net_amount;
            $gst_tot += $gst;
            // reimbursed if Personal Card or Cash
            if ($exp['card'] == 2 || $exp['card'] == 5) {
                $amount_reimbursed += $exp['amount'];
            }
        }


        // total
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($col1, $cell_height, '', 1);
        $pdf->Cell($col1_ins, $cell_height, '', 1);
        $pdf->Cell($col2, $cell_height, '', 1);
        $pdf->Cell($col3, $cell_height, '', 1);
        $pdf->Cell($col4, $cell_height, '', 1);
        $pdf->Cell($col5, $cell_height, '$' . number_format($amount_tot, 2), 1);
        $pdf->Cell($col6, $cell_height, '$' . number_format($net_amount_tot, 2), 1, null, null, true);
        $pdf->Cell($col7, $cell_height, '$' . number_format($gst_tot, 2), 1, null, null, true);
        $pdf->Cell($col8, $cell_height, '$' . number_format($amount_tot, 2), 1, null, null, true);
        $pdf->Ln();

        $pdf->Ln(5);

        // due to employee
        $pdf->SetX(218);
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($heading2_col1, $cell_height, 'Due To Employee: ');
        $pdf->Cell($col8, $cell_height, '$' . number_format($amount_reimbursed, 2), 1);
        $pdf->Ln();


        $pdf_filename = 'expense_summary_' . date('dmYHis') . '.pdf';
        return $pdf->Output($pdf_filename, $params['output']);
    }

    // get GST of respective countries
    function getDynamicGST($val, $country_id) {

        switch ($country_id) {
            case 1:
                $gst = $val / 11;
                break;
            case 2:
                $gst = ($val * 3) / 23;
                break;
        }

        return $gst;
    }

    function getExpenseCards($card_id) {

        switch ($card_id) {
            case 1:
                $card = 'Company Card';
                break;
            case 2:
                $card = 'Personal Card';
                break;
            case 3:
                $card = 'AU Main Card';
                break;
            case 4:
                $card = 'NZ Main Card';
                break;
            case 5:
                $card = 'Cash';
                break;
        }

        return $card;
    }

    function getLeaveType($leave_type_id) {

        switch ($leave_type_id) {
            case 1:
                $lt = 'Annual';
                break;
            case 2:
                $lt = 'Personal(sick)';
                break;
            case 3:
                $lt = "Personal(carer's)";
                break;
            case 4:
                $lt = 'Compassionate';
                break;
            case 5:
                $lt = 'Cancel Previous Leave';
                break;
            case -1:
                $lt = 'Other';
                break;
        }

        return $lt;
    }

    // Vehicle
    public function getVehicles($params) {

        $sel_str = "SELECT *";

        $filter_arr = array();

        $filter_arr[] = " v.`active` = 1 ";

        if ($params['country_id'] != "") {
            $filter_arr[] = " v.`country_id` = '{$params['country_id']}' ";
        }

        if ($params['tech_vehicle'] != "") {
            $filter_arr[] = " v.`tech_vehicle` = {$params['tech_vehicle']} ";
        }

        if ($params['vehicle_id'] != "") {
            $filter_arr[] = " v.`vehicles_id` = {$params['vehicle_id']} ";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . implode(" AND ", $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str .= " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			{$sel_str}
			FROM `vehicles` AS v
			LEFT JOIN `staff_accounts` AS sa ON v.`StaffID` = sa.`StaffID`
			{$filter_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;

        return mysql_query($sql);
    }

    // escalate_agency_info
    function getEscalateAgencyInfo($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND eai.`active` = 1";
        $filter_arr[] = "AND eai.`deleted` = '0'";



        if ($params['country_id'] != "") {
            $filter_arr[] = "AND eai.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND eai.`agency_id` = {$params['agency_id']}";
        }

        if ($params['date'] != "") {
            $filter_arr[] = "AND CAST( eai.`date_created` AS Date ) = '{$params['date']}'";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }



        $sel_str = "
			*
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `escalate_agency_info` AS eai
			{$join_tbl_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getAge($d1) {
        // Age
        $date1 = date_create(date('Y-m-d', strtotime($d1)));
        $date2 = date_create(date('Y-m-d'));
        $diff = date_diff($date1, $date2);
        $age = $diff->format("%r%a");
        $age_val = (((int) $age) != 0) ? $age : 0;
        return $age_val;
    }

    function getDifferenceInHours($d1, $d2) {

        $date1 = new DateTime(date('Y-m-d H:i:s', strtotime($d1)));
        $date2 = new DateTime(date('Y-m-d H:i:s', strtotime($d2)));

        $diff = $date2->diff($date1);

        $hours = $diff->h;
        $hours = $hours + ($diff->days * 24);

        return $hours;
    }

    function getAllocationOpt($allo) {

        switch ($allo) {
            case 1:
                $allo_opt = '2 Hours';
                break;
            case 2:
                $allo_opt = '4 Hours';
                break;
            case 3:
                $allo_opt = 'Today';
                break;
        }
        return $allo_opt;
    }

    function getAllocatedBy($staff_id) {
        $sql = mysql_query("
			SELECT `FirstName`, LastName
			FROM `staff_accounts`
			WHERE `StaffID` = {$staff_id}
		");
        $row = mysql_fetch_array($sql);
        return $this->formatStaffName($row['FirstName'], $row['LastName']);
    }

    // global_settings
    function getGlobalSettings($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND gs.`active` = 1";
        $filter_arr[] = "AND gs.`deleted` = '0'";



        if ($params['country_id'] != "") {
            $filter_arr[] = "AND gs.`country_id` = {$params['country_id']}";
        }



        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }



        $sel_str = "
			*
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `global_settings` AS gs
			LEFT JOIN `staff_accounts` AS sa ON gs.`allocate_personnel` = sa.`StaffID`
			{$join_tbl_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // notifications
    function getNotifications($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND n.`active` = 1";
        $filter_arr[] = "AND n.`deleted` = '0'";



        if ($params['notify_to'] != "") {
            $filter_arr[] = "AND n.`notify_to` = {$params['notify_to']}";
        }

        if (is_numeric($params['read'])) {
            $filter_arr[] = "AND n.`read` = {$params['read']}";
        }

        if ($params['notf_type']) {
            $filter_arr[] = "AND n.`notf_type` = {$params['notf_type']}";
        }



        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }



        $sel_str = "
			*
		";

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'j.`job_type`':
                    $sel_str = " DISTINCT j.`job_type` ";
                    break;
                case 'j.`service`':
                    $sel_str = " DISTINCT j.`service`, ajt.`id` , ajt.`type` ";
                    break;
            }
        } else if ($params['sum_age'] == "1") {
            $sel_str = " SUM( DATEDIFF( '" . date('Y-m-d') . "', CAST( j.`created` AS DATE ) ) ) AS sum_age ";
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }


        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `notifications` AS n
			{$join_tbl_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function sumExpense($exp_sum_id) {
        $exp_sql = mysql_query("
			SELECT SUM( `amount` ) AS jtot
			FROM `expenses`
			WHERE `expense_summary_id` ={$exp_sum_id}
		");
        $row = mysql_fetch_array($exp_sql);
        return $row['jtot'];
    }

    function getEnteredBy($exp_sum_id) {
        $exp_sql = mysql_query("
			SELECT eb_sa.`FirstName`, eb_sa.`LastName`
			FROM `expenses` AS exp
			LEFT JOIN `staff_accounts` AS eb_sa ON exp.`entered_by` = eb_sa.`StaffID`
			WHERE exp.`expense_summary_id` ={$exp_sum_id}
			GROUP BY exp.`entered_by`
			LIMIT 1
		");
        $row = mysql_fetch_array($exp_sql);
        return "{$row['FirstName']} {$row['LastName']}";
    }

    function getAllocateDeadLine($all_opt, $all_ts) {

        if ($all_opt == 1 || $all_opt == 2) {

            if ($all_opt == 1) { // 2 hours
                $append_hour = 2;
            } else if ($all_opt == 2) { // 4 hours
                $append_hour = 4;
            }

            $deadline = date('Y-m-d H:i:s', strtotime($all_ts . " +{$append_hour} hours"));
        } else if ($all_opt == 3) {
            $deadline = date('Y-m-d 18:00:00');
        }

        return $deadline;
    }

    // insert new notification
    function insertNewNotification($param) {

        // pass notification type, default is 1, general notification
        $notf_type = ( $param['notf_type'] != '' ) ? $param['notf_type'] : 1;

        $sql_str2 = "
			INSERT INTO
			`notifications`(
				`notification_message`,
				`notify_to`,
				`notf_type`,
				`country_id`
			)
			VALUES(
				'{$param['notf_msg']}',
				{$param['staff_id']},
				{$notf_type},
				{$param['country_id']}
			)
		";
        mysql_query($sql_str2);

        mysql_query("
			UPDATE `staff_accounts`
			SET `sound_notification` = 1
			WHERE `StaffID` = {$param['staff_id']}
		");
    }

    // get active services
    function getActiveServices() {

        return mysql_query("
			SELECT *
			FROM `alarm_job_type`
			WHERE `active` =1
		");
    }

    // get Agency Service Price
    function getAgencyServicePrice($agency, $ajt) {

        $sql = mysql_query("
			SELECT *
			FROM `agency_services`
			WHERE `agency_id` = {$agency}
			AND `service_id` = {$ajt}
		");
        $row = mysql_fetch_array($sql);
        return $row['price'];
    }

    // get active services
    function getAlarmPower() {

        return mysql_query("
			SELECT *
			FROM `alarm_pwr`
		");
    }

    // get Agency Service Price
    function getAgencyAlarmsPrice($agency, $alarm_pwr_id) {

        $sql = mysql_query("
			SELECT *
			FROM `agency_alarms`
			WHERE `agency_id` = {$agency}
			AND `alarm_pwr_id` = {$alarm_pwr_id}
		");
        $row = mysql_fetch_array($sql);
        return $row['price'];
    }

    function sendCalendarFile($param) {

        // data
        // santize input
        $summary = filter_var(trim($param['event_name']), FILTER_SANITIZE_STRING);
        $date = date("Ymd\THis");
        $datestart = date("Ymd\THis", strtotime(str_replace("/", "-", filter_var(trim($param['date_start']), FILTER_SANITIZE_STRING))));
        $dateend = date("Ymd\THis", strtotime(str_replace("/", "-", filter_var(trim($param['date_end']), FILTER_SANITIZE_STRING))));
        $filename = 'iCalendar' . date('YmdHis');

        $eol = PHP_EOL;
        $unique_id = md5(time());

        $headers .= "MIME-version: 1.0" . $eol;
        $headers .= "Content-class: urn:content-classes:calendarmessage" . $eol;
        $headers .= "Content-type: text/calendar;name={$filename}.ics;method=REQUEST; charset=UTF-8" . $eol;

// attachment
        $message = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//hacksw/handcal//NONSGML v1.0//EN
BEGIN:VEVENT
UID:{$unique_id}
DTSTAMP:{$date}
SUMMARY:{$summary}
DESCRIPTION:{$param['description']}
DTSTART:{$datestart}
DTEND:{$dateend}
END:VEVENT
END:VCALENDAR
";

        echo $message;
        echo "<br /><br />";
        echo $param['to_email'];

        // mail it
        mail($param['to_email'], $param['subject'], $message, $headers);
    }

    // get agency
    function getAgency($params) {

        // custom join
        $join_tbl_str = '';
        if ($params['join_table']) {

            if ($params['join_table'] == '`postcode_regions`') {
                $join_tbl_str .= " LEFT JOIN `postcode_regions` AS pr ON a.`postcode_region_id` = pr.`postcode_region_id` ";
            }

            if ($params['join_table'] == 'sales_rep') {
                $join_tbl_str .= " LEFT JOIN `staff_accounts` AS sr_sa ON a.`salesrep` = sr_sa.`StaffID` ";
            }

            if ($params['join_table'] == 'country') {
                $join_tbl_str .= " LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id` ";
            }

            if ($params['join_table'] == 'subscription_notes_update_by') {
                $join_tbl_str .= " LEFT JOIN `staff_accounts` AS snub ON a.`subscription_notes_update_by` = snub.`StaffID` ";
            }
        }


        // filters
        $filter_arr = array();

        if ($params['get_agency_notes'] == 1) {
            $filter_arr[] = "AND a.`agency_specific_notes` !='' ";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['status'] != "") {
            $filter_arr[] = "AND a.`status` = '{$params['status']}'";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if ($params['state'] != "") {
            $filter_arr[] = "AND a.`state` = '{$params['state']}'";
        }

        if ($params['sales_rep'] != "") {
            $filter_arr[] = "AND a.`salesrep` = '{$params['sales_rep']}'";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND a.`agency_name` LIKE '%{$params['phrase']}%'";
        }

        if ($params['region_postcode'] != '') {
            $filter_arr[] = " AND a.`postcode` IN ( {$params['region_postcode']} ) ";
        }

        if (is_numeric($params['subscription'])) {
            $filter_arr[] = "AND a.`allow_upfront_billing` = '{$params['subscription']}'";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE a.`agency_id`> 0 " . implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }



        // SELECT
        if ($params['custom_select'] != '') {
            $sel_str = $params['custom_select'];
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else {
            $sel_str = " *, a.`status` AS a_status ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }




        $sql = "
			SELECT {$sel_str}
			FROM `agency` AS a
			{$join_tbl_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['display_echo'] == 1) {
            echo $sql;
        }



        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get properties serviced to sats
    function getPropertyServicedToSats($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND p.deleted = 0";
        $filter_arr[] = "AND ps.`service` = 1";


        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }


        if ($params['region_postcodes'] != "") {
            $filter_arr[] = " AND p.`postcode` IN ( {$params['region_postcodes']} ) ";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'ps.`property_id`':
                    $sel_str = " DISTINCT ps.`property_id` ";
                    break;
            }
        } else {
            $sel_str = " * ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        //echo $sql;


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getPostcodeDuplicates() {

        // run comparison through all postcode
        $country_id = $_SESSION['country_default'];
        $sql_str = "
			SELECT *
			FROM `postcode_regions`
			WHERE `country_id` = {$country_id}
			AND `deleted` = 0
		";
        $sql = mysql_query($sql_str);

        $duplicate = [];
        while ($row = mysql_fetch_array($sql)) {

            // breakdown csv postcode, then compare to db postcode except itself
            $arr1 = explode(",", $row['postcode_region_postcodes']);
            $arr2 = array_filter($arr1);
            foreach ($arr2 as $pc) {

                $sql_str2 = "
				SELECT *
				FROM `postcode_regions`
				WHERE `country_id` = {$country_id}
				AND `postcode_region_postcodes` LIKE '%{$pc}%'
				AND `postcode_region_id` != {$row['postcode_region_id']}
				AND `deleted` = 0
				";

                $sql2 = mysql_query($sql_str2);
                if (mysql_num_rows($sql2) > 0) {
                    $row2 = mysql_fetch_array($sql2);
                    /*
                      $duplicate[] = array(
                      'postcode'=>$pc,
                      'current_postcode_region_id'=>$row['postcode_region_id'],
                      'matched_postcode_region_id'=>$row2['postcode_region_id']
                      );
                     */

                    if (!in_array($pc, $duplicate)) {
                        $duplicate[] = $pc;
                    }
                }
            }
        }

        return $duplicate;
    }

    function getServiceColors($service) {


        if ($service == 2 || $service == 12) { // smoke alarm
            $service_class_color = 'servBgcolorNTextColor_sa';
            $service_color = '#b4151b';
            $serv_btn_clr = '';
            $tab_class_name = 'j_sa';
        } else if ($service == 5) { // safety switch
            $service_class_color = 'servBgcolorNTextColor_ss';
            $service_color = '#f15a22';
            $serv_btn_clr = 'submitbtnOrange';
            $tab_class_name = 'j_ss';
        } else if ($service == 6) { // corded windows
            $service_class_color = 'servBgcolorNTextColor_cw';
            $service_color = '#00AE4D';
            $serv_btn_clr = 'submitbtnGreen';
            $tab_class_name = 'j_cw';
        } else if ($service == 7) { // water meter
            $service_class_color = 'servBgcolorNTextColor_wm';
            $service_color = '#00aeef';
            $serv_btn_clr = 'submitbtnBue';
            $tab_class_name = 'j_pb';
        }else if ($service == 15) { // Water Flow
            $service_class_color = 'servBgcolorNTextColor_wf';
            $service_color = '#24B8EF';
            $serv_btn_clr = 'submitbtnBue';
            $tab_class_name = 'j_wf';
        }

        return array(
            'serv_class_color' => $service_class_color,
            'bg_color' => $service_color,
            'btn_class_color' => $serv_btn_clr,
            'tab_class' => $tab_class_name
        );
    }

    function exportCsv($param) {

        // file name
        $filename = "{$param['csv_name']}_" . rand() . date("YmdHis") . ".csv";

        // send headers for download
        header("Content-Type: text/csv");
        header("Content-Disposition: Attachment; filename={$filename}");
        header("Pragma: no-cache");

        // headers
        echo $param['csv_header'];

        // body
        echo $param['csv_body'];
    }

    // get properties serviced to sats
    function getSmokeAlarms($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND sa.`deleted` = 0";
        $filter_arr[] = "AND sa.`active` = 1";


        if ($params['country_id'] != "") {
            $filter_arr[] = "AND sa.`country_id` = {$params['country_id']}";
        }

        if ($params['smoke_alarm_id'] != "") {
            $filter_arr[] = "AND sa.`smoke_alarm_id` = {$params['smoke_alarm_id']}";
        }

        if ($params['make'] != "") {
            $filter_arr[] = "AND sa.`make` LIKE '%{$params['make']}%'";
        }

        if ($params['model'] != "") {
            $filter_arr[] = "AND sa.`model` LIKE '%{$params['model']}%'";
        }

        if ($params['search'] != "") {
            $filter_arr[] = "AND CONCAT(sa.`make`, sa.`model`) LIKE '%{$params['search']}%'";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'ps.`property_id`':
                    $sel_str = " DISTINCT ps.`property_id` ";
                    break;
            }
        } else {
            $sel_str = " * ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `smoke_alarms` AS sa
			WHERE sa.`smoke_alarm_id` > 0
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";



        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getSaPowerType($power_type) {

        switch ($power_type) {
            case 1:
                $power_type_name = '3v';
                break;
            case 2:
                $power_type_name = '3vli';
                break;
            case 3:
                $power_type_name = '9v';
                break;
            case 4:
                $power_type_name = '9vli';
                break;
            case 5:
                $power_type_name = '240v';
                break;
            case 6:
                $power_type_name = '240vli';
                break;
        }

        return $power_type_name;
    }

    function getSaDetectionType($detection_type) {

        switch ($detection_type) {
            case 1:
                $detection_type_name = 'Photo-Electric';
                break;
            case 2:
                $detection_type_name = 'Ionisation';
                break;
        }

        return $detection_type_name;
    }

    // basic PHP upload
    function nativeFileUpload($params) {

        // data
        $rand = rand();
        $todayTs = date('YmdHis');

        $target_dir = "uploads/temp/";
        $orig_file_name = basename($params['files']["name"]);
        $orig_file_name_final = "et_attachments_{$rand}_{$todayTs}_{$orig_file_name}";
        $target_file = $target_dir . $orig_file_name_final;
        $docFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // filename renamed, used for final upload if problem occured
        $file_name_renamed = "et_attachments_{$rand}_{$todayTs}_fn." . $docFileType;
        $target_file_final = $target_dir . $file_name_renamed;

        $uploadError = 0;
        $upload_success = 0;
        $error_msg = [];


        // Check if file already exists
        if (file_exists($target_file)) {
            $error_msg[] = "Sorry, file already exists.";
            $uploadError = 1;
        }

        // Check file size
        // default upload size limit: 500kb
        $upload_size_limit = ($params['image_only'] != '') ? $params['image_only'] : 500000;
        if ($params['files']["size"] > $upload_size_limit) {
            $error_msg[] = "Sorry, your file is too large.";
            $uploadError = 1;
        }

        if ($params['image_only'] == 1) {

            // Check if image file is a actual image or fake image
            if ($params['files']["tmp_name"] != '') {
                $check = getimagesize($params['files']["tmp_name"]);
                if ($check == false) {
                    $error_msg[] = "File is not an image.";
                    $uploadError = 1;
                }
            }

            // Allow certain file formats
            if ($docFileType != "jpg" && $docFileType != "png" && $docFileType != "jpeg" && $docFileType != "gif") {
                $error_msg[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadError = 1;
            }
        }



        if ($uploadError == 1) { // Some Error, cancel upload
            $upload_msg = "Error found, Stop Upload";
            $upload_success = 0;
        } else { // if everything is ok, try to upload file
            // Main upload function , moves tmp file to server's upload directory
            if (move_uploaded_file($params['files']["tmp_name"], $target_file_final)) {
                $upload_msg = "Upload Success";
                $upload_success = 1;
            } else {
                $upload_msg = "Upload failed";
                $upload_success = 0;
            }


            $upload_msg = "Upload Success";
            $upload_success = 1;
        }


        return array(
            'upload_success' => $upload_success,
            'upload_msg' => $upload_msg,
            'error_msg' => $error_msg,
            'server_upload_path' => $target_file_final,
            'upload_data' => "
				Upload Directory: {$target_dir}<br />
				File Extension: {$docFileType}<br />
				Original File Name: {$orig_file_name}<br />
				Renamed File: {$file_name_renamed}<br />
				Original File Path: {$target_file}<br />
				Final File Path(Renamed File): {$target_file_final}<br />
			"
        );
    }

    // get properties serviced to sats
    function getContractorAppointment($params) {



        // filters
        $filter_arr = array();

        $filter_arr[] = "AND ca.`deleted` = 0";
        $filter_arr[] = "AND ca.`active` = 1";


        if ($params['country_id'] != "") {
            $filter_arr[] = "AND ca.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND ca.`agency_id` = {$params['agency_id']}";
        }

        if ($params['contractor_appointment_id'] != "") {
            $filter_arr[] = "AND ca.`contractor_appointment_id` = {$params['contractor_appointment_id']}";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'ps.`property_id`':
                    $sel_str = " DISTINCT ps.`property_id` ";
                    break;
            }
        } else {
            $sel_str = " * ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_str}
			FROM `contractor_appointment` AS ca
			WHERE ca.`contractor_appointment_id` > 0
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";



        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getInvoiceTotal($job_id) {

        # Job Details
        $job_details = getJobDetails2($job_id);
        # Alarm Details
        $alarm_details = getPropertyAlarms($job_id, 1, 0, 2);
        $num_alarms = sizeof($alarm_details);

        $grand_total = $job_details['job_price'];

        // installed alarm
        for ($x = 0; $x < $num_alarms; $x++) {
            if ($alarm_details[$x]['new'] == 1) {
                $grand_total += $alarm_details[$x]['alarm_price'];
            }
        }

        /*
          // removed alarm
          for($x = 0; $x < $numDelAlarm; $x++)
          {
          $grand_total += $delAlarm[$x]['alarm_price'];
          }
         */

        // surcharge
        $sc_sql = mysql_query("
			SELECT *, m.`name` AS m_name
			FROM `agency_maintenance` AS am
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			WHERE am.`agency_id` = {$job_details['agency_id']}
		");
        $sc = mysql_fetch_array($sc_sql);
        if ($grand_total != 0 && $sc['surcharge'] == 1) {
            $grand_total += $sc['price'];
        }

        return $grand_total;
    }

    function getInvoiceNumber($job_id) {
        // append checkdigit to job id for new invoice number
        $check_digit = getCheckDigit(trim($job_id));
        return $bpay_ref_code = "{$job_id}{$check_digit}";
    }

    function NLMjobStatusCheck($property_id) {

        $sql = mysql_query("
			SELECT *
			FROM `jobs`
			WHERE `property_id` = {$property_id}
			AND `del_job` = 0
			AND (
				`status` = 'Booked' OR
				`status` = 'Pre Completion' OR
				`status` = 'Merged Certificates'
			)
		");

        if (mysql_num_rows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // get Sales
    function jGetSales($country_id, $date) {

        // date removed
        // AND j.`date` = '{$date}'
        // job price
        $sql = mysql_query("
			SELECT SUM(j.`job_price`) AS jprice
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$country_id}
			AND j.`status` = 'Merged Certificates'
		");

        $row = mysql_fetch_array($sql);
        $tot_job_price = $row['jprice'];

        // alarm price
        $sql = mysql_query("
				SELECT SUM(alrm.`alarm_price`) AS aprice
				FROM `alarm` AS alrm
				LEFT JOIN `jobs` AS j ON  alrm.`job_id` = j.`id`
				LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
				LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
				WHERE p.`deleted` =0
				AND a.`status` = 'active'
				AND j.`del_job` = 0
				AND a.`country_id` = {$country_id}
				AND j.`status` = 'Merged Certificates'
				AND alrm.`new`	= 1
				AND alrm.`ts_discarded` = 0
			");

        $row = mysql_fetch_array($sql);
        $tot_alarm_price = $row['aprice'];


        return $tot_fin = $tot_job_price + $tot_alarm_price;
    }

    // get Number of Techs Today
    function jGetNumOfTechToday($country_id, $date) {

        $sql = mysql_query("
			SELECT *
			FROM  `tech_run`
			WHERE `date` = '{$date}'
			AND `country_id` = {$country_id}
			GROUP BY `assigned_tech`
		");
        return mysql_num_rows($sql);
    }

    // get today's number of jobs completed or merged
    function jGetNumJobsCompleted($country_id, $date) {

        // date removed
        // AND j.`date` = '{$date}'

        $sql = mysql_query("
			SELECT COUNT(j.`id`) AS jcount
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`status` = 'Merged Certificates'
			AND a.`country_id` = {$country_id}
			AND p.`deleted` = 0
			AND a.`status` = 'active'
			AND j.`del_job` =0
		");

        $row = mysql_fetch_array($sql);
        return $row['jcount'];
    }

    // get property data
    function getPropertyOnly($params) {


        // filters
        $filter_arr = array();

        $filter_arr[] = "AND a.`status` = 'active'";


        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if (is_numeric($params['p_deleted'])) {
            $filter_arr[] = "AND p.`deleted` = {$params['p_deleted']}";
        }

        if ($params['region_postcodes'] != "") {
            $filter_arr[] = " AND p.`postcode` IN ( {$params['region_postcodes']} ) ";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['nlm_display'] == 1) {
            $filter_arr[] = "AND p.`nlm_display` = 1";
        }

        if ($params['nlm_owing'] == 1) {
            $filter_arr[] = "AND p.`nlm_owing` = 1";
        }

        if ($params['write_off'] == 1) {
            $filter_arr[] = "AND p.`write_off` = 1";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // date from - to
        if ($params['deleted_date_from'] != '' && $params['deleted_date_to'] != '') {
            $from2 = date("Y-m-d", strtotime(str_replace("/", "-", $params['deleted_date_from'])));
            $to2 = date("Y-m-d", strtotime(str_replace("/", "-", $params['deleted_date_to'])));
            $filter_arr[] = "AND CAST(p.`deleted_date` AS DATE) BETWEEN '{$from2}' AND '{$to2}'";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = $params['custom_select'];
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'p.`state`':
                    $sel_str = " DISTINCT p.`state` ";
                    break;
                case 'a.`agency_id`':
                    $sel_str = " DISTINCT a.`agency_id`, a.`agency_name` ";
                    break;
            }
        } else {
            $sel_str = "
				*,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }

        // custom sort
        if ($params['custom_sort'] != '') {
            $sort_str = "ORDER BY {$params['custom_sort']}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_joins'] == 'nlm') {
            $custom_joins_str = "
				LEFT JOIN `staff_accounts` AS sa ON p.`nlm_by_sats_staff` = sa.`StaffID`
				LEFT JOIN `agency` AS nlm_a ON p.`nlm_by_agency` = nlm_a.`agency_id`
			";
        }

        $sql = "
			SELECT {$sel_str}
			FROM `property` AS p
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$custom_joins_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}
		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get Credit Request data
    function getCreditRequestData($params) {


        // filters
        $filter_arr = array();

        $filter_arr[] = "AND cr.`active` = 1";

        if ($params['country_id'] != "") {
            $filter_arr[] = "AND cr.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['deleted'] != "") {
            $filter_arr[] = "AND cr.`deleted` = {$params['deleted']}";
        }

        if ($params['cr_id'] != "") {
            $filter_arr[] = "AND cr.`credit_request_id` = {$params['cr_id']}";
        }

        if ($params['requested_by'] != "") {
            $filter_arr[] = "AND cr.`requested_by` = {$params['requested_by']}";
        }

        if (is_numeric($params['result'])) {
            $filter_arr[] = "AND cr.`result` = {$params['result']}";
        } else if ($params['result'] == 'ALL') {
            // dont use result filter, should show all results
        } else {
            $filter_arr[] = "AND cr.`result` IS NULL";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = "AND CAST(cr.`date_of_request` AS Date) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
            }
        }


        /*
          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
          (a.`agency_name` LIKE '%{$params['phrase']}%')
          )";
          }
         */

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['distinct'] != "") {
            switch ($params['distinct']) {
                case 'cr.`requested_by`':
                    $sel_str = " DISTINCT cr.`requested_by`, rb.`FirstName`, rb.`LastName` ";
                    break;
            }
        } else {
            $sel_str = "
			*,
			cr.`reason` AS cr_reason,
			cr.`comments` AS cr_comments,
			rb.`FirstName` AS rb_fname,
			rb.`LastName` AS rb_lname,
			rb.`StaffID` AS rb_staff_id,
			who.`FirstName` AS who_fname,
			who.`LastName` AS who_lname,
			who.`StaffID` AS who_staff_id
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        /*
          if($params['custom_joins']=='nlm'){
          $custom_joins_str = "
          LEFT JOIN `staff_accounts` AS sa ON p.`nlm_by_sats_staff` = sa.`StaffID`
          LEFT JOIN `agency` AS nlm_a ON p.`nlm_by_agency` = nlm_a.`agency_id`
          ";
          }
         */

        $sql = "
			SELECT {$sel_str}
			FROM `credit_requests` AS cr
			LEFT JOIN `jobs` AS j ON cr.`job_id` = j.`id`
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			LEFT JOIN `staff_accounts` AS rb ON cr.`requested_by` = rb.`StaffID`
			LEFT JOIN `staff_accounts` AS who ON cr.`who` = who.`StaffID`
			{$custom_joins_str}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }


        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function kpi_getTotalPropertyCount($country_id) {

        $fg = 14; // Defence Housing
        //$fg_filter = "AND a.`franchise_groups_id` != {$fg}";

        return mysql_query("
			SELECT DISTINCT p.`property_id`
			FROM `property_services` AS ps
			LEFT JOIN `property` AS p ON ps.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE ps.`service` =1
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND a.`country_id` = {$country_id}
			{$fg_filter}
		");
    }

    function getDynamicHandyManID() {

        $url = $_SERVER['SERVER_NAME'];

        if ($_SESSION['country_default'] == 1) { // AU
            if (strpos($url, "crmdev") === false) { // live
                $handyman_id = 31;
            } else { // dev
                $handyman_id = 27;
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if (strpos($url, "crmdev") === false) { // live
                $handyman_id = 32;
            } else { // dev
                $handyman_id = 6;
            }
        }

        return $handyman_id;
    }

    // get SMS replies
    function getSMSrepliesMergedData($params) {


        // filters
        $filter_arr = array();

        $filter_arr[] = "AND sas.`active` = 1";

        if ($params['tech'] != "") {
            $filter_arr[] = "AND j.`assigned_tech` = '{$params['tech']}'";
        }

        if ($params['cb_status'] != "") {
            $filter_arr[] = "AND sas.`cb_status` = '{$params['cb_status']}'";
        }

        if ($params['sent_by'] != "") {
            $filter_arr[] = "AND sas.`sent_by` = {$params['sent_by']}";
        }

        if ($params['sr_id'] != "") {
            $filter_arr[] = "AND sar.`sms_api_replies_id` = {$params['sr_id']}";
        }

        if ($params['unread'] != "") {
            $filter_arr[] = "AND sar.`unread` = 1";
        }

        if ($params['sms_type'] != "") {
            $filter_arr[] = "AND sas.`sms_type` = {$params['sms_type']}";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
            }
        }

        /*
          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
          (a.`agency_name` LIKE '%{$params['phrase']}%')
          )";
          }
         */

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct'] != "") {

            switch ($params['distinct']) {
                case 'sas.`sent_by`':
                    $sel_str = "DISTINCT sas.`sent_by`, sa.`StaffID`, sa.`FirstName`, sa.`LastName` ";
                    break;
            }
        } else {
            $sel_str = "
				*,
				sas.`created_date` AS sas_created_date,
				sar.`created_date` AS sar_created_date
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        if ($params['sms_page'] == 'incoming') {
            $join_type = 'INNER JOIN';
        } else if ($params['sms_page'] == 'outgoing') {
            $join_type = 'LEFT JOIN';
        }


        $sql = "SELECT {$sel_str}
		FROM `sms_api_sent` AS sas
		{$join_type} `sms_api_replies` AS sar ON sas.`message_id` = sar.`message_id`
		LEFT JOIN `jobs` AS j ON sas.`job_id` = j.`id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `staff_accounts` AS sa ON sas.`sent_by` = sa.`StaffID`
		LEFT JOIN `sms_api_type` AS sat ON sas.`sms_type` = sat.`sms_api_type_id`
		LEFT JOIN `staff_accounts` AS ass_tech ON j.`assigned_tech` = ass_tech.`StaffID`
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getSMStemplate($params) {

        switch ($params['sms_type']) {
            case 16:
                $sms_temp = "This is to confirm your appointment made today for the {$params['date']} @ {$params['time']} to service the {$params['serv_name']} at {$params['paddress']}. Please ensure someone is home to allow access. SATS {$params['tenant_number']}";
                break;
            case 4: // No-Show, Agency Notified
                $sms_temp = "We attended your property today to check your smoke alarms as per our appointment and nobody was home. Please call {$params['tenant_number']} to reschedule or we will notify {$params['landlord_txt']} of the missed appointment";
                break;
            case 10: // Entry Notice, SMS EN
                $sms_temp = "Smoke Alarm Testing Services (SATS) have issued you an Entry Notice to test the {$params['serv_name']} at {$params['paddress']} on {$params['jdatetemp']} and will collect the keys from your Real Estate. Click here to view <link>";
                break;
            case 9: // Entry Notice, SMS EN
                $sms_temp = "Smoke Alarm Testing Services (SATS) have issued you an Entry Notice to test the {$params['serv_name']} at {$params['paddress']} on {$params['jdatetemp']} and will collect the keys from your agency. Email may appear in Spam/Junk folders. View this Entry Notice by clicking this link <link>";
                break;
        }

        return $sms_temp;
    }


    function get_parsed_sms_template($params) {

        $job_id = $params['job_id'];
        $sms_api_type_id = $params['sms_api_type_id'];

        if( $job_id > 0 && $sms_api_type_id > 0 ){

            // get SMS template body
            echo $sms_temp_sql_str = "
            SELECT
                `sms_api_type_id`,
                `type_name`,
                `body`
            FROM `sms_api_type`
            WHERE `sms_api_type_id` = {$sms_api_type_id}
            ";
            $sms_temp_sql = mysql_query($sms_temp_sql_str);
            $sms_temp_row = mysql_fetch_array($sms_temp_sql);

            echo "<br /><br />";

            // get jobs data
            echo $job_sql_str = "
            SELECT
                j.`id` AS jid,
                j.`service` AS j_service,
                j.`date` AS j_date,
                j.`time_of_day`,

                p.`property_id` AS prop_id,
                p.`address_1` AS p_address_1,
                p.`address_2` AS p_address_2,
                p.`address_3` AS p_address_3,
                p.`state` AS p_state,
                p.`postcode` AS p_postcode,

                a.`agency_id`,
                a.`agency_name` AS agency_name,
                a.`franchise_groups_id`,

                c.`tenant_number`,

                ajt.`id` AS ajt_id,
                ajt.`type` AS ajt_type,
                ajt.`full_name` AS service_full_name
            FROM `jobs` AS j
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
            LEFT JOIN `countries` AS c ON a.`country_id` = c.`country_id`
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            WHERE j.`id` = {$job_id}
            ";
            $jobs_sql = mysql_query($job_sql_str);
            $job_row = mysql_fetch_array($jobs_sql);

            echo "<br /><br />";

            // data
            $p_address = "{$job_row['p_address_1']} {$job_row['p_address_2']} {$job_row['p_address_3']} {$job_row['p_state']} {$job_row['p_postcode']}";
            $job_date = ( $this->isDateNotEmpty($job_row['j_date']) ) ? date('d/m/Y', strtotime($job_row['j_date'])) : null;
            $time_of_day = $job_row['time_of_day'];
            $service = $job_row['service_full_name'];
            $tenant_number = $job_row['tenant_number'];
            $agency_id = $job_row['agency_id'];
            $country_id = $job_row['country_id'];

            // orig EN link
            $orig_url = "https://{$_SERVER['SERVER_NAME']}/view_entry_notice_new.php?letterhead=1&i={$job_id}&m=" . md5($agency_id . $job_id);
            /*
            $short_url = $this->getFDynamicLink($country_id, $orig_url);
            if (!$short_url) {
                $short_url = $orig_url;
            }
            $en_link = $short_url;
            */

            $encrypt_decrypt = new Openssl_Encrypt_Decrypt();
            $encode_encrypt_job_id = rawurlencode($encrypt_decrypt->encrypt($job_id));
            //$en_ci_link = $this->crm_ci_redirect(rawurlencode("/pdf/entry_notice/?job_id={$encode_encrypt_job_id}"));
            $ci_domain = $this->getDynamicCiDomain();
            $en_ci_link = "{$ci_domain}/pdf/entry_notice/?job_id={$encode_encrypt_job_id}";
            $en_link = $en_ci_link;


            // private FG
            if ($this->getAgencyPrivateFranchiseGroups($job_row['franchise_groups_id']) == true) {
                $agency_name = 'your agency';
            } else {
                $agency_name = $job_row['agency_name'];
            }

            // parse tags using find and replace
            $find = array('{agency_name}', '{p_address}', '{job_date}', '{time_of_day}', '{serv_name}', '{tenant_number}', '{en_link}');
            $replace = array($agency_name, $p_address, $job_date, $time_of_day, $service, $tenant_number, $en_link);

            return str_replace($find, $replace, $sms_temp_row['body']);

        }

    }


    function getAgencyPrivateFranchiseGroups($franchise_group) {

        $private_fg = false;
        if ($_SESSION['country_default'] == 1) { // AU
            if ($franchise_group == 10) { // AU private ID
                $private_fg = true;
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            if ($franchise_group == 37) { // NZ private ID
                $private_fg = true;
            }
        }

        return $private_fg;
    }

    // Special Agencies that can view hidden columns on agency portal view agencies page
    function getVIPonAgencyViewProp() {

        $sql = mysql_query("
			SELECT *
			FROM `crm_settings`
			WHERE `country_id` = {$_SESSION['country_default']}
		");
        if (mysql_num_rows($sql) > 0) {
            $row = mysql_fetch_array($sql);
            return $vip = explode(",", $row['agency_portal_vip_agencies']);
            //return array(3899,1448,3962,1598,4644,3446);
        }
    }

    // check first visit
    function checkfirstVisit($job_id, $service) {

        // FIRST VISIT: SA = no smoke alarms, CW = no windows, bundle = no smoke alarms
        if ($service == 6) { // Corded Window
            // Corded Window
            $sql = mysql_query("
				SELECT *
				FROM `corded_window`
				WHERE `job_id` ={$job_id}
			");
        } else {

            // Smoke Alarms
            $sql = mysql_query("
				SELECT *
				FROM `alarm`
				WHERE `job_id` ={$job_id}
			");
        }

        if (mysql_num_rows($sql) == 0) {
            return true;
        } else {
            return false;
        }
    }

    // check first visit
    function check_prop_first_visit($property_id) {

        if( $property_id > 0 ){

            // exclude other supplier(1) and upfront bill(2)
            $job_sql = mysql_query("
                SELECT COUNT(id) AS j_count
                FROM `jobs`
                WHERE `property_id` = {$property_id}
                AND `status` = 'Completed'
                AND `assigned_tech` != 1
                AND `assigned_tech` != 2
            ");

            $job_row = mysql_fetch_array($job_sql);

            if( $job_row['j_count'] == 0 ) { // first visit
                return true;
            } else {
                return false;
            }

        }


    }

    /*
      function previewEmailTemplate($job_id,$body){

      if( $job_id!='' ){

      // instantiate class
      $crm = new Sats_Crm_Class;

      $loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
      $loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";

      // get tenant number from countries
      $ctn_sql = mysql_query("
      SELECT `tenant_number`
      FROM `countries`
      WHERE `country_id` = {$_SESSION['country_default']}
      ");
      $ctn = mysql_fetch_array($ctn_sql);

      $jparams = array(
      'job_id' => $job_id
      );
      $job_sql = $this->getJobsData($jparams);
      $row = mysql_fetch_array($job_sql);

      $paddress = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']}";
      $jdate = ( $this->isDateNotEmpty($row['jdate']) )?date('d/m/Y',strtotime($row['jdate'])):'';
      $landlord = "{$row['landlord_firstname']} {$row['landlord_lastname']}";
      $tenant1 = "{$row['tenant_firstname1']} {$row['tenant_lastname1']}";
      $tenant2 = "{$row['tenant_firstname2']} {$row['tenant_lastname2']}";
      $tenant3 = "{$row['tenant_firstname3']} {$row['tenant_lastname3']}";
      $tenant4 = "{$row['tenant_firstname4']} {$row['tenant_lastname4']}";

      $find = array(
      "{agency_name}",
      "{property_address}",
      "{service_type}",
      "{job_date}",
      "{job_number}",
      "{landlord}",
      "{tenant_phone_number}",
      "{tenant_1}",
      "{tenant_2}",
      "{tenant_3}",
      "{tenant_4}",
      "{agency_phone_number}",
      "{user}",
      "{tech_comments}"
      );
      $search = array(
      $row['agency_name'],
      $paddress,
      $row['type'],
      $jdate,
      $row['jid'],
      $landlord,
      $ctn['tenant_number'],
      $tenant1,
      $tenant2,
      $tenant3,
      $tenant4,
      $row['a_phone'],
      $loggedin_staff_name,
      $row['tech_comments']
      );

      //$subject_fin = str_replace($find, $search, $subject);
      $message_fin = str_replace($find, $search, $body);

      return $message_fin;

      }

      }
     */

    function parseEmailTemplateTags($params, $body) {

        $loggedin_staff_id = $_SESSION['USER_DETAILS']['StaffID'];
        $loggedin_staff_name = "{$_SESSION['USER_DETAILS']['FirstName']} {$_SESSION['USER_DETAILS']['LastName']}";

        // get tenant number from countries
        $ctn_sql = mysql_query("
			SELECT `tenant_number`
			FROM `countries`
			WHERE `country_id` = {$_SESSION['country_default']}
		");
        $ctn = mysql_fetch_array($ctn_sql);

        $blank = '<span class="colorItRed">BLANK</span>';

        if ($params['agency_id'] != '') { // agency
            // get agency data
            $jparams = array(
                'agency_id' => $params['agency_id'],
                'display_echo' => 0
            );
            $a_sql = $this->getAgency($jparams);
            $row = mysql_fetch_array($a_sql);

            $agency_address = "{$row['address_1']} {$row['address_2']} {$row['address_3']} {$row['state']} {$row['postcode']}";

            // put agency emails into an array
            $agency_emails_exp = explode("\n", trim($row['agency_emails']));
            $agency_emails_imp = implode(", ", $agency_emails_exp);

            // put account emails into an array
            $account_emails_exp = explode("\n", trim($row['account_emails']));
            $account_emails_imp = implode(", ", $account_emails_exp);

            $find = array(
                "{agency_name}",
                "{tenant_phone_number}",
                "{agency_phone_number}",
                "{agency_email}",
                "{agency_accounts_email}",
                "{agency_address}",
                "{user}"
            );
            $search = array(
                ( trim($row['agency_name']) != '') ? $row['agency_name'] : $blank,
                ( trim($ctn['tenant_number']) != '') ? $ctn['tenant_number'] : $blank,
                ( trim($row['phone']) != '') ? $row['phone'] : $blank,
                ( trim($agency_emails_imp) != '') ? $agency_emails_imp : $blank,
                ( trim($account_emails_imp) != '') ? $account_emails_imp : $blank,
                ( trim($agency_address) != '') ? $agency_address : $blank,
                ( trim($loggedin_staff_name) != '') ? $loggedin_staff_name : $blank
            );

            //$subject_fin = str_replace($find, $search, $subject);
            $message_fin = str_replace($find, $search, $body);
        } else if ($params['job_id'] != '') { // jobs
            // get jobs data
            $jparams = array(
                'job_id' => $params['job_id'],
                'remove_deleted_filter' => 1
            );
            $job_sql = $this->getJobsData($jparams);
            $row = mysql_fetch_array($job_sql);

            $property_id = $row['property_id'];
            $paddress = "{$row['p_address_1']} {$row['p_address_2']} {$row['p_address_3']} {$row['p_state']} {$row['p_postcode']}";
            $jdate = ( $this->isDateNotEmpty($row['jdate']) ) ? date('d/m/Y', strtotime($row['jdate'])) : '';

            $landlord = "{$row['landlord_firstname']} {$row['landlord_lastname']}";
            $agency_address = "{$row['a_address_1']} {$row['a_address_2']} {$row['a_address_3']} {$row['a_state']} {$row['a_postcode']}";



            $find = array(
                "{agency_name}",
                "{property_address}",
                "{service_type}",
                "{job_date}",
                "{job_number}",
                "{landlord}",
                "{tenant_phone_number}",
                "{agency_phone_number}",
                "{user}",
                "{tech_comments}",
                "{agency_email}",
                "{agency_accounts_email}",
                "{agency_address}"
            );


            $search = array(
                ( trim($row['agency_name']) != '') ? $row['agency_name'] : $blank,
                ( trim($paddress) != '') ? $paddress : $blank,
                ( trim($row['type']) != '') ? $row['type'] : $blank,
                ( trim($jdate) != '') ? $jdate : $blank,
                ( trim($row['jid']) != '') ? $row['jid'] : $blank,
                ( trim($landlord) != '') ? $landlord : $blank,
                ( trim($ctn['tenant_number']) != '') ? $ctn['tenant_number'] : $blank,
                ( trim($row['a_phone']) != '') ? $row['a_phone'] : $blank,
                ( trim($loggedin_staff_name) != '') ? $loggedin_staff_name : $blank,
                ( trim($row['tech_comments']) != '') ? $row['tech_comments'] : $blank,
                ( trim($agency_emails_imp) != '') ? $agency_emails_imp : $blank,
                ( trim($account_emails_imp) != '') ? $account_emails_imp : $blank,
                ( trim($agency_address) != '') ? $agency_address : $blank
            );


            // new tenants switch
            //$new_tenants = 0;
            $new_tenants = NEW_TENANTS;
            $tenants_names_arr = [];

            if ($new_tenants == 1) { // NEW TENANTS
                $pt_params = array(
                    'property_id' => $property_id,
                    'active' => 1
                );
                $pt_sql = Sats_Crm_Class::getNewTenantsData($pt_params);

                $pt_i = 1;
                while ($pt_row = mysql_fetch_array($pt_sql)) {

                    $find[] = '{tenant_' . $pt_i . '}';
                    if ($pt_row['tenant_firstname'] != '') {
                        $search[] = "{$pt_row['tenant_firstname']} {$pt_row['tenant_lastname']}";
                    } else {
                        $search[] = $blank;
                    }
                    $pt_i++;
                }
            } else { // OLD TENANTS
                $num_tenants = getCurrentMaxTenants();
                for ($pt_i = 1; $pt_i <= $num_tenants; $pt_i++) {

                    $find[] = '{tenant_' . $pt_i . '}';
                    if ($row['tenant_firstname' . $pt_i] != '') {
                        $search[] = "{$row['tenant_firstname' . $pt_i]} {$row['tenant_lastname' . $pt_i]}";
                    } else {
                        $search[] = $blank;
                    }
                }
            }


            //$subject_fin = str_replace($find, $search, $subject);
            $message_fin = str_replace($find, $search, $body);
        }

        return $message_fin;
    }

    // get Agency Booking Notes
    function getBookingNotes($params) {


        // filters
        $filter_arr = array();

        $filter_arr[] = "AND bn.`active` = 1";

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = '{$params['agency_id']}'";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `booking_notes` AS bn
			LEFT JOIN `agency` AS a ON bn.`agency_id` = a.`agency_id`
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function addBookingNotesLog($params) {

        $sql = "
			INSERT INTO
			`booking_notes_log` (
				`booking_notes_id`,
				`title`,
				`msg`,
				`staff_id`,
				`date_created`,
				`active`,
				`country_id`
			)
			VALUES (
				{$params['bn_id']},
				'{$params['title']}',
				'{$params['msg']}',
				{$params['staff_id']},
				'" . date('Y-m-d H:i:s') . "',
				1,
				{$params['country_id']}
			)
		";
        mysql_query($sql);
    }

    // get Agency Booking Notes
    function getBookingNotesLog($params) {


        // filters
        $filter_arr = array();

        $filter_arr[] = "AND bnl.`active` = 1";

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = '{$params['agency_id']}'";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `booking_notes_log` AS bnl
			LEFT JOIN `booking_notes` AS bn ON bnl.`booking_notes_id` = bn.`booking_notes_id`
			LEFT JOIN `agency` AS a ON bn.`agency_id` = a.`agency_id`
			LEFT JOIN `staff_accounts` AS st_ac ON bnl.`staff_id` = st_ac.`StaffID`
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get Email Templates
    function getEmailTemplates($params) {


        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND et.`active` = {$params['active']}";
        }

        if ($params['email_templates_id'] != "") {
            $filter_arr[] = "AND et.`email_templates_id` = {$params['email_templates_id']}";
        }

        if ($params['temp_type'] != "") {
            $filter_arr[] = "AND et.`temp_type` = {$params['temp_type']}";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*, ett.`name` AS ett_name, et.`active` AS et_active
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `email_templates` AS et
			LEFT JOIN `email_templates_type` AS ett ON et.`temp_type` = ett.`email_templates_type_id`
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get Email Templates Tag
    function getEmailTemplateTag($params) {


        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND ett.`active` = {$params['active']}";
        }

        if ($params['email_templates_id'] != "") {
            $filter_arr[] = "AND ett.`email_templates_tag_id` = {$params['email_templates_id']}";
        }



        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `email_templates_tag` AS ett
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // get Email Templates Type
    function getEmailTemplateType($params) {


        // filters
        $filter_arr = array();


        if ($params['active'] != "") {
            $filter_arr[] = "AND et_type.`active` = {$params['active']}";
        }

        if ($params['email_templates_type_id'] != "") {
            $filter_arr[] = "AND et_type.`email_templates_type_id` = {$params['email_templates_type_id']}";
        }



        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }
         */

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				bn.`notes` LIKE '%{$params['phrase']}%' OR
				a.`agency_name` LIKE '%{$params['phrase']}%'
			 )";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE " . substr(implode(" ", $filter_arr), 3);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `email_templates_type` AS et_type
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // create Yearly Maintenance Renewals
    function processYearlyMaintenanceRenewals($country_id, $staff_id) {

        $ym_tot = 0;

        $last_year = date("Y", strtotime("-1 year"));
        $next_month = date("m", strtotime("+1 month"));
        $max_day = date("t", strtotime("{$last_year}-{$next_month}"));

        $this_year = date("Y");
        $this_month = date("m");
        $date_str = "";

        // if december
        if (intval($this_month) == 12) {
            $this_month_max_day = date("t", strtotime("{$this_year}-01"));
            $date_str = " AND j.`date` BETWEEN '{$this_year}-01-01' AND '{$this_year}-01-{$this_month_max_day}'";
        } else {
            $date_str = " AND j.`date` BETWEEN '{$last_year}-{$next_month}-01' AND '{$last_year}-{$next_month}-{$max_day}'";
        }

        // get jobs
        $fp_sql = mysql_query("
			SELECT j.`property_id`, j.`job_price`, j.`service`, ps.`price` AS ps_price, p.`agency_id`
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			INNER JOIN `property_services` AS ps ON ( j.`property_id` = ps.`property_id` AND j.`service` = ps.`alarm_job_type_id` )
			WHERE j.`status` = 'Completed'
			AND j.`job_type` = 'Yearly Maintenance'
			AND ps.`service` =1
			AND p.`deleted` =0
			AND a.`status` = 'active'
			AND j.`del_job` = 0
			AND a.`country_id` = {$country_id}
			{$date_str}
		");

        $next_month_full = date("Y-m-01 H:i:s", strtotime("+1 month"));


        while ($fp = mysql_fetch_array($fp_sql)) {

            $agency_id = $fp['agency_id'];

            // get Franchise Group
            $agen_sql = mysql_query("
				SELECT `franchise_groups_id`
				FROM `agency`
				WHERE `agency_id` = {$agency_id}
			");
            $agen = mysql_fetch_array($agen_sql);

            // if agency is DHA agencies with franchise group = 14(Defence Housing) OR if agency has maintenance program
            $dha_need_processing = 0;
            if (isDHAagenciesV2($agen['franchise_groups_id']) == true || agencyHasMaintenanceProgram($agency_id) == true) {
                $dha_need_processing = 1;
            }


            // insert jobs
            mysql_query("
				INSERT INTO
				`jobs` (
					`status`,
					`retest_interval`,
					`auto_renew`,
					`job_type`,
					`property_id`,
					`sort_order`,
					`job_price`,
					`service`,
					`created`,
					`start_date`,
					`dha_need_processing`
				)
				VALUE(
					'Pending',
					365,
					1,
					'Yearly Maintenance',
					{$fp['property_id']},
					1,
					{$fp['ps_price']},
					{$fp['service']},
					'{$next_month_full}',
					'{$next_month_full}',
					'{$dha_need_processing}'
				)
			");

            $job_id = mysql_insert_id();
            $alarm_job_type_id = $fp['service'];

            // get alarm job type
            $ajt_sql = mysql_query("
				SELECT *
				FROM `alarm_job_type`
				WHERE `id` = {$alarm_job_type_id}
			");
            $ajt = mysql_fetch_array($ajt_sql);


            // if bundle
            if ($ajt['bundle'] == 1) {

                $b_ids = explode(",", trim($ajt['bundle_ids']));
                // insert bundles
                foreach ($b_ids as $val) {
                    mysql_query("
						INSERT INTO
						`bundle_services`(
							`job_id`,
							`alarm_job_type_id`
						)
						VALUES(
							{$job_id},
							{$val}
						)
					");


                    $bundle_id = mysql_insert_id();
                    $bs_id = $bundle_id;
                    $bs2_sql = getbundleServices($job_id, $bs_id);
                    $bs2 = mysql_fetch_array($bs2_sql);
                    $ajt_id = $bs2['alarm_job_type_id'];

                    //echo "Job ID: {$job_id} - ajt ID: {$alarm_job_type_id} Bundle ID: {$bundle_id} <br />";
                    // sync alarm
                    runSync($job_id, $ajt_id, $bundle_id);
                }
            } else {

                runSync($job_id, $alarm_job_type_id);
            }


            // insert job logs
            mysql_query("
				INSERT INTO
				`job_log` (
					`contact_type`,
					`eventdate`,
					`comments`,
					`job_id`,
					`staff_id`,
					`eventtime`
				)
				VALUES (
					'Service Due',
					'" . date('Y-m-d') . "',
					'Service Due Job Created',
					'{$job_id}',
					'{$staff_id}',
					'" . date("H:i") . "'
				)
			");
        }



        $ym_tot = mysql_num_rows($fp_sql);

        // insert renewal record
        mysql_query("
			INSERT INTO
			`renewals`(
				`StaffID`,
				`country_id`,
				`date`,
				`num_jobs_created`
			)
			VALUES(
				'{$staff_id}',
				{$country_id},
				'" . date("Y-m-d H:i:s") . "',
				{$ym_tot}
			)
		");
    }

    function getCrmSettings($country_id) {
        return mysql_query("
			SELECT *
			FROM `crm_settings`
			WHERE `country_id` = {$country_id}
		");
    }

    function updateSmsCredit($country_id) {

        if( $country_id == 2 ){ // NZ only

            $ws_sms = new WS_SMS($country_id);
            $sms_credit = $ws_sms->getBalance();
            $today = date('Y-m-d H:i:s');

            // update SMS credit
            $sql = "
                UPDATE `crm_settings`
                SET
                    `sms_credit` = {$sms_credit},
                    `sms_credit_update_ts` = '{$today}'
                WHERE `country_id` = {$country_id}
            ";
            mysql_query($sql);

        }        
    }

    function uploadPropertyFiles_old($files_arr, $property_id) {

        #ensure property id set
        if (intval($property_id) == 0)
            return false;

        #security measure, don't allow ..
        if (stristr($files_arr['fileupload']['name'], ".."))
            return false;


        # if subdir doesn't exist then create it first
        if (!is_dir(UPLOAD_PATH_BASE . $property_id)) {
            @mkdir(UPLOAD_PATH_BASE . $property_id, 0777);
        }

        $filename = preg_replace('/#+/', 'num', $files_arr['fileupload']['name']);
        $filename2 = preg_replace('/\s+/', '_', $filename);
        $filename3 = rand() . date('YmdHis') . $filename2;

        if (move_uploaded_file($files_arr['fileupload']['tmp_name'], UPLOAD_PATH_BASE . $property_id . "/" . $filename3)) {

            // appended - insert log
            mysql_query("
				INSERT INTO
				`property_event_log`
				(
					`property_id`,
					`staff_id`,
					`event_type`,
					`event_details`,
					`log_date`,
					`hide_delete`
				)
				VALUES(
					{$property_id},
					{$_SESSION['USER_DETAILS']['StaffID']},
					'File Upload',
					'" . mysql_real_escape_string($filename3) . " Uploaded',
					'" . date('Y-m-d H:i:s') . "',
					1
				)"
            );

            return true;
        } else {

            return false;
        }
    }

    function get240vRfAgencyAlarm($agency_id) {
        $sql_str = "
			SELECT `price`
			FROM `agency_alarms`
			WHERE `agency_id` = {$agency_id}
			AND `alarm_pwr_id` = 10
			LIMIT 1
		";
        $sql = mysql_query($sql_str);
        $row = mysql_fetch_array($sql);
        return $row['price'];
    }

    function getIcAlarmAgencyService($agency_id) {
        $sql_str = "
			SELECT `price`
			FROM `agency_services`
			WHERE `agency_id` = {$agency_id}
			AND `service_id` = 12
		";
        $sql = mysql_query($sql_str);
        $row = mysql_fetch_array($sql);
        return $row['price'];
    }

    // get Email Templates Sent
    function getEmailTemplateSent($params) {


        // filters
        $filter_arr = array();

        if ($params['active'] != "") {
            $filter_arr[] = "AND ets.`active` = {$params['active']}";
        }

        if ($params['job_log_id'] != "") {
            $filter_arr[] = "AND ets.`job_log_id` = {$params['job_log_id']}";
        }

        if ($params['log_id'] != "") {
            $filter_arr[] = "AND ets.`log_id` = {$params['log_id']}";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          bn.`notes` LIKE '%{$params['phrase']}%' OR
          a.`agency_name` LIKE '%{$params['phrase']}%'
          )";
          }
         */


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE ets.`email_templates_sent_id` > 0 " . implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
			SELECT {$sel_str}
			FROM `email_templates_sent` AS ets
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // basic PHP upload
    function standardBasicUpload($params) {

        // data
        $rand = rand();
        $todayTs = date('YmdHis');

        $target_dir = ( $params['upload_folder'] != '' ) ? $params['upload_folder'] : "uploads/temp/";
        $orig_file_name = basename($params['files']["name"]);
        $file_name_prefix = ( $params['filename_prefix'] != '' ) ? $params['filename_prefix'] : 'upload_file';
        $orig_file_name_final = "{$file_name_prefix}_{$rand}_{$todayTs}_{$orig_file_name}";
        $target_file = $target_dir . $orig_file_name_final;
        $docFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // filename renamed, used for final upload if problem occured
        $file_name_renamed = "{$file_name_prefix}_{$rand}_{$todayTs}_fn." . $docFileType;
        $target_file_final = $target_dir . $file_name_renamed;

        $uploadError = 0;
        $upload_success = 0;
        $error_msg = [];


        // Check if file already exists
        if (file_exists($target_file)) {
            $error_msg[] = "Sorry, file already exists.";
            $uploadError = 1;
        }

        // Check file size
        // default upload size limit: 500kb
        $upload_size_limit = ( $params['upload_size_limit'] != '' ) ? $params['upload_size_limit'] : 500000;
        if ($params['files']["size"] > $upload_size_limit) {
            $error_msg[] = "Sorry, your file is too large.";
            $uploadError = 1;
        }

        if ($params['image_only'] == 1) {

            // Check if image file is a actual image or fake image
            if ($params['files']["tmp_name"] != '') {
                $check = getimagesize($params['files']["tmp_name"]);
                if ($check == false) {
                    $error_msg[] = "File is not an image.";
                    $uploadError = 1;
                }
            }

            // Allow certain file formats
            if ($docFileType != "jpg" && $docFileType != "png" && $docFileType != "jpeg" && $docFileType != "gif") {
                $error_msg[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                $uploadError = 1;
            }
        }


        if ($params['go_upload'] == 1) {

            if ($uploadError == 1) { // Some Error, cancel upload
                $upload_msg = "Error found, Stop Upload";
                $upload_success = 0;
            } else { // if everything is ok, try to upload file
                // Main upload function , moves tmp file to server's upload directory
                if (move_uploaded_file($params['files']["tmp_name"], $target_file_final)) {
                    $upload_msg = "Upload Success";
                    $upload_success = 1;
                } else {
                    $upload_msg = "Upload failed";
                    $upload_success = 0;
                }


                $upload_msg = "Upload Success";
                $upload_success = 1;
            }
        }



        return array(
            'upload_success' => $upload_success,
            'upload_msg' => $upload_msg,
            'error_msg' => $error_msg,
            'server_upload_path' => $target_file_final,
            'upload_data' => "
				Upload Directory: {$target_dir}<br />
				File Extension: {$docFileType}<br />
				Original File Name: {$orig_file_name}<br />
				Renamed File: {$file_name_renamed}<br />
				Original File Path: {$target_file}<br />
				Final File Path(Renamed File): {$target_file_final}<br />
			"
        );
    }

    function insertJobLog($params) {

        $log_msg = mysql_real_escape_string($params['log_msg']);
        $log_type = mysql_real_escape_string($params['log_type']);

        if ($params['log_date'] != '') {
            // log date must be Y-m-d format
            $today_date = date('Y-m-d', strtotime($params['log_date']));
            $today_time = date('H:i', strtotime($params['log_date']));
        } else {
            $today_date = date('Y-m-d');
            $today_time = date('H:i');
        }

        $staff_id = ( $params['staff_id'] != '' ) ? $params['staff_id'] : $_SESSION['USER_DETAILS']['StaffID'];

        $sql = "
			INSERT INTO
			`job_log` (
				`contact_type`,
				`eventdate`,
				`comments`,
				`job_id`,
				`staff_id`,
				`eventtime`
			)
			VALUES (
				'{$log_type}',
				'{$today_date}',
				'{$log_msg}',
				{$params['job_id']},
				{$staff_id},
				'{$today_time}'
			)
		";

        if ($params['display_query'] == 1) {
            echo $sql;
        }

        // job log
        mysql_query($sql);
    }

    // get Invoice Payments
    function getInvoicePaymentsData($params) {


        // filters
        $filter_arr = array();

        if ($params['ip_id'] != "") {
            $filter_arr[] = "AND ip.`invoice_payment_id` = {$params['ip_id']}";
        }

        if ($params['active'] != "") {
            $filter_arr[] = "AND ip.`active` = {$params['active']}";
        }

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND ip.`job_id` = {$params['job_id']}";
        }

        if ($params['payment_date'] != "") {
            $filter_arr[] = "AND ip.`payment_date` = {$params['payment_date']}";
        }

        if ($params['type_of_payment'] != "") {
            $filter_arr[] = "AND ip.`type_of_payment` = {$params['type_of_payment']}";
        }

        if ($params['created_by'] != "") {
            $filter_arr[] = "AND ip.`created_by` = {$params['created_by']}";
        }


        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          bn.`notes` LIKE '%{$params['phrase']}%' OR
          a.`agency_name` LIKE '%{$params['phrase']}%'
          )";
          }
         */


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE ip.`invoice_payment_id` > 0 " . implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*, ip.`active` AS ip_active
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }

        $join_table_imp = '';
        $join_table_str = [];
        if (count($params['join_table']) > 0) {

            foreach ($params['join_table'] as $join_table) {
                switch ($join_table) {
                    case 'jobs':
                        $join_table_str[] = 'LEFT JOIN `jobs` AS j ON ip.`job_id` = j.`id`';
                        break;
                    case 'created_by_who':
                        $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa ON ip.`created_by` = sa.`StaffID`';
                        break;
                    case 'payment_types':
                        $join_table_str[] = 'LEFT JOIN `payment_types` AS pt ON ip.`type_of_payment` = pt.`payment_type_id`';
                        break;
                }
            }
        }

        $join_table_imp = implode(" ", $join_table_str);


        $sql = "
			SELECT {$sel_str}
			FROM `invoice_payments` AS ip
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getPaymentTypes() {
        $sql = "
			SELECT *
			FROM `payment_types` AS pt
			WHERE `active` = 1
		";
        return mysql_query($sql);
    }

    // get Invoice Credit
    function getInvoiceCreditsData($params) {


        // filters
        $filter_arr = array();

        if ($params['ip_id'] != "") {
            $filter_arr[] = "AND ic.`invoice_credit_id` = {$params['ip_id']}";
        }

        if ($params['active'] != "") {
            $filter_arr[] = "AND ic.`active` = {$params['active']}";
        }

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND ic.`job_id` = {$params['job_id']}";
        }

        if ($params['credit_date'] != "") {
            $filter_arr[] = "AND ic.`credit_date` = {$params['credit_date']}";
        }

        if ($params['approved_by'] != "") {
            $filter_arr[] = "AND ic.`approved_by` = {$params['approved_by']}";
        }


        if ($params['created_by'] != "") {
            $filter_arr[] = "AND ic.`created_by` = {$params['created_by']}";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }


          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          bn.`notes` LIKE '%{$params['phrase']}%' OR
          a.`agency_name` LIKE '%{$params['phrase']}%'
          )";
          }
         */


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE ic.`invoice_credit_id` > 0 " . implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*,
				ic.`active` AS ic_active,
				sa_who.`FirstName` AS sa_who_fname,
				sa_who.`LastName` AS sa_who_lname,
				sa_ab.`FirstName` AS sa_ab_fname,
				sa_ab.`LastName` AS sa_ab_lname
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }

        $join_table_imp = '';
        $join_table_str = [];
        if (count($params['join_table']) > 0) {

            foreach ($params['join_table'] as $join_table) {
                switch ($join_table) {
                    case 'created_by_who':
                        $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa_who ON ic.`created_by` = sa_who.`StaffID`';
                        break;
                    case 'approved_by':
                        $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa_ab ON ic.`approved_by` = sa_ab.`StaffID`';
                        break;
                }
            }
        }

        $join_table_imp = implode(" ", $join_table_str);


        $sql = "
			SELECT {$sel_str}
			FROM `invoice_credits` AS ic
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getInvoiceBalance($job_id) {

        // instantiate class
        $crm = new Sats_Crm_Class;

        $invoice_amount = getJobAmountGrandTotal($job_id, $_SESSION['country_default']);

        // total payments
        $params = array(
            'job_id' => $job_id,
            'echo_query' => 0,
            'custom_select' => 'SUM(amount_paid) AS jsum'
        );
        $payments_sql = $crm->getInvoicePaymentsData($params);
        $payment = mysql_fetch_array($payments_sql);
        $tot_amount_paid = $payment['jsum'];

        // total credit
        $params = array(
            'job_id' => $job_id,
            'echo_query' => 0,
            'custom_select' => 'SUM(credit_paid) AS jsum'
        );
        $credits_sql = $crm->getInvoiceCreditsData($params);
        $credit = mysql_fetch_array($credits_sql);
        $tot_credit_paid = $credit['jsum'];

        return $balance = $invoice_amount - $tot_amount_paid - $tot_credit_paid;
    }

    // get Invoice Credit
    function getUnpaidJobs($params) {


        // filters
        $filter_arr = array();


        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND p.`agency_id` = {$params['agency_id']}";
        }

        if ($params['job_date'] != "") {
            $filter_arr[] = "AND j.`date` = '{$params['job_date']}'";
        }

        if (is_numeric($params['credit_reason'])) {
            $filter_arr[] = "AND inv_cred.`credit_reason` = {$params['credit_reason']}";
        }

        if ($params['filterDate'] != '') {
            if ($params['filterDate']['from'] != "" && $params['filterDate']['to'] != "") {
                $filter_arr[] = " AND ( j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
            }
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "
			AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )
			 ";
        }

        // combine all filters
        $filter_str = " WHERE j.`id` > 0 " . implode(" ", $filter_arr);


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(j.`id`) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*,
				j.`id` AS jid,
				j.`status` AS jstatus,
				j.`service` AS jservice,
				j.`created` AS jcreated,
				j.`date` AS jdate,
				j.`comments` AS j_comments,

				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3,
				p.`state` AS p_state,
				p.`postcode` AS p_postcode,
				p.`comments` AS p_comments,
				p.`compass_index_num`,

				a.`agency_id` AS a_id,
				a.`phone` AS a_phone,
				a.`address_1` AS a_address_1,
				a.`address_2` AS a_address_2,
				a.`address_3` AS a_address_3,
				a.`state` AS a_state,
				a.`postcode` AS a_postcode,
				a.`account_emails`,
				a.`agency_emails`,
				a.`franchise_groups_id`
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }

        // custom sort
        if ($params['custom_sort'] != '') {
            $sort_str = "ORDER BY {$params['custom_sort']}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }

        // join table
        $join_table_arr = [];
        if ($params['join_table'] != '') {

            if ($params['join_table'] == 'inv_cred') {
                $join_table_arr[] = 'INNER JOIN `invoice_credits` AS inv_cred ON j.`id` = inv_cred.`job_id`';
            }
        }

        // combine all filters
        $join_table_str = '';
        if (count($join_table_arr) > 0) {
            $join_table_str = implode(" ", $join_table_arr);
        }


        $sql = "
			SELECT {$sel_str}
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			{$join_table_str}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getJobTotalAmount($job_id) {

        $grand_total = 0;

        $sql = mysql_query("
			SELECT *
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`id` = {$job_id}
		");
        $row = mysql_fetch_array($sql);

        // get amount
        $grand_total = $row['job_price'];

        // get new alarm
        $a_sql = mysql_query("
			SELECT *
			FROM `alarm`
			WHERE `job_id`  = {$job_id}
			AND `new` = 1
			AND `ts_discarded` = 0
		");
        while ($a = mysql_fetch_array($a_sql)) {
            $grand_total += $a['alarm_price'];
        }

        // get safety switch
        $ss_sql = mysql_query("
        SELECT ss_stock.`sell_price`
        FROM `safety_switch` AS ss
        LEFT JOIN `safety_switch_stock` AS ss_stock ON ss.`ss_stock_id` = ss_stock.`ss_stock_id`
        WHERE ss.`job_id` = {$job_id}
        AND ss.`new` = 1
        AND ss.`discarded` = 0
		");
        while ($ss_row = mysql_fetch_object($ss_sql)) {
            $grand_total += $ss_row->sell_price;
        }


        // surcharge
        $sc_sql = mysql_query("
			SELECT *, m.`name` AS m_name
			FROM `agency_maintenance` AS am
			LEFT JOIN `maintenance` AS m ON am.`maintenance_id` = m.`maintenance_id`
			WHERE am.`agency_id` = {$row['agency_id']}
			AND am.`maintenance_id` > 0
		");
        $sc = mysql_fetch_array($sc_sql);
        if ($grand_total != 0 && $sc['surcharge'] == 1) {

            $grand_total += $sc['price'];
        }

        //New job_variation tweak start
        $jv_sql = mysql_query("
            SELECT 
            jv.id, 
            jv.amount,
            jv.type,
            jv.reason
            FROM `job_variation` as jv
            WHERE jv.job_id = {$job_id}
            AND jv.active = 1
        "); 

        while ($jv_row = mysql_fetch_array($jv_sql)) {
            if( $jv_row['type'] == 1 ){ ##discount
                $grand_total -= $jv_row['amount'];
            }else{ ##surcharge
                $grand_total += $jv_row['amount'];
            }
        }        
        //New job_variation tweak end

        return $grand_total;
    }

    function getJobInvoicePayments($job_id) {
        $sql = mysql_query("
			SELECT SUM(`amount_paid`) AS amount_paid_tot
			FROM `invoice_payments`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		");
        $row = mysql_fetch_array($sql);
        return $row['amount_paid_tot'];
    }

    function getJobInvoiceRefunds($job_id) {
        $sql = mysql_query("
			SELECT SUM(`amount_paid`) AS refund_paid_tot
			FROM `invoice_refunds`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		");
        $row = mysql_fetch_array($sql);
        return $row['refund_paid_tot'];
    }

    function getJobInvoiceCredits($job_id) {
        $sql = mysql_query("
			SELECT SUM(`credit_paid`) AS credit_paid_tot
			FROM `invoice_credits`
			WHERE `job_id` = {$job_id}
			AND `active` = 1
		");
        $row = mysql_fetch_array($sql);
        return $row['credit_paid_tot'];
    }

    // update job invoice details
    function updateInvoiceDetails($job_id) {

        if ($job_id != '') {

            // get job details
            $job_sql = mysql_query("
				SELECT `invoice_amount`, `invoice_payments`, `invoice_refunds`, `invoice_credits`, `invoice_balance`
				FROM `jobs`
				WHERE `id` = {$job_id}
			");
            $job = mysql_fetch_array($job_sql);
            $invoice_amount_orig = $job['invoice_amount'];
            $invoice_payments_orig = $job['invoice_payments'];
            $invoice_refunds_orig = $job['invoice_refunds'];
            $invoice_credits_orig = $job['invoice_credits'];
            $invoice_balance_orig = $job['invoice_balance'];




            // get the calculated values
            // invoice amount
            $inv_a = $this->getJobTotalAmount($job_id);
            $invoice_amount = ( $inv_a > 0 ) ? $inv_a : 0;

            // invoice payments
            $inv_p = $this->getJobInvoicePayments($job_id);
            $invoice_payments = ( $inv_p > 0 ) ? $inv_p : 0;

            // invoice refunds
            $inv_r = $this->getJobInvoiceRefunds($job_id);
            $invoice_refunds = ( $inv_r > 0 ) ? $inv_r : 0;

            // invoice credits
            $inv_c = $this->getJobInvoiceCredits($job_id);
            $invoice_credits = ( $inv_c != null ) ? $inv_c : 0;

            // invoice balance
            $invoice_balance = ($invoice_amount + $invoice_refunds) - ( $invoice_payments + $invoice_credits);

            $test_val = "
			invoice_amount_orig: {$invoice_amount_orig} - invoice_amount: {$invoice_amount}<br />
			invoice_payments_orig: {$invoice_payments_orig} - invoice_payments: {$invoice_payments}<br />
			invoice_refunds_orig: {$invoice_refunds_orig} - invoice_refunds: {$invoice_refunds}<br />
			invoice_credits_orig: {$invoice_credits_orig} - invoice_credits: {$invoice_credits}<br />
			invoice_balance_orig: {$invoice_balance_orig} - invoice_balance: {$invoice_balance}<br />
			";
            //echo $test_val;
            // only update if invoice details changed
            if (
                    $invoice_amount_orig == '' ||
                    $invoice_amount_orig != $invoice_amount ||
                    $invoice_payments_orig != $invoice_payments ||
                    $invoice_refunds_orig != $invoice_refunds ||
                    $invoice_credits_orig != $invoice_credits ||
                    $invoice_balance_orig != $invoice_balance
            ) {

                mysql_query("
					UPDATE `jobs`
					SET
						`invoice_amount` = '{$invoice_amount}',
						`invoice_payments` = '{$invoice_payments}',
						`invoice_refunds` = '{$invoice_refunds}',
						`invoice_credits` = '{$invoice_credits}',
						`invoice_balance` = '{$invoice_balance}'
					WHERE `id` = {$job_id}
				");
                //echo "Invoice Details Updated!";
            }
        }
    }

    function getTrustAccountSoftware($tas_id) {



        if ($tas_id != '') {

            switch ($tas_id) {

                case 1:
                    $tas_val = 'REST';
                    break;
                case 2:
                    $tas_val = 'Property Tree';
                    break;
                case 3:
                    $tas_val = 'Console';
                    break;
                case 4:
                    $tas_val = 'Palace';
                    break;
                case 5:
                    $tas_val = 'Sherlock';
                    break;
                case 6:
                    $tas_val = 'Palace Liquid';
                    break;
                case 7:
                    $tas_val = 'PropertyMe';
                    break;
                case -1:
                    $tas_val = 'Other';
                    break;
            }

            return $tas_val;
        } else {

            $tas_array = array(
                array(
                    'index' => 1,
                    'value' => 'REST'
                ),
                array(
                    'index' => 2,
                    'value' => 'Property Tree'
                ),
                array(
                    'index' => 3,
                    'value' => 'Console'
                ),
                array(
                    'index' => 4,
                    'value' => 'Palace'
                ),
                array(
                    'index' => 5,
                    'value' => 'Sherlock'
                ),
                array(
                    'index' => 6,
                    'value' => 'Palace Liquid'
                ),
                array(
                    'index' => 7,
                    'value' => 'PropertyMe'
                )
            );

            return $tas_array;
        }
    }

    function getOverdueTotal($agency_id, $having_filter, $number_format = true) {

        $today = date('Y-m-d');

        $sql_str = "
			SELECT j.`id`, j.`invoice_balance`, j.`date`, DATE_ADD(j.`date`, INTERVAL 30 DAY) AS due_date, DATEDIFF( '{$today}', j.`date`) AS DateDiff
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`id` > 0
			AND j.`job_price` > 0
			AND j.`invoice_balance` > 0
			AND j.`status` = 'Completed'
			AND ( a.`status` = 'Active' OR a.`status` = 'Deactivated' )
			AND a.`agency_id` = {$agency_id}
			HAVING {$having_filter}
		";

        $sql = mysql_query($sql_str);
        $tot = 0;
        while ($row = mysql_fetch_array($sql)) {
            $tot += $row['invoice_balance'];
        }

        if ($number_format == true) {
            return number_format($tot, 2);
        } else {
            return $tot;
        }
    }

    function getAllStaffAccountsRegardlessOfCountry($exclude_sa_id_arr) {

        if (count($exclude_sa_id_arr) > 0) {
            $exclude_sa_id_str = implode(",", $exclude_sa_id_arr);
            $filter = "AND `StaffID` NOT IN({$exclude_sa_id_str})";
        }

        return mysql_query("
			SELECT *
			FROM `staff_accounts`
			WHERE `Deleted` = 0
			AND `active` = 1
			{$filter}
			ORDER BY `FirstName` ASC, `LastName` ASC
		");
    }

    function getMenus() {

        return mysql_query("
			SELECT *
			FROM `menu`
			WHERE `active` = 1
			ORDER BY `sort_index` ASC
		");
    }

    function getAllStaffClasses() {

        return mysql_query("
			SELECT *
			FROM `staff_classes`
			WHERE `active` = 1
			ORDER BY `sort_index` ASC
		");
    }

    function getPagesPerMenu($menu_id, $active) {

        $filter_str = " WHERE crm_page_id > 0 ";

        if (is_numeric($active)) {
            $filter_str .= " AND `active` = {$active} ";
        }

        $sql = "
			SELECT *
			FROM `crm_pages`
			{$filter_str}
			AND `menu` = {$menu_id}
			ORDER BY `page_name` ASC
		";
        return mysql_query($sql);
    }

    function menusStaffCanView($sc_id, $sa_id) {
        $resultSet = mysql_query("
            SELECT
                m.menu_id
            FROM `menu` as m
            WHERE
                (
                    m.menu_id IN (
                        SELECT
                            menu
                        FROM menu_permission_class as pc
                        WHERE
                            pc.active AND
                            pc.staff_class = {$sc_id}
                    ) OR
                    m.menu_id IN (
                        SELECT
                            menu
                        FROM menu_permission_user as pu
                        WHERE
                            pu.active AND
                            pu.user = {$sa_id}
                            AND pu.denied = 0
                    )
                ) AND m.menu_id NOT IN (
                    SELECT
                        menu
                    FROM menu_permission_user as pu
                    WHERE
                        pu.active AND
                        pu.user = {$sa_id}
                        AND pu.denied = 1
                )
        ");


        return fetchAllArray($resultSet);
    }

    function canViewMenuByStaffClass($menu_id, $sc_id) {
        $sql_str = "
			SELECT *
			FROM `menu_permission_class`
			WHERE `active`
			AND `menu` = {$menu_id}
			AND `staff_class` = {$sc_id}
		";
        $sql = mysql_query($sql_str);
        if (mysql_num_rows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function canViewMenuByStaffAccounts($menu_id, $sa_id, $denied) {
        $sql_str = "
			SELECT *
			FROM `menu_permission_user`
			WHERE `active`
			AND `menu` = {$menu_id}
			AND `user` = {$sa_id}
			AND `denied` = {$denied}
		";
        $sql = mysql_query($sql_str);
        if (mysql_num_rows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function pagesStaffCanView($sc_id, $sa_id) {
        $resultSet = mysql_query("
            SELECT
                cp.crm_page_id
            FROM `crm_pages` as cp
            WHERE
                (
                    cp.crm_page_id IN (
                        SELECT
                            page
                        FROM crm_page_permission_class as pc
                        WHERE
                            pc.active AND
                            pc.staff_class = {$sc_id}
                    ) OR
                    cp.crm_page_id IN (
                        SELECT
                            page
                        FROM crm_page_permission_user as pu
                        WHERE
                            pu.active AND
                            pu.user = {$sa_id}
                            AND pu.denied = 0
                    )
                ) AND crm_page_id NOT IN (
                    SELECT
                        page
                    FROM crm_page_permission_user as pu
                    WHERE
                        pu.active AND
                        pu.user = {$sa_id}
                        AND pu.denied = 1
                )
        ");

        return fetchAllArray($resultSet);
    }

    function canViewPageByStaffClass($page_id, $sc_id) {
        $sql_str = "
			SELECT *
			FROM `crm_page_permission_class`
			WHERE `active`
			AND `page` = {$page_id}
			AND `staff_class` = {$sc_id}
		";
        $sql = mysql_query($sql_str);
        if (mysql_num_rows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function canViewPageByStaffAccounts($page_id, $sa_id, $denied) {
        $sql_str = "
			SELECT *
			FROM `crm_page_permission_user`
			WHERE `active`
			AND `page` = {$page_id}
			AND `user` = {$sa_id}
			AND `denied` = {$denied}
		";
        $sql = mysql_query($sql_str);
        if (mysql_num_rows($sql) > 0) {
            return true;
        } else {
            return false;
        }
    }

    function canViewPage($page_id, $sa_id, $sc_id) {

        if (
                (
                $this->canViewPageByStaffClass($page_id, $sc_id) == true ||
                $this->canViewPageByStaffAccounts($page_id, $sa_id, 0) == true
                ) &&
                $this->canViewPageByStaffAccounts($page_id, $sa_id, 1) == false
        ) {
            return true;
        } else {
            return false;
        }
    }

    function canViewMenu($menu_id, $sa_id, $sc_id) {

        if (
                (
                $this->canViewMenuByStaffClass($menu_id, $sc_id) == true ||
                $this->canViewMenuByStaffAccounts($menu_id, $sa_id, 0) == true
                ) &&
                $this->canViewMenuByStaffAccounts($menu_id, $sa_id, 1) == false
        ) {
            return true;
        } else {
            return false;
        }
    }

    // STATEMENTS PDF
    function getInvoiceStatements($params) {
        $sql = "
			SELECT
				j.`id` AS jid,
				j.`date` AS jdate,
				j.`invoice_amount`,
				j.`invoice_payments`,
				j.`invoice_credits`,
				j.`invoice_balance`,
				p.`address_1` AS p_address_1,
				p.`address_2` AS p_address_2,
				p.`address_3` AS p_address_3
			FROM `jobs` AS j
			LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
			LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
			WHERE j.`id` > 0
			AND j.`status` = 'Completed'
			AND p.`agency_id` = {$params['agency_id']}
			AND j.`job_price` > 0
			AND j.`invoice_balance` > 0
		";
        return mysql_query($sql);
    }

    function getStatementsPdf($params) {

        $crm = new Sats_Crm_Class;

        // start fpdf
        $pdf = new jPDF('P', 'mm', 'A4');

        $pdf->agency_id = $params['agency_id'];

        $pdf->setPath($_SERVER['DOCUMENT_ROOT']);
        $pdf->setCountryData($params['country_id']);

        $pdf->SetTopMargin(10); // top margin
        $pdf->SetAutoPageBreak(true, 30); // bottom margin
        $pdf->AliasNbPages();
        $pdf->AddPage();



        $cell_height = 5;
        $font_size = 8;

        $col1 = 17;
        $col3 = 96;
        $col5 = 15;


        $pdf->SetFont('Arial', '', $font_size);

        // static financial year
        $financial_year = '2019-07-01';
        // unpaid marker
        $unpaid_marker_str = '
		 OR(
			 j.`unpaid` = 1 AND
			 j.`invoice_balance` > 0
		 )
		 ';

        $custom_filter = "
			AND j.`job_price` > 0
			AND j.`invoice_balance` > 0
			AND (
				j.`status` = 'Completed' OR
				j.`status` = 'Merged Certificates'
			)
			AND (
				a.`status` = 'Active' OR
				a.`status` = 'Deactivated'
			)

			AND j.`date` >= '{$financial_year}'
			{$unpaid_marker_str}
		";

        $jparams = array(
            'custom_filter' => $custom_filter,
            'agency_id' => $params['agency_id'],
            'echo_query' => 0
        );
        $statement_sql = $crm->getUnpaidJobs($jparams);



        $balance_tot = 0;
        $not_overdue = 0;
        $overdue_31_to_60 = 0;
        $overdue_61_to_90 = 0;
        $overdue_91_more = 0;

        while ($row = mysql_fetch_array($statement_sql)) {

            $jdate = ( $crm->isDateNotEmpty($row['jdate']) ) ? date('d/m/Y', strtotime($row['jdate'])) : '';

            // append checkdigit to job id for new invoice number
            $check_digit = getCheckDigit(trim($row['jid']));
            $bpay_ref_code = "{$row['jid']}{$check_digit}";

            $p_address = "{$row['p_address_1']} {$row['p_address_2']}, {$row['p_address_3']}";

            $invoice_amount = number_format($row['invoice_amount'], 2);
            $invoice_payments = number_format($row['invoice_payments'], 2);
            $invoice_credits = number_format($row['invoice_credits'], 2);

            $balance_tot += $row['invoice_balance'];
            $invoice_balance = number_format($row['invoice_balance'], 2);

            if ($invoice_payments > 0) {
                $invoice_payments_str = '$' . $invoice_payments;
            } else {
                $invoice_payments_str = '';
            }

            if ($invoice_credits > 0) {
                $invoice_credits_str = '-$' . $invoice_credits;
            } else {
                $invoice_credits_str = '';
            }


            // Age
            $date1 = date_create(date('Y-m-d', strtotime($row['jdate'])));
            $date2 = date_create(date('Y-m-d'));
            $diff = date_diff($date1, $date2);
            $age = $diff->format("%r%a");
            $age_val = (((int) $age) != 0) ? $age : 0;


            if ($age_val <= 30) { // not overdue, within 30 days
                $not_overdue += $row['invoice_balance'];
            } else if ($age_val >= 31 && $age_val <= 60) { // overdue, within 31 - 60 days
                $overdue_31_to_60 += $row['invoice_balance'];
            } else if ($age_val >= 61 && $age_val <= 90) { // overdue, within 61 - 90 days
                $overdue_61_to_90 += $row['invoice_balance'];
            } else if ($age_val >= 91) { // overdue over 91 days or more
                $overdue_91_more += $row['invoice_balance'];
            }


            $url = $_SERVER['SERVER_NAME'];
            if ($_SESSION['country_default'] == 1) { // AU
                if (strpos($url, "crmdev") === false) { // live
                    $compass_fg_id = 39;
                } else { // dev
                    $compass_fg_id = 34;
                }
            }

            $fg_id = $row['franchise_groups_id'];

            $pdf->Cell($col1, $cell_height, $jdate, 1);
            $pdf->Cell($col1, $cell_height, $bpay_ref_code, 1);
            if ($fg_id == $compass_fg_id) { // compass only
                $pdf->Cell($col1, $cell_height, $row['compass_index_num'], 1);
                $pdf->Cell($col3 - 18, $cell_height, $p_address, 1);
            } else {
                $pdf->Cell($col3, $cell_height, $p_address, 1);
            }
            $pdf->Cell($col5, $cell_height, '$' . $invoice_amount, 1);
            $pdf->Cell($col5, $cell_height, $invoice_payments_str, 1);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell($col5, $cell_height, $invoice_credits_str, 1);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell($col5, $cell_height, '$' . $invoice_balance, 1);
            $pdf->Ln();
        }




        /*
          // test rows
          for( $i=0; $i<50; $i++ ){
          $pdf->Cell($col1,$cell_height,'Test Rows',1);
          $pdf->Cell($col1,$cell_height,'$0',1);
          $pdf->Cell($col3,$cell_height,'$0',1);
          $pdf->Cell($col5,$cell_height,'$0',1);
          $pdf->Cell($col5,$cell_height,'$0',1);
          $pdf->SetTextColor(255,0,0);
          $pdf->Cell($col5,$cell_height,'$0',1);
          $pdf->SetTextColor(0,0,0);
          $pdf->Cell($col5,$cell_height,'$0',1);
          $pdf->Ln();
          }
         */


        $x = $pdf->GetX();
        $y = $pdf->GetY();


        $pdf->setX(10);
        $pdf->setY($y + 3);


        $cell_width = 38;
        $cell_height = 7;
        $cell_border = 1;
        $cell_new_line = 0;
        $cell_align = 'R';
        $cell_change_txt_color = true;

        $cell_height = 10;

        // grey
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetFont('Arial', 'B', $font_size);

        $cell_height = 5;
        $pdf->Cell($cell_width, $cell_height, '0-30 days (Not Overdue)', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '31-60 days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '61-90 days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '91+ days OVERDUE', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, 'Total Amount Due', $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);

        $pdf->Ln();


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', $font_size);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format($not_overdue, 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format($overdue_31_to_60, 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format($overdue_61_to_90, 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format($overdue_91_more, 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        // grey
        $pdf->SetFillColor(238, 238, 238);
        $pdf->SetFont('Arial', 'B', $font_size);
        $pdf->Cell($cell_width, $cell_height, '$' . number_format($balance_tot, 2), $cell_border, $cell_new_line, $cell_align, $cell_change_txt_color);
        $pdf->SetFillColor(255, 255, 255);

        //$pdf_filename = 'statements_'.date('dmYHis').'.pdf';
        if ($params['ret'] == 1) {
            return $pdf->Output($params['file_name'], $params['output']);
        } else {
            $pdf->Output($params['file_name'], $params['output']);
        }
    }

    // get tenants from new tenants table
    function getNewTenantsData($params) {


        // filters
        $filter_arr = array();


        if ($params['pt_id'] != "") {
            $filter_arr[] = "AND pt.`property_tenant_id` = {$params['pt_id']} ";
        }

        if ($params['property_id'] != "") {
            $filter_arr[] = "AND pt.`property_id` = {$params['property_id']} ";
        }

        if ($params['pm_tenant_id'] != "") {
            $filter_arr[] = "AND pt.`pm_tenant_id` = {$params['pm_tenant_id']} ";
        }

        if ($params['active'] != "") {
            $filter_arr[] = "AND pt.`active` = {$params['active']} ";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = " AND ( pt.`createdDate` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "
          AND (
          (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
          (a.`agency_name` LIKE '%{$params['phrase']}%')
          )
          ";
          }
         */

        // combine all filters
        $filter_str = " WHERE pt.`property_tenant_id` > 0 " . implode(" ", $filter_arr);


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(pt.`property_tenant_id`) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " * ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }


        $sql = "
			SELECT {$sel_str}
			FROM `property_tenants` AS pt
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function NLM_Property($property_id, $reason_they_left, $other_reason, $nlm_from) {

        $return = [];
        $staff_id = $_SESSION['USER_DETAILS']['StaffID'];
        $staff_name = $_SESSION['USER_DETAILS']['FirstName'] . " " . $_SESSION['USER_DETAILS']['LastName'];

        if ($this->NLMjobStatusCheck($property_id) == true) {

            $return['nlm_chk_flag'] = 1;
            $return['ret_msg'] = 'This Property has an active job so cant be NLM';
        } else {


            // check if property has money owing and needs to verify paid
            $update_verify_paid_marker_str = '';
            if( $this->check_verify_paid($property_id) == true ){
                $update_verify_paid_marker_str = '`nlm_display` = 1,';
            }


            // update property
            $sql1 = "
			UPDATE property
			SET
				`agency_deleted`=0,
				`booking_comments` = 'No longer managed as of " . date("d/m/Y") . " - by SATS.',
				`is_nlm` = 1,
				`nlm_timestamp` = '" . date('Y-m-d H:i:s') . "',
				{$update_verify_paid_marker_str}
				`nlm_by_sats_staff` = '{$staff_id}'
			WHERE property_id = {$property_id}
			";
            mysql_query($sql1);


            ## New > when NLM clear api_prop_id from api_property_data table 
            $sql_clear_api_property_data = "
			DELETE 
            FROM `api_property_data`
			WHERE `crm_prop_id` = {$property_id}
			";
            mysql_query($sql_clear_api_property_data);
            ## New > when NLM clear api_prop_id from api_property_data table end


            // update jobs
            $sql2 = "
				UPDATE jobs
				SET `status` = 'Cancelled',
					`comments` = 'This property was marked No Longer Managed by SATS on " . date("d/m/Y") . " and all jobs cancelled',
					`cancelled_date` = '" . date('Y-m-d') . "'
				WHERE `status` != 'Completed'
				AND property_id = {$property_id}
			";
            mysql_query($sql2);

            //insert job logs
            $to_cancel_jobs_sql = mysql_query("
                SELECT `id` AS jid
                FROM `jobs`
                WHERE `property_id` = {$property_id} 
                AND `status` != 'Completed'
                ");
            $cancel_job_log = "Job <strong>Cancelled</strong> due to <strong>NLM</strong> by SATS";
            while ($to_cancel_jobs_sql_row = mysql_fetch_array($to_cancel_jobs_sql)) {
                $params_logs = array(
                    'job_id' => $to_cancel_jobs_sql_row['jid'],
                    'log_type' => 'Job Cancelled',
                    'log_msg' => $cancel_job_log
                );
                $this->insertJobLog($params_logs);
            }
            //insert job logs end

            if( $property_id > 0 ){
               
                // this month
                $this_month_start = date("Y-m-0");
                $this_month_end = date("Y-m-t");

                // get completed job this month
                $job_sql_str = "
                SELECT j.`id`
                FROM `jobs` AS j               
                WHERE j.`property_id` = {$property_id}
                AND j.`status` = 'Completed'
                AND j.`job_price` > 0
                AND j.`date` BETWEEN '{$this_month_start}' AND '{$this_month_end}'                         
                ";
                $job_sql = mysql_query($job_sql_str);

                // get status change this month
                $ps_sql_str = "
                SELECT ps.`status_changed`
                FROM `property` AS p 
                INNER JOIN `property_services` AS ps ON p.`property_id` = ps.`property_id`
                WHERE p.`property_id` = {$property_id} 
                AND CAST( ps.`status_changed` AS DATE ) BETWEEN '{$this_month_start}' AND '{$this_month_end}'
                ";
                $ps_sql = mysql_query($ps_sql_str);

                $clear_is_payable = null;
                $payable = '';
                if( mysql_num_rows($job_sql) > 0 && mysql_num_rows($ps_sql) > 0 ){

                    // DO nothing, leave is_payable as it is

                }else{

                    // clear is_payable
                    $clear_is_payable = "`is_payable` = 0,";
                    $payable = '0';
                }

            }           
            
            $ps_sql2 = mysql_query("
            SELECT 
                ps.`property_services_id` AS ps_id,
                ps.`is_payable`,
                ajt.`type` AS service_type_name 
            FROM `property_services` AS ps  
            LEFT JOIN `alarm_job_type` AS ajt ON ps.`alarm_job_type_id` = ajt.`id`              
            WHERE ps.`property_id` = {$property_id}  
            AND ps.`service` NOT IN(0,3)
            AND ps.`service` = 1
            ");

            while ($ps_row2 = mysql_fetch_array($ps_sql2)){
                // if( $ps_row2['ps_id'] > 0 ){ 
                    $update_property_services = "
                    UPDATE `property_services`
                    SET 
                        `service` = 2,
                        {$clear_is_payable}
                        `status_changed` = '".date('Y-m-d H:i:s')."'
                    WHERE `property_services_id` = {$ps_row2['ps_id']}
                    AND `property_id` = {$property_id}
                    ";
                    mysql_query($update_property_services);

                    if ($payable == '0') {
                        $insertLogps = "
                        INSERT INTO
                        property_event_log (
                            property_id,
                            staff_id,
                            event_type,
                            event_details,
                            log_date
                        )
                        VALUES (
                            " . $property_id . ",
                            '{$staff_id}',
                            'Property Service Updated',
                            'Property Service <b>{$ps_row2['service_type_name']}</b> unmarked <b>payable</b>',
                            '" . date('Y-m-d H:i:s') . "'
                        )
                        ";
                        mysql_query($insertLogps);
                    }
                // }
            }

            $qlr = "
			SELECT reason FROM leaving_reason WHERE id = {$reason_they_left}
			";

            $data = mysql_fetch_assoc(mysql_query($qlr));
            if ($reason_they_left == -1 && $data['reason'] == '') {
                $nlm_reason = $other_reason;
            } else {
                $nlm_reason = $data['reason'];
            }

            // add logs
            $insertLogQuery = "
			INSERT INTO
			property_event_log (
				property_id,
				staff_id,
				event_type,
				event_details,
				log_date
			)
			VALUES (
				" . $property_id . ",
				'{$staff_id}',
				'No Longer Managed',
				'By {$staff_name}, Details: <strong>{$nlm_reason}</strong>, NLM date: {$nlm_from}',
				'" . date('Y-m-d H:i:s') . "'
			)
			";
            mysql_query($insertLogQuery);

            $insertpropertyleaving = "
			INSERT INTO
			property_nlm_reason (
				property_id,
                reason,
                other_reason
			)
			VALUES (
                " . $property_id . ",
                '".$reason_they_left."',
                '".$other_reason."'
			)
			";
            mysql_query($insertpropertyleaving);


            ## by Gherx > add email notification
            $domain = $_SERVER['SERVER_NAME'];
            if( $_SESSION['country_default']==1 ){ // AU
                // go to au
                $cntry_iso = ".sats.com.au";

                if( strpos($domain,"crmdev") !== false ){ // DEV
                   $e_to = "devaccounts@sats.com.au";
                }else{ // LIVE
                    $e_to = "accounts@sats.com.au";
                }


            }else if( $_SESSION['country_default']==2 ){ // NZ
                // go to nz
                $cntry_iso = ".sats.co.nz";

                if( strpos($domain,"crmdev") !== false ){ // DEV
                    $e_to = "devaccounts@sats.co.nz";
                }else{ // LIVE
                    $e_to = "accounts@sats.co.nz";
                }
            }
            
            //get property nlm details
            $prop_q = mysql_query("
            SELECT property_id, address_1, address_2, address_3
            FROM `property` 
            WHERE property_id = {$property_id}
            ");
            $prop_row = mysql_fetch_array($prop_q);
            $prop_id = $prop_row['property_id'];
            $prop_link = "https://{$this->getDynamicDomain()}{$cntry_iso}/view_property_details.php?id={$prop_id}";
            $prop_name = "{$prop_row['address_1']} {$prop_row['address_2']}, {$prop_row['address_3']}";

            // get country
            $cntry_sql = getCountryViaCountryId($_SESSION['country_default']);
            $cntry = mysql_fetch_array($cntry_sql);

            $email_noti_message_text = "
            Dear Accounts,
            <br/>
            <br/>
            <a href='".$prop_link."'>{$prop_name}</a> (Property ID ".$property_id.") has been marked NLM.
            <br/>
            <br/>
            Please confirm billing related to this property.
            <br/>
            <br/>
            Regards,    
            <br/>
            SATS Dev Team
            ";

            $email_noti_from = "Smoke Alarm Testing Services <{$cntry['outgoing_email']}>";
            //$email_noti_to = "bent@sats.com.au";
            $email_noti_to = $e_to;
            $email_noti_subject = "Property NLM Notification";
            $email_noti_message = $email_noti_message_text;
            $email_noti_params = array(
                'from' => $email_noti_from,
                'to' => $email_noti_to,
                'subject' => $email_noti_subject,
                'message' => $email_noti_message
            );
            if( $_SESSION['country_default']==1 ){ //Email for AU only
                $this->nativeEmail($email_noti_params);
            }
            
            ## by Gherx > add email notification end

            // deactivate properties_from_other_company
            mysql_query("
            UPDATE `properties_from_other_company`
            SET `active` = 0
            WHERE `property_id` = {$property_id}
            ");

            $return['nlm_chk_flag'] = 0;
        }


        return json_encode($return);
    }

    // old code copied from undelete_property.php
    function restoreProperty($params) {

        $property_id = $params['property_id'];
        $clear_tenants = $params['clear_tenants'];
        $pm_prop_id = $params['pm_prop_id'];

        $staff_id = $_SESSION['USER_DETAILS']['StaffID'];
        $staff_name = $_SESSION['USER_DETAILS']['FirstName'] . " " . $_SESSION['USER_DETAILS']['LastName'];
        $pm_prop_id_str = '';

        if ($pm_prop_id != '') {
            // $pm_prop_id_str = " `propertyme_prop_id` = '{$pm_prop_id}', ";
            mysql_query("UPDATE `api_property_data` SET `api_prop_id` = '".$pm_prop_id."' WHERE `property_id`=".$property_id);
        }


        /*
          // clear tenants
          if($clear_tenants==1){

          $del_ten_str .= "
          `tenant_firstname1` = NULL,
          `tenant_lastname1` = NULL,
          `tenant_ph1` = NULL,
          `tenant_email1` = NULL,
          `tenant_mob1` =  NULL,
          `tenant_firstname2` = NULL,
          `tenant_lastname2` = NULL,
          `tenant_ph2` = NULL,
          `tenant_email2` = NULL,
          `tenant_mob2` =  NULL,
          ";

          }
         */


        $Query = "
			UPDATE property
			SET
			deleted=0,
			{$del_ten_str}
			agency_deleted=0,
			`is_nlm` = 0,
			`nlm_display` = NULL,
			`nlm_timestamp` = NULL,
			`nlm_by_sats_staff` = NULL,
			`nlm_by_agency` = NULL
			WHERE property_id={$property_id};
		";

        mysql_query($Query);
        if (mysql_affected_rows() > 0) {



            $sa_sql = mysql_query("
				SELECT *
				FROM `staff_accounts`
				WHERE `StaffID` ={$staff_id}
			");
            $s = mysql_fetch_array($sa_sql);

            $insertLogQuery = "INSERT INTO property_event_log (property_id, staff_id, event_type, event_details, log_date)
							VALUES (" . $property_id . ", " . $staff_id . ", 'Property Restored', 'Restored to match Active property on PropertyMe', NOW())";
            mysql_query($insertLogQuery);

            /*
              // update status changed
              mysql_query("
              UPDATE `property_services`
              SET `status_changed` = '".date("Y-m-d H:i:s")."'
              WHERE `property_id` = '{$property_id}'
              ");
             */

            return true;
        } else {


            return false;
            //echo "An error has occurred, it looks like the property may have already been restored! (please check)<br>\n";
        }
    }

    // get Invoice Payments
    function getInvoiceRefundsData($params) {


        // filters
        $filter_arr = array();

        if ($params['ip_id'] != "") {
            $filter_arr[] = "AND ir.`invoice_refund_id` = {$params['ip_id']}";
        }

        if ($params['active'] != "") {
            $filter_arr[] = "AND ir.`active` = {$params['active']}";
        }

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND ir.`job_id` = {$params['job_id']}";
        }

        if ($params['payment_date'] != "") {
            $filter_arr[] = "AND ir.`payment_date` = {$params['payment_date']}";
        }

        if ($params['type_of_payment'] != "") {
            $filter_arr[] = "AND ir.`type_of_payment` = {$params['type_of_payment']}";
        }

        if ($params['created_by'] != "") {
            $filter_arr[] = "AND ir.`created_by` = {$params['created_by']}";
        }


        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = "AND CAST(sar.`created_date` AS DATE) BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}'";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "AND (
          bn.`notes` LIKE '%{$params['phrase']}%' OR
          a.`agency_name` LIKE '%{$params['phrase']}%'
          )";
          }
         */


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE ir.`invoice_refund_id` > 0 " . implode(" ", $filter_arr);
        }


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				*, ir.`active` AS ic_active
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }

        $join_table_imp = '';
        $join_table_str = [];
        if (count($params['join_table']) > 0) {

            foreach ($params['join_table'] as $join_table) {
                switch ($join_table) {
                    case 'jobs':
                        $join_table_str[] = 'LEFT JOIN `jobs` AS j ON ir.`job_id` = j.`id`';
                        break;
                    case 'created_by_who':
                        $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa ON ir.`created_by` = sa.`StaffID`';
                        break;
                    case 'payment_types':
                        $join_table_str[] = 'LEFT JOIN `payment_types` AS pt ON ir.`type_of_payment` = pt.`payment_type_id`';
                        break;
                }
            }
        }

        $join_table_imp = implode(" ", $join_table_str);


        $sql = "
			SELECT {$sel_str}
			FROM `invoice_refunds` AS ir
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function getInvoiceCreditReason($credit_reason_id) {

        if ($credit_reason_id == -1) { // other
            $credit_reason = 'Other';
        } else {
            $credit_reason_sql = $this->getCreditReason($credit_reason_id);
            $cr_row = mysql_fetch_array($credit_reason_sql);
            $credit_reason = $cr_row['reason'];
        }

        return $credit_reason;
    }

    function getAll240vAlarm($job_id) {

        $sql = mysql_query("
            SELECT COUNT(al.`alarm_id`) AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
			WHERE al.`job_id` = {$job_id}
            AND al.`ts_discarded` = 0
            AND al_pwr.`is_240v` = 1
        ");

        $row = mysql_fetch_array($sql);
        $al_count = $row['al_count'];

        if ( $al_count > 0 ) {
            return true;
        } else {
            return false;
        }

    }

    // find an expired 240v alarm
    function findExpired240vAlarm($job_id, $year) {

        $year2 = ( $year != '' ) ? $year : date("Y");

        $sql = mysql_query("
			SELECT COUNT(al.`alarm_id`) AS al_count
            FROM `alarm` AS al
            LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
			WHERE al.`job_id` = {$job_id}
			AND al.`expiry` <= '{$year2}'
            AND al.`ts_discarded` = 0
            AND al_pwr.`is_240v` = 1
		");

        $row = mysql_fetch_array($sql);
        $al_count = $row['al_count'];

        if ( $al_count > 0 ) {
            return true;
        } else {
            return false;
        }

    }

    // get new Property Managers
    function getNewPropertyManagers($params) {


        // filters
        $filter_arr = array();

        if ($params['aua_id'] != "") {
            $filter_arr[] = "AND aua.`agency_user_account_id` = {$params['aua_id']} ";
        }

        if ($params['active'] != "") {
            $filter_arr[] = "AND aua.`active` = {$params['active']} ";
        }

        if ($params['user_type'] != "") {
            $filter_arr[] = "AND aua.`user_type` = {$params['user_type']} ";
        }

        if ($params['email'] != "") {
            $filter_arr[] = "AND aua.`email` = {$params['email']} ";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND aua.`agency_id` = {$params['agency_id']} ";
        }


        // combine all filters
        $filter_str = " WHERE aua.`agency_user_account_id` > 0 " . implode(" ", $filter_arr);


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(aua.`agency_user_account_id`) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = " * ";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }


        $sql = "
			SELECT {$sel_str}
			FROM `agency_user_accounts` AS aua
			LEFT JOIN `agency_user_account_types` AS auat ON aua.`user_type` = auat.`agency_user_account_type_id`
			LEFT JOIN `agency` AS a ON aua.`agency_id` = a.`agency_id`
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    // parse the tags on logs link
    public function parseDynamicLink($params) {

        $log_details = $params['log_details'];

        // property logs
        $tag = '{p_address}';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // get logs data
            $log_sql_str = "
			SELECT `l`.`property_id`
			FROM `logs` AS `l`
			WHERE `l`.`log_id` = {$params['log_id']}
			";
            $log_sql = mysql_query($log_sql_str);
            $l_row = mysql_fetch_array($log_sql);
            $property_id = $l_row['property_id'];

            if (isset($property_id) && $property_id > 0) {

                // get property data
                $p_sql_str = "
				SELECT
					`p`.`property_id`,
					`p`.`address_1`,
					`p`.`address_2`,
					`p`.`address_3`,
					`p`.`state`,
					`p`.`postcode`
				FROM `property` AS `p`
				WHERE `p`.`property_id` = {$property_id}
				";
                $p_sql = mysql_query($p_sql_str);
                $p_row = mysql_fetch_array($p_sql);
                $vpd_link = "<a href='/view_property_details.php?id={$property_id}'>{$p_row['address_1']} {$p_row['address_2']} {$p_row['address_3']}</a>";

                // replace tags
                $log_details = str_replace($tag, $vpd_link, $log_details);
            }
        }


        // agency user
        $tag = 'agency_user';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // break down the tag to get the agency user ID
            $tag_string = $this->get_part_of_string($log_details, '{', '}');
            $str_exp = explode(':', $tag_string);
            $aua_id = $str_exp[1];


            // get agency user data
            $sel_query = "
				aua.`agency_user_account_id`,
				aua.`fname`,
				aua.`lname`
			";

            $user_sql_str = "
			SELECT `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`
			FROM `agency_user_accounts` AS `aua`
			LEFT JOIN `agency_user_account_types` AS `auat` ON aua.`user_type` = auat.`agency_user_account_type_id`
			LEFT JOIN `agency` AS `a` ON aua.`agency_id` = a.`agency_id`
			WHERE `aua`.`agency_user_account_id` = {$aua_id}
			";
            $user_sql = mysql_query($user_sql_str);
            $user_row = mysql_fetch_array($user_sql);
            $user_full_name = "{$user_row['fname']} {$user_row['lname']}";

            // replace tags
            $log_details = str_replace('{' . $tag_string . '}', $user_full_name, $log_details);
        }


        // created by
        $tag = '{created_by}';
        // find the tag
        if (strpos($log_details, $tag) !== false) {

            // get logs data
            $log_sql_str = "
			SELECT `l`.`created_by`
			FROM `logs` AS `l`
			WHERE `l`.`log_id` = {$params['log_id']}
			";
            $log_sql = mysql_query($log_sql_str);

            if (mysql_num_rows($log_sql) > 0) {

                $l_row = mysql_fetch_array($log_sql);
                $created_by = $l_row['created_by'];

                // get agency user data
                $sel_query = "
					aua.`agency_user_account_id`,
					aua.`fname`,
					aua.`lname`
				";

                $user_sql_str = "
				SELECT `aua`.`agency_user_account_id`, `aua`.`fname`, `aua`.`lname`
				FROM `agency_user_accounts` AS `aua`
				LEFT JOIN `agency_user_account_types` AS `auat` ON aua.`user_type` = auat.`agency_user_account_type_id`
				LEFT JOIN `agency` AS `a` ON aua.`agency_id` = a.`agency_id`
				WHERE `aua`.`agency_user_account_id` = {$created_by}
				";
                $user_sql = mysql_query($user_sql_str);
                $user_row = mysql_fetch_array($user_sql);
                $user_full_name = "{$user_row['fname']} {$user_row['lname']}";

                // replace tags
                $log_details = str_replace($tag, $user_full_name, $log_details);
            }
        }


        return $log_details;
    }

    public function get_part_of_string($string, $start_str, $end_str) {

        $startpos = strpos($string, $start_str);
        $endpos = strpos($string, $end_str);

        $length = $endpos - $startpos;
        return substr($string, $startpos + 1, $length - 1);
    }

    // get Daily Figures data per Date
    function getDailyFiguresPerDate($date) {

        return mysql_query("
			SELECT *
			FROM `daily_figures_per_date`
			WHERE `date` = '{$date}'
			AND `country_id` = {$_SESSION['country_default']}
		");
    }

    // get Invoice Credit
    function getNewLogs($params) {


        // filters
        $filter_arr = array();


        if ($params['job_id'] != "") {
            $filter_arr[] = "AND l.`job_id` = {$params['job_id']} ";
        }

        if ($params['property_id'] != "") {
            $filter_arr[] = "AND l.`property_id` = {$params['property_id']} ";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND l.`agency_id` = {$params['agency_id']} ";
        }

        if ($params['display_in_vjd'] != "") {
            $filter_arr[] = "AND l.`display_in_vjd` = '{$params['display_in_vjd']}' ";
        }

        if ($params['display_in_vpd'] != "") {
            $filter_arr[] = "AND l.`display_in_vpd` = '{$params['display_in_vpd']}' ";
        }

        if ($params['display_in_vad'] != "") {
            $filter_arr[] = "AND l.`display_in_vad` = '{$params['display_in_vad']}' ";
        }

        if ($params['display_in_portal'] != "") {
            $filter_arr[] = "AND l.`display_in_portal` = '{$params['display_in_portal']}' ";
        }

        if ($params['display_in_accounts'] != "") {
            $filter_arr[] = "AND l.`display_in_accounts` = '{$params['display_in_accounts']}' ";
        }

        if ($params['display_in_accounts_hid'] != "") {
            $filter_arr[] = "AND l.`display_in_accounts_hid` = '{$params['display_in_accounts_hid']}' ";
        }

        if ($params['display_in_sales'] != "") {
            $filter_arr[] = "AND l.`display_in_sales` = '{$params['display_in_sales']}' ";
        }

        if (is_numeric($params['deleted'])) {
            $filter_arr[] = "AND l.`deleted` = '{$params['deleted']}' ";
        }

        /*
          if($params['filterDate']!=''){
          if( $params['filterDate']['from']!="" && $params['filterDate']['to']!="" ){
          $filter_arr[] = " AND ( j.`date` BETWEEN '{$params['filterDate']['from']}' AND '{$params['filterDate']['to']}' ) ";
          }
          }

          if($params['phrase']!=''){
          $filter_arr[] = "
          AND (
          (CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
          (a.`agency_name` LIKE '%{$params['phrase']}%')
          )
          ";
          }
         */

        // combine all filters
        $filter_str = " WHERE l.`log_id` > 0 " . implode(" ", $filter_arr);


        //custom query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        if ($params['custom_select'] != '') {
            $sel_str = " {$params['custom_select']} ";
        } else if ($params['return_count'] == 1) {
            $sel_str = " COUNT(*) AS jcount ";
        } else if ($params['distinct_sql'] != "") {

            $sel_str = " DISTINCT {$params['distinct_sql']} ";
        } else {
            $sel_str = "
				l.`log_id`,
				l.`created_date`,
				l.`title`,
				l.`details`,

				ltit.`title_name`,

				aua.`fname`,
				aua.`lname`,
				aua.`photo`
			";
        }




        // sort
        if ($params['sort_list'] != '') {

            $sort_str_arr = array();
            foreach ($params['sort_list'] as $sort_arr) {
                if ($sort_arr['order_by'] != "" && $sort_arr['sort'] != '') {
                    $sort_str_arr[] = "{$sort_arr['order_by']} {$sort_arr['sort']}";
                }
            }

            $sort_str_imp = implode(", ", $sort_str_arr);
            $sort_str = "ORDER BY {$sort_str_imp}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str .= " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        if ($params['custom_join_table'] != '') {
            $custom_table_join = $params['custom_join_table'];
        }

        /*
          $join_table_imp = '';
          $join_table_str = [];
          if(  count($params['join_table'])>0 ){

          foreach($params['join_table'] as $join_table){
          switch( $join_table ){
          case 'created_by_who':
          $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa_who ON ic.`created_by` = sa_who.`StaffID`';
          break;
          case 'approved_by':
          $join_table_str[] = 'LEFT JOIN `staff_accounts` AS sa_ab ON ic.`approved_by` = sa_ab.`StaffID`';
          break;
          }
          }

          }

          $join_table_imp = implode(" ",$join_table_str);
         */


        $sql = "
			SELECT {$sel_str}
			FROM `logs` AS l
			LEFT JOIN `log_titles` AS ltit ON l.`title` = ltit.`log_title_id`
			LEFT JOIN `agency_user_accounts` AS aua ON l.`created_by` = aua.`agency_user_account_id`
			LEFT JOIN `staff_accounts` AS sa ON l.`created_by_staff` = sa.`StaffID`
			{$join_table_imp}
			{$custom_table_join}
			{$filter_str}
			{$custom_filter_str}
			{$group_by_str}
			{$sort_str}
			{$pag_str}

		";

        if ($params['echo_query'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    public function findBookedJobsNotOnAnySTR($start, $limit) {

        $today = date('Y-m-d');
        $next_2_days = date('Y-m-d', strtotime('+2 days'));

        if (is_numeric($start) && is_numeric($limit)) {
            $limit_str = " LIMIT {$start}, {$limit}";
        }

        $sql_str = "SELECT
		j.`id` AS jid,
		j.`created` AS jcreated,
		j.`date` AS jdate,
		j.`service` AS jservice,
		j.`job_type`,

		p.`property_id`,
		p.`address_1` AS p_address_1,
		p.`address_2` AS p_address_2,
		p.`address_3` AS p_address_3,
		p.`state` AS p_state,
		p.`postcode` AS p_postcode,

		a.`agency_id`,
		a.`agency_name`,

		sa.`FirstName`,
		sa.`LastName`,

		tr.`tech_run_id`,
		tr.`date`
		FROM `jobs` AS j
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
		LEFT JOIN `staff_accounts` AS sa ON j.`assigned_tech` = sa.`StaffID`
		LEFT JOIN `tech_run_rows` AS trr ON ( j.`id` = trr.`row_id` AND trr.`row_id_type` = 'job_id'  )
		LEFT JOIN `tech_run` AS tr ON ( trr.`tech_run_id` = tr.`tech_run_id` )
		WHERE j.`status` = 'Booked'
		AND j.`date` = '{$next_2_days}'
		AND tr.`tech_run_id` IS NULL
		GROUP BY j.`id`
		{$limit_str}
		";

        return mysql_query($sql_str);
    }

    function get_job_data($params) {

        // filters
        $filter_arr = [];

        if ($params['job_id'] != "") {
            $filter_arr[] = "AND j.`id` = {$params['job_id']}";
        }

        if ($params['j_del'] != "") {
            $filter_arr[] = "AND j.`del_job` = {$params['j_del']}";
        }
        if ($params['p_del'] != "") {
            $filter_arr[] = "AND p.`deleted` = {$params['p_del']}";
        }
        if ($params['a_status'] != "") {
            $filter_arr[] = "AND a.`status` = {$params['a_status']}";
        }

        if ($params['maintenance_id'] != "") {
            $filter_arr[] = "AND am.`maintenance_id` = {$params['maintenance_id']}";
        }

        if ($params['job_service'] != '') {
            $filter_arr[] = " AND j.`service` = '{$params['job_service']}' ";
        }

        if ($params['country_id'] != "") {
            $filter_arr[] = "AND a.`country_id` = {$params['country_id']}";
        }

        if ($params['agency_id'] != "") {
            $filter_arr[] = "AND a.`agency_id` = {$params['agency_id']}";
        }

        if ($params['job_type'] != '') {
            $filter_arr[] = "AND j.`job_type` = '{$params['job_type']}'";
        }

        if ($params['postcode_region_id'] != "") {
            $filter_arr[] = "AND p.`postcode` IN ( {$params['postcode_region_id']} )";
        }

        if ($params['a_postcode_region_id'] != "") {
            $filter_arr[] = "AND a.`postcode` IN ( {$params['a_postcode_region_id']} )";
        }

        if ($params['a_state'] != '') {
            $filter_arr[] = "AND a.`state` = '{$params['a_state']}'";
        }

        if ($params['job_status'] != '') {

            // amend for covid-19
            if( $params['job_status'] == 'On Hold' ){
                $filter_arr[] = "AND j.`status` IN('On Hold','On Hold - COVID')";
            }else{
                $filter_arr[] = "AND j.`status` = '{$params['job_status']}'";
            }

        }

        if ($params['booked'] == 1) {
            $filter_arr[] = "AND j.`status` = 'Booked'";
        }

        if ($params['job_created'] != '') {
            $filter_arr[] = "AND CAST( j.`created` AS DATE ) = '{$params['job_created']}'";
        }

        if ($params['ts_completed'] == 1) {
            $filter_arr[] = "AND j.`ts_completed` = 1";
        }

        if (is_numeric($params['dk'])) {
            $filter_arr[] = "AND j.`door_knock` = {$params['dk']}";
        }

        if ($params['date'] != '') {
            $filter_arr[] = "AND j.`date` = '{$params['date']}'";
        }

        if (is_numeric($params['urgent_job'])) {
            $filter_arr[] = "AND j.`urgent_job` = '{$params['urgent_job']}'";
        }

        if (is_numeric($params['auto_renew'])) {
            $filter_arr[] = "AND a.`auto_renew` ={$params['auto_renew']}";
        }

        if (is_numeric($params['out_of_tech_hours'])) {
            $filter_arr[] = "AND j.`out_of_tech_hours` = {$params['out_of_tech_hours']}";
        }

        if ($params['date_range'] != '') {
            $filter_arr[] = "AND j.`date` BETWEEN '{$params['date_range']['from']}' AND '{$params['date_range']['to']}'";
        }

        if ($params['exclude_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND (
				j.`status` != 'On Hold' AND
				j.`status` != 'Pending' AND
				j.`status` != 'Completed' AND
				j.`status` != 'Cancelled'
			)";
        }

        if ($params['status_booked_or_completed'] == 1) {
            $filter_arr[] = "AND (
				j.`status` = 'Booked' OR
				j.`status` = 'Completed'
			)";
        }

        if ($params['completed_status_for_kpi_report'] == 1) {
            $filter_arr[] = "AND ( j.`status` = 'Completed' OR j.`status` = 'Merged Certificates' )";
        }

        if ($params['query_for_estimated_income'] == 1) {
            $filter_arr[] = "AND (
				 ( j.`status` = 'Booked' AND j.`door_knock` !=1 ) OR
				  j.`status` = 'Completed'  OR
				  j.`status` = 'Merged Certificates'
			)";
        }

        if ($params['exclude_tech_other_supplier'] == 1) {
            $filter_arr[] = "AND ";
        }

        if ($params['dha_need_processing'] == 1) {
            $filter_arr[] = "AND j.`dha_need_processing` = 1";
        }

        if ($params['phrase'] != '') {
            $filter_arr[] = "AND (
				(CONCAT_WS(' ', LOWER(p.address_1), LOWER(p.address_2), LOWER(p.address_3), LOWER(p.state), LOWER(p.postcode)) LIKE '%{$params['phrase']}%') OR
				(a.`agency_name` LIKE '%{$params['phrase']}%')
			 )";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE j.`id` > 0 " . implode(" ", $filter_arr);
        }


        // custom select query
        if ($params['custom_select'] != '') {
            $sel_str = "{$params['custom_select']}";
        } else {
            $sel_str = "*";
        }

        //custom filter query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        //custom sort query
        if ($params['sort_query'] != '') {
            $sort_str = $params['sort_query'];
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        //$new_pm = 0;
        $new_pm = NEW_PM;

        $sql = "
		SELECT {$sel_str}
		FROM `jobs` AS j
        LEFT JOIN `extra_job_notes` AS ejn ON j.`id` = ejn.`job_id`
		LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
        LEFT JOIN `api_property_data` AS apd ON p.`property_id` = apd.`crm_prop_id`
        LEFT JOIN `alarm_pwr` AS al_p ON p.`preferred_alarm_id` = al_p.`alarm_pwr_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        LEFT JOIN `agency_priority` AS aght ON a.`agency_id` = aght.`agency_id`
		LEFT JOIN `job_reason` AS jr ON j.`job_reason_id` = jr.`job_reason_id`
		LEFT JOIN `staff_accounts` AS ass_tech ON j.`assigned_tech` = ass_tech.`StaffID`
		LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
		LEFT JOIN `agency_user_accounts` AS aua ON p.`pm_id_new` = aua.`agency_user_account_id`
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";


        if ($params['display_echo'] == 1) {
            echo $sql;
        }

        if ($params['return_count'] == 1) {
            $j_sql = mysql_query($sql);
            $row = mysql_fetch_array($j_sql);
            return $row['jcount'];
        } else {
            return mysql_query($sql);
        }
    }

    function crm_ci_redirect($page, $page_params = null) {

        $domain = $_SERVER['SERVER_NAME'];
        if (CURRENT_COUNTRY == 1) { // AU
            // go to NZ
            $country_iso_txt = 'NZ';

            if (strpos($domain, "crmdev") !== false) { // DEV
                $crm_ci_link = 'https://crmdevci.sats.com.au';
            } else { // LIVE
                $crm_ci_link = 'https://crmci.sats.com.au';
            }
        } else if (CURRENT_COUNTRY == 2) { // NZ
            // go to AU
            $country_iso_txt = 'AU';

            if (strpos($domain, "crmdev") !== false) { // DEV
                $crm_ci_link = 'https://crmdevci.sats.co.nz';
            } else { // LIVE
                $crm_ci_link = 'https://crmci.sats.co.nz';
            }
        }

        /*
        // get staff username and pass
        $sa_sql = mysql_query("
			SELECT *
			FROM `staff_accounts`
			WHERE `StaffID` ={$_SESSION['USER_DETAILS']['StaffID']}
		");
        $sa = mysql_fetch_array($sa_sql);

        $encrypt = new cast128();
        $encrypt->setkey(SALT);

        $staff_email = $sa['Email'];
        $staff_password = $encrypt->decrypt(utf8_decode($sa['Password']));

        return "{$crm_ci_link}/login/authenticate/?username=" . rawurlencode($staff_email) . "&password=" . rawurlencode($staff_password) . "&page=" . rawurlencode($page) . "&page_params=" . rawurlencode($page_params);
        */

        return "{$crm_ci_link}/login/authenticate/?staff_id={$_SESSION['USER_DETAILS']['StaffID']}&page=" . rawurlencode($page) . "&page_params=" . rawurlencode($page_params);

    }

    function getDynamicCiDomain() {

        $domain = $_SERVER['SERVER_NAME'];
        if ($_SESSION['country_default'] == 1) { // AU
            // go to NZ
            $country_iso_txt = 'NZ';

            if (strpos($domain, "crmdev") !== false) { // DEV
                $crm_ci_link = 'https://crmdevci.sats.com.au';
            } else { // LIVE
                $crm_ci_link = 'https://crmci.sats.com.au';
            }
        } else if ($_SESSION['country_default'] == 2) { // NZ
            // go to AU
            $country_iso_txt = 'AU';

            if (strpos($domain, "crmdev") !== false) { // DEV
                $crm_ci_link = 'https://crmdevci.sats.co.nz';
            } else { // LIVE
                $crm_ci_link = 'https://crmci.sats.co.nz';
            }
        }

        return $crm_ci_link;
    }

    function descryptPassword($password) {

        $encrypt = new cast128();
        $encrypt->setkey(SALT);
        return $encrypt->decrypt(utf8_decode($password));
    }

    function tester() {

        if ($_SESSION['country_default'] == 1) { // AU

            // 11 - Vanessa Halfpenny
            // 2025 - Daniel Kramarzewski
            // 2070 - Developer Testing
            // 2287  - Ben Taylor
            return array(11, 2025, 2070, 2287);

        } else if ($_SESSION['country_default'] == 2) { // NZ

            // 11 - Vanessa Halfpenny
            // 2025 - Daniel Kramarzewski
            // 2070 - Developer Testing
            // 2231  - Ben Taylor
            return array(11, 2025, 2070, 2231);

        }

    }

    // bitly url shortener
    public function shortenUrl($url) {

        $params = array();
        $params['access_token'] = BITLY_API_ACCESS_TOKEN; // access token from bitly
        $params['longUrl'] = $url;
        $results = bitly_get('shorten', $params);

        return $results['data']['url'];
    }

    // Custom cURL
    function jcustom_curl($params) {

        $ch = curl_init();

        // parameters
        $data = $params['data'];
        $data_string = json_encode($data);

        // define options
        $optArray = array(
            CURLOPT_URL => $params['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CUSTOMREQUEST => $params['post_get'],
            CURLOPT_HTTPHEADER => $params['header']
        );

        if (!empty($params['data'])) {
            $optArray[CURLOPT_POSTFIELDS] = $data_string;
        }

        if ($params['display_cron_options'] == 1) {
            echo "<pre>";
            print_r($optArray);
            echo "</pre>";
        }

        // apply those options
        curl_setopt_array($ch, $optArray);

        // Execute
        $output = curl_exec($ch);

        // decode json
        $result_json = json_decode($output);

        if ($params['display_json'] == 1) {
            echo "<pre>";
            print_r($result_json);
            echo "</pre>";
        }

        curl_close($ch); // Close curl handle

        return $result_json;
    }

    // BLINK API ( URL shortener ) -- START
    // GET ACCESS TOKEN
    function getBlinkAccessToken() {

        $url = "https://app.bl.ink/api/v3/access_token";
        $header = array(
            "Content-Type: application/json"
        );

        // authentication data
        // using refresh token
        $data = array(
            "email" => BLINK_EMAIL,
            "refresh_token" => BLINK_REFRESH_TOKEN
        );

        /*
          // using password
          $data = array(
          "email" => BLINK_EMAIL,
          "password" => BLINK_PASS
          );
         */

        $params = array(
            'url' => $url,
            'post_get' => 'POST',
            'header' => $header,
            'data' => $data,
            'display_cron_options' => 0,
            'display_json' => 0
        );

        $result_json = $this->jcustom_curl($params);

        return $access_token = $result_json->access_token;
    }

    // GET DOMAIN
    function getBlinkDomain($access_token) {

        $url = "https://app.bl.ink/api/v3/domains";
        $header = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );

        $params = array(
            'url' => $url,
            'post_get' => 'POST',
            'header' => $header,
            'display_cron_options' => 0,
            'display_json' => 1
        );

        $result_json = $this->jcustom_curl($params);

        return $domain_id = $result_json->objects[0]->id;
    }

    // SHORTEN LINK
    function shortenLink($orig_link, $access_token) {

        $domain_id = BLINK_DOMAIN_ID;

        $url = "https://app.bl.ink/api/v3/{$domain_id}/links";
        $header = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json"
        );
        // parameters
        $data = array(
            "url" => $orig_link
        );

        $params = array(
            'url' => $url,
            'post_get' => 'POST',
            'data' => $data,
            'header' => $header,
            'display_cron_options' => 0,
            'display_json' => 0
        );

        $result_json = $this->jcustom_curl($params);

        return $short_link = $result_json->objects->short_link;
    }

    // BLINK API ( URL shortener ) -- END


    function get_agency_connected_service($api_service_id = null) {

        $api_service_arr = array(
            array(
                'id' => 5,
                'name' => 'Console Cloud'
            ),
            array(
                'id' => 7,
                'name' => 'Maintenance Manager'
            ),
            array(
                'id' => 6,
                'name' => 'Our Tradie'
            ),
            array(
                'id' => 4,
                'name' => 'Palace'
            ),
            array(
                'id' => 3,
                'name' => 'Property Tree'
            ),
            array(
                'id' => 1,
                'name' => 'PropertyMe'
            ),
            array(
                'id' => 2,
                'name' => 'Tapi'
            )
        );

        if ($api_service_id > 0) {

            foreach ($api_service_arr as $index => $api_service) {

                if ($api_service['id'] == $api_service_id) {
                    return $api_service;
                }
            }
        } else {
            return $api_service_arr;
        }
    }

    public function getCreditReason($credit_reason_id = null) {

        $append_str = null;
        if ($credit_reason_id > 0) {
            $append_str = " AND `credit_reason_id` = {$credit_reason_id} ";
        }

        $sql_str = "
			SELECT *
			FROM `credit_reason`
			WHERE `active` = 1
			{$append_str}
		";
        return mysql_query($sql_str);
    }

    function get_agency_api($params) {

        // custom select query
        if ($params['custom_select'] != '') {
            $sel_str = "{$params['custom_select']}";
        } else {
            $sel_str = "*";
        }

        // filters
        $filter_arr = [];

        if ($params['agency_api_id'] > 0) {
            $filter_arr[] = "AND `agency_api_id` = {$params['agency_api_id']}";
        }

        if ($params['active'] > 0) {
            $filter_arr[] = "AND `active` = {$params['active']}";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE `agency_api_id` > 0 " . implode(" ", $filter_arr);
        }


        //custom filter query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        //custom sort query
        if ($params['sort_query'] != '') {
            $sort_str = "ORDER BY {$params['sort_query']}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
		SELECT {$sel_str}
		FROM `agency_api`
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";


        if ($params['display_echo'] == 1) {
            echo $sql;
        }

        return mysql_query($sql);
    }

    function get_agency_api_integration($params) {

        // custom select query
        if ($params['custom_select'] != '') {
            $sel_str = "{$params['custom_select']}";
        } else {
            $sel_str = "*";
        }

        // filters
        $filter_arr = [];

        if ($params['api_integration_id'] > 0) {
            $filter_arr[] = "AND agen_api_int.`api_integration_id` = {$params['api_integration_id']}";
        }

        if (is_numeric($params['active'])) {
            $filter_arr[] = "AND agen_api_int.`active` = {$params['active']}";
        }

        if ($params['agency_id'] > 0) {
            $filter_arr[] = "AND agen_api_int.`agency_id` = {$params['agency_id']}";
        }

        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE agen_api_int.`api_integration_id` > 0 " . implode(" ", $filter_arr);
        }


        // joins
        $join_table_imp = '';
        $join_table_str = [];
        if (count($params['join_table']) > 0) {

            foreach ($params['join_table'] as $join_table) {
                switch ($join_table) {
                    case 'jobs':
                        $join_table_str[] = 'LEFT JOIN `agency` AS a ON agen_api_int.`agency_id` = a.`agency_id`';
                        break;
                }
            }
        }

        $join_table_imp = implode(" ", $join_table_str);

        if ($params['custom_joins'] != '') {
            $custom_joins_str = $params['custom_joins'];
        }

        //custom filter query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        //custom sort query
        if ($params['sort_query'] != '') {
            $sort_str = "ORDER BY {$params['sort_query']}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
		SELECT {$sel_str}
		FROM `agency_api_integration` AS agen_api_int
		LEFT JOIN `agency_api` AS agen_api ON agen_api_int.`connected_service` = agen_api.`agency_api_id`
		{$join_table_imp}
		{$custom_joins_str}
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";


        if ($params['display_echo'] == 1) {
            echo $sql;
        }

        return mysql_query($sql);
    }

    function get_agency_api_tokens($params) {

        // custom select query
        if ($params['custom_select'] != '') {
            $sel_str = "{$params['custom_select']}";
        } else {
            $sel_str = "*";
        }

        // filters
        $filter_arr = [];

        if ($params['agency_api_token_id'] > 0) {
            $filter_arr[] = "AND agen_api_tok.`agency_api_token_id` = {$params['agency_api_token_id']}";
        }

        if ($params['api_id'] > 0) {
            $filter_arr[] = "AND agen_api_tok.`api_id` = {$params['api_id']}";
        }

        if ($params['agency_id'] > 0) {
            $filter_arr[] = "AND agen_api_tok.`agency_id` = {$params['agency_id']}";
        }

        if ($params['active'] > 0) {
            $filter_arr[] = "AND agen_api_tok.`active` = {$params['active']}";
        }


        // combine all filters
        if (count($filter_arr) > 0) {
            $filter_str = " WHERE agen_api_tok.`agency_api_token_id` > 0 " . implode(" ", $filter_arr);
        }

        //custom filter query
        if ($params['custom_filter'] != '') {
            $custom_filter_str = $params['custom_filter'];
        }

        // joins
        $join_table_imp = '';
        $join_table_str = [];
        if (count($params['join_table']) > 0) {

            foreach ($params['join_table'] as $join_table) {
                switch ($join_table) {
                    case 'jobs':
                        $join_table_str[] = 'LEFT JOIN `agency` AS a ON agen_api_tok.`agency_id` = a.`agency_id`';
                        break;
                }
            }
        }

        $join_table_imp = implode(" ", $join_table_str);

        if ($params['custom_joins'] != '') {
            $custom_joins_str = $params['custom_joins'];
        }

        //custom sort query
        if ($params['sort_query'] != '') {
            $sort_str = "ORDER BY {$params['sort_query']}";
        }


        // GROUP BY
        if ($params['group_by'] != '') {
            $group_by_str = "GROUP BY {$params['group_by']}";
        }


        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }


        $sql = "
		SELECT {$sel_str}
		FROM `agency_api_tokens` AS agen_api_tok
		LEFT JOIN `agency_api` AS agen_api ON agen_api_tok.`api_id` = agen_api.`agency_api_id`
		{$join_table_imp}
		{$custom_joins_str}
		{$filter_str}
		{$custom_filter_str}
		{$group_by_str}
		{$sort_str}
		{$pag_str}
		";


        if ($params['display_echo'] == 1) {
            echo $sql;
        }

        return mysql_query($sql);
    }

    public function remove_space($string) {
        return str_replace(' ', '', $string);
    }

    public function remove_whitespace($string) {
        return preg_replace('/\s+/', '', $string);
    }

    private function getFDynamicLink_ApiKey($country_id) {
        if ((int) $country_id === 1) {
            return "AIzaSyB88Wb3cS0dxCVED3a7T5pj_Sf1vfvHYlY";
        } elseif ((int) $country_id === 2) {
            return "AIzaSyA9EQfCyG6NprM6ws1JXoh83DDKBkWoBjY";
        }
    }

    private function getFDynamicLink_DomainUriPrefix($country_id) {
        if ((int) $country_id === 1) {
            return "https://url.sats.com.au";
        } elseif ((int) $country_id === 2) {
            return "https://url.sats.co.nz";
        }
    }

    public function getFDynamicLink($country_id, $link) {
        $params = [
            "dynamicLinkInfo" => [
                "domainUriPrefix" => $this->getFDynamicLink_DomainUriPrefix($country_id),
                "link" => $link
            ]
        ];

        $url = "https://firebasedynamiclinks.googleapis.com/v1/shortLinks?key=" . $this->getFDynamicLink_ApiKey($country_id);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params))
        );
        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

        $dynamic_link = json_decode($result, true);
        if (isset($dynamic_link['shortLink'])) {
            return $dynamic_link['shortLink'];
        } else {
            return false;
        }
    }

    public function display_job_icons($params){

        $icons_str = '<img src="/images/serv_img/'.getServiceIcons($params['service_type']).'" />';

        // if job type is 'IC Upgrade' show IC upgrade icon
        if( $params['job_type'] == 'IC Upgrade' ){
            $icons_str .= '<img src="/images/serv_img/upgrade_colored.png" class="j_icons" />';
        }

        if( $params['job_type'] == '240v Rebook' ){
            $icons_str .= '<img src="/images/240v_colored.png" class="j_icons" />';
        }

        if( $params['job_type'] == 'Fix or Replace' ){
            $icons_str .= '<img src="/images/fr_colored.png" class="j_icons" />';
        }

        return $icons_str;

    }

    public function display_job_icons_v2($params){

        $icons_str = null;

        $job_id = $params['job_id'];

        if( $job_id > 0 ){

            // get jobs data
            $job_sql = mysql_query("
            SELECT
                j.`id` AS jid,
                j.`service` AS jservice,
                j.`job_type`,
                j.`assigned_tech`,
                j.`is_eo`,

                ajt.`type` AS sevice_type_name,

                p.`state` AS p_state,
                p.`service_garage`
            FROM `jobs` AS j            
            LEFT JOIN `alarm_job_type` AS ajt ON j.`service` = ajt.`id`
            LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
            WHERE j.`id` = {$job_id}
            ");
            $job_row = mysql_fetch_array($job_sql);

            $icons_str .= '<img src="/images/serv_img/'.getServiceIcons($job_row['jservice']).'" class="j_icons" />';

            // if job type is 'IC Upgrade' show IC upgrade icon
            if( $job_row['job_type'] == 'IC Upgrade' ){
                $icons_str .= '<img src="/images/serv_img/upgrade_colored.png" class="j_icons" />';
            }

            if( $job_row['job_type'] == '240v Rebook' || $job_row['is_eo'] == 1 ){
                $icons_str .= '<img src="/images/240v_colored.png" class="j_icons" />';
            }

            if( $job_row['job_type'] == 'Fix or Replace' ){
                $icons_str .= '<img src="/images/fr_colored.png" class="j_icons" />';
            }            

            /*
            // empty, OS and UB
            if( $job_row['assigned_tech'] == "" || $job_row['assigned_tech'] == 1 || $job_row['assigned_tech'] == 2 ){
                $icons_str .= '<img src="/images/no_tech.png" class="j_icons" />';
            }
            */

            return $icons_str;

        }

    }

    function get_fn_agencies(){

        // First National script
        if ( CURRENT_COUNTRY == 1 ) { // AU

            if ( IS_PRODUCTION == 1 ) { // LIVE

                // 4718 - First National Sarina
                $fn_agency_main = 4718;
                // 4318 - First National Mackay
                // 4724 - First National Nebo
                $fn_agency_sub = array(4318,4724);
                $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            } else { // DEV

                // 4188 - First National Sarina
                $fn_agency_main = 4188;
                // 4186 - First National Mackay
                // 4187 - First National Nebo
                $fn_agency_sub = array(4186,4187);
                $fn_agency_sub_imp = implode(",",$fn_agency_sub);

            }

        }

        return array(
            'fn_agency_main' => $fn_agency_main,
            'fn_agency_sub' => $fn_agency_sub
        );

    }

    function get_vision_agencies(){

        // First National script
        if ( CURRENT_COUNTRY == 1 ) { // AU

            if ( IS_PRODUCTION == 1 ) { // LIVE

                // 4637 - Vision Real Estate Mackay
                $vision_agency_main = 4637;
                // 6782 - Vision Real Estate Dysart
                $vision_agency_sub = array(6782);
                $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            } else { // DEV

                // 4192 - Vision Real Estate Mackay
                $vision_agency_main = 4192;
                // 4193 - Vision Real Estate Dysart
                $vision_agency_sub = array(4193);
                $vision_agency_sub_imp = implode(",",$vision_agency_sub);

            }

        }

        return array(
            'vision_agency_main' => $vision_agency_main,
            'vision_agency_sub' => $vision_agency_sub
        );

    }


    // set financial year here instead of config or init files, bec those files are not dynamic and cannnot be pushed live
    public function get_accounts_financial_year(){

        // accounts date filter
        if( CURRENT_COUNTRY == 1 ){ // AU
            $accounts_financial_year = '2020-06-01';
        }else if( CURRENT_COUNTRY == 2 ){ // NZ
            $accounts_financial_year = '2019-12-01';
        }

        return $accounts_financial_year;

    }


    // check if property has money owing and needs to verify paid
    public function check_verify_paid($property_id){

        $accounts_financial_year = $this->get_accounts_financial_year();

        $job_sql_str = "
        SELECT COUNT(j.`id`) AS jcount
        FROM `jobs` AS j
        WHERE j.`property_id` = {$property_id}
        AND j.`status` = 'Completed'
        AND j.`invoice_balance` > 0
        AND (
            j.`date` >= '{$accounts_financial_year}'  OR
            j.`unpaid` = 1
        )
        ";

        $job_sql = mysql_query($job_sql_str);
        $job_row = mysql_fetch_array($job_sql);
        $job_count = $job_row['jcount'];

        if( $job_count > 0 ){
            return true;
        }else{
            return false;
        }

    }

    // get postcode from new `postcode` table
    public function get_postcodes($params) {

        if( $params['sel_query'] != '' ){
            $sel_sql_str = $params['sel_query'];
        }else{
            $sel_sql_str = "*";
        }

        // filters
        $filter_arr = []; // clear        

        // filter by Region
        if ( $params['region_id'] > 0 ) {
            $filter_arr[] = "AND r.`regions_id` = {$params['region_id']}";
        }

        // filter by Sub region
        if ( $params['sub_region_id'] > 0 ) {
            $filter_arr[] = "AND pc.`sub_region_id` = {$params['sub_region_id']}";
        }

        // filter by Sub region array
        if ( count($params['sub_region_id_arr']) > 0 ) {            
            $sub_region_id_imp = implode(",",$params['sub_region_id_arr']);
            $filter_arr[] = "AND pc.`sub_region_id` IN({$sub_region_id_imp})";
        }

        // filter by Sub region implode
        if ( $params['sub_region_id_imp'] != '' ) {                        
            $filter_arr[] = "AND pc.`sub_region_id` IN({$params['sub_region_id_imp']})";
        }

        // filter by Sub region
        if ( $params['postcode'] != '' ) {
            $filter_arr[] = "AND pc.`postcode` = {$params['postcode']}";
        }

        // filter by Sub region
        if ( $params['deleted'] != '' ) {
            $filter_arr[] = "AND pc.`deleted` = {$params['deleted']}";
        }

        // custom filter
        if ( $params['custom_where'] != '' ) {
            $filter_arr[] = $params['custom_where'];
        }

        // combine all filters
        $filter_arr_imp = null;
        if (count($filter_arr) > 0) {
            $filter_arr_imp = implode(' ', $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str = " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_sql_str}
			FROM `postcode` AS pc
			LEFT JOIN `sub_regions` AS sr ON pc.`sub_region_id` = sr.`sub_region_id`
            LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
            WHERE pc.`id` > 0
			{$filter_arr_imp}
			{$sort_str}
			{$pag_str}
		";

        if( $params['display_query'] == 1 ){
            echo $sql;
        }

        return mysql_query($sql);

    }


    // get postcode from new `postcode` table
    public function get_sub_region($params) {

        if( $params['sel_query'] != '' ){
            $sel_sql_str = $params['sel_query'];
        }else{
            $sel_sql_str = "*";
        }

        // filters
        $filter_arr = []; // clear        

        // filter by Region
        if ( $params['region_id'] > 0 ) {
            $filter_arr[] = "AND r.`regions_id` = {$params['region_id']}";
        }

        // filter by Sub region
        if ( $params['sub_region_id'] > 0 ) {
            $filter_arr[] = "AND sr.`sub_region_id` = {$params['sub_region_id']}";
        }

        // filter by Sub region array
        if ( count($params['sub_region_id_arr']) > 0 ) {            
            $sub_region_id_imp = implode(",",$params['sub_region_id_arr']);
            $filter_arr[] = "AND sr.`sub_region_id` IN({$sub_region_id_imp})";
        }

        // filter by Sub region implode
        if ( $params['sub_region_id_imp'] != '' ) {                        
            $filter_arr[] = "AND sr.`sub_region_id` IN({$params['sub_region_id_imp']})";
        }

        // filter by Sub region
        if ( $params['active'] != '' ) {
            $filter_arr[] = "AND sr.`active` = {$params['active']}";
        }

        // filter by State
        if ( $params['state'] != '' ) {
            $filter_arr[] = "AND r.`region_state` = '{$params['state']}'";
        }

        // custom filter
        if ( $params['custom_where'] != '' ) {
            $filter_arr[] = $params['custom_where'];
        }

        // combine all filters
        $filter_arr_imp = null;
        if (count($filter_arr) > 0) {
            $filter_arr_imp = implode(' ', $filter_arr);
        }

        // sort
        if ($params['sort_list'] != "") {
            if ($params['sort_list']['order_by'] != "" && $params['sort_list']['sort'] != '') {
                $sort_str = " ORDER BY {$params['sort_list']['order_by']} {$params['sort_list']['sort']} ";
            }
        }

        // paginate
        if ($params['paginate'] != "") {
            if (is_numeric($params['paginate']['offset']) && is_numeric($params['paginate']['limit'])) {
                $pag_str = " LIMIT {$params['paginate']['offset']}, {$params['paginate']['limit']} ";
            }
        }

        $sql = "
			SELECT {$sel_sql_str}
			FROM `sub_regions` AS sr
            LEFT JOIN `regions` AS r ON sr.`region_id` = r.`regions_id`
            WHERE sr.`sub_region_id` > 0
			{$filter_arr_imp}
			{$sort_str}
			{$pag_str}
		";

        if( $params['display_query'] == 1 ){
            echo $sql;
        }

        return mysql_query($sql);

    }



    public function mark_is_eo($job_id, $year=null) {  
        
        if( $job_id > 0 ){

            // copied from findExpired240vAlarm
            $year2 = ( $year != '' ) ? $year : date("Y");
            $alarm_sql = mysql_query("
                SELECT COUNT(al.`alarm_id`) AS al_count
                FROM `alarm` AS al
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                WHERE al.`job_id` = {$job_id}
                AND al.`expiry` <= '{$year2}'
                AND al.`ts_discarded` = 0
                AND al_pwr.`is_240v` = 1
            ");
            $alarm_row = mysql_fetch_object($alarm_sql);
            $alarm_count = $alarm_row->al_count;

            // FR - 240v check, find 240v alarms even if not expired
            $alarm_sql2 = mysql_query("
                SELECT COUNT(al.`alarm_id`) AS al_count
                FROM `alarm` AS al
                LEFT JOIN `jobs` AS j ON al.`job_id` = j.`id`
                LEFT JOIN `alarm_pwr` AS al_pwr ON al.`alarm_power_id` = al_pwr.`alarm_pwr_id`
                WHERE al.`job_id` = {$job_id}	
                AND j.`job_type` = 'Fix or Replace'		
                AND al.`ts_discarded` = 0
                AND al_pwr.`is_240v` = 1
            ");
            $alarm_row2 = mysql_fetch_object($alarm_sql2);
            $alarm_count2 = $alarm_row2->al_count;

            if ( $alarm_count > 0 || $alarm_count2 > 0 ) {
            
                // set this job as EO = for electrician only
                mysql_query("
                UPDATE `jobs`
                SET `is_eo` = 1
                WHERE `id` = {$job_id}
                ");
                
            } 

        }        

    }


    public function insert_job_markers($job_id,$new_job_type){

        $today = date('Y-m-d H:i:s');
        if( $job_id > 0 ){

            // get current job type
            $job_sql = mysql_query("
            SELECT `job_type`
            FROM `jobs`        
            WHERE `id` = {$job_id}
            ");
            $job_row = mysql_fetch_object($job_sql);

            if( $new_job_type != $job_row->job_type ){
                
                // determine what kind of job type change
                if( $job_row->job_type != '240v Rebook' && $new_job_type == '240v Rebook' ){
                    $job_type_change = 1;
                }else if ( $job_row->job_type == 'Change of Tenancy' && $new_job_type == 'Yearly Maintenance' ){
                    $job_type_change = 2;
                }
                

                // log this change, using ben's `job_markers` table
                mysql_query("
                INSERT INTO 
                `job_markers` (
                    `job_id`,
                    `job_type_change`,
                    `date`
                )
                VALUES(
                    {$job_id},
                    {$job_type_change},
                    '{$today}'
                )
                ");               

            }

        }                

    }


    public function display_orca_or_cavi_alarms($agency_id){

        if( $agency_id > 0 ){

            $has_orca_0_price = false;
            $has_cavi = false;
            $alarm_make = null;

            $agen_al_sql_str = "
                SELECT aa.`agency_alarm_id`, aa.`price`, ap.`alarm_make`
                FROM `agency_alarms` AS aa
                LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                WHERE aa.`agency_id` = {$agency_id}
                AND ap.`active` = 1
            ";
            $agen_al_sql = mysql_query($agen_al_sql_str);        
            while( $agen_al_row = mysql_fetch_object($agen_al_sql)  ){

                if( $agen_al_row->alarm_make == 'Orca' && $agen_al_row->price == 0 ){
                    $has_orca_0_price = true;
                }

                if( $agen_al_row->alarm_make == 'Cavius' ){
                    $has_cavi = true;
                }

            }

            if( $has_orca_0_price == true && $has_cavi == false ){
                $alarm_make = "Orca";
            }else{
                $alarm_make = "Cavius";
            }

            return $alarm_make;


        }        

    }


    public function display_free_emerald_or_paid_brooks( $agency_id, $use_short = false ){

        if( $agency_id > 0 ){

            // get state
            $agency_sql_str = "
            SELECT `state`
            FROM `agency`
            WHERE `agency_id` = {$agency_id}
            ";
            $agency_sql = mysql_query($agency_sql_str);
            $agency_row = mysql_fetch_object($agency_sql);  
            
            $alarm_make = null;

            // PAID alarms, brooks    
            // find 240v or 3vLi  
            $agen_al_sql_str = "
                SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                FROM `agency_alarms` AS aa
                LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                WHERE aa.`agency_id` = {$agency_id}  
                AND ap.`alarm_pwr_id` IN(2,7)           
                AND ap.`active` = 1
            ";        
            $agen_al_sql = mysql_query($agen_al_sql_str); 
            $agen_al_row = mysql_fetch_object($agen_al_sql);
            $brooks_count = $agen_al_row->aa_count;                                          


            if( $brooks_count > 0 ){ // found brooks alarm
                
                $alarm_make = "Brooks";

            }else{ // else emerald

                // FREE alarms, emerald
                if( $agency_row->state == 'NSW' || $agency_row->state == 'ACT' ){
                
                    // find 9v(EP) or 240v(EP)
                    $agen_al_sql_str = "
                        SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                        FROM `agency_alarms` AS aa
                        LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                        WHERE aa.`agency_id` = {$agency_id}        
                        AND ap.`alarm_pwr_id` IN(18,21)
                        AND aa.`price` = 0
                        AND ap.`active` = 1
                    ";        
                    $agen_al_sql = mysql_query($agen_al_sql_str); 
                    $agen_al_row = mysql_fetch_object($agen_al_sql);
                    $free_emerald_count = $agen_al_row->aa_count;

                }else if( $agency_row->state == 'SA' ){
                    
                    // find 3VLi(EP) or 240v(EP)
                    $agen_al_sql_str = "
                        SELECT COUNT(aa.`agency_alarm_id`) AS aa_count
                        FROM `agency_alarms` AS aa
                        LEFT JOIN `alarm_pwr` AS ap ON aa.`alarm_pwr_id` = ap.`alarm_pwr_id`
                        WHERE aa.`agency_id` = {$agency_id}
                        AND ap.`alarm_pwr_id` IN(19,21)
                        AND aa.`price` = 0
                        AND ap.`active` = 1
                    ";        
                    $agen_al_sql = mysql_query($agen_al_sql_str); 
                    $agen_al_row = mysql_fetch_object($agen_al_sql);
                    $free_emerald_count = $agen_al_row->aa_count;

                }

                if( $free_emerald_count > 0 ){

                    if( $use_short == true ){
                        $alarm_make = "Emerald";
                    }else{
                        $alarm_make = "Emerald Planet";
                    }

                }                               

            }

            return $alarm_make;


        }        

    }

    public function job_price_breakdown($params){

        $job_id = $params['job_id'];
        $service_type = $params['service_type'];
        $property_id = $params['property_id'];
    
        $dynamic_price = 0;
        $ret_arr = [];

        $price_variation_total = 0;
        $price_variation_total_str = null;

        // get jobs data
        $job_sql = mysql_query("
        SELECT 
            j.`job_price`,
            j.`date`,
            j.`invoice_amount`,
            a.`agency_id`
        FROM `jobs` AS j
        LEFT JOIN `property` AS p ON j.`property_id` = p.`property_id`
		LEFT JOIN `agency` AS a ON p.`agency_id` = a.`agency_id`
        WHERE j.`id` = {$job_id}
        ");
        $job_row = mysql_fetch_object($job_sql);
        $dynamic_price = $job_row->invoice_amount;

        // get jobs variation
        $jv_sql = mysql_query("
        SELECT 
            `type`,
            `amount`
        FROM `job_variation`
        WHERE `job_id` = {$job_id}
        AND `active` = 1
        ");

        if( mysql_num_rows($jv_sql) > 0 ){

            while( $jv_row = mysql_fetch_object($jv_sql) ){  
            
                // calculation in reverse
                if( $jv_row->type == 1 ){ // discount
    
                    $price_variation_total+=$jv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($jv_row->amount,2)." Discount";
    
                }else{ // surcharge
    
                    $price_variation_total-=$jv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($jv_row->amount,2)." Surcharge";
    
                }            
        
            }  

        }
                      
                            
        // get agency price variation
        $apv_sql_str = "
        SELECT 
            apv.`type` AS apv_type,
            apv.`amount`,
            apv.`scope`,
    
            ajt.`type` AS ajt_type,
            ajt.`short_name`
        FROM `agency_price_variation` AS apv
        LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
        WHERE apv.`agency_id` = {$job_row->agency_id}    
        AND (
            apv.`scope` = 0 OR
            apv.`scope` = {$service_type}
        )
        AND (
            apv.`active` = 1 OR 
            DATE(apv.`deleted_ts`) > '{$job_row->date}'
        )
        AND (
            apv.`expiry` >= '{$job_row->date}'   
            OR apv.`expiry` IS NULL
        )   
        AND DATE(apv.`created_date`) <= '{$job_row->date}'
        ";
        $apv_sql = mysql_query($apv_sql_str);   
        
        if( mysql_num_rows($apv_sql) > 0 ){

            while( $apv_row = mysql_fetch_object($apv_sql) ){  
            
                $service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;
               
                // calculation in reverse
                if( $apv_row->apv_type == 1 ){ // discount
    
                    $price_variation_total+=$apv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($apv_row->amount,2)." {$service_type_str}Discount";
    
                }else{ // surcharge
    
                    $price_variation_total-=$apv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($apv_row->amount,2)." {$service_type_str}Surcharge";
    
                }            
        
            }

        }      
        

        // get property variation
        $pv_sql_str = "
        SELECT 
            apv.`type` AS apv_type,
            apv.`amount`,
            apv.`scope`,
    
            ajt.`type` AS ajt_type,
            ajt.`short_name`
        FROM `property_variation` AS pv        
        LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$property_id} )
        LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
        WHERE apv.`agency_id` = {$job_row->agency_id}    
        AND (
            apv.`scope` = 1 OR
            apv.`scope` = {$service_type}
        )
        AND (
            pv.`active` = 1 OR
            DATE(pv.`deleted_ts`) > '{$job_row->date}'
        )
        AND pv.`date_applied` <= '{$job_row->date}'
        AND (
            apv.`active` = 1 OR 
            DATE(apv.`deleted_ts`) > '{$job_row->date}'
        )
        AND (
            apv.`expiry` >= '{$job_row->date}'   
            OR apv.expiry IS NULL
        ) 
        AND DATE(apv.`created_date`) <= '{$job_row->date}'
        ";
        $pv_sql = mysql_query($pv_sql_str);  

        if( mysql_num_rows($pv_sql) > 0 ){

            while( $pv_row = mysql_fetch_object($pv_sql) ){  
            
                $service_type_str = ( $pv_row->scope >= 2 )?"{$pv_row->short_name} Service ":null;
                     
                // calculation in reverse
                if( $pv_row->apv_type == 1 ){ // discount
    
                    $price_variation_total+=$pv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($pv_row->amount,2)." {$service_type_str}Discount";
    
                }else{ // surcharge
    
                    $price_variation_total-=$pv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($pv_row->amount,2)." {$service_type_str}Surcharge";
    
                }            
        
            }
            
        }        
            
        $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
    
        $final_total_str = ( $price_variation_total )?'$'.number_format($dynamic_price_total,2):null;
    
        $dynamic_price_text = '$'.number_format($dynamic_price,2);
        $price_text = '$'.number_format($dynamic_price_total,2);
        $price_breakdown_text = $final_total_str.$price_variation_total_str.' = $'.number_format($dynamic_price,2);
    
        $ret_arr = array(
            'dynamic_price' => $dynamic_price,
            'price_variation_total' => $price_variation_total,
            'dynamic_price_total' => $dynamic_price_total,
            'dynamic_price_text' => $dynamic_price_text,
            'price_text' => $price_text,
            'price_breakdown_text' => $price_breakdown_text                  
        );

        return $ret_arr;
    
    }


    public function get_property_price_variation($params){

        $service_type = $params['service_type'];
        $property_id = $params['property_id'];
        $no_dis_sur = $params['no_dis_sur'];

        $today = date('Y-m-d');
    
        // get dynamic price
        $dynamic_price = 0;
        $ret_arr = [];

        $price_variation_total = 0;
        $price_variation_total_str = null;

        // get property data
        $prop_sql = mysql_query("
        SELECT 
            `agency_id`,
            `holiday_rental`,
            `state`
        FROM `property`
        WHERE `property_id` = {$property_id}
        ");
        $prop_row = mysql_fetch_object($prop_sql);
        $agency_id = $prop_row->agency_id;             
    
        // get price increase excluded agency
        $piea_sql = mysql_query("
        SELECT *
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$agency_id}
        AND (
            `exclude_until` >= '{$today}' OR
            `exclude_until` IS NULL
        )
        ");     
        
        // get short term service price
        $stsp_sql = mysql_query("
        SELECT *
        FROM `short_term_service_price`
        WHERE `service_type` = {$service_type}
        AND `state` = '{$prop_row->state}'
        ");       
                            
        if( mysql_num_rows($piea_sql) > 0 ){ // agency is price increase excluded

            // get property services
            $ps_sql = mysql_query("
            SELECT *
            FROM `property_services`
            WHERE `alarm_job_type_id` = {$service_type}
            AND `service` = 1
            AND `property_id` = {$property_id}
            ");

            if( mysql_num_rows($ps_sql) > 0 ){

                $ps_row = mysql_fetch_object($ps_sql);           
                $dynamic_price = $ps_row->price;
            
            }else{

                // get agency services
                $agen_serv_sql = mysql_query("
                SELECT *
                FROM `agency_services`
                WHERE `service_id` = {$service_type}
                AND `agency_id` = {$agency_id}
                ");
                $agen_serv_row = mysql_fetch_object($agen_serv_sql);                                
                $dynamic_price = $agen_serv_row->price;                
                
            }

            $dynamic_price_total = $dynamic_price; // no added price variation
    
        }else if( $prop_row->holiday_rental == 1 && mysql_num_rows($stsp_sql) > 0 ){ // short term service price
            
            $stsp_row = mysql_fetch_object($stsp_sql);
            $dynamic_price = $stsp_row->price;
            $dynamic_price_total = $dynamic_price; // no added price variation
            
        }else{ // agency and property variation        

            // get agency specific service price
            $assp_sql = mysql_query("
            SELECT *
            FROM `agency_specific_service_price`
            WHERE `service_type` = {$service_type}
            AND `agency_id` = {$agency_id}
            "); 
            $assp_row = mysql_fetch_object($assp_sql);  

            // get agency default service price
            $adsp_sql = mysql_query("
            SELECT *
            FROM `agency_default_service_price`
            WHERE `service_type` = {$service_type}
            "); 
            $adsp_row = mysql_fetch_object($adsp_sql);  

            if (mysql_num_rows($assp_sql) > 0) {
                $dynamic_price = $assp_row->price;
            } else {
                $dynamic_price = $adsp_row->price;
            }

            // get agency price variation
            $apv_sql = mysql_query("
            SELECT 
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `agency_price_variation` AS apv
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 0 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.expiry >= '{$today}'
                OR apv.expiry IS NULL
            )
            AND apv.`active` = 1
            ");                  
        
            while( $apv_row = mysql_fetch_object($apv_sql) ){  
                
                $service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;
                            
                if( $apv_row->apv_type == 1 ){ // discount
                    $price_variation_total-=$apv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($apv_row->amount,2)." {$service_type_str}Discount";
                }else{ // surcharge
                    $price_variation_total+=$apv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($apv_row->amount,2)." {$service_type_str}Surcharge";
                }            
        
            }

            // get property variation
            $pv_sql = mysql_query("
            SELECT 
                apv.`type` AS apv_type,
                apv.`amount`,
                apv.`scope`,
        
                ajt.`type` AS ajt_type,
                ajt.`short_name`
            FROM `property_variation` AS pv        
            LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$property_id} )
            LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
            WHERE apv.`agency_id` = {$agency_id}    
            AND (
                apv.`scope` = 1 OR
                apv.`scope` = {$service_type}
            )
            AND (
                apv.expiry >= '{$today}'
                OR apv.expiry IS NULL
            )
            AND apv.`active` = 1
            AND pv.`active` = 1
            ");  
        
            while( $pv_row = mysql_fetch_object($pv_sql) ){  
                
                $service_type_str = ( $pv_row->scope >= 2 )?"{$pv_row->short_name} Service ":null;
                            
                if( $pv_row->apv_type == 1 ){ // discount
                    $price_variation_total-=$pv_row->amount;
                    $price_variation_total_str .= " - \$".number_format($pv_row->amount,2)." {$service_type_str}Discount";
                }else{ // surcharge
                    $price_variation_total+=$pv_row->amount;
                    $price_variation_total_str .= " + \$".number_format($pv_row->amount,2)." {$service_type_str}Surcharge";
                }            
        
            }
                
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
    
        }
    
        $final_total_str = ( $price_variation_total )?' = $'.number_format($dynamic_price_total,2):null;
    
        $dynamic_price_text = '$'.number_format($dynamic_price,2);
        $price_text = '$'.number_format($dynamic_price_total,2);
        $price_breakdown_text = '$'.number_format($dynamic_price,2).$price_variation_total_str.$final_total_str;
    
        return $ret_arr = array(
            'dynamic_price' => $dynamic_price,
            'price_variation_total' => $price_variation_total,
            'dynamic_price_total' => $dynamic_price_total,
            'dynamic_price_text' => $dynamic_price_text,
            'price_text' => $price_text,
            'price_breakdown_text' => $price_breakdown_text                  
        );
    
    }


    public function free_alarms($alarm_price,$agency_id){

        $today = date('Y-m-d');

        // get price increase excluded agency
        $piea_sql = mysql_query("
        SELECT *
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$agency_id}
        AND (
            `exclude_until` >= '{$today}' OR
            `exclude_until` IS NULL
        )
        ");    
        
        if( mysql_num_rows($piea_sql) > 0 ){ // agency is excluded to price increase
            return $alarm_price;
        }else{ // price increase, alarm price is 0
            return 0;
        }

    }

    public function get_agency_price_variation($params){

        $service_type = $params['service_type'];
        $agency_id = $params['agency_id'];
    
        $today = date('Y-m-d');
    
        // get dynamic price
        $dynamic_price = 0;
        $ret_arr = [];
    
        // get price increase excluded agency
        $piea_sql = mysql_query("
        SELECT *
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$agency_id}
        AND (
            `exclude_until` >= '{$today}' OR
            `exclude_until` IS NULL
        )
        ");        
    
        // get agency specific service price
        $assp_sql = mysql_query("
        SELECT *
        FROM `agency_specific_service_price`
        WHERE `service_type` = {$service_type}
        AND `agency_id` = {$agency_id}
        ");
        
        // get agency default service price
        $adsp_sql = mysql_query("
        SELECT *
        FROM `agency_default_service_price`
        WHERE `service_type` = {$service_type}
        ");  
    
        // get agency price variation
        $apv_sql = mysql_query("
        SELECT 
            apv.`type` AS apv_type,
            apv.`amount`,
            apv.`scope`,
    
            ajt.`type` AS ajt_type,
            ajt.`short_name`
        FROM `agency_price_variation` AS apv
        LEFT JOIN `alarm_job_type` AS ajt ON ( apv.`scope` = ajt.`id` AND apv.`scope` >= 2 )
        WHERE apv.`agency_id` = {$agency_id}    
        AND (
            apv.`scope` = 0 OR
            apv.`scope` = {$service_type}
        )
        AND (
            apv.expiry >= '{$today}'
            OR apv.expiry IS NULL
        )
        AND apv.`active` = 1
        ");  
    
        $price_variation_total = 0;
        $price_variation_total_str = null;
    
        while( $apv_row = mysql_fetch_object($apv_sql) ){  
            
            $service_type_str = ( $apv_row->scope >= 2 )?"{$apv_row->short_name} Service ":null;
                        
            if( $apv_row->apv_type == 1 ){ // discount
                $price_variation_total-=$apv_row->amount;
                $price_variation_total_str .= " - \$".number_format($apv_row->amount,2)." {$service_type_str}Discount";
            }else{ // surcharge
                $price_variation_total+=$apv_row->amount;
                $price_variation_total_str .= " + \$".number_format($apv_row->amount,2)." {$service_type_str}Surcharge";
            }            
    
        }  
                            
        if( mysql_num_rows($piea_sql) > 0 ){ // price increase excluded agency IF block
            
            // get agency services
            $agen_serv_sql = mysql_query("
            SELECT *
            FROM `agency_services`
            WHERE `service_id` = {$service_type}
            AND `agency_id` = {$agency_id}
            ");
            $agen_serv_row = mysql_fetch_object($agen_serv_sql);                
            
            $dynamic_price = $agen_serv_row->price;
            $dynamic_price_total = $dynamic_price; // no added price variation
    
        }else if( mysql_num_rows($assp_sq) > 0 ){ // agency specific service price IF block
    
            $assp_row = mysql_fetch_object($assp_sql);
            $dynamic_price = $assp_row->price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
            
        }else if( mysql_num_rows($adsp_sql) > 0 ){ // agency default service price IF block
    
            $adsp_row = mysql_fetch_object($adsp_sql);    
            $dynamic_price = $adsp_row->price;
            $dynamic_price_total = $dynamic_price+$price_variation_total; // add variations
    
        }
    
        $final_total_str = ( $price_variation_total != 0 )?' = $'.number_format($dynamic_price_total,2):null;
    
        $dynamic_price_text = '$'.number_format($dynamic_price,2);
        $price_text = '$'.number_format($dynamic_price_total,2);
        $price_breakdown_text = '$'.number_format($dynamic_price,2).$price_variation_total_str.$final_total_str;
    
        return $ret_arr = array(
            'dynamic_price' => $dynamic_price,
            'price_variation_total' => $price_variation_total,
            'dynamic_price_total' => $dynamic_price_total,
            'dynamic_price_text' => $dynamic_price_text,
            'price_text' => $price_text,
            'price_breakdown_text' => $price_breakdown_text                  
        );
    
    }

    public function get_quotes_new_name($alarm_pwr_id){

        $sel = "
            SELECT ap.`alarm_make`, qa.`title`
            FROM `alarm_pwr` as ap
            LEFT JOIN `quote_alarms` AS qa ON ap.`alarm_pwr_id` = qa.`alarm_pwr_id`
            WHERE ap.`alarm_pwr_id` = $alarm_pwr_id
        ";
        $sql = mysql_query($sel); 
        $row = mysql_fetch_array($sql);

        if( $row['title'] != "" ){
            return $row['title'];
        }else{
            return $row['alarm_make'];
        }

    }


    public function update_page_total($params){

        $page = $params['page'];
        $total = $params['total'];

        // check if page total exist
        $page_to_sql = mysql_query("
        SELECT COUNT(`page_total_id`) AS page_tol_count
        FROM `page_total`
        WHERE `page` = '{$page}'
        ");
        $page_to_row = mysql_fetch_object($page_to_sql);

        if( $page_to_row->page_tol_count > 0 ){ // it exist

           // update
           mysql_query("
           UPDATE `page_total`
           SET
               `total` = {$total}
           WHERE `page` = '{$page}'
           ");

        }else{

           // insert
           mysql_query("
           INSERT INTO
           `page_total`(
               `page`,
               `total`
           )
           VALUES(
               '{$page}',
               {$total}
           )
           ");

        }

    }


    // also update on CI system_model.php -- start
    public function check_if_job_created_before_agency_exclusion_expired($obj){

        $sql = mysql_query("
        SELECT COUNT(`id`) AS jcount
        FROM `price_increase_excluded_agency`
        WHERE `agency_id` = {$obj->agency_id}
        AND `exclude_until` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        ");
        $row = mysql_fetch_object($sql);
        
        if( $row->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_agency_level_variation_expired($obj){

        $sql = mysql_query("
        SELECT COUNT(`id`) AS jcount
        FROM `agency_price_variation`
        WHERE `agency_id` = {$obj->agency_id}
        AND `expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND `scope` = 0
        AND `active` = 1
        ");
        $row = mysql_fetch_object($sql);
        
        if( $row->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_property_level_variation_expired($obj){

        $sql = mysql_query("
        SELECT COUNT(pv.`id`) AS jcount
        FROM `property_variation` AS pv        
		LEFT JOIN `agency_price_variation` AS apv ON ( pv.`agency_price_variation` = apv.`id` AND pv.`property_id` = {$obj->property_id} )
        WHERE apv.`expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND apv.`scope` = 1
        AND apv.`active` = 1
        AND pv.`active` = 1
        ");
        $row = mysql_fetch_object($sql);
        
        if( $row->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }

    public function check_if_job_created_before_service_level_variation_expired($obj){

        $sql = mysql_query("
        SELECT COUNT(`id`) AS jcount
        FROM `agency_price_variation`
        WHERE `agency_id` = {$obj->agency_id}
        AND `expiry` >= '".date('Y-m-d',strtotime($obj->jcreated))."'
        AND `scope` = {$obj->service_type}
        AND `active` = 1
        ");
        $row = mysql_fetch_object($sql);
        
        if( $row->jcount > 0 ){
            return true;
        }else{
            return false;
        }

    }
    // also update on CI system_model.php -- end

    //get safety squad by property_source in db table (properties_from_other_company)
    public function get_property_source($property_id){
        $sql_str= "
            SELECT 
                sac.`sac_id`,
                sac.`company_name`
            FROM `properties_from_other_company` AS pfoc
            LEFT JOIN `smoke_alarms_company` AS sac ON pfoc.`company_id` = sac.`sac_id`
            LEFT JOIN `property` AS prop ON pfoc.`property_id` = prop.`property_id`
            WHERE pfoc.`property_id` = {$property_id}
            AND pfoc.`active` = 1
        ";
        $sql = mysql_query($sql_str);
    
        if (mysql_num_rows($sql) > 0) {
            return mysql_fetch_array($sql);
        } else {
            return false;
        }
    
    }

    // check the links for old and ci
    public function check_links(){
        $sql_str= "
            SELECT 
                vl.`vpd`
            FROM `vpd_link` as vl
        ";
        $sql = mysql_query($sql_str);
        $row = mysql_fetch_array($sql);

        return $row['vpd'];
    
    }

}

?>