<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$dbname = 'esuuxjty_v12_tracking';
$username = 'esuuxjty_v12_user';
$password = 'Sumedang@98';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // TABEL 1: resi_data - Data resi aktif yang masih dalam proses
    $pdo->exec("CREATE TABLE IF NOT EXISTS resi_data (
        id BIGINT PRIMARY KEY,
        tanggal VARCHAR(100),
        resi VARCHAR(50) UNIQUE,
        ekspedisi VARCHAR(100),
        jamScan VARCHAR(20),
        statusAPI VARCHAR(100) DEFAULT '',
        namaPenerima VARCHAR(100) DEFAULT '',
        tglDiterima VARCHAR(20) DEFAULT '',
        keterangan TEXT DEFAULT '',
        created_by VARCHAR(50) DEFAULT 'System',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_resi (resi),
        INDEX idx_updated (updated_at)
    )");
    // TAMBAH INI SETELAH LINE 31
    // Simple Pusher Function - No Library Needed
function triggerPusher($channel, $event, $data) {
    $app_id = "2022916";
    $key = "f34c00dfd65c19c38d15";
    $secret = "944f8b4185a318cd5fec";
    $cluster = "ap1";
    
    $timestamp = time();
    $body = json_encode($data);
    
    $string_to_sign = "POST\n/apps/$app_id/events\nauth_key=$key&auth_timestamp=$timestamp&auth_version=1.0&body_md5=" . md5($body);
    $signature = hash_hmac('sha256', $string_to_sign, $secret);
    
    $url = "https://api-$cluster.pusherapp.com/apps/$app_id/events";
    
    $post_data = json_encode([
        'name' => $event,
        'channel' => $channel,
        'data' => $body
    ]);
    
    // Debug: Log semua parameter
    error_log("Pusher URL: " . $url);
    error_log("Pusher data: " . $post_data);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . "?auth_key=$key&auth_timestamp=$timestamp&auth_version=1.0&body_md5=" . md5($body) . "&auth_signature=$signature");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    // Debug: Log hasil
    error_log("Pusher HTTP code: " . $http_code);
    error_log("Pusher response: " . $result);
    error_log("Curl error: " . $curl_error);
    
    return $result;
}
    
    
    
    
   // TABEL 2: inventory_products - Master data produk
$pdo->exec("CREATE TABLE IF NOT EXISTS inventory_products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(20) NOT NULL UNIQUE,
    nama_barang VARCHAR(255) NOT NULL,
    unit VARCHAR(10) DEFAULT 'pcs',
    stok_awal INT DEFAULT 0,
    harga DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    INDEX idx_sku (sku)
)");
    
    // TABEL 3: inventory_movements - History pergerakan stock
    $pdo->exec("CREATE TABLE IF NOT EXISTS inventory_movements (
        id BIGINT PRIMARY KEY AUTO_INCREMENT,
        sku VARCHAR(20) NOT NULL,
        type ENUM('in', 'out') NOT NULL,
        qty INT NOT NULL,
        notes VARCHAR(255) DEFAULT '',
        movement_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user VARCHAR(50) DEFAULT 'system',
        device VARCHAR(100) DEFAULT '',
        INDEX idx_sku_date (sku, movement_date)
    )");
    
    // TABEL 2: delivered_data - Data resi yang sudah diterima (FINAL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS delivered_data (
        id BIGINT PRIMARY KEY,
        tanggal VARCHAR(100),
        resi VARCHAR(50) UNIQUE,
        ekspedisi VARCHAR(100),
        statusAPI VARCHAR(100),
        namaPenerima VARCHAR(100),
        tglDiterima VARCHAR(20),
        keterangan TEXT,
        movedDate VARCHAR(30),
        moved_by VARCHAR(50),
        moved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_resi (resi),
        INDEX idx_moved (moved_at)
    )");
    
    // TABEL 3: returns_data - Data resi yang dikembalikan (FINAL)
    $pdo->exec("CREATE TABLE IF NOT EXISTS returns_data (
        id BIGINT PRIMARY KEY,
        tanggal VARCHAR(100),
        resi VARCHAR(50) UNIQUE,
        ekspedisi VARCHAR(100),
        statusAPI VARCHAR(100),
        namaPenerima VARCHAR(100) DEFAULT '',
        tglDiterima VARCHAR(20) DEFAULT '',
        keterangan TEXT,
        returnDate VARCHAR(30),
        moved_by VARCHAR(50),
        moved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_resi (resi),
        INDEX idx_moved (moved_at)
    )");
    
    // TABEL 4: tracking_history - untuk cache API response
    $pdo->exec("CREATE TABLE IF NOT EXISTS tracking_history (
        resi VARCHAR(50) PRIMARY KEY,
        summary JSON,
        detail JSON,
        history JSON,
        lastUpdate VARCHAR(30),
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_updated (updated_at)
    )");
    
    // TABEL 5: settings - untuk API config & preferences
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE,
        setting_value TEXT,
        user VARCHAR(50) DEFAULT 'global',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // TABEL 6: sync_log - untuk realtime tracking
    $pdo->exec("CREATE TABLE IF NOT EXISTS sync_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(50),
        table_name VARCHAR(50),
        resi VARCHAR(50),
        user VARCHAR(50),
        device VARCHAR(100),
        data_before JSON,
        data_after JSON,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_timestamp (timestamp),
        INDEX idx_user (user)
    )");
    
    // TABEL 7: active_users - untuk tracking online users
    $pdo->exec("CREATE TABLE IF NOT EXISTS active_users (
        username VARCHAR(50) PRIMARY KEY,
        device VARCHAR(100),
        last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'online'
    )");
    
    // TABEL 8: autocheck_history - untuk nyimpen history auto check
$pdo->exec("CREATE TABLE IF NOT EXISTS autocheck_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    resi VARCHAR(50) NOT NULL,
    courier VARCHAR(100) NOT NULL,
    before_status VARCHAR(100) NOT NULL,
    after_status VARCHAR(100) NOT NULL,
    result VARCHAR(50) NOT NULL,
    cost INT DEFAULT 15,
    check_time TIMESTAMP NOT NULL,
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    INDEX idx_date (check_time),
    INDEX idx_user (user),
    INDEX idx_resi (resi)
)");
    
    // TABEL 9: autocheck_events - untuk log start/stop auto check
$pdo->exec("CREATE TABLE IF NOT EXISTS autocheck_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_type ENUM('START', 'STOP') NOT NULL,
    interval_minutes INT DEFAULT 0,
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    timestamp TIMESTAMP NOT NULL,
    INDEX idx_user_time (user, timestamp)
)");

// TABEL 10: autocheck_positions - untuk simpan posisi auto check
$pdo->exec("CREATE TABLE IF NOT EXISTS autocheck_positions (
    user VARCHAR(50) PRIMARY KEY,
    position INT DEFAULT 0,
    device VARCHAR(100) DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");


// CUSTOMER ENGAGEMENT TABLES
// TABEL 1: customer_profiles - Master data customer
$pdo->exec("CREATE TABLE IF NOT EXISTS customer_profiles (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    telepon VARCHAR(15) NOT NULL,
    alamat TEXT DEFAULT '',
    platform_preference VARCHAR(20) DEFAULT 'both',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(15,2) DEFAULT 0,
    last_order_date DATE DEFAULT NULL,
    customer_segment VARCHAR(20) DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    INDEX idx_telepon (telepon),
    INDEX idx_nama (nama),
    INDEX idx_segment (customer_segment)
)");

// TABEL 2: customer_orders - Orders dengan profit calculation
$pdo->exec("CREATE TABLE IF NOT EXISTS customer_orders (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT NOT NULL,
    platform VARCHAR(20) NOT NULL,
    tanggal DATE NOT NULL,
    sku VARCHAR(20) NOT NULL,
    nama_produk VARCHAR(255) NOT NULL,
    qty INT NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    hpp DECIMAL(15,2) NOT NULL,
    hpp_total DECIMAL(15,2) NOT NULL,
    profit DECIMAL(15,2) NOT NULL,
    margin_pct DECIMAL(5,2) NOT NULL,
    resi VARCHAR(50) DEFAULT '',
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    INDEX idx_customer (customer_id),
    INDEX idx_platform (platform),
    INDEX idx_resi (resi),
    INDEX idx_tanggal (tanggal),
    FOREIGN KEY (customer_id) REFERENCES customer_profiles(id)
)");

// TABEL BARU: WA Notification Log - TAMBAH INI
$pdo->exec("CREATE TABLE IF NOT EXISTS wa_notification_log (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(20) DEFAULT 'manual',
    resi VARCHAR(50) DEFAULT '',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_status VARCHAR(20) DEFAULT 'sent',
    user VARCHAR(50) DEFAULT 'system',
    device VARCHAR(100) DEFAULT '',
    INDEX idx_phone (phone),
    INDEX idx_resi (resi),
    INDEX idx_sent_at (sent_at)
)");

// TABEL 3: notification_history - WhatsApp notifications tracking
$pdo->exec("CREATE TABLE IF NOT EXISTS notification_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT NOT NULL,
    order_id BIGINT DEFAULT NULL,
    message_type VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_status VARCHAR(20) DEFAULT 'pending',
    webhook_response JSON DEFAULT NULL,
    user VARCHAR(50) DEFAULT 'system',
    INDEX idx_customer (customer_id),
    INDEX idx_order (order_id),
    INDEX idx_sent_at (sent_at),
    FOREIGN KEY (customer_id) REFERENCES customer_profiles(id)
)");

// TABEL 4: ab_test_campaigns - A/B testing untuk WhatsApp messages
$pdo->exec("CREATE TABLE IF NOT EXISTS ab_test_campaigns (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(100) NOT NULL,
    message_type VARCHAR(50) NOT NULL,
    base_template TEXT NOT NULL,
    variant_a TEXT NOT NULL,
    variant_b TEXT NOT NULL,
    variant_c TEXT DEFAULT NULL,
    target_segment VARCHAR(50) DEFAULT 'all',
    sent_a INT DEFAULT 0,
    sent_b INT DEFAULT 0,
    sent_c INT DEFAULT 0,
    opened_a INT DEFAULT 0,
    opened_b INT DEFAULT 0,
    opened_c INT DEFAULT 0,
    clicked_a INT DEFAULT 0,
    clicked_b INT DEFAULT 0,
    clicked_c INT DEFAULT 0,
    winning_variant VARCHAR(1) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user VARCHAR(50) DEFAULT 'system',
    INDEX idx_campaign_name (campaign_name),
    INDEX idx_status (status)
)");

    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $user = $input['user'] ?? 'Anonymous';
    $device = $input['device'] ?? 'Unknown Device';
    
    // Update user activity untuk realtime tracking
    if ($user !== 'Anonymous') {
        $pdo->prepare("REPLACE INTO active_users (username, device) VALUES (?, ?)")
            ->execute([$user, $device]);
    }
    
    // TABEL untuk Auto Reports Settings
$pdo->exec("CREATE TABLE IF NOT EXISTS auto_reports_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(50) NOT NULL,
    schedule_time TIME NOT NULL,
    target_group VARCHAR(100) DEFAULT '',
    target_admin VARCHAR(100) DEFAULT '',
    enabled TINYINT(1) DEFAULT 1,
    report_types JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_enabled (user, enabled)
)");

// TABEL untuk History pengiriman report  
$pdo->exec("CREATE TABLE IF NOT EXISTS auto_reports_history (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    report_type VARCHAR(50) NOT NULL,
    sent_to VARCHAR(500) NOT NULL,
    sent_at TIMESTAMP NOT NULL,
    message_preview TEXT,
    status VARCHAR(20) DEFAULT 'sent',
    user VARCHAR(50) DEFAULT 'system',
    INDEX idx_sent_at (sent_at)
)");



    
    switch ($action) {
        case 'save_resi':
    // Insert resi baru ke table aktif - FIXED: Remove manual ID to prevent collision
    $stmt = $pdo->prepare("INSERT INTO resi_data (tanggal, resi, ekspedisi, jamScan, statusAPI, namaPenerima, tglDiterima, keterangan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE ekspedisi=VALUES(ekspedisi), statusAPI=VALUES(statusAPI), updated_at=NOW()");
    $stmt->execute([
        $input['tanggal'],
        $input['resi'],
        $input['ekspedisi'],
        $input['jamScan'],
        $input['statusAPI'] ?? '',
        $input['namaPenerima'] ?? '',
        $input['tglDiterima'] ?? '',
        $input['keterangan'] ?? '',
        $user
    ]);
    
    // Get the actual inserted/updated ID
    $actualId = $pdo->lastInsertId();
    if (!$actualId) {
        // If update (not insert), get existing ID
        $stmt = $pdo->prepare("SELECT id FROM resi_data WHERE resi = ?");
        $stmt->execute([$input['resi']]);
        $actualId = $stmt->fetchColumn();
    }
    
    logSync($pdo, 'save_resi', 'resi_data', $input['resi'], $user, $device, null, $input);
    
    echo json_encode([
        'success' => true, 
        'id' => (int)$actualId,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    break;
            
        case 'load_all':
    // Load data resi aktif (dengan jamScan)
    $resi = $pdo->query("SELECT id, ROW_NUMBER() OVER (ORDER BY updated_at DESC) as no, tanggal, resi, ekspedisi, jamScan, statusAPI, namaPenerima, tglDiterima, keterangan, DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as last_update FROM resi_data ORDER BY updated_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Load data delivered (tanpa jamScan, jamScan dikosongkan)
    $delivered = $pdo->query("SELECT id, ROW_NUMBER() OVER (ORDER BY moved_at DESC) as no, tanggal, resi, ekspedisi, '' as jamScan, statusAPI, namaPenerima, tglDiterima, keterangan, movedDate, DATE_FORMAT(moved_at, '%Y-%m-%d %H:%i:%s') as last_update FROM delivered_data ORDER BY moved_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Load data returns (tanpa jamScan, jamScan dikosongkan)
    $returns = $pdo->query("SELECT id, ROW_NUMBER() OVER (ORDER BY moved_at DESC) as no, tanggal, resi, ekspedisi, '' as jamScan, statusAPI, '' as namaPenerima, '' as tglDiterima, keterangan, returnDate, DATE_FORMAT(moved_at, '%Y-%m-%d %H:%i:%s') as last_update FROM returns_data ORDER BY moved_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Load tracking history
    $tracking_raw = $pdo->query("SELECT resi, summary, detail, history, lastUpdate FROM tracking_history")->fetchAll(PDO::FETCH_ASSOC);
    $tracking_history = [];
    foreach($tracking_raw as $track) {
        $tracking_history[$track['resi']] = [
            'summary' => json_decode($track['summary'], true),
            'detail' => json_decode($track['detail'], true),
            'history' => json_decode($track['history'], true),
            'lastUpdate' => $track['lastUpdate']
        ];
    }
    
// Load inventory products - PERBAIKI INI  
$products = $pdo->query("SELECT id, sku, nama_barang, unit, stok_awal, harga, user, created_at, updated_at FROM inventory_products ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Load inventory movements - TAMBAHIN INI JUGA
$movements = $pdo->query("SELECT m.*, p.nama_barang FROM inventory_movements m LEFT JOIN inventory_products p ON m.sku = p.sku ORDER BY m.created_at DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'resiData' => $resi,
    'deliveredData' => $delivered,
    'returnsData' => $returns,
    'trackingHistory' => $tracking_history,
    'productsData' => $products,
    'movementsData' => $movements,
    'timestamp' => date('Y-m-d H:i:s')
]);
    break;
            
        case 'update_status':
            // Update status resi yang masih aktif
            $old_data = $pdo->prepare("SELECT * FROM resi_data WHERE resi=?");
            $old_data->execute([$input['resi']]);
            $old = $old_data->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $pdo->prepare("UPDATE resi_data SET statusAPI=?, namaPenerima=?, tglDiterima=?, keterangan=?, updated_at=NOW() WHERE resi=?");
            $stmt->execute([
                $input['statusAPI'] ?? $input['status'] ?? '',
                $input['namaPenerima'] ?? '',
                $input['tglDiterima'] ?? '',
                $input['keterangan'] ?? '',
                $input['resi']
            ]);
            
            logSync($pdo, 'update_status', 'resi_data', $input['resi'], $user, $device, $old, $input);
            
            echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
            break;
            
        case 'save_tracking_history':
            // Save tracking history dari API response
            $stmt = $pdo->prepare("REPLACE INTO tracking_history (resi, summary, detail, history, lastUpdate) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['resi'],
                json_encode($input['summary'] ?? []),
                json_encode($input['detail'] ?? []),
                json_encode($input['history'] ?? []),
                $input['lastUpdate'] ?? date('c')
            ]);
            
            echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
            break;
            
        case 'move_to_delivered':
            $pdo->beginTransaction();
            
            // Get data dari resi_data
            $stmt = $pdo->prepare("SELECT * FROM resi_data WHERE resi=?");
            $stmt->execute([$input['resi']]);
            $resi_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resi_data) {
                // Insert ke delivered_data (tanpa jamScan - data final)
                $stmt = $pdo->prepare("INSERT INTO delivered_data (id, tanggal, resi, ekspedisi, statusAPI, namaPenerima, tglDiterima, keterangan, movedDate, moved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $resi_data['id'],
                    $resi_data['tanggal'],
                    $resi_data['resi'],
                    $resi_data['ekspedisi'],
                    $input['statusAPI'] ?? $resi_data['statusAPI'] ?? 'DELIVERED',
                    $input['namaPenerima'] ?? $resi_data['namaPenerima'] ?? 'Penerima',
                    $input['tglDiterima'] ?? $resi_data['tglDiterima'] ?? date('Y-m-d'),
                    $input['keterangan'] ?? $resi_data['keterangan'] ?? 'Paket telah diterima',
                    $input['movedDate'] ?? date('c'),
                    $user
                ]);
                
                // Delete dari resi_data
                $pdo->prepare("DELETE FROM resi_data WHERE resi=?")->execute([$input['resi']]);
                
                logSync($pdo, 'move_delivered', 'delivered_data', $input['resi'], $user, $device, $resi_data, $input);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
            } else {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Resi not found']);
            }
            break;
            
        case 'move_to_returns':
            $pdo->beginTransaction();
            
            // Get data dari resi_data
            $stmt = $pdo->prepare("SELECT * FROM resi_data WHERE resi=?");
            $stmt->execute([$input['resi']]);
            $resi_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resi_data) {
                // Insert ke returns_data (tanpa jamScan - data final)
                $stmt = $pdo->prepare("INSERT INTO returns_data (id, tanggal, resi, ekspedisi, statusAPI, keterangan, returndate, moved_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([
    $resi_data['id'],
    $resi_data['tanggal'],
    $resi_data['resi'],
    $resi_data['ekspedisi'],
    $input['statusAPI'] ?? $resi_data['statusAPI'] ?? 'RETURNED TO SENDER',
    $input['keterangan'] ?? $resi_data['keterangan'] ?? 'Paket dikembalikan ke pengirim',
    $input['returnDate'] ?? date('c'),
    $user
]);
                
                // Delete dari resi_data
                $pdo->prepare("DELETE FROM resi_data WHERE resi=?")->execute([$input['resi']]);
                
                logSync($pdo, 'move_returns', 'returns_data', $input['resi'], $user, $device, $resi_data, $input);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
            } else {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Resi not found']);
            }
            break;
            
        case 'check_sync':
            // REALTIME SYNC - cek update dari user lain
            $last_check = $input['last_check'] ?? '1970-01-01 00:00:00';
            $current_user = $input['user'] ?? '';
            
            $stmt = $pdo->prepare("SELECT * FROM sync_log WHERE timestamp > ? AND user != ? ORDER BY timestamp DESC LIMIT 20");
            $stmt->execute([$last_check, $current_user]);
            $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get active users untuk indicator
            $active_users = $pdo->query("SELECT username, device, last_seen FROM active_users WHERE last_seen > DATE_SUB(NOW(), INTERVAL 2 MINUTE) ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'has_updates' => count($updates) > 0,
                'updates' => $updates,
                'active_users' => $active_users,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'delete_resi':
            $table = $input['table'] ?? 'resi_data';
            $allowed_tables = ['resi_data', 'delivered_data', 'returns_data'];
            
            if (in_array($table, $allowed_tables)) {
                // Get old data untuk log
                $old_data = $pdo->prepare("SELECT * FROM $table WHERE resi=?");
                $old_data->execute([$input['resi']]);
                $old = $old_data->fetch(PDO::FETCH_ASSOC);
                
                // Delete
                $stmt = $pdo->prepare("DELETE FROM $table WHERE resi=?");
                $stmt->execute([$input['resi']]);
                
                // Delete tracking history juga
                $pdo->prepare("DELETE FROM tracking_history WHERE resi=?")->execute([$input['resi']]);
                
                logSync($pdo, 'delete_resi', $table, $input['resi'], $user, $device, $old, null);
                
                echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid table']);
            }
            break;
            
        case 'get_stats':
            // Get statistics untuk dashboard
            $total_resi = $pdo->query("SELECT COUNT(*) as count FROM resi_data")->fetch()['count'];
            $total_delivered = $pdo->query("SELECT COUNT(*) as count FROM delivered_data")->fetch()['count'];
            $total_returns = $pdo->query("SELECT COUNT(*) as count FROM returns_data")->fetch()['count'];
            $last_activity = $pdo->query("SELECT MAX(timestamp) as last_time FROM sync_log")->fetch()['last_time'];
            
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_resi' => (int)$total_resi,
                    'total_delivered' => (int)$total_delivered,
                    'total_returns' => (int)$total_returns,
                    'last_activity' => $last_activity
                ],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
           case 'save_settings':
    $user = $input['user'] ?? '';  
    $settings = $input['settings'] ?? [];
    
    if (!$user || !$settings) {  
        echo json_encode(['success' => false, 'message' => 'Missing user or settings']);
        break;
    }
    
    try {
        foreach ($settings as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, user) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP");
            $stmt->execute([$key, $value, $user]);
        }
        
        echo json_encode(['success' => true, 'timestamp' => date('Y-m-d H:i:s')]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
case 'load_settings':
    $user = $input['user'] ?? '';  // TAMBAH INI
    
    if (!$user) {  // TAMBAH VALIDATION
        echo json_encode(['success' => false, 'message' => 'Missing user']);
        break;
    }
    
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE user = ?");
    $stmt->execute([$user]);
    $settings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $settings = [];
    foreach($settings_raw as $setting) {
        $settings[$setting['setting_key']] = $setting['setting_value'];
    }
    
    echo json_encode([
        'success' => true,
        'settings' => $settings,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    
    break;
   // INVENTORY CASES - TAMBAH DISINI
case 'save_product':
// Save new product to inventory
$sku = $input['sku'] ?? '';
$nama_barang = $input['nama_barang'] ?? '';
$unit = $input['unit'] ?? 'pcs';
$stok_awal = (int)($input['stok_awal'] ?? 0);
$harga = (float)($input['harga'] ?? 0);  // TAMBAHIN INI
$user = $input['user'] ?? 'system';

if (!$sku || !$nama_barang) {
    echo json_encode(['success' => false, 'message' => 'SKU dan nama barang required']);
    break;
}

try {
    $stmt = $pdo->prepare("INSERT INTO inventory_products (sku, nama_barang, unit, stok_awal, harga, user, device) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$sku, $nama_barang, $unit, $stok_awal, $harga, $user, $input['device'] ?? '']);  // TAMBAHIN HARGA
    
    // Debug: Log sebelum trigger pusher
    error_log("Before pusher trigger for SKU: " . $sku);
    
    // Trigger Pusher untuk inventory update
    $pusher_result = triggerPusher('inventory-channel', 'product-added', [
        'sku' => $sku,
        'nama_barang' => $nama_barang,
        'user' => $user,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Debug: Log hasil pusher
    error_log("Pusher result: " . print_r($pusher_result, true));
    
    echo json_encode(['success' => true, 'message' => 'Product saved successfully', 'pusher_debug' => $pusher_result]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
break;
        
    case 'load_inventory':
        // Load all inventory data
        try {
            $stmt = $pdo->query("SELECT * FROM inventory_products ORDER BY created_at DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update_product':
    $id = $input['id'] ?? '';
    $sku = $input['sku'] ?? '';
    $nama_barang = $input['nama_barang'] ?? '';
    $unit = $input['unit'] ?? 'pcs';
    $stok_awal = (int)($input['stok_awal'] ?? 0);
    $harga = (float)($input['harga'] ?? 0);  // TAMBAHIN INI
    
    if (!$id || !$sku || !$nama_barang) {
        echo json_encode(['success' => false, 'message' => 'ID, SKU dan nama barang required']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE inventory_products SET sku=?, nama_barang=?, unit=?, stok_awal=?, harga=? WHERE id=?");
        $stmt->execute([$sku, $nama_barang, $unit, $stok_awal, $harga, $id]);  // TAMBAHIN HARGA
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    case 'delete_product':
        $id = $input['id'] ?? '';
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM inventory_products WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'search_product':
        $search = $input['search'] ?? '';
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM inventory_products WHERE sku LIKE ? OR nama_barang LIKE ? ORDER BY created_at DESC");
            $stmt->execute(["%$search%", "%$search%"]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        case 'save_movement':
        // Save stock movement
        $sku = $input['sku'] ?? '';
        $type = $input['type'] ?? '';  // 'in' or 'out'
        $qty = (int)($input['qty'] ?? 0);
        $notes = $input['notes'] ?? '';
        $movement_date = $input['movement_date'] ?? date('Y-m-d');
        $user = $input['user'] ?? 'system';
        
        if (!$sku || !$type || $qty <= 0) {
            echo json_encode(['success' => false, 'message' => 'SKU, type, dan qty required']);
            break;
        }
        
        if (!in_array($type, ['in', 'out'])) {
            echo json_encode(['success' => false, 'message' => 'Type harus in atau out']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO inventory_movements (sku, type, qty, notes, movement_date, user, device) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$sku, $type, $qty, $notes, $movement_date, $user, $input['device'] ?? '']);
            echo json_encode(['success' => true, 'message' => 'Movement saved successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'load_movements':
        // Load movement history
        try {
            $stmt = $pdo->query("SELECT m.*, p.nama_barang FROM inventory_movements m LEFT JOIN inventory_products p ON m.sku = p.sku ORDER BY m.created_at DESC LIMIT 100");
            $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'movements' => $movements]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
        case 'delete_movement':
    // Delete movement - SIMPLIFIED VERSION
    $sku = $input['sku'] ?? '';
    $user = $input['user'] ?? 'system';
    
    if (!$sku) {
        echo json_encode(['success' => false, 'message' => 'SKU required']);
        break;
    }
    
    
    try {
        // Delete latest movement untuk SKU ini dari user ini (lebih aman)
        $stmt = $pdo->prepare("DELETE FROM inventory_movements WHERE sku = ? AND user = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$sku, $user]);
        
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected > 0) {
            echo json_encode(['success' => true, 'message' => 'Latest movement deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No movement found for this SKU and user']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    
    case 'get_stock_report':
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    
    try {
        $sql = "SELECT p.sku, p.nama_barang, p.unit, p.stok_awal, p.harga,
                COALESCE(SUM(CASE WHEN m.type = 'in' THEN m.qty ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN m.type = 'out' THEN m.qty ELSE 0 END), 0) as total_out,
                (p.stok_awal + COALESCE(SUM(CASE WHEN m.type = 'in' THEN m.qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN m.type = 'out' THEN m.qty ELSE 0 END), 0)) as current_stock
                FROM inventory_products p 
                LEFT JOIN inventory_movements m ON p.sku = m.sku";
        
        $params = [];
        if ($start_date && $end_date) {
            $sql .= " AND m.movement_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
        }
        
        $sql .= " GROUP BY p.sku ORDER BY p.nama_barang";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $report = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $report]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'get_movement_history':
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    $sku = $input['sku'] ?? '';
    
    try {
        $sql = "SELECT m.*, p.nama_barang FROM inventory_movements m 
                LEFT JOIN inventory_products p ON m.sku = p.sku WHERE 1=1";
        $params = [];
        
        if ($start_date && $end_date) {
            $sql .= " AND m.movement_date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        }
        
        if ($sku) {
            $sql .= " AND m.sku = ?";
            $params[] = $sku;
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $history]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'get_low_stock_report':
    $threshold = (int)($input['threshold'] ?? 5);
    
    try {
        $sql = "SELECT p.sku, p.nama_barang, p.unit, p.stok_awal,
                COALESCE(SUM(CASE WHEN m.type = 'in' THEN m.qty ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN m.type = 'out' THEN m.qty ELSE 0 END), 0) as total_out,
                (p.stok_awal + COALESCE(SUM(CASE WHEN m.type = 'in' THEN m.qty ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN m.type = 'out' THEN m.qty ELSE 0 END), 0)) as current_stock
                FROM inventory_products p 
                LEFT JOIN inventory_movements m ON p.sku = m.sku 
                GROUP BY p.sku 
                HAVING current_stock <= ? 
                ORDER BY current_stock ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$threshold]);
        $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $lowStock, 'threshold' => $threshold]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'get_top_products':
    $start_date = $input['start_date'] ?? '';
    $end_date = $input['end_date'] ?? '';
    $limit = (int)($input['limit'] ?? 10);
    
    try {
        $sql = "SELECT m.sku, p.nama_barang, p.unit,
                SUM(m.qty) as total_sold,
                COUNT(m.id) as transaction_count
                FROM inventory_movements m 
                LEFT JOIN inventory_products p ON m.sku = p.sku 
                WHERE m.type = 'out'";
        
        $params = [];
        if ($start_date && $end_date) {
            $sql .= " AND m.movement_date BETWEEN ? AND ?";
            $params = [$start_date, $end_date];
        }
        
        $sql .= " GROUP BY m.sku ORDER BY total_sold DESC LIMIT " . (int)$limit;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $topProducts]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    
    case 'save_autocheck_history':
    // Save auto check history
    $resi = $input['resi'] ?? '';
    $courier = $input['courier'] ?? '';
    $before_status = $input['before_status'] ?? '';
    $after_status = $input['after_status'] ?? '';
    $result = $input['result'] ?? '';
    $cost = (int)($input['cost'] ?? 15);
    $check_time = $input['check_time'] ?? date('Y-m-d H:i:s');
    $user = $input['user'] ?? 'system';
    $device = $input['device'] ?? '';
    
    if (!$resi || !$courier) {
        echo json_encode(['success' => false, 'message' => 'Resi dan courier required']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO autocheck_history (resi, courier, before_status, after_status, result, cost, check_time, user, device) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$resi, $courier, $before_status, $after_status, $result, $cost, $check_time, $user, $device]);
        echo json_encode(['success' => true, 'message' => 'Auto check history saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
   case 'load_autocheck_history':
    // Load auto check history (filter by date if provided)
    $user = $input['user'] ?? '';
    $date = $input['date'] ?? date('Y-m-d'); // Default hari ini
    
    try {
        // Load history untuk hari tertentu, sorted by newest first
        $stmt = $pdo->prepare("SELECT * FROM autocheck_history WHERE check_time LIKE ? ORDER BY check_time DESC LIMIT 50");
        $stmt->execute([$date . '%']);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // TAMBAH INI: Query stats tanpa limit (untuk dashboard yang benar)
        $statsStmt = $pdo->prepare("SELECT COUNT(*) as total_checks, SUM(cost) as total_cost FROM autocheck_history WHERE check_time LIKE ?");
        $statsStmt->execute([$date . '%']);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'history' => $history,
            'date' => $date,
            'count' => count($history),
            'stats' => $stats  // TAMBAH INI JUGA
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    
    case 'save_autocheck_event':
    // Save auto check start/stop events
    $event_type = $input['event_type'] ?? '';
    $interval_minutes = (int)($input['interval_minutes'] ?? 0);
    $user = $input['user'] ?? 'system';
    $device = $input['device'] ?? '';
    $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
    
    if (!$event_type || !in_array($event_type, ['START', 'STOP'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid event type']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO autocheck_events (event_type, interval_minutes, user, device, timestamp) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$event_type, $interval_minutes, $user, $device, $timestamp]);
        echo json_encode(['success' => true, 'message' => 'Auto check event saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'load_autocheck_position':
    // Load auto check position untuk user
    $user = $input['user'] ?? '';
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User required']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT position FROM autocheck_positions WHERE user = ?");
        $stmt->execute([$user]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $position = $result ? (int)$result['position'] : 0;
        echo json_encode(['success' => true, 'position' => $position]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'save_autocheck_position':
    // Save auto check position untuk user
    $user = $input['user'] ?? '';
    $position = (int)($input['position'] ?? 0);
    $device = $input['device'] ?? '';
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User required']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("REPLACE INTO autocheck_positions (user, position, device) VALUES (?, ?, ?)");
        $stmt->execute([$user, $position, $device]);
        echo json_encode(['success' => true, 'message' => 'Position saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    
   case 'save_auto_report_settings':
    $schedule_time = $input['schedule_time'] ?? '';
    $target_group = $input['target_group'] ?? '';
    $target_admin = $input['target_admin'] ?? '';
    $report_types = $input['report_types'] ?? [];
    $enabled = $input['enabled'] ?? 1;
    
    if (!$schedule_time) {
        echo json_encode(['success' => false, 'message' => 'Schedule time required']);
        break;
    }
    
    try {
        // Check if user already has settings
        $checkStmt = $pdo->prepare("SELECT id FROM auto_reports_settings WHERE user = ?");
        $checkStmt->execute([$user]);
        $existingId = $checkStmt->fetchColumn();
        
        if ($existingId) {
            // UPDATE existing record
            $stmt = $pdo->prepare("UPDATE auto_reports_settings SET schedule_time=?, target_group=?, target_admin=?, enabled=?, report_types=?, updated_at=CURRENT_TIMESTAMP WHERE user=?");
            $stmt->execute([$schedule_time, $target_group, $target_admin, $enabled, json_encode($report_types), $user]);
        } else {
            // INSERT new record
            $stmt = $pdo->prepare("INSERT INTO auto_reports_settings (user, schedule_time, target_group, target_admin, enabled, report_types) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user, $schedule_time, $target_group, $target_admin, $enabled, json_encode($report_types)]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Auto report settings saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'load_auto_report_settings':
    try {
        $stmt = $pdo->prepare("SELECT * FROM auto_reports_settings WHERE user = ?");
        $stmt->execute([$user]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings && $settings['report_types']) {
            $settings['report_types'] = json_decode($settings['report_types'], true);
        }
        
        echo json_encode(['success' => true, 'settings' => $settings]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'save_report_history':
    $report_type = $input['report_type'] ?? '';
    $sent_to = $input['sent_to'] ?? '';
    $message_preview = $input['message_preview'] ?? '';
    $status = $input['status'] ?? 'sent';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO auto_reports_history (report_type, sent_to, sent_at, message_preview, status, user) VALUES (?, ?, NOW(), ?, ?, ?)");
        $stmt->execute([$report_type, $sent_to, $message_preview, $status, $user]);
        
        echo json_encode(['success' => true, 'message' => 'Report history saved']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    case 'load_report_history':
    try {
        $stmt = $pdo->prepare("SELECT * FROM auto_reports_history WHERE user = ? ORDER BY sent_at DESC LIMIT 50");
        $stmt->execute([$user]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'history' => $history]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    case 'check_report_sent_today':
    $date = $input['date'] ?? date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM auto_reports_history WHERE user = ? AND DATE(sent_at) = ?");
        $stmt->execute([$user, $date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $already_sent = (int)$result['count'] > 0;
        
        echo json_encode([
            'success' => true, 
            'already_sent' => $already_sent,
            'count' => (int)$result['count'],
            'date' => $date
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    case 'save_order_with_movements':
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // 1. Save Customer
        $customerNama = $input['customer_nama'] ?? '';
        $customerHP = $input['customer_hp'] ?? '';
        $customerAlamat = $input['customer_alamat'] ?? '';
        $platform = $input['platform'] ?? '';
        $tanggal = $input['tanggal'] ?? date('Y-m-d');
        $products = $input['products'] ?? [];
        
        // Validation
        if (!$customerNama || !$customerHP || !$platform || empty($products)) {
            throw new Exception('Customer info dan products wajib diisi');
        }
        
        // Save customer
        $stmt = $pdo->prepare("INSERT INTO customer_profiles (nama, telepon, alamat, platform_preference, user, device) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customerNama, $customerHP, $customerAlamat, $platform, $user, $device]);
        $customerId = $pdo->lastInsertId();
        
        $orderIds = [];
        $movementCount = 0;
        
        // 2. Loop Save Orders + Movements
        foreach ($products as $product) {
            $sku = $product['sku'] ?? '';
            $qty = (int)($product['qty'] ?? 0);
            $hargaJual = (float)($product['hargaJual'] ?? 0);
            $hpp = (float)($product['hpp'] ?? 0);
            $namaBarang = $product['nama'] ?? '';
            
            if (!$sku || $qty <= 0 || $hargaJual <= 0) {
                throw new Exception("Product data tidak lengkap: {$sku}");
            }
            
            // Calculate profit
            $hppTotal = $hpp * $qty;
            $profit = $hargaJual - $hppTotal;
            $margin = $hargaJual > 0 ? (($profit / $hargaJual) * 100) : 0;
            
            // Save order
            $stmt = $pdo->prepare("INSERT INTO customer_orders (customer_id, platform, tanggal, sku, nama_produk, qty, harga_jual, hpp, hpp_total, profit, margin_pct, status, user, device) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?, ?)");
            $stmt->execute([$customerId, $platform, $tanggal, $sku, $namaBarang, $qty, $hargaJual, $hpp, $hppTotal, $profit, $margin, $user, $device]);
            $orderIds[] = $pdo->lastInsertId();
            
            // Save movement (stock out)
            $movementNotes = "Orderan " . ucfirst($platform);
            $stmt = $pdo->prepare("INSERT INTO inventory_movements (sku, type, qty, notes, movement_date, user, device) VALUES (?, 'out', ?, ?, ?, ?, ?)");
            $stmt->execute([$sku, $qty, $movementNotes, $tanggal, $user, $device]);
            $movementCount++;
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Trigger Pusher notifications
        triggerPusher('inventory-channel', 'orders-updated', [
            'customer_id' => $customerId,
            'customer_name' => $customerNama,
            'platform' => $platform,
            'orders_count' => count($orderIds),
            'movements_count' => $movementCount,
            'user' => $user,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        triggerPusher('inventory-channel', 'inventory-updated', [
            'type' => 'stock_movement',
            'movements_count' => $movementCount,
            'user' => $user,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Order berhasil disimpan + Stock terupdate',
            'customer_id' => $customerId,
            'order_ids' => $orderIds,
            'movements_count' => $movementCount,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction
        $pdo->rollBack();
        
        error_log("Order with movements failed: " . $e->getMessage());
        error_log("User: {$user}, Device: {$device}");
        error_log("Input data: " . json_encode($input));
        
        echo json_encode([
            'success' => false,
            'message' => 'Gagal menyimpan order: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    break;
    
    case 'save_claude_settings':
        $claude_api_key = $input['claude_api_key'] ?? '';
        
        if (!$claude_api_key) {
            echo json_encode(['success' => false, 'message' => 'Claude API key required']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, user) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP");
            $stmt->execute(['claude_api_key', $claude_api_key, $user]);
            echo json_encode(['success' => true, 'message' => 'Claude API key saved successfully']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'load_claude_settings':
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'claude_api_key' AND user = ?");
            $stmt->execute([$user]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'claude_api_key' => $result ? $result['setting_value'] : ''
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
    
        
         // ===== CUSTOMER ENGAGEMENT ACTIONS =====
    case 'save_customer':
        $nama = $input['nama'] ?? '';
        $telepon = $input['telepon'] ?? '';
        $alamat = $input['alamat'] ?? '';
        $platform = $input['platform_preference'] ?? 'both';
        
        if (!$nama || !$telepon) {
            echo json_encode(['success' => false, 'message' => 'Nama dan telepon wajib diisi']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO customer_profiles (nama, telepon, alamat, platform_preference, user, device) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nama, $telepon, $alamat, $platform, $user, $device]);
            echo json_encode(['success' => true, 'message' => 'Customer berhasil disimpan', 'id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'load_customers':
        try {
            $stmt = $pdo->query("SELECT * FROM customer_profiles ORDER BY created_at DESC");
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'customers' => $customers]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'update_customer':
        $id = $input['id'] ?? '';
        $nama = $input['nama'] ?? '';
        $telepon = $input['telepon'] ?? '';
        $alamat = $input['alamat'] ?? '';
        $platform = $input['platform_preference'] ?? 'both';
        
        if (!$id || !$nama || !$telepon) {
            echo json_encode(['success' => false, 'message' => 'ID, nama dan telepon wajib diisi']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE customer_profiles SET nama=?, telepon=?, alamat=?, platform_preference=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $stmt->execute([$nama, $telepon, $alamat, $platform, $id]);
            echo json_encode(['success' => true, 'message' => 'Customer berhasil diupdate']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'delete_customer':
        $id = $input['id'] ?? '';
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Customer ID required']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("DELETE FROM customer_profiles WHERE id=?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Customer berhasil dihapus']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

   case 'save_order':
    $customer_id = $input['customer_id'] ?? '';
    $platform = $input['platform'] ?? '';
    $tanggal = $input['tanggal'] ?? date('Y-m-d');
    $sku = $input['sku'] ?? '';
    $qty = (int)($input['qty'] ?? 0);
    $harga_jual = (float)($input['harga_jual'] ?? 0);
    $resi = $input['resi'] ?? '';
    
    if (!$customer_id || !$platform || !$sku || $qty <= 0 || $harga_jual <= 0) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi dengan benar']);
        break;
    }
    
    // ===== STOCK VALIDATION =====
    try {
        $stockStmt = $pdo->prepare("
            SELECT 
                p.stok_awal,
                COALESCE(SUM(CASE WHEN m.type = 'in' THEN m.qty ELSE 0 END), 0) as total_in,
                COALESCE(SUM(CASE WHEN m.type = 'out' THEN m.qty ELSE 0 END), 0) as total_out
            FROM inventory_products p 
            LEFT JOIN inventory_movements m ON p.sku = m.sku 
            WHERE p.sku = ?
            GROUP BY p.sku
        ");
        $stockStmt->execute([$sku]);
        $stockData = $stockStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stockData) {
            $currentStock = $stockData['stok_awal'] + $stockData['total_in'] - $stockData['total_out'];
            if ($currentStock < $qty) {
                echo json_encode(['success' => false, 'message' => "❌ Stock tidak cukup! Tersedia: $currentStock, Dibutuhkan: $qty"]);
                break;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di inventory']);
            break;
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error checking stock: ' . $e->getMessage()]);
        break;
    }
    // ===== END STOCK VALIDATION =====
    
    try {
        // Begin transaction untuk atomicity
        $pdo->beginTransaction();
        
        // Get HPP dan nama produk dari inventory
        $stmt = $pdo->prepare("SELECT nama_barang, harga FROM inventory_products WHERE sku = ?");
        $stmt->execute([$sku]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan di inventory']);
            break;
        }
        
        $hpp = $product['harga'];
        $nama_produk = $product['nama_barang'];
        $hpp_total = $hpp * $qty;
        $profit = $harga_jual - $hpp_total;
        $margin_pct = $harga_jual > 0 ? ($profit / $harga_jual) * 100 : 0;
        
        // 1. Save order ke customer_orders
        $stmt = $pdo->prepare("INSERT INTO customer_orders (customer_id, platform, tanggal, sku, nama_produk, qty, harga_jual, hpp, hpp_total, profit, margin_pct, resi, user, device) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_id, $platform, $tanggal, $sku, $nama_produk, $qty, $harga_jual, $hpp, $hpp_total, $profit, $margin_pct, $resi, $user, $device]);
        
        $orderId = $pdo->lastInsertId();
        
        // 2. ✅ TAMBAHAN: Create inventory movement (stock out)
        $movementNotes = '';
        switch(strtolower($platform)) {
            case 'tiktok':
                $movementNotes = 'Orderan TikTok';
                break;
            case 'shopee':
                $movementNotes = 'Orderan Shopee';
                break;
            default:
                $movementNotes = 'Orderan ' . ucfirst($platform);
        }
        
        $movementStmt = $pdo->prepare("INSERT INTO inventory_movements (sku, type, qty, notes, movement_date, user, device) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $movementStmt->execute([
            $sku,
            'out',  // Stock keluar
            $qty,
            $movementNotes,
            $tanggal,
            $user,
            $device
        ]);
        
        // Commit transaction
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order + inventory movement berhasil disimpan', 
            'id' => $orderId,
            'movement_created' => true
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    case 'load_orders':
    try {
        $stmt = $pdo->query("SELECT 
            o.id,
            o.tanggal as order_date,
            o.platform,
            o.sku as product_sku, 
            o.nama_produk as product_name,
            o.qty,
            o.harga_jual,
            o.profit,
            o.margin_pct as margin,
            o.resi as no_resi,
            o.status,
            c.nama as customer_name,
            c.telepon
            FROM customer_orders o 
            LEFT JOIN customer_profiles c ON o.customer_id = c.id 
            ORDER BY o.created_at DESC");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'orders' => $orders]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    case 'update_order':
        $id = $input['id'] ?? '';
        $customer_id = $input['customer_id'] ?? '';
        $platform = $input['platform'] ?? '';
        $harga_jual = (float)($input['harga_jual'] ?? 0);
        $resi = $input['resi'] ?? '';
        $status = $input['status'] ?? 'Pending';
        
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            break;
        }
        
        try {
            // Recalculate profit jika harga_jual berubah
            if ($harga_jual > 0) {
                $stmt = $pdo->prepare("SELECT hpp_total FROM customer_orders WHERE id = ?");
                $stmt->execute([$id]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($order) {
                    $profit = $harga_jual - $order['hpp_total'];
                    $margin_pct = ($profit / $harga_jual) * 100;
                    
                    $stmt = $pdo->prepare("UPDATE customer_orders SET customer_id=?, platform=?, harga_jual=?, profit=?, margin_pct=?, resi=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                    $stmt->execute([$customer_id, $platform, $harga_jual, $profit, $margin_pct, $resi, $status, $id]);
                }
            } else {
                $stmt = $pdo->prepare("UPDATE customer_orders SET customer_id=?, platform=?, resi=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$customer_id, $platform, $resi, $status, $id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Order berhasil diupdate']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

case 'delete_order':
    $id = $input['id'] ?? '';
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Order ID required']);
        break;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get order untuk rollback
        $orderStmt = $pdo->prepare("SELECT sku, qty, tanggal FROM customer_orders WHERE id = ?");
        $orderStmt->execute([$id]);
        $orderData = $orderStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($orderData) {
            // Delete order
            $stmt = $pdo->prepare("DELETE FROM customer_orders WHERE id = ?");
            $stmt->execute([$id]);
            
            // Rollback stock
            $rollbackStmt = $pdo->prepare("INSERT INTO inventory_movements (sku, type, qty, notes, movement_date, user, device) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $rollbackStmt->execute([$orderData['sku'], 'in', $orderData['qty'], 'Rollback - Order dibatal', $orderData['tanggal'], $user, $device]);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Order berhasil dihapus + stock dikembalikan']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    case 'get_platform_stats':
        try {
            $month = $input['month'] ?? date('Y-m');
            
            $stmt = $pdo->prepare("SELECT platform, COUNT(*) as total_orders, SUM(profit) as total_profit, AVG(margin_pct) as avg_margin FROM customer_orders WHERE DATE_FORMAT(tanggal, '%Y-%m') = ? GROUP BY platform");
            $stmt->execute([$month]);
            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'stats' => $stats, 'month' => $month]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

    case 'get_customer_analytics':
        $customer_id = $input['customer_id'] ?? '';
        
        if (!$customer_id) {
            echo json_encode(['success' => false, 'message' => 'Customer ID required']);
            break;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(profit) as total_profit, AVG(profit) as avg_profit, MAX(tanggal) as last_order FROM customer_orders WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            $analytics = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'analytics' => $analytics]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
        
        case 'sync_order_tracking':
    // Sync order status dengan tracking status
    try {
        $pdo->beginTransaction();
        
        // 1. Update orders yang ada resi di resi_data (masih pending/proses)
        $stmt = $pdo->prepare("
            UPDATE customer_orders o 
            JOIN resi_data r ON o.resi = r.resi 
            SET o.status = CASE 
                WHEN r.statusAPI = 'DELIVERED' THEN 'Delivered'
                WHEN r.statusAPI LIKE '%RETURN%' THEN 'Returned'  
                WHEN r.statusAPI = 'Pending' OR r.statusAPI = 'Error' THEN 'Pending'
                ELSE 'Processing'
            END
            WHERE o.resi != ''
        ");
        $stmt->execute();
        $updated_resi = $stmt->rowCount();
        
        // 2. Update orders yang ada resi di delivered_data
        $stmt = $pdo->prepare("
            UPDATE customer_orders o 
            JOIN delivered_data d ON o.resi = d.resi 
            SET o.status = 'Delivered'
            WHERE o.resi != ''
        ");
        $stmt->execute();
        $updated_delivered = $stmt->rowCount();
        
        // 3. Update orders yang ada resi di returns_data  
        $stmt = $pdo->prepare("
            UPDATE customer_orders o 
            JOIN returns_data r ON o.resi = r.resi 
            SET o.status = 'Returned'
            WHERE o.resi != ''
        ");
        $stmt->execute();
        $updated_returns = $stmt->rowCount();
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order tracking synced',
            'updated' => [
                'resi_data' => $updated_resi,
                'delivered' => $updated_delivered, 
                'returns' => $updated_returns
            ]
        ]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Sync error: ' . $e->getMessage()]);
    }
    break;

case 'update_order_by_resi':
    // Update specific order status by resi
    $resi = $input['resi'] ?? '';
    $tracking_status = $input['tracking_status'] ?? '';
    
    if (!$resi || !$tracking_status) {
        echo json_encode(['success' => false, 'message' => 'Resi dan tracking status required']);
        break;
    }
    
    try {
        // Convert tracking status ke order status
        $order_status = 'Processing';
        if ($tracking_status === 'DELIVERED') {
            $order_status = 'Delivered';
        } elseif (strpos($tracking_status, 'RETURN') !== false) {
            $order_status = 'Returned';
        } elseif (in_array($tracking_status, ['Pending', 'Error'])) {
            $order_status = 'Pending';
        }
        
        $stmt = $pdo->prepare("UPDATE customer_orders SET status = ? WHERE resi = ?");
        $stmt->execute([$order_status, $resi]);
        
        echo json_encode(['success' => true, 'message' => 'Order status updated', 'order_status' => $order_status]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
    
    
    case 'load_customers_with_stats':
    try {
        $stmt = $pdo->query("
            SELECT 
                c.*,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.harga_jual), 0) as total_spent,
                MAX(o.tanggal) as last_order_date,
                CASE 
                    WHEN COUNT(o.id) >= 10 THEN 'vip'
                    WHEN COUNT(o.id) >= 3 THEN 'regular'
                    ELSE 'new'
                END as customer_segment
            FROM customer_profiles c 
            LEFT JOIN customer_orders o ON c.id = o.customer_id 
            GROUP BY c.id 
            ORDER BY total_spent DESC, total_orders DESC
        ");
        
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'customers' => $customers]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'get_customer_detail':
    $customer_id = $input['customer_id'] ?? '';
    
    if (!$customer_id) {
        echo json_encode(['success' => false, 'message' => 'Customer ID required']);
        break;
    }
    
    try {
        // Get customer info
        $stmt = $pdo->prepare("
            SELECT 
                c.*,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.harga_jual), 0) as total_spent,
                MAX(o.tanggal) as last_order_date,
                CASE 
                    WHEN COUNT(o.id) >= 10 THEN 'vip'
                    WHEN COUNT(o.id) >= 3 THEN 'regular'
                    ELSE 'new'
                END as customer_segment
            FROM customer_profiles c 
            LEFT JOIN customer_orders o ON c.id = o.customer_id 
            WHERE c.id = ?
            GROUP BY c.id
        ");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$customer) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            break;
        }
        
        // Get customer orders
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                r.statusAPI as tracking_status,
                CASE 
                    WHEN r.statusAPI = 'DELIVERED' THEN 'Delivered'
                    WHEN r.statusAPI LIKE '%RETURN%' THEN 'Returned'
                    WHEN r.resi IS NOT NULL THEN 'Shipped'
                    ELSE o.status
                END as current_status
            FROM customer_orders o 
            LEFT JOIN resi_data r ON o.resi = r.resi
            LEFT JOIN delivered_data d ON o.resi = d.resi  
            LEFT JOIN returns_data rt ON o.resi = rt.resi
            WHERE o.customer_id = ? 
            ORDER BY o.tanggal DESC 
            LIMIT 20
        ");
        $stmt->execute([$customer_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'customer' => $customer, 
            'orders' => $orders
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'save_wa_log':
    $phone = $input['phone'] ?? '';
    $message = $input['message'] ?? '';
    $type = $input['type'] ?? 'manual'; // manual, auto_shipped, auto_delivered
    $resi = $input['resi'] ?? '';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO wa_notification_log 
            (phone, message, type, resi, sent_at, user, device) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?)
        ");
        $stmt->execute([$phone, $message, $type, $resi, $user, $device]);
        
        echo json_encode(['success' => true, 'message' => 'WA log saved']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

case 'get_customer_by_resi':
    $resi = $input['resi'] ?? '';
    
    if (!$resi) {
        echo json_encode(['success' => false, 'message' => 'Resi required']);
        break;
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, o.nama_produk, o.qty
            FROM customer_orders o 
            JOIN customer_profiles c ON o.customer_id = c.id 
            WHERE o.resi = ?
            LIMIT 1
        ");
        $stmt->execute([$resi]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'customer' => $customer
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;
        
        
        default:
            echo json_encode([
                'status' => 'ready', 
                'message' => 'V12 Panel MySQL API - FIXED with Auto-Generated NO',
                'version' => '2.3',
                'features' => ['auto-generated no field', 'exact field match', 'realtime sync', 'tracking history', 'multi-user'],
                'tables' => ['resi_data', 'delivered_data', 'returns_data', 'tracking_history', 'settings', 'sync_log', 'active_users'],
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
    }
    
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage(), 'timestamp' => date('Y-m-d H:i:s')]);
}

// Function untuk log sync realtime
function logSync($pdo, $action, $table, $resi, $user, $device, $before, $after) {
    $stmt = $pdo->prepare("INSERT INTO sync_log (action, table_name, resi, user, device, data_before, data_after) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $action, 
        $table,
        $resi, 
        $user, 
        $device, 
        $before ? json_encode($before) : null,
        $after ? json_encode($after) : null
    ]);
}
?>