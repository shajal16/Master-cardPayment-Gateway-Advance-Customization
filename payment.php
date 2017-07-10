<?php

/* Main processing page

This is the best NVP example and most useful source code to view if wanting to build an integration. If wanting to utilise REST,
see the Hosted Checkout Return to Merchant REST version.

1. Create one MerchantConfiguration object for each merchant ID.
2. Create one Parser object.
3. Generate a unique Order ID with a method of your choice to use to identify the order. 
4. Set a receipt page to be re-directed to on order completion by using the data-complete tag 
   (alternatively, a function can be specified here).
5. Call Parser object FormRequest method to create a payment page session by making 
   a CREATE_CHECKOUT_SESSION apiOperation request that will be sent to the payment server.
   A successful request will return a session.id and and successIndicator.
6. Store the sussessIndicator and order ID for later use in the receipt page
7. Pass the session.id to the Checkout.configure function which will be called next.
8. When the customer selects the "Payment" button, either call Checkout.showLightbox()
   or  Checkout.showPaymentPage() (note both examples are shown below, but you would only
   use one of these methods).
9. The receipt page compares the sussessIndicator and the resultIndicator to make sure the transaction was successfull, and if
   required, full order details can be retrieved by calling the RETRIEVE_ORDER apiOperation request passing the Order ID. 

*/

session_unset();
session_start();
//echo $_SESSION['orderID']."<br>";
//$orderid = $_SESSION['orderID'];
include "api_lib.php";
include "configuration.php";
include "connection.php";
include '../includes/library.php';
include "../cartmain.php";
$cart = new Cart;

$hostname = 'localhost';
$username = 'root';
$password = 'root';
$db = 'testdb';
$global_dbh = mysql_connect($hostname, $username, $password)
or die("Could not connect to database");
mysql_select_db($db, $global_dbh)
or die("Could not select database");

$orderid = $_POST['order_id'];
 
	$billing_firstname = $_POST['billing_firstname'];
    $billing_lastname = $_POST['billing_lastname'];  	
	$billing_email = $_POST['billing_email'];
    $billing_address = $_POST['billing_address'];
    $billing_area = $_POST['billing_area'];
    $billing_city = $_POST['billing_city'];
    $billing_zipcode = $_POST['billing_zipcode'];
    $billing_phone = $_POST['billing_phone'];
	
	$shipping_firstname = $_POST['shipping_firstname'];
    $shipping_lastname = $_POST['shipping_lastname'];  	
	$shipping_email = $_POST['shipping_email'];
    $shipping_address = $_POST['shipping_address'];
    $shipping_area = $_POST['shipping_area'];
    $shipping_city = $_POST['shipping_city'];
    $shipping_zipcode = $_POST['shipping_zipcode'];
    $shipping_phone = $_POST['shipping_phone'];
	
	$demopassword = "letsshop";
	$password = md5('letsshop');
/*---------------------------------------------*/
    $msg ="";
    if($cart->total_items() > 0){
    $cartItems = $cart->contents();
    foreach($cartItems as $item){
	
	$product_name = $item["name"];
	$product_id = $item["id"];

	$sql_delivery ="SELECT * FROM product WHERE product_id='$product_id'";
	if($result = mysql_query($sql_delivery))
	{

		while($output = mysql_fetch_assoc($result))
		{
			$initial = $item['qty'];
			$inventory_product = $output['inventory'];
			if($initial> $inventory_product){				
		    $msg = $msg." $inventory_product Items are available for $product_name";
			//echo "<script>alert('$msg')</script>";
			}
			
		}
	}
   }
 }
  
if($msg!="")
 {
//$_SESSION["msg"] = $msg;

echo '<script type="text/javascript">';
echo 'window.location.href = "../viewcartmain.php";';
echo '</script>';
 }
/*---------------------------------------------*/


$sql ="INSERT INTO `customers` (`first_name`,`last_name`,`email`,`phone`,`zip`,`address`,`city`,
		`area`,`s_first_name`,`s_last_name`,`s_email`,`s_phone`,`s_zip`,`s_address`,`s_city`,
		`s_area`,`orderid`) VALUES ('$billing_firstname','$billing_lastname','$billing_email','$billing_phone','$billing_zipcode','$billing_address','$billing_city','$billing_area','$shipping_firstname','$shipping_lastname','$shipping_email','$shipping_phone','$shipping_zipcode','$shipping_address','$shipping_city','$shipping_area','$orderid')";
		
		if(mysql_query($sql))
		{
			
		}else{

			echo mysql_error();

		}


$output=0;

$sql_order = "SELECT * FROM `members` WHERE email ='$billing_email'";
if($order_table = mysql_query ($sql_order))
{
while ($fetch_order = mysql_fetch_assoc($order_table)){
	$dataemail = $fetch_order['email'];
	if(!empty($dataemail))
	{
	  $output=1;
	}else{
	
    }
}
}else{
	echo mysql_error();
}

if($output==1){
	//echo "login";
}else{
     $is_active = 1;
     $vars['activate_code'] = get_rand_id(15);
     $activation_code = $vars['activate_code'];
	 
	 //echo $activation_code;
	 
     $sql_member= "INSERT INTO `MEMBERSALL` (`first_name`,`last_name`,`email`,`address`,`area`,`city`,`zipcode`,`phone`,`password`,`is_active`,`activate_code`) 
     VALUES('$billing_firstname','$billing_lastname','$billing_email','$billing_address','$billing_area','$billing_city','$billing_zipcode','$billing_phone','$password','$is_active','$activation_code') ";
	
	if(mysql_query($sql_member)){
		
	
	$id =0;
	
$sql_memberid = "SELECT * FROM `members` WHERE email ='$billing_email'";
if($order_id = mysql_query ($sql_memberid))
{
while ($fetch_orderid = mysql_fetch_assoc($order_id)){
	$id = $fetch_orderid['members_id'];
}
}


	$email = $billing_email;
	$subject = " Welcome to example ::: Account activation on example";
	$headers.= "MIME-Version: 1.0\r\n";
	$headers.= "Content-type: text/html; charset=iso-8859-1\r\n";
	//$headers.= "From: example Team<Hi@codegreensolutions.net>\r\n";
    $headers.= "From: example Team<example@example.com>\r\n";
    $headers.= "Reply-To: example Team<info@example.com>\r\n";
	$headers.= "X-MSMail-Priority: High\r\n";

    //$body = "Welcome to example"."<br>"."Account Information"."<br>"."Username: ".$email." Password: ".$demopassword;
	
	$body = "<table border='0' cellspacing='3' cellpadding='3'>
  <TR>
    <TD><STRONG><strong>Username: ".$email." Password: ".$demopassword.",</strong></STRONG></TD>
  </TR>
  <TR>
    <TD>Thanks for joining example community.</TD>
  </TR>
  <TR>
    <TD>To protect your identity, please confirm your registration by clicking here:</TD>
  </TR>
  <TR>
    <TD><A href='http://example.com/desherbiz/signup3.php?vercode=".$vars['activate_code'].$id."' target='_blank'>Activate your account at   example.com.</A><BR><br>Or copy and paste the link in your browser window <STRONG><br><strong>[http://example.com/desherbiz/signup3.php?vercode=".$vars['activate_code'].$id."]</strong></STRONG></TD>
  </TR>
  <TR>
    <TD>We look forward to seeing you at example</TD>
  </TR>
  <TR>
    <TD>Good Luck!</TD>
  </TR>
</table>";	
	
	mail($email , $subject, $body, $headers);


		
	}else{
		echo mysql_error();
	}	
	
   
}


  
  //Ensure this is the first invokation of this page
	if($_SERVER['REQUEST_METHOD'] == "POST") 
	{
		
		if (array_key_exists("submit", $_POST))
	  unset($_POST["submit"]);
		
    $order_amount = $_POST["order_amount"];   
	
$b = str_replace( ',', '', $order_amount );

if( is_numeric( $b ) ) {
    $order_amount = $b;
}


    $order_currency = $_POST["order_currency"];
    $customer_receipt_email = "'" . $_POST["billing_email"] . "'";
	

	

	

    
    //Creates the Merchant Object from config. If you are using multiple merchant ID's, 
		// you can pass in another configArray each time, instead of using the one from configuration.php
    $merchantObj = new Merchant($configArray);

	  // The Parser object is used to process the response from the gateway and handle the connections
	  // and uses connection.php
	  $parserObj = new Parser($merchantObj);

    //The Gateway URL can be set by using the following function, or the 
    //value can be set in configuration.php
    //$merchantObj->SetGatewayUrl("https://secure.uat.tnspayments.com/api/nvp");	
	  $requestUrl = $parserObj->FormRequestUrl($merchantObj);
	
	  //This is a library if useful functions
	  $new_api_lib = new api_lib;

		//Use a method to create a unique Order ID. Store this for later use in the receipt page or receipt function.
    $order_id = $orderid;  
   			
   	//Form the array to obtain the checkout session ID.									 
		$request_assoc_array = array("apiOperation"=>"CREATE_CHECKOUT_SESSION",
														 	   "order.id"=>$order_id,
														 	   "order.amount"=>$order_amount,
														     "order.currency"=>$order_currency,
															 "interaction.displayControl.billingAddress" =>"HIDE"
														 		);
														 		
		//This builds the request adding in the merchant name, api user and password.											 		
		$request = $parserObj->ParseRequest($merchantObj, $request_assoc_array);
									
		//Submit the transaction request to the payment server
		$response = $parserObj->SendTransaction($merchantObj,$request);
		
		//Parse the response
		$parsed_array = $new_api_lib->parse_from_nvp($response);								 

		//Store the successIndicator for later use in the receipt page or receipt function.
		$successIndicator = $parsed_array['successIndicator'];
 
    //The session ID is passed to the Checkout.configure() function below.
 		$session_id = $parsed_array['session.id'];
 
    //Store the variables in the session, or a database could be used for example
    $_SESSION['successIndicator']= $successIndicator;
    $_SESSION['orderID']= $order_id;
	 
	  $merchantID = "'" . $merchantObj->GetMerchantId() . "'";
  };
  
  ?>
  
<html>
	
    <head> 
    
       <script src="https://easternbank.test.gateway.mastercard.com/checkout/version/35/checkout.js" 
               	 data-error="errorCallback"
               	 data-cancel="http://example.com/desherbiz/viewcartmain.php"
                 data-complete="http://example.com/desherbiz/paymentgateway/response.php"
                >
       </script>

       <script type="text/javascript">
            function errorCallback(error) {
                  alert(JSON.stringify(error));
            }
            
            function completeCallback(resultIndicator, sessionVersion) {
                  alert("Result Indicator");
				  				alert(JSON.stringify(resultIndicator));
                  alert("Session Version:");
				  				alert(JSON.stringify(sessionVersion));
				  				alert("Successful Payment");
            }
           
            function cancelCallback() {
                  alert('Payment cancelled');
            
            }
       
       
						Checkout.configure({
    					merchant   : <?php echo $merchantID; ?>,
    					order      : {
        				amount     : <?php echo json_encode($order_amount); ?>,
        				currency   : <?php echo json_encode($order_currency); ?>,
        				description: 'Order Process',
        				id				 : <?php echo json_encode($order_id); ?>,
        			
            	},
    				
    					customer :{
    						email: <?php echo $customer_receipt_email; ?>
    				  },
    					interaction: {
        				merchant: {
            		name: 'example.COM',
            			address: {
            				line1: 'Ga 11/2 Sahajadpur,Level 2 ',
            				line2: 'Gulshan, Dhaka-1212 , Bangladesh '
        					},
        					  logo:  'https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcTBLF3ZEk8M5Uh-AU3cxUEyAMScuyuKUF1gJJoN_Zwo4pvXLlpY'
        				}
    					},
    					session: { 
            			id: <?php echo json_encode($session_id); ?>
       						}
						});
						
        </script>
    
    </head>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="business_img/zoom/jquery.fancybox.css" rel="stylesheet" type="text/css" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/single_product.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="css/squeezebox.css" type="text/css" />
<link rel="stylesheet" type="text/css" media="all" href="css/event/base_versioned.css">
<link rel="stylesheet" type="text/css" media="all" href="css/event/about.css">
<link rel="stylesheet" type="text/css" media="all" href="css/store/store.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css" >
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

<!--<script src='business_img/zoom/jquery-1.8.3.min.js'></script>-->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src='business_img/zoom/jquery.elevatezoom.js'></script>
<script src="business_img/zoom/jquery.fancybox.css" type="text/javascript"></script>
    <body>
    	  	
    		<p style="text-align:center;"><a href="../"><img src="http://ebl.com.bd/images/eastern-bank-ltd.gif" alt="Main Order Home Page" /></a></p>

        <br><br><br><br>
        <!--<h1 align="center"> Hosted Checkout - Return To Merchant - PHP/JavaScript/NVP</h1>-->
        <h2 align="center"> <u>Order Summary</u></h2>
        <p style="text-align:center;"> <strong> Order Amount : BDT <?php if (isset($order_amount)) echo $_POST["order_amount"]; ?></p>
        <p style="text-align:center;"> Currency&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp: <?php if (isset($order_currency)) echo $order_currency ?></strong> </p>
        <br>
        
        <!-- Note in reality only one of the following functions will be called -->
        <p style="text-align:center;"><button class="btn btn-primary" value="Pay NOW" onclick="Checkout.showLightbox();"> Pay Now </button> </p>
        <!-- <p style="text-align:center;"><input type="button" value="Pay with Payment Page" onclick="Checkout.showPaymentPage();" /></p>
				-->
        <p style="text-align:center;"><a href= "../viewcartmain.php"><br><br><input type="button" value="Cancel Payment" /></a></p>
    </body>
</html>