<?php
// ============================================================
// VRverse Admin Panel — Unified API Backend (admin-api.php)
// Handles all AJAX/form actions for admin-panel.html
// ============================================================

define('VRVERSE_ADMIN', true);

// ── Security Headers ──────────────────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Type: application/json; charset=UTF-8');

// ── Database Config ───────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'vrverse_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Admin Credentials ─────────────────────────────────────
define('ADMIN_USERNAME',      'admin');
define('ADMIN_PASSWORD_HASH', password_hash('Admin@VRverse2025', PASSWORD_BCRYPT, ['cost' => 12]));
// NOTE: In production, replace the above with a stored bcrypt hash string, e.g.:
// define('ADMIN_PASSWORD_HASH', '$2y$12$...');

// ── Security Settings ─────────────────────────────────────
define('ADMIN_SESSION_NAME',     'VRVERSE_ADMIN_SESSION');
define('ADMIN_SESSION_LIFETIME', 3600);   // 1 hour
define('MAX_LOGIN_ATTEMPTS',     5);
define('LOCKOUT_TIME',           900);    // 15 minutes in seconds
define('ITEMS_PER_PAGE',         15);

// ─────────────────────────────────────────────────────────
// Session Bootstrap
// ─────────────────────────────────────────────────────────
function adminStartSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(ADMIN_SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => ADMIN_SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,   // set true in production with HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}
adminStartSession();

// ─────────────────────────────────────────────────────────
// DB Connection (singleton PDO)
// ─────────────────────────────────────────────────────────
function adminGetDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            jsonDie('Database connection failed. Check your configuration.');
        }
    }
    return $pdo;
}

// ─────────────────────────────────────────────────────────
// Auth Helpers
// ─────────────────────────────────────────────────────────
function adminIsLoggedIn(): bool {
    if (empty($_SESSION['admin_logged_in'])) return false;
    if (isset($_SESSION['admin_last_active']) &&
        (time() - $_SESSION['admin_last_active']) > ADMIN_SESSION_LIFETIME) {
        adminLogout();
        return false;
    }
    if (isset($_SESSION['admin_ip']) && $_SESSION['admin_ip'] !== ($_SERVER['REMOTE_ADDR'] ?? '')) {
        adminLogout();
        return false;
    }
    $_SESSION['admin_last_active'] = time();
    return true;
}

function adminRequireLogin(): void {
    if (!adminIsLoggedIn()) {
        jsonDie('Unauthorized. Please log in.', 401);
    }
}

function adminLogout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ─────────────────────────────────────────────────────────
// Rate Limiting (file-based, per-IP)
// ─────────────────────────────────────────────────────────
function getRateLimitFile(): string {
    return sys_get_temp_dir() . '/vrverse_admin_rl_' . md5($_SERVER['REMOTE_ADDR'] ?? '') . '.json';
}

function checkRateLimit(): array {
    $file = getRateLimitFile();
    $data = ['attempts' => 0, 'first_attempt' => time(), 'locked_until' => 0];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
    }
    if ($data['locked_until'] > time()) {
        $wait = (int)ceil(($data['locked_until'] - time()) / 60);
        return ['blocked' => true, 'wait' => $wait, 'attempts' => $data['attempts']];
    }
    if ((time() - $data['first_attempt']) > LOCKOUT_TIME) {
        $data = ['attempts' => 0, 'first_attempt' => time(), 'locked_until' => 0];
        file_put_contents($file, json_encode($data));
    }
    return ['blocked' => false, 'attempts' => $data['attempts']];
}

function recordLoginAttempt(bool $success): void {
    $file = getRateLimitFile();
    $data = ['attempts' => 0, 'first_attempt' => time(), 'locked_until' => 0];
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: $data;
    }
    if ($success) { @unlink($file); return; }
    $data['attempts']++;
    if ($data['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $data['locked_until'] = time() + LOCKOUT_TIME;
    }
    file_put_contents($file, json_encode($data));
}

// ─────────────────────────────────────────────────────────
// CSRF
// ─────────────────────────────────────────────────────────
function adminCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function adminVerifyCsrf(): void {
    $token = $_POST['csrf_token']
          ?? $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? (json_decode(file_get_contents('php://input'), true)['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        jsonDie('Invalid CSRF token.', 403);
    }
}

// ─────────────────────────────────────────────────────────
// Utility Helpers
// ─────────────────────────────────────────────────────────
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function clean(?string $v): string {
    return trim((string)$v);
}

function jsonDie(string $msg, int $code = 500): never {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function jsonOk(array $data = []): never {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function adminPaginate(int $total, int $page, int $perPage): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;
    return ['total' => $total, 'page' => $page, 'per_page' => $perPage,
            'total_pages' => $totalPages, 'offset' => $offset];
}

function adminFormatDate(string $date): string {
    return date('d M Y, h:i A', strtotime($date));
}

function statusBadgeData(string $status): array {
    $map = [
        'confirmed' => ['#e8f5e9','#2e7d32'],
        'pending'   => ['#fff8e1','#f57f17'],
        'shipped'   => ['#e3f2fd','#1565c0'],
        'delivered' => ['#f3e5f5','#6a1b9a'],
        'cancelled' => ['#ffebee','#c62828'],
        'unread'    => ['#fff8e1','#f57f17'],
        'read'      => ['#e8f5e9','#2e7d32'],
        'replied'   => ['#e3f2fd','#1565c0'],
        'approved'  => ['#e8f5e9','#2e7d32'],
        'rejected'  => ['#ffebee','#c62828'],
        'active'    => ['#e8f5e9','#2e7d32'],
        'inactive'  => ['#ffebee','#c62828'],
    ];
    return $map[$status] ?? ['#f0f2f5','#6a737d'];
}

// ─────────────────────────────────────────────────────────
// ROUTER — dispatch by ?action=
// ─────────────────────────────────────────────────────────
$action = clean($_GET['action'] ?? $_POST['action'] ?? '');

switch ($action) {

    // ── AUTH ──────────────────────────────────────────────

    case 'csrf_token':
        echo json_encode(['success' => true, 'token' => adminCsrfToken()]);
        exit;

    case 'login':
        $rl = checkRateLimit();
        if ($rl['blocked']) {
            jsonDie("Too many failed attempts. Please wait {$rl['wait']} minute(s).", 429);
        }
        $username = clean($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
            recordLoginAttempt(true);
            session_regenerate_id(true);
            $_SESSION['admin_logged_in']   = true;
            $_SESSION['admin_username']    = $username;
            $_SESSION['admin_ip']          = $_SERVER['REMOTE_ADDR'] ?? '';
            $_SESSION['admin_last_active'] = time();
            $_SESSION['csrf_token']        = bin2hex(random_bytes(32));
            jsonOk(['username' => $username, 'csrf_token' => $_SESSION['csrf_token']]);
        } else {
            recordLoginAttempt(false);
            $rl2      = checkRateLimit();
            $remaining = MAX_LOGIN_ATTEMPTS - ($rl2['attempts'] ?? 0);
            jsonDie('Invalid username or password.' .
                ($remaining > 0 ? " ({$remaining} attempts remaining)" : ''), 401);
        }

    case 'logout':
        adminLogout();
        jsonOk(['message' => 'Logged out.']);

    case 'check_auth':
        echo json_encode(['success' => true, 'logged_in' => adminIsLoggedIn(),
            'username' => $_SESSION['admin_username'] ?? null]);
        exit;

    // ── DASHBOARD ─────────────────────────────────────────

    case 'dashboard':
        adminRequireLogin();
        $db = adminGetDB();
        $row = $db->query("SELECT COUNT(*) as cnt, COALESCE(SUM(total_amount),0) as rev FROM bookings")->fetch();
        $monthlyRevenue = $db->query(
            "SELECT DATE_FORMAT(created_at,'%b %Y') as month,
                    COUNT(*) as orders,
                    SUM(total_amount) as revenue
             FROM bookings
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY YEAR(created_at), MONTH(created_at)
             ORDER BY created_at ASC"
        )->fetchAll();
        $orderStatus = $db->query("SELECT status, COUNT(*) as cnt FROM bookings GROUP BY status")->fetchAll();
        $recentOrders = $db->query(
            "SELECT order_id,full_name,product_name,total_amount,status,created_at
             FROM bookings ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();
        $recentMessages = $db->query(
            "SELECT id,name,email,subject,status,created_at FROM contacts ORDER BY created_at DESC LIMIT 5"
        )->fetchAll();
        jsonOk([
            'stats' => [
                'orders'     => (int)$row['cnt'],
                'revenue'    => (float)$row['rev'],
                'pending'    => (int)$db->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn(),
                'users'      => (int)$db->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetchColumn(),
                'messages'   => (int)$db->query("SELECT COUNT(*) FROM contacts WHERE status='unread'")->fetchColumn(),
                'reviews'    => (int)$db->query("SELECT COUNT(*) FROM reviews WHERE status='approved'")->fetchColumn(),
                'newsletter' => (int)$db->query("SELECT COUNT(*) FROM newsletter")->fetchColumn(),
                'today'      => (int)$db->query("SELECT COUNT(*) FROM bookings WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
            ],
            'monthly_revenue'  => $monthlyRevenue,
            'order_status'     => $orderStatus,
            'recent_orders'    => $recentOrders,
            'recent_messages'  => $recentMessages,
        ]);

    // ── ORDERS ────────────────────────────────────────────

    case 'orders_list':
        adminRequireLogin();
        $db      = adminGetDB();
        $search  = clean($_GET['search'] ?? '');
        $status  = clean($_GET['status'] ?? '');
        $payment = clean($_GET['payment'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $view    = clean($_GET['view'] ?? '');

        if ($view) {
            $st = $db->prepare("SELECT * FROM bookings WHERE order_id=?");
            $st->execute([$view]);
            $order = $st->fetch();
            jsonOk(['order' => $order ?: null]);
        }

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = "(order_id LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s,$s,$s]);
        }
        if ($status)  { $where[] = "status=?";         $params[] = $status; }
        if ($payment) { $where[] = "payment_method=?"; $params[] = $payment; }
        $whereStr = implode(' AND ', $where);

        $stCount = $db->prepare("SELECT COUNT(*) FROM bookings WHERE {$whereStr}");
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();
        $pag   = adminPaginate($total, $page, ITEMS_PER_PAGE);

        $p2     = array_merge($params, [$pag['per_page'], $pag['offset']]);
        $stList = $db->prepare("SELECT * FROM bookings WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stList->execute($p2);
        $orders = $stList->fetchAll();

        $payments = $db->query("SELECT DISTINCT payment_method FROM bookings ORDER BY payment_method")->fetchAll(PDO::FETCH_COLUMN);
        jsonOk(['orders' => $orders, 'pagination' => $pag, 'payment_methods' => $payments]);

    case 'order_update_status':
        adminRequireLogin();
        adminVerifyCsrf();
        $db      = adminGetDB();
        $orderId = clean($_POST['order_id'] ?? '');
        $status  = clean($_POST['status'] ?? '');
        $allowed = ['pending','confirmed','shipped','delivered','cancelled'];
        if (!$orderId || !in_array($status, $allowed)) jsonDie('Invalid parameters.');
        $db->prepare("UPDATE bookings SET status=? WHERE order_id=?")->execute([$status, $orderId]);
        jsonOk(['message' => "Order #{$orderId} status updated to " . ucfirst($status) . "."]);

    case 'order_delete':
        adminRequireLogin();
        adminVerifyCsrf();
        $db      = adminGetDB();
        $orderId = clean($_POST['order_id'] ?? '');
        if (!$orderId) jsonDie('Invalid order ID.');
        $db->prepare("DELETE FROM bookings WHERE order_id=?")->execute([$orderId]);
        jsonOk(['message' => "Order #{$orderId} deleted."]);

    // ── MESSAGES ──────────────────────────────────────────

    case 'messages_list':
        adminRequireLogin();
        $db     = adminGetDB();
        $search = clean($_GET['search'] ?? '');
        $status = clean($_GET['status'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $view   = (int)($_GET['view'] ?? 0);

        if ($view) {
            $db->prepare("UPDATE contacts SET status='read' WHERE id=? AND status='unread'")->execute([$view]);
            $st = $db->prepare("SELECT * FROM contacts WHERE id=?");
            $st->execute([$view]);
            $msg = $st->fetch();
            jsonOk(['message' => $msg ?: null]);
        }

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = "(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s,$s,$s]);
        }
        if ($status) { $where[] = "status=?"; $params[] = $status; }
        $whereStr = implode(' AND ', $where);

        $stCount = $db->prepare("SELECT COUNT(*) FROM contacts WHERE {$whereStr}");
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();
        $pag   = adminPaginate($total, $page, ITEMS_PER_PAGE);

        $p2     = array_merge($params, [$pag['per_page'], $pag['offset']]);
        $stList = $db->prepare("SELECT * FROM contacts WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stList->execute($p2);
        $messages = $stList->fetchAll();

        $unreadCount = (int)$db->query("SELECT COUNT(*) FROM contacts WHERE status='unread'")->fetchColumn();
        jsonOk(['messages' => $messages, 'pagination' => $pag, 'unread_count' => $unreadCount]);

    case 'message_update_status':
        adminRequireLogin();
        adminVerifyCsrf();
        $db      = adminGetDB();
        $id      = (int)($_POST['msg_id'] ?? 0);
        $status  = clean($_POST['status'] ?? '');
        $allowed = ['unread','read','replied'];
        if (!$id || !in_array($status, $allowed)) jsonDie('Invalid parameters.');
        $db->prepare("UPDATE contacts SET status=? WHERE id=?")->execute([$status, $id]);
        jsonOk(['message' => 'Message status updated.']);

    case 'message_delete':
        adminRequireLogin();
        adminVerifyCsrf();
        $db = adminGetDB();
        $id = (int)($_POST['msg_id'] ?? 0);
        if (!$id) jsonDie('Invalid message ID.');
        $db->prepare("DELETE FROM contacts WHERE id=?")->execute([$id]);
        jsonOk(['message' => 'Message deleted.']);

    case 'messages_mark_all_read':
        adminRequireLogin();
        adminVerifyCsrf();
        adminGetDB()->query("UPDATE contacts SET status='read' WHERE status='unread'");
        jsonOk(['message' => 'All unread messages marked as read.']);

    // ── USERS ─────────────────────────────────────────────

    case 'users_list':
        adminRequireLogin();
        $db     = adminGetDB();
        $search = clean($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = "(full_name LIKE ? OR email LIKE ?)";
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s, $s]);
        }
        if ($status !== '') { $where[] = "is_active=?"; $params[] = (int)$status; }
        $whereStr = implode(' AND ', $where);

        $stCount = $db->prepare("SELECT COUNT(*) FROM users WHERE {$whereStr}");
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();
        $pag   = adminPaginate($total, $page, ITEMS_PER_PAGE);

        $p2     = array_merge($params, [$pag['per_page'], $pag['offset']]);
        $stList = $db->prepare(
            "SELECT u.*, (SELECT COUNT(*) FROM bookings b WHERE b.user_id=u.id) as order_count
             FROM users u WHERE {$whereStr} ORDER BY u.created_at DESC LIMIT ? OFFSET ?"
        );
        $stList->execute($p2);
        $users = $stList->fetchAll();
        jsonOk(['users' => $users, 'pagination' => $pag]);

    case 'user_toggle_active':
        adminRequireLogin();
        adminVerifyCsrf();
        $db  = adminGetDB();
        $uid = (int)($_POST['user_id'] ?? 0);
        if (!$uid) jsonDie('Invalid user ID.');
        $cur = $db->prepare("SELECT is_active FROM users WHERE id=?");
        $cur->execute([$uid]);
        $row = $cur->fetch();
        if (!$row) jsonDie('User not found.');
        $newVal = $row['is_active'] ? 0 : 1;
        $db->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$newVal, $uid]);
        jsonOk(['message' => 'User ' . ($newVal ? 'activated' : 'deactivated') . ' successfully.', 'is_active' => $newVal]);

    case 'user_delete':
        adminRequireLogin();
        adminVerifyCsrf();
        $db  = adminGetDB();
        $uid = (int)($_POST['user_id'] ?? 0);
        if (!$uid) jsonDie('Invalid user ID.');
        $db->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        jsonOk(['message' => 'User deleted successfully.']);

    // ── REVIEWS ───────────────────────────────────────────

    case 'reviews_list':
        adminRequireLogin();
        $db     = adminGetDB();
        $search = clean($_GET['search'] ?? '');
        $status = clean($_GET['status'] ?? '');
        $rating = clean($_GET['rating'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $where  = ['1=1'];
        $params = [];
        if ($search) {
            $where[]  = "(reviewer_name LIKE ? OR review_text LIKE ? OR product LIKE ?)";
            $s        = "%{$search}%";
            $params   = array_merge($params, [$s,$s,$s]);
        }
        if ($status) { $where[] = "status=?"; $params[] = $status; }
        if ($rating) { $where[] = "rating=?"; $params[] = (int)$rating; }
        $whereStr = implode(' AND ', $where);

        $stCount = $db->prepare("SELECT COUNT(*) FROM reviews WHERE {$whereStr}");
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();
        $pag   = adminPaginate($total, $page, ITEMS_PER_PAGE);

        $p2     = array_merge($params, [$pag['per_page'], $pag['offset']]);
        $stList = $db->prepare("SELECT * FROM reviews WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stList->execute($p2);
        $reviews = $stList->fetchAll();

        $pendingCount = (int)$db->query("SELECT COUNT(*) FROM reviews WHERE status='pending'")->fetchColumn();
        $avgRating    = (float)$db->query("SELECT COALESCE(AVG(rating),0) FROM reviews WHERE status='approved'")->fetchColumn();
        jsonOk(['reviews' => $reviews, 'pagination' => $pag,
                'pending_count' => $pendingCount, 'avg_rating' => round($avgRating, 1)]);

    case 'review_update_status':
        adminRequireLogin();
        adminVerifyCsrf();
        $db      = adminGetDB();
        $id      = (int)($_POST['review_id'] ?? 0);
        $status  = clean($_POST['status'] ?? '');
        $allowed = ['pending','approved','rejected'];
        if (!$id || !in_array($status, $allowed)) jsonDie('Invalid parameters.');
        $db->prepare("UPDATE reviews SET status=? WHERE id=?")->execute([$status, $id]);
        jsonOk(['message' => 'Review status updated to ' . ucfirst($status) . '.']);

    case 'review_delete':
        adminRequireLogin();
        adminVerifyCsrf();
        $db = adminGetDB();
        $id = (int)($_POST['review_id'] ?? 0);
        if (!$id) jsonDie('Invalid review ID.');
        $db->prepare("DELETE FROM reviews WHERE id=?")->execute([$id]);
        jsonOk(['message' => 'Review deleted.']);

    case 'reviews_approve_all':
        adminRequireLogin();
        adminVerifyCsrf();
        adminGetDB()->query("UPDATE reviews SET status='approved' WHERE status='pending'");
        jsonOk(['message' => 'All pending reviews approved.']);

    // ── NEWSLETTER ────────────────────────────────────────

    case 'newsletter_list':
        adminRequireLogin();
        $db     = adminGetDB();
        $search = clean($_GET['search'] ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $where  = ['1=1'];
        $params = [];
        if ($search) { $where[] = "email LIKE ?"; $params[] = "%{$search}%"; }
        $whereStr = implode(' AND ', $where);

        $stCount = $db->prepare("SELECT COUNT(*) FROM newsletter WHERE {$whereStr}");
        $stCount->execute($params);
        $total = (int)$stCount->fetchColumn();
        $pag   = adminPaginate($total, $page, ITEMS_PER_PAGE);

        $p2     = array_merge($params, [$pag['per_page'], $pag['offset']]);
        $stList = $db->prepare("SELECT * FROM newsletter WHERE {$whereStr} ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stList->execute($p2);
        $subs = $stList->fetchAll();
        jsonOk(['subscribers' => $subs, 'pagination' => $pag]);

    case 'newsletter_add':
        adminRequireLogin();
        adminVerifyCsrf();
        $db    = adminGetDB();
        $email = filter_var(clean($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonDie('Please enter a valid email address.');
        $st = $db->prepare("SELECT id FROM newsletter WHERE email=?");
        $st->execute([$email]);
        if ($st->fetch()) jsonDie('This email is already subscribed.');
        $db->prepare("INSERT INTO newsletter (email) VALUES (?)")->execute([$email]);
        jsonOk(['message' => 'Subscriber added successfully.']);

    case 'newsletter_delete':
        adminRequireLogin();
        adminVerifyCsrf();
        $db = adminGetDB();
        $id = (int)($_POST['sub_id'] ?? 0);
        if (!$id) jsonDie('Invalid subscriber ID.');
        $db->prepare("DELETE FROM newsletter WHERE id=?")->execute([$id]);
        jsonOk(['message' => 'Subscriber removed.']);

    case 'newsletter_delete_all':
        adminRequireLogin();
        adminVerifyCsrf();
        adminGetDB()->query("DELETE FROM newsletter");
        jsonOk(['message' => 'All subscribers deleted.']);

    case 'newsletter_export_csv':
        adminRequireLogin();
        // Override JSON header for CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
        $all = adminGetDB()->query("SELECT email, created_at FROM newsletter ORDER BY created_at DESC")->fetchAll();
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Email', 'Subscribed Date']);
        foreach ($all as $row) { fputcsv($out, [$row['email'], $row['created_at']]); }
        fclose($out);
        exit;

    // ── SETTINGS ─────────────────────────────────────────

    case 'settings_info':
        adminRequireLogin();
        $db         = adminGetDB();
        $dbSize     = $db->query("SELECT ROUND(SUM(data_length + index_length) / 1024, 2) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'")->fetchColumn();
        $tableCount = $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'")->fetchColumn();
        jsonOk([
            'php_version'      => phpversion(),
            'db_name'          => DB_NAME,
            'db_size_kb'       => (float)$dbSize,
            'table_count'      => (int)$tableCount,
            'session_lifetime' => ADMIN_SESSION_LIFETIME / 60,
            'max_attempts'     => MAX_LOGIN_ATTEMPTS,
            'lockout_minutes'  => LOCKOUT_TIME / 60,
            'server_time'      => date('d M Y, h:i:s A'),
        ]);

    case 'change_password':
        adminRequireLogin();
        adminVerifyCsrf();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, ADMIN_PASSWORD_HASH)) {
            jsonDie('Current password is incorrect.');
        }
        if (strlen($new) < 8) {
            jsonDie('New password must be at least 8 characters.');
        }
        if (!preg_match('/[A-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
            jsonDie('Password must contain at least one uppercase letter and one number.');
        }
        if ($new !== $confirm) {
            jsonDie('New passwords do not match.');
        }

        $newHash    = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        $configPath = __FILE__;
        $content    = file_get_contents($configPath);
        $content    = preg_replace(
            "/define\('ADMIN_PASSWORD_HASH',\s*password_hash\('[^']*',\s*PASSWORD_BCRYPT.*?\)\);/s",
            "define('ADMIN_PASSWORD_HASH', '" . addslashes($newHash) . "');",
            $content
        );
        $content = preg_replace(
            "/define\('ADMIN_PASSWORD_HASH',\s*'[^']+'\);/",
            "define('ADMIN_PASSWORD_HASH', '" . addslashes($newHash) . "');",
            $content
        );
        if (file_put_contents($configPath, $content) !== false) {
            adminLogout();
            jsonOk(['message' => 'Password changed successfully! Please log in again.', 'logout' => true]);
        } else {
            jsonDie('Could not save new password. Check file permissions.');
        }

    // ── DEFAULT ───────────────────────────────────────────

    default:
        jsonDie('Unknown action: ' . e($action), 400);
}