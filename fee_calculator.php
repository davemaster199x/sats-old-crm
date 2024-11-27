<?php
ini_set('precision', 17); // float precision
if( $_POST['go'] ){
	
	$amount = $_POST['amount'];
	$fee = $_POST['fee'];
	$total = $amount-$fee;

}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Fee Calculator</title>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://files.coinmarketcap.com/static/widget/currency.js"></script>
<style>
table th, table td{
	text-align: right;
	padding: 4px;
}
a{
	color: black;
	text-decoration: none;
}
</style>
</head>

<body>
<a href="fee_calculator.php"><h1>Etherium Fee Calculator</h1></a>
<div style="width:300px; margin-bottom: 10px;">
<div class="coinmarketcap-currency-widget" data-currencyid="1027" data-base="USD" data-secondary="" data-ticker="true" data-rank="true" data-marketcap="true" data-volume="true" data-stats="USD" data-statsticker="false"></div>
</div>
<form action="fee_calculator.php" method="post">
	<table>
		<tr>
			<th>Amount:</th>
			<td><input type="text" name="amount" value="<?php echo $amount; ?>" /></td>
		</tr>
		<tr>
			<th>Fee:</th>
			<td><input type="text" name="fee" value="<?php echo $fee; ?>" /></td>
		</tr>
		<tr>
			<th>Total:</th>
			<td><input type="text" name="total" value="<?php echo $total; ?>" /></td>
		</tr>
		<tr>
			<th></th>
			<td>
				<button type="button" id="clear">Clear</button>
				<input type="submit" name="go" value="Go" />
			</td>
		</tr>
	</table>
<form>
<script>
jQuery("#clear").click(function(){

	jQuery('input[type="text"]').val('');

});
</script>
</body>

</html> 
