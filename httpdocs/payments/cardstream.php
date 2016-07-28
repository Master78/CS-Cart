<?php

if (!defined('AREA')) {
    die('Access denied');
}

if (isset($_POST['responseCode']) && $_POST['responseCode'] != "") {

    // Get the password
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", mysql_real_escape_string($_GET['order_id']));
    $processor_data = fn_get_payment_method_data($payment_id);

    $order_info = fn_get_order_info($_GET['order_id']);


    if (($_POST['responseCode'] == 0) && ($order_info)) {

        if ($_POST['amountReceived'] == str_replace(".", "", $order_info['total'])) {
            $pp_response['order_status'] = "P";
            $pp_response["reason_text"] = $_POST['responseMessage'];
            $pp_response["transaction_id"] = $_POST['xref'];

        } else {
            $pp_response['order_status'] = 'F';
            $pp_response["reason_text"] = "Amount received doesnt match the amount required.";
        }

    } else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = "Payment Failed - Detail: \"" . $_POST['responseMessage'] . "\"";
    }

    fn_finish_payment($_GET['order_id'], $pp_response, false);
    fn_order_placement_routines($_GET['order_id']);

} else {
    $orderid = (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '-' . fn_date_format(time(), '%H_%M_%S');
    $return = Registry::get('config.http_location') . "/$index_script?dispatch=payment_notification.notify&payment=cardstream&order_id=$order_id";

    $VPBillingPhoneNumber = $order_info['phone'];
    $VPBillingEmail = $order_info['email'];

    $total = str_replace(".", "", $order_info['total']);

    $VPBillingStreet = $order_info['b_address'];

    if ($order_info['b_address_2'] !== "") {
        $VPBillingStreet .= ", \n" . $order_info['b_address_2'];
    }

    if ($order_info['b_city'] !== "") {
        $VPBillingStreet .= ", \n" . $order_info['b_city'];

    }

    if ($order_info['b_county'] !== "") {
        $VPBillingStreet .= ", \n" . $order_info['b_county'];
    }

    if ($order_info['b_state'] !== "") {
        $VPBillingStreet .= ", \n" . $order_info['b_state'];
    }

    if ($order_info['b_country'] !== "") {
        $VPBillingStreet .= ", \n" . $order_info['b_country'];
    }

    $VPBillingStreet = trim($VPBillingStreet);

    $VPBillingPostCode = $order_info['b_zipcode'];

    $msg = fn_get_lang_var('text_cc_processor_connection');
    $msg = str_replace('[processor]', 'CardStream Server', $msg);


    if (isset($processor_data["params"]['passphrase'])) {
        $sign = array(
            'merchantID' => $processor_data["params"]["merchantid"],
            'amount' => $total,
            'countryCode' => $processor_data["params"]["countrycode"],
            'currencyCode' => $processor_data["params"]["currencycode"],
            'transactionUnique' => $orderid,
            'redirectURL' => $return,
            'customerAddress' => $VPBillingStreet,
            'customerPostCode' => $VPBillingPostCode,
            'customerEmail' => $VPBillingEmail,
            'customerPhone' => $VPBillingPhoneNumber,
            'merchantData' => 'cs-cart-hosted-3'
        );

        ksort($sign);
        $sig_fields = http_build_query($sign) . $processor_data["params"]['passphrase'];
        $signature = hash('SHA512', $sig_fields);
    }

    echo <<<EOT
<html>
<body onLoad="document.process.submit();">
  <form action="https://gateway.cardstream.com/hosted/" method="POST" name="process">
	    <input type="hidden" name="merchantID" value="{$processor_data["params"]["merchantid"]}" />
	    <input type="hidden" name="amount" value="{$total}" />
	    <input type="hidden" name="countryCode" value="{$processor_data["params"]["countrycode"]}" />
	    <input type="hidden" name="currencyCode" value="{$processor_data["params"]["currencycode"]}" />
	    <input type="hidden" name="transactionUnique" value="{$orderid}" />
	    <input type="hidden" name="redirectURL" value="{$return}" />
	    <input type="hidden" name="customerAddress" value="{$VPBillingStreet}" />
	    <input type="hidden" name="signature" value="{$signature}" />
	    <input type="hidden" name="customerPostCode" value="{$VPBillingPostCode}" />
	    <input type="hidden" name="customerEmail" value="{$VPBillingEmail}" />
	    <input type="hidden" name="customerPhone" value="{$VPBillingPhoneNumber}" />
	    <input type="hidden" name="merchantData" value="cs-cart-hosted-3" />
	</form>
	<p>
	<div align=center>{$msg}</div>
	</p>
 </body>
</html>
EOT;
}

exit;
?>
