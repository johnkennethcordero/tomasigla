<?php
// ============================================================
// TomaSIGLA API — index.php
// ============================================================

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . "/db.php";

$action = $_GET['action'] ?? '';

// Helper to clean decimal values
function dec($val) {
    $v = trim($val ?? '');
    return ($v === '' || $v === null) ? null : (float)$v;
}

switch ($action) {

    // ============================================================
    // AUTH
    // ============================================================

    case 'login':
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';
        $type     = $_POST['type']     ?? 'user';

        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email and password required.']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            exit;
        }

        if ($type === 'admin' && $user['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Access denied. Admins only.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'user'    => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ]
        ]);
        break;

    // ----

    case 'register':
        $name     = trim($_POST['name']     ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password =      $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }

        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $hash]);

        echo json_encode(['success' => true, 'message' => 'Registered successfully.']);
        break;

    // ============================================================
    // TOURIST SPOTS
    // ============================================================

    case 'get_spots':
        $search   = '%' . ($_GET['search']   ?? '') . '%';
        $category =        $_GET['category'] ?? '';

        $sql = "SELECT * FROM tourist_spots WHERE (name LIKE ? OR description LIKE ? OR address LIKE ?)";
        $params = [$search, $search, $search];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " AND status = 'active' ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    // ----

    case 'add_spot':
        $stmt = $pdo->prepare("
            INSERT INTO tourist_spots (name, category, description, address, latitude, longitude, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            $_POST['address']     ?? '',
            dec($_POST['latitude']  ?? ''),
            dec($_POST['longitude'] ?? ''),
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // ----

    case 'update_spot':
        $stmt = $pdo->prepare("
            UPDATE tourist_spots
            SET name=?, category=?, description=?, address=?, latitude=?, longitude=?, image=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            $_POST['address']     ?? '',
            dec($_POST['latitude']  ?? ''),
            dec($_POST['longitude'] ?? ''),
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
            $_POST['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    // ----

    case 'delete_spot':
        $stmt = $pdo->prepare("DELETE FROM tourist_spots WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
        break;

    // ============================================================
    // BUSINESSES
    // ============================================================

    case 'get_businesses':
        $search   = '%' . ($_GET['search']   ?? '') . '%';
        $category =        $_GET['category'] ?? '';

        $sql = "SELECT * FROM businesses WHERE (name LIKE ? OR description LIKE ? OR address LIKE ?)";
        $params = [$search, $search, $search];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        $sql .= " AND status = 'active' ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    // ----

    case 'add_business':
        $stmt = $pdo->prepare("
            INSERT INTO businesses (name, category, description, address, contact, latitude, longitude, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            $_POST['address']     ?? '',
            $_POST['contact']     ?? '',
            dec($_POST['latitude']  ?? ''),
            dec($_POST['longitude'] ?? ''),
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // ----

    case 'update_business':
        $stmt = $pdo->prepare("
            UPDATE businesses
            SET name=?, category=?, description=?, address=?, contact=?, latitude=?, longitude=?, image=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            $_POST['address']     ?? '',
            $_POST['contact']     ?? '',
            dec($_POST['latitude']  ?? ''),
            dec($_POST['longitude'] ?? ''),
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
            $_POST['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    // ----

    case 'delete_business':
        $stmt = $pdo->prepare("DELETE FROM businesses WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
        break;

    // ============================================================
    // PRODUCTS
    // ============================================================

    case 'get_products':
        $search   = '%' . ($_GET['search']   ?? '') . '%';
        $category =        $_GET['category'] ?? '';

        $sql = "
            SELECT p.*, b.name AS business_name
            FROM products p
            LEFT JOIN businesses b ON p.business_id = b.id
            WHERE (p.name LIKE ? OR p.description LIKE ?)
        ";
        $params = [$search, $search];

        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }

        $sql .= " AND p.status = 'active' ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    // ----

    case 'add_product':
        $stmt = $pdo->prepare("
            INSERT INTO products (name, category, description, price, business_id, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $bizId = trim($_POST['business_id'] ?? '');
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            dec($_POST['price']   ?? '') ?? 0,
            $bizId !== '' ? (int)$bizId : null,
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // ----

    case 'update_product':
        $stmt = $pdo->prepare("
            UPDATE products
            SET name=?, category=?, description=?, price=?, business_id=?, image=?, status=?
            WHERE id=?
        ");
        $bizId = trim($_POST['business_id'] ?? '');
        $stmt->execute([
            $_POST['name']        ?? '',
            $_POST['category']    ?? '',
            $_POST['description'] ?? '',
            dec($_POST['price']   ?? '') ?? 0,
            $bizId !== '' ? (int)$bizId : null,
            $_POST['image']       ?? '',
            $_POST['status']      ?? 'active',
            $_POST['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    // ----

    case 'delete_product':
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
        break;

    // ============================================================
    // EVENTS
    // ============================================================

    case 'get_events':
        $search = '%' . ($_GET['search'] ?? '') . '%';
        $type   =        $_GET['type']   ?? '';

        $sql = "SELECT * FROM events WHERE (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $params = [$search, $search, $search];

        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }

        $sql .= " AND status = 'active' ORDER BY event_date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    // ----

    case 'add_event':
        $stmt = $pdo->prepare("
            INSERT INTO events (title, type, description, location, event_date, event_time, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $date = trim($_POST['event_date'] ?? '');
        $time = trim($_POST['event_time'] ?? '');
        $stmt->execute([
            $_POST['title']       ?? '',
            $_POST['type']        ?? '',
            $_POST['description'] ?? '',
            $_POST['location']    ?? '',
            $date !== '' ? $date : null,
            $time !== '' ? $time : null,
            $_POST['status']      ?? 'active',
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        break;

    // ----

    case 'update_event':
        $stmt = $pdo->prepare("
            UPDATE events
            SET title=?, type=?, description=?, location=?, event_date=?, event_time=?, status=?
            WHERE id=?
        ");
        $date = trim($_POST['event_date'] ?? '');
        $time = trim($_POST['event_time'] ?? '');
        $stmt->execute([
            $_POST['title']       ?? '',
            $_POST['type']        ?? '',
            $_POST['description'] ?? '',
            $_POST['location']    ?? '',
            $date !== '' ? $date : null,
            $time !== '' ? $time : null,
            $_POST['status']      ?? 'active',
            $_POST['id'],
        ]);
        echo json_encode(['success' => true]);
        break;

    // ----

    case 'delete_event':
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        echo json_encode(['success' => true]);
        break;

    // ============================================================
    // CHATBOT
    // ============================================================

    case 'chatbot':
        $message = strtolower(trim($_POST['message'] ?? ''));

        $reply = "Sorry, I didn't understand that. Try asking about spots, events, or businesses in Sto. Tomas!";

        if (str_contains($message, 'spot') || str_contains($message, 'tourist')) {
            $stmt = $pdo->query("SELECT name, address FROM tourist_spots WHERE status='active' LIMIT 3");
            $spots = $stmt->fetchAll();
            $list  = implode(', ', array_column($spots, 'name'));
            $reply = "Here are some popular spots: $list. Check the map for directions!";
        } elseif (str_contains($message, 'event') || str_contains($message, 'festival')) {
            $stmt = $pdo->query("SELECT title, event_date FROM events WHERE status='active' ORDER BY event_date ASC LIMIT 3");
            $evts  = $stmt->fetchAll();
            $list  = implode(', ', array_column($evts, 'title'));
            $reply = "Upcoming events: $list. Visit the Events tab for details!";
        } elseif (str_contains($message, 'business') || str_contains($message, 'shop') || str_contains($message, 'food')) {
            $stmt = $pdo->query("SELECT name, category FROM businesses WHERE status='active' LIMIT 3");
            $biz   = $stmt->fetchAll();
            $list  = implode(', ', array_column($biz, 'name'));
            $reply = "Popular businesses: $list. Check the Business tab for more!";
        } elseif (str_contains($message, 'hello') || str_contains($message, 'hi') || str_contains($message, 'hey')) {
            $reply = "Hello! Welcome to TomaSIGLA — your guide to Sto. Tomas, Batangas. How can I help you?";
        }

        echo json_encode(['success' => true, 'reply' => $reply]);
        break;

    // ============================================================
    // DEFAULT
    // ============================================================

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
        break;
}
?>