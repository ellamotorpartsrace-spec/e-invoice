<?php
// config/database.php
// Dual-Environment Database Connection (Hostinger & Local XAMPP Auto-Detection)

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port = "3306";
    private $charset = 'utf8mb4';
    private $conn = null;

    public function __construct() {
        // Auto-detect environment: Local (XAMPP on Windows) vs Production (Linux on Hostinger)
        if (php_sapi_name() === 'cli') {
            $isLocal = (DIRECTORY_SEPARATOR === '\\');
        } else {
            $h = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $hOnly = explode(':', $h)[0];
            $isLocal = in_array($hOnly, ['localhost', '127.0.0.1', '::1']) || str_ends_with($hOnly, '.test') || str_contains($hOnly, 'ngrok') || preg_match('/^(192\.168|10\.|172\.(1[6-9]|2[0-9]|3[0-1]))\./', $hOnly);
        }

        if ($isLocal) {
            // --- LOCAL XAMPP / DEVELOPMENT ---
            $this->host     = getenv('DB_HOST') ?: "localhost";
            $this->dbname   = getenv('DB_NAME') ?: "ella_parts_db";
            $this->username = getenv('DB_USER') ?: "root";
            $this->password = getenv('DB_PASS') ?: "elladbPogisiBen";
        } else {
            // --- PRODUCTION / CLOUD HOSTING (Hostinger / cPanel / VPS) ---
            $this->host     = getenv('DB_HOST') ?: "localhost";
            $this->dbname   = getenv('DB_NAME') ?: "u123456789_einvoice_db";
            $this->username = getenv('DB_USER') ?: "u123456789_dbuser";
            $this->password = getenv('DB_PASS') ?: "YourStrongDatabasePassword123!";
        }
    }

    public function getConnection(): ?PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset};port={$this->port}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage() . "<br><small>Tip: Check your database credentials in <code>config/database.php</code></small>");
        }
    }
}
