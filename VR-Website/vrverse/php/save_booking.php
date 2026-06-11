<?php
require_once 'config.php';
setHeaders();
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResp(false,'Method not allowed.');

$raw      = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name     = clean($raw['full_name']         ?? '');
$mail     = filter_var(trim($raw['email']   ?? ''), FILTER_SANITIZE_EMAIL);
$phone    = clean($raw['phone']             ?? '');
$product  = clean($raw['product_name']      ?? '');
$price    = floatval($raw['product_price']  ?? 0);
$loc      = clean($raw['delivery_location'] ?? '');
$addr     = clean($raw['full_address']      ?? '');
$date     = $raw['delivery_date']           ?? '';
$payment  = clean($raw['payment_method']    ?? '');
$dcharge  = floatval($raw['delivery_charge'] ?? 0);
$total    = floatval($raw['total_amount']   ?? 0);

if (!$name)    jsonResp(false,'Please enter your full name.');
if (!$mail || !filter_var($mail,FILTER_VALIDATE_EMAIL)) jsonResp(false,'Please enter a valid email.');
if (!$phone)   jsonResp(false,'Please enter your phone number.');
if (!$product) jsonResp(false,'Please select a product.');
if (!$loc)     jsonResp(false,'Please select a delivery location.');
if (!$addr)    jsonResp(false,'Please enter your full address.');
if (!$date)    jsonResp(false,'Please select a delivery date.');
if (!$payment) jsonResp(false,'Please select a payment method.');
if ($total<=0) jsonResp(false,'Invalid total amount.');

$order_id = 'VR'.strtoupper(substr(md5(uniqid(rand(),true)),0,8));

startSess();
$uid = getUser()['id'] ?? null;

$db = getDB();
$st = $db->prepare("INSERT INTO bookings 
    (user_id,full_name,email,phone,product_name,product_price,
    delivery_location,full_address,delivery_date,payment_method,
    delivery_charge,total_amount,order_id)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
$st->execute([$uid,$name,$mail,$phone,$product,$price,$loc,$addr,$date,$payment,$dcharge,$total,$order_id]);

jsonResp(true,'Order placed successfully!', [
    'order_id'     => $order_id,
    'total_amount' => $total,
    'status'       => 'confirmed'
]);
