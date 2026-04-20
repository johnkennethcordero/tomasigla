<?php
require_once 'db.php';

$queries = [
"CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"CREATE TABLE IF NOT EXISTS tourist_spots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80),
  description TEXT,
  address VARCHAR(255),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  image VARCHAR(255),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"CREATE TABLE IF NOT EXISTS businesses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80),
  description TEXT,
  address VARCHAR(255),
  contact VARCHAR(80),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  image VARCHAR(255),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80),
  description TEXT,
  price DECIMAL(10,2) DEFAULT 0,
  business_id INT,
  image VARCHAR(255),
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE SET NULL
)",

"CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  type VARCHAR(80),
  description TEXT,
  location VARCHAR(255),
  event_date DATE DEFAULT NULL,
  event_time TIME DEFAULT NULL,
  status ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"INSERT IGNORE INTO users (name, email, password, role)
 VALUES ('Admin', 'admin@tomasigla.com',
 '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')"
// password is: password
];

foreach ($queries as $q) {
    try {
        $pdo->exec($q);
        echo "✓ OK: " . substr($q, 0, 50) . "...<br>";
    } catch (PDOException $e) {
        echo "✗ Error: " . $e->getMessage() . "<br>";
    }
}

echo "<br><strong>Done! DELETE this file now.</strong>";
?>