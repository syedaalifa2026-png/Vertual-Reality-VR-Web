<?php
require_once 'config.php';
setHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResp(false, 'Method not allowed.');

$raw  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$name = clean($raw['full_name'] ?? '');
$mail = filter_var(trim($raw['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$pass = $raw['password'] ?? '';
$conf = $raw['confirm_password'] ?? '';

if (!$name)  jsonResp(false, 'Please enter your full name.');
if (!$mail)  jsonResp(false, 'Please enter your email.');
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) jsonResp(false, 'Invalid email address.');
if (!$pass)  jsonResp(false, 'Please enter a password.');
if (strlen($pass) < 6) jsonResp(false, 'Password must be at least 6 characters.');
if ($pass !== $conf)   jsonResp(false, 'Passwords do not match.');

$db = getDB();
$st = $db->prepare("SELECT id FROM users WHERE email=?");
$st->execute([$mail]);
if ($st->fetch()) jsonResp(false, 'This email is already registered. Please login.');

$hash   = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]);
$avatar = strtoupper(mb_substr($name, 0, 1));

$st = $db->prepare("INSERT INTO users (full_name,email,password,avatar) VALUES (?,?,?,?)");
$st->execute([$name, $mail, $hash, $avatar]);
$uid = (int)$db->lastInsertId();

startSess();
$_SESSION = array();
$_SESSION['user'] = [
    'id' => $uid,
    'full_name' => $name,
    'email' => $mail,
    'avatar' => $avatar
];
session_regenerate_id(true);

error_log("register.php - Session created for user: " . $mail);
error_log("register.php - Session ID: " . session_id());

jsonResp(true, 'Account created successfully! Welcome to VRverse!', ['user' => $_SESSION['user']]);
?>