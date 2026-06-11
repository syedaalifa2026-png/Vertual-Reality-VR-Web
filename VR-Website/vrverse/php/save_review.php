<?php
require_once 'config.php';
setHeaders();
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResp(false,'Method not allowed.');

$raw              = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$reviewer_name    = clean($raw['reviewer_name'] ?? '');
$mail             = filter_var(trim($raw['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$product          = clean($raw['product']       ?? '');
$rating           = intval($raw['rating']       ?? 0);
$text             = clean($raw['review_text']   ?? '');

if (!$reviewer_name)           jsonResp(false,'Please enter your name.');
if (!$text)                    jsonResp(false,'Please write your review.');
if (strlen($text)<5)           jsonResp(false,'Review is too short. Please write more.');
if ($rating<1||$rating>5)      jsonResp(false,'Please select a star rating (1-5).');

startSess();
$user = getUser();
$uid  = $user['id'] ?? null;
// If logged in, use their name/email
if ($user) {
    $reviewer_name = $user['full_name'];
    $mail          = $user['email'];
}

$db = getDB();
$st = $db->prepare("INSERT INTO reviews (user_id,reviewer_name,email,product,rating,review_text) VALUES (?,?,?,?,?,?)");
$st->execute([$uid, $reviewer_name, $mail?:null, $product?:null, $rating, $text]);

jsonResp(true,'Thank you for your review!', ['reviewer_name'=>$reviewer_name,'rating'=>$rating]);
