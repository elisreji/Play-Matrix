<?php
header('Content-Type: application/json');
require_once 'razorpay_config.php'; // Include Keys

$amount = 0;
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (isset($data['amount'])) {
    $amount = (float) $data['amount'] * 100; // to paise
}

if ($amount <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Check if keys are set
if ($razorpay_key_id === 'rzp_test_YourKeyHere' || $razorpay_key_secret === 'YourSecretHere') {
    // Still using placeholders? Return error so user knows to update config
    echo json_encode(['error' => 'Please configure your Razorpay API Keys in razorpay_config.php']);
    exit;
}

$ch = curl_init();
$url = 'https://api.razorpay.com/v1/orders';
$receipt = 'order_' . uniqid();

$postFields = [
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => $receipt,
    'payment_capture' => 1
];

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, "$razorpay_key_id:$razorpay_key_secret");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'CURL Error: ' . curl_error($ch)]);
} else {
    // If successful, pass response directly
    // If unauthorized (401), pass error
    if ($http_code !== 200) {
        $resp = json_decode($result, true);
        echo json_encode(['error' => $resp['error']['description'] ?? 'Razorpay API Error']);
    } else {
        echo $result;
    }
}
curl_close($ch);
?>
