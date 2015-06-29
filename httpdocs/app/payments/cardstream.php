<?php

if (!defined('AREA')) {
    die('Access denied');
}

if (defined('PAYMENT_NOTIFICATION')) {

	if (isset($_POST['responseCode'])) {
	
	    // Get the password
	    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", (int)$_GET['order_id']);
	    $processor_data = fn_get_payment_method_data($payment_id);
	
	    $order_info = fn_get_order_info($_GET['order_id']);

	    if (($_POST['responseCode'] === '0') && ($order_info)) {
	        if (isset($_POST['signature'])) {
	            $sign = $_POST;
	            unset($sign['signature']);
	            $signature = createSignature($sign, $processor_data['processor_params']['passphrase']);
	        }
	
	        if (isset($signature) && $signature !== $_POST['signature']) {
	            $pp_response['order_status'] = 'F';
	            $pp_response["reason_text"] = "Signature not matched on CardStream payment response.";
	
	        } else if ($_POST['amountReceived'] == (string)($order_info['total'] * 100)) {
	            $pp_response['order_status'] = "P";
	            $pp_response["reason_text"] = $_POST['responseMessage'];
	            $pp_response["transaction_id"] = $_POST['xref'];
	
	        } else {
	            $pp_response['order_status'] = 'F';
	            $pp_response["reason_text"] = "Amount received doesn't match the amount required.";
	        }
	
	    } else {
	        $pp_response['order_status'] = 'F';
	        $pp_response["reason_text"] = "Payment Failed - Detail: \"" . $_POST['responseMessage'] . "\"";
	    }
	} else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = "An unknown error occured";	
	}

    fn_finish_payment($_GET['order_id'], $pp_response, false);
    fn_order_placement_routines('route', $_GET['order_id'], false);
		
} else {

    $orderid = (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '-' . fn_date_format(time(), '%H_%M_%S');
    $return = fn_url("payment_notification.process?payment=cardstream&order_id=$order_id&fake=true", AREA, 'current');    
	$address = "{$order_info['b_address']} {$order_info['b_address_2']} {$order_info['b_city']} {$order_info['b_county']} {$order_info['b_state']} {$order_info['b_country']}";

	$fields = array(
		"merchantID" 		=> $processor_data["processor_params"]["merchant_id"], 
		"amount" 			=> $order_info['total'] * 100, 
		"countryCode" 		=> $processor_data["processor_params"]["countrycode"], 
		"currencyCode" 		=> $processor_data["processor_params"]["currencycode"], 
		"transactionUnique" => $orderid, 
		"redirectURL" 		=> $return, 
		"customerAddress" 	=> $address, 
		"customerPostCode" 	=> $order_info['b_zipcode'], 
		"customerEmail" 	=> $order_info['email'], 
		"customerPhone" 	=> $order_info['phone'], 
		"merchantData" 		=> "cs-cart-hosted-1", 
	);
    
    $msg = fn_get_lang_var('text_cc_processor_connection');
    $msg = str_replace('[processor]', 'CardStream Server', $msg);
    
    $output = '<html>
				<body onLoad="document.process.submit();">
				  <form action="https://gateway.cardstream.com/hosted/" method="POST" name="process">';
				  
	foreach ($fields as $key => $value) {
		$output .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';			
	}
	
	if (isset($processor_data["processor_params"]['passphrase'])) {
		$output .= '<input type="hidden" name="signature" value="' . createSignature($fields, $processor_data["processor_params"]['passphrase']) . '">';
	}
	
	$output .= '</form>
				<p style="text-align: center">' . $msg . '</p>
				</body>
				</html>';
	
	echo $output;
}


function createSignature(array $data, $key, array $fields = null) {
	
	$pairs = ($fields ? array_intersect_key($data, array_flip($fields)) : $data);

	ksort($pairs);

	// Create the URL encoded signature string
	$ret = http_build_query($pairs, '', '&');

	// Normalise all line endings (CRNL|NLCR|NL|CR) to just NL (%0A)
	$ret = preg_replace('/%0D%0A|%0A%0D|%0A|%0D/i', '%0A', $ret);
	
	// Hash the signature string and the key together
	$ret = hash("SHA512", $ret . $key);

	return $ret;	
}

exit;
?>
