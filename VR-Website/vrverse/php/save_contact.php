<?php
require_once 'config.php';
setHeaders();
if ($_SERVER['REQUEST_METHOD']!=='POST') jsonResp(false,'Method not allowed.');

$raw     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name    = clean($raw['name']    ?? '');
$mail    = filter_var(trim($raw['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$subject = clean($raw['subject'] ?? '');
$message = clean($raw['message'] ?? '');

if (!$name)    jsonResp(false,'Please enter your name.');
if (!$mail || !filter_var($mail,FILTER_VALIDATE_EMAIL)) jsonResp(false,'Please enter a valid email.');
if (!$message) jsonResp(false,'Please enter your message.');
if (strlen($message)<5) jsonResp(false,'Message is too short.');

$db = getDB();
$st = $db->prepare("INSERT INTO contacts (name,email,subject,message) VALUES (?,?,?,?)");
$st->execute([$name,$mail,$subject,$message]);

jsonResp(true,'Your message has been sent! We will get back to you soon.');
