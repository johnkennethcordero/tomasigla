<?php
require_once 'db.php';

$queries = [
"CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)",

"INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('maintenance_mode', '0'),
('section_spots', '1'),
('section_businesses', '1'),
('section_products', '1'),
('section_events', '1'),
('home_banner_title', 'Welcome to Sto. Tomas'),
('home_banner_subtitle', 'Discover tourist spots, local products & events'),
('home_announcement', ''),
('home_featured_spot_id', ''),
('home_featured_business_id', '')"
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        echo "✓ OK<br>";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "<br>";
    }
}
echo "<b>Done! DELETE this file now.</b>";
?>