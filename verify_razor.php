<?php
require_once 'razorpay_config.php';
use Razorpay\Api\Api;

try {
    $api = getRazorpayApi();
    // Try to list orders, which should work if the key/secret are valid.
    $orders = $api->order->all(['count' => 1]);
    echo "SUCCESS: Key pair is valid!\n";
    print_r($orders);
} catch (Exception $e) {
    echo "FAILURE: Authentication failed for Razorpay.\n";
    echo "Key ID: " . RAZORPAY_KEY_ID . "\n";
    echo "Message: " . $e->getMessage() . "\n";
}
?>
