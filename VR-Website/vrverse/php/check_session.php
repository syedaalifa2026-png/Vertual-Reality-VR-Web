<?php
require_once 'config.php';
setHeaders();
startSess();

// Debug: Log session for troubleshooting
error_log("check_session.php - Session ID: " . session_id());
error_log("check_session.php - Session user: " . print_r($_SESSION['user'] ?? 'null', true));

$user = getUser();
if ($user && isset($user['id'])) {
    jsonResp(true, 'Session active.', ['user' => $user]);
} else {
    jsonResp(false, 'Not logged in.', ['user' => null]);
}
