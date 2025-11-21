<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$invoice_id = $_REQUEST['invoiceId'];
if (empty($invoice_id)) {
    $up_response = file_get_contents('php://input');
    $up_response_decode = json_decode($up_response, true);
    $invoice_id = $up_response_decode['invoice_id'];
}

if (empty($invoice_id)) {
    errorExit("Direct access is not allowed.");
}

$apiKey =  trim($methodExtras['api_key']);
$host = parse_url(trim($methodExtras['api_url']),  PHP_URL_HOST);
    $apiUrl = "https://api.zinipay.com/v1/payment/verify";

$invoice_data = [
    'invoiceId' => $invoice_id
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode($invoice_data),
    CURLOPT_HTTPHEADER => [
        "zini-api-key: " . $apiKey,
        "accept: application/json",
        "content-type: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    errorExit("cURL Error #:" . $err);
}


if (empty($response)) {
    errorExit("Invalid Response From Payment API.");
}

$data = json_decode($response, true);

if (!isset($data['status']) && !isset($data['metadata'])) {
    errorExit("Invalid Response From Payment API.");
}

//new
// Validate the presence of metadata and user_id
if (!isset($data['metadata']['user_id'])) {
    errorExit("User ID not found in metadata.");
}

$clientId = $data['metadata']['user_id'];
// Get latest client data using client_id
$getClient = $conn->prepare("SELECT * FROM clients WHERE client_id = :clientId");
$getClient->execute([
    'clientId' => $clientId
]);
if ($getClient->rowCount() === 0) {
    errorExit("Client not found.");
}
$client = $getClient->fetch(PDO::FETCH_ASSOC);
//new

if (isset($data['status']) && $data['status'] == 'COMPLETED') {
    $orderId = $data['val_id'];
    $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:orderId");
    $paymentDetails->execute([
        "orderId" =>  $orderId
    ]);

    if ($paymentDetails->rowCount()) {
        $paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);
        if (
            !countRow([
                'table' => 'payments',
                'where' => [
                    'client_id' => $client['client_id'],
                    'payment_status' => 3,
                    'payment_delivery' => 2,
                    'payment_extra' => $orderId,
                ]
            ])
        ) {
            $paidAmount = floatval($paymentDetails["payment_amount"]);
            if ($paymentFee > 0) {
                $fee = ($paidAmount * ($paymentFee / 100));
                $paidAmount -= $fee;
            }
            if ($paymentBonusStartAmount != 0 && $paidAmount > $paymentBonusStartAmount) {
                $bonus = $paidAmount * ($paymentBonus / 100);
                $paidAmount += $bonus;
            }

            $update = $conn->prepare('UPDATE payments SET 
                    client_balance=:balance,
                    payment_status=:status, 
                    payment_delivery=:delivery, 
                    payment_delivery=:delivery WHERE payment_id=:id');
            $update->execute([
                'balance' => $client["balance"],
                'status' => 3,
                'delivery' => 2,
                'id' => $paymentDetails['payment_id'],
            ]);

            $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id');
            $balance->execute([
                "balance" => $client["balance"] + $paidAmount, //new
                "id" => $client["client_id"] //new
            ]);
            header("Location: " . site_url("addfunds"));
            exit();
        } else {
            errorExit("Order ID is already used.");
        }
    } else {
        errorExit("Order ID not found.");
    }
}

http_response_code(405);
die();

