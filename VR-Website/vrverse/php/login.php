<?php
require_once 'config.php';
setHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResp(false, 'Method not allowed.');

$raw  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$mail = filter_var(trim($raw['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$pass = $raw['password'] ?? '';

if (!$mail || !$pass) jsonResp(false, 'Email and password are required.');
if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) jsonResp(false, 'Invalid email address.');

$db = getDB();
$st = $db->prepare("SELECT id,full_name,email,password,avatar,is_active FROM users WHERE email=?");
$st->execute([$mail]);
$user = $st->fetch();

if (!$user)                                    jsonResp(false, 'No account found with this email.');
if (!password_verify($pass, $user['password'])) jsonResp(false, 'Incorrect password. Please try again.');
if (!$user['is_active'])                       jsonResp(false, 'This account has been deactivated.');

$avatar = $user['avatar'] ?: strtoupper(mb_substr($user['full_name'], 0, 1));

// Start session and set user
startSess();

// Clear any existing session data
$_SESSION = array();

// Set new session data
$_SESSION['user'] = [
    'id' => (int)$user['id'],
    'full_name' => $user['full_name'],
    'email' => $user['email'],
    'avatar' => $avatar
];

// Regenerate session ID for security
session_regenerate_id(true);

error_log("login.php - Session set for user: " . $user['email']);
error_log("login.php - Session ID: " . session_id());

jsonResp(true, 'Login successful! Welcome back!', ['user' => $_SESSION['user']]);
