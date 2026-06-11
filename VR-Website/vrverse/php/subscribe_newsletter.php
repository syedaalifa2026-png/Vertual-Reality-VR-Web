<?php
require_once 'config.php';
setHeaders();
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResp(false,'Method not allowed.');

$raw  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$mail = filter_var(trim($raw['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!$mail || !filter_var($mail,FILTER_VALIDATE_EMAIL)) jsonResp(false,'Please enter a valid email address.');

$db = getDB();
$st = $db->prepare("SELECT id FROM newsletter WHERE email=?");
$st->execute([$mail]);
if ($st->fetch()) jsonResp(false,'This email is already subscribed!');

$st = $db->prepare("INSERT INTO newsletter (email) VALUES (?)");
$st->execute([$mail]);
jsonResp(true,'Subscribed successfully! Thank you.');
