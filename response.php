<?php
ob_start();
session_start();
date_default_timezone_set('UTC');
include "api_lib.php";
include "configuration.php";
include "connection.php";
include "../cartmain.php";  
$cart = new Cart;

$hostname = 'db658761488.db.1and1.com';
$username = 'dbo658761488';
$password = 'HHBNJ6&&^*999jhu';
$db = 'db658761488';
$global_dbh = mysql_connect($hostname, $username, $password)
or die("Could not connect to database");
mysql_select_db($db, $global_dbh)
or die("Could not select database");


error_reporting(E_ALL);

$errorMessage = "";
$errorCode = "";
$gatewayCode = "";
$result = "";

$responseArray = array();

$resultInd =  (isset($_GET["resultIndicator"]))?$_GET["resultIndicator"]:"";
$successInd = $_SESSION['successIndicator']; 
//echo $_SESSION['orderID'];

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <link rel="stylesheet" type="text/css" href="assets/paymentstyle.css" />
    <head>
      <title>:::example:::</title>
      <meta http-equiv="Content-Type" content="text/html, charset=iso-8859-1">
    </head>
    
    <body>
<!--
		<p style="text-align:center;"><a href="../index.html"><img src="http://rlv.zcache.com/put_image_text_logo_here_create_make_my_own_design_postage-r909b636bb70b4b6ca785e139637d5211_6b72g_8byvr_324.jpg" alt="Main Order Home Page" /></a></p>
    <center><h1><br/><u>Payment Receipt Page</u></h1></center>-->
 
 <!--   
<?php

if (strcmp($resultInd, $successInd) == 0)
	{
?>
		 <tr class="title">
             <td colspan="2" height="25"><P><strong>&nbsp;</strong></P></td>
         </tr>
         <tr>
             <td align="right" width="50%"><strong><center><h1>Your Payment was successful!</h1></center></strong></td>
         </tr>    
<?php

	}
	else
	{
?>

	<tr class="title">
             <td colspan="2" height="25"><P><strong>&nbsp;</strong></P></td>
         </tr>
         <tr>
             <td align="right" width="50%"><strong><center><h1>Your Payment was Unsuccessful!</h1></center></strong></td>
         </tr>
<?php
	}
?>


  <table width="60%" align="center" cellpadding="5" border="0">

  <?php
    // echo HTML displaying Error headers if error is found
    if ($errorCode != "" || $errorMessage != "") {
  ?>
      <tr class="title">
             <td colspan="2" height="25"><P><strong>&nbsp;Error Response</strong></P></td>
         </tr>
         <tr>
             <td align="right" width="50%"><strong><i><?=$errorCode?>: </i></strong></td>
             <td width="50%"><?=$errorMessage?></td>
         </tr>
  <?php
    }
    else {
  ?>
      <tr class="title">
             <td colspan="2" height="25"><P><strong>&nbsp;<?=$gatewayCode?></strong></P></td>
         </tr>
        
  <?php
     }
  ?>
         
  </table>

  <br/><br/>-->
   
   <?php
   
	$orderID = $_SESSION['orderID'];
	
	$merchantObj = new Merchant($configArray);

	 $parserObj = new Parser($merchantObj);

	 $requestUrl = $parserObj->FormRequestUrl($merchantObj);

	 $request_assoc_array = array("apiOperation"=>"RETRIEVE_ORDER",
														 		"order.id"=>$orderID
														 );
	 
	 $request = $parserObj->ParseRequest($merchantObj, $request_assoc_array);
	 $response = $parserObj->SendTransaction($merchantObj, $request);
	 
	 $new_api_lib = new api_lib;
	 $parsed_array = $new_api_lib->parse_from_nvp($response);
	 
	 $orderid = $parsed_array['transaction[0].order.id'];
	 $cardnumber =  $parsed_array['transaction[0].sourceOfFunds.provided.card.number'];
	 $cardscheme =  $parsed_array['transaction[0].sourceOfFunds.provided.card.scheme'];
	 $timerecord =  $parsed_array['transaction[0].timeOfRecord'];
	 $amount =  $parsed_array['transaction[0].transaction.amount'];
	 $receiptnumber =  $parsed_array['transaction[0].transaction.receipt'];
	 
			$customer_email ="";
			$sql_order="SELECT * FROM `customers`  WHERE orderid = '$orderid'";
			if($output =mysql_query($sql_order)){
				while($row =mysql_fetch_assoc($output)){
					$customer_id = $row['id'];
					
				}
			}
			

	
	$sql_transaction = "INSERT INTO transaction (orderid,customerid,cardnumber,cardscheme,timerecord, amount,receiptnumber) VALUES ('$orderid','$customer_id','$cardnumber','$cardscheme','$timerecord',
	'$amount','$receiptnumber')";
	
	if(mysql_query($sql_transaction))
	{
		
	}else{
		echo mysql_error();
	}
	
	 
	$deliveryday = 0;
	$deliverycharge = 0;
	$product_chk_bid =0;
	$product_chk_bid_prev =0;
	$count = 0;
	$diff = 0;
	$cartItems = $cart->contents();
	$totalweight=0;
	
	foreach($cartItems as $item){
	$totalweight = $totalweight+($item['weight']*$item['qty']);
	$product_name = $item["name"];
	$product_id = $item["id"];
	$sql_delivery ="SELECT * FROM product INNER JOIN business ON product.business_id=business.business_id WHERE product.product_id='$product_id'";
	if($result = mysql_query($sql_delivery))
	{

		while($output = mysql_fetch_assoc($result))
		{	   
	       if($count ==0){
			 $product_chk_bid = $output['business_id'];
			 $product_chk_bid_prev = $output['business_id'];
			 $deliveryday =$output['maxdeliveryday'];
		   }
		   
		   if($count>=1){
			   
			   $product_chk_bid = $output['business_id'];
			   if($product_chk_bid !=$product_chk_bid_prev)
			   {
				   $diff = $diff+1;
			   }
			   $temp = $output['maxdeliveryday'];
			   if($temp>$deliveryday)
			   {
				   $deliveryday = $temp;
			   }
		   }
		   
		}
	}
	
	$count=$count+1;

		
	}
	
	$deliverydate= Date('Y-m-d', strtotime("+".($deliveryday+1)."days"));
	
	$deliverycharge = 0;
	
	$cityname=$_SESSION['city'];
	$sameshop = 0;
	$diffshop = 0;
	$demo = "";
	$sql_delivery_charge ="SELECT * FROM `delivery_charge`";
	if($result_delivery_charge = mysql_query($sql_delivery_charge))
	{
			while($output_delivery_charge = mysql_fetch_assoc($result_delivery_charge))
		{
			$minweight = $output_delivery_charge['min'];
            $maxweight = $output_delivery_charge['max'];
			
	if($totalweight ==0){
	 $deliverycharge = 0;
	}else{
	if($totalweight>=$minweight && $totalweight<=$maxweight)
	{	

		if($cityname=="dhaka" || $cityname=="Dhaka" || $cityname=="DHAKA" )
			
			{
		  $sameshop =  $output_delivery_charge['charge'];
		  $diffshop =  $output_delivery_charge['diff_shop'];
			}else{
				$sameshop =  $output_delivery_charge['outsidesame'];
		  $diffshop =  $output_delivery_charge['outsidedifferent'];
				
			}
	}	

	}
	  
	    }
	

		if($diff>=1){
			$deliverycharge = $diffshop;			
		}else{
			$deliverycharge = $sameshop ;
		}
	}
			$servicecharge =0;
			$sql_servicecharge="SELECT * FROM `servicecharge`";
			if($output1 =mysql_query($sql_servicecharge)){
				while($row1 =mysql_fetch_assoc($output1)){
					$servicecharge = $row1['charge'];					
				}
			}
			
			$bankcharge =0;
			$sql_bankcharge="SELECT * FROM `bankcharge`";
			if($output2 =mysql_query($sql_bankcharge)){
				while($row2 =mysql_fetch_assoc($output2)){
					$bankcharge = $row2['charge'];					
				}
			}
	
	$insertOrder ="INSERT INTO orders (id ,customer_id, total_price,total_weight,delivery_charge, created, modified,deliverydate) VALUES ('".$orderid."','".$customer_id."', '".$cart->total()."','".$totalweight."','".$deliverycharge."', '".date("Y-m-d H:i:s")."', '".date("Y-m-d H:i:s")."','".$deliverydate."')";
	
	if(mysql_query($insertOrder)) 
	{

            $cartItems = $cart->contents();
			
            foreach($cartItems as $item){
				
$result2 ="SELECT * FROM product WHERE product.product_id='".$item['id']."'";
if ($output = mysql_query($result2)) {
        while($row1 = mysql_fetch_assoc($output)) {
				$bid=$row1['business_id'];
				$product_price = $row1['price']-(($row1['price']/100)*$row1['discount']);
		}
}

            $product_sale = $product_price*$item['qty'];
			$product_service = ($product_sale/100)*$servicecharge;
			$product_bankcharge = ($product_sale/100)*$bankcharge;
			$sale_ref = rand(10000,100000).'_'.$orderid;
			$service_ref =rand(10000,100000).'_'.$orderid;
			$bankcharge_ref =rand(10000,100000).'_'.$orderid;
            $date = date("Y-m-d h:i:s");
			$date1 = date("Y-m-d");
            
$product_id = $item['id'];
$quantity = $item['qty'];
            
$sql_update = "SELECT inventory FROM product WHERE product_id = '$product_id'";
if($output_sql_update = mysql_query($sql_update))
{
	while($fetch_sql_update = mysql_fetch_array($output_sql_update))
	{
		$past_quantity = $fetch_sql_update['inventory'];
		$recent_quantity = $past_quantity - $quantity;
		$sql_update_change = "UPDATE product SET inventory ='$recent_quantity' WHERE product_id = '$product_id'";
		mysql_query($sql_update_change);
	}
}
              
                $sql = "INSERT INTO order_items (order_id, product_id,customer_id,business_id, quantity, product_sale,product_service,sale_ref,service_ref,created,additional_instruction,customer_upload,deliverydate,product_bankcharge,bankcharge_ref,datetime) VALUES ('".$orderid."', '".$item['id']."','".$customer_id."','".$bid."','".$item['qty']."','".$product_sale."','".$product_service."','".$sale_ref."','".$service_ref."','".$date1."','".$item['addi']."','".$item['customerupload']."','".$deliverydate."','".$product_bankcharge."','".$bankcharge_ref."','".$date."');";
				
			mysql_query($sql);	
				
        
            }
       $cart->destroy();
	   $_SESSION['city']=" ";
       header("Location: ../orderSuccess.php?id=$orderid");
	}else{
		echo mysql_error();
	}

 
	

	/*print_r($parsed_array);
	
	echo '</pre>';*/
 
   ?>
   
 <!-- 	
   <table width="60%" align="center" cellpadding="5" border="0">
   	<center>
   		
  			 <tr class="title">
             <td colspan="2" height="25"><center><h1><u><strong>&nbsp;Order Details</strong></h1></u></td>
         </tr>
         <tr>
             <td colspan="2" height="25"><center><strong>&nbsp;Merchant: <?php echo $parsed_array['merchant']; ?> </strong></td>
         </tr>
          <tr>
             <td colspan="2" height="25"><center><strong>&nbsp;Order Amount: <?php echo $parsed_array['amount']; ?> </strong></td>
         </tr>
         <tr>
             <td colspan="2" height="25"><center><strong>&nbsp;Order Curreny: <?php echo $parsed_array['currency']; ?> </strong></td>
         </tr>
         <tr>
             <td colspan="2" height="25"><center><strong>&nbsp;Order ID: <?php echo $orderID; ?> </strong></td>
         </tr>
         <tr>
             <td colspan="2" height="25"><center><strong>&nbsp;Masked Card Number: <?php echo $parsed_array['sourceOfFunds.provided.card.number']; ?> </strong></td>
         </tr>
         
     </table>
         
      
		<h2 align="center"><a href="../index.html">Return to the Main Order Page</a></h2>-->
   
    
    </body>
<html>