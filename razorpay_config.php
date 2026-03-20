<?php
// Razorpay Configuration
// Replace with your actual Live keys when ready

define('RAZORPAY_KEY_ID', 'rzp_test_SL8TAla7mMpzDe');
define('RAZORPAY_KEY_SECRET', 'rVYe4Kv47guqLZeey1ZD28ay');

// Path to Razorpay SDK
require_once __DIR__ . '/razorpay-php-2.9.2/Razorpay.php';

use Razorpay\Api\Api;

// Set to true to simulate successful payments without valid API keys (for development/testing)
define('RAZORPAY_TEST_SIMULATION', true);

function getRazorpayApi() {
    return new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
}
?>
