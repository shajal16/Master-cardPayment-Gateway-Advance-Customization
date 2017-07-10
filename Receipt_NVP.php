<?php
session_start();

include "api_lib.php";
include "configuration.php";
include "connection.php";
include "../cartmain.php";  
$cart = new Cart;
//$id = $auth->get_members_id();
    if($cart->total_items() > 0){
    $cartItems = $cart->contents();
    foreach($cartItems as $item){
	
	$product_name = $item["name"];
	echo $product_name;
	
	}
	}
error_reporting(E_ALL);

$errorMessage = "";
$errorCode = "";
$gatewayCode = "";
$result = "";

$responseArray = array();

$resultInd =  (isset($_GET["resultIndicator"]))?$_GET["resultIndicator"]:"";
$successInd = $_SESSION['successIndicator']; 
 echo $_SESSION['orderID'];
?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <link rel="stylesheet" type="text/css" href="assets/paymentstyle.css" />
    <head>
      <title>DirectApi Example</title>
      <meta http-equiv="Content-Type" content="text/html, charset=iso-8859-1">
    </head>
    
    <body>

		<p style="text-align:center;"><a href="../index.html"><img src="http://rlv.zcache.com/put_image_text_logo_here_create_make_my_own_design_postage-r909b636bb70b4b6ca785e139637d5211_6b72g_8byvr_324.jpg" alt="Main Order Home Page" /></a></p>
    <center><h1><br/><u>Payment Receipt Page</u></h1></center>
 
    
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

  <br/><br/>
   
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
	echo '<pre>';
	print_r($parsed_array);
	echo '</pre>';
			die(); 
   ?>
   
  	
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
         
      
		<h2 align="center"><a href="../index.html">Return to the Main Order Page</a></h2>
   
    
    </body>
<html>