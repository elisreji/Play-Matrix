<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'playmatrix';

try {
    // 1. Connect to MySQL server without selecting a database
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully.<br>";

    // 2. Create the Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '$dbname' created or already exists.\n";

    // 3. Select the Database
    $pdo->exec("USE `$dbname` ");

    // 4. Read database.sql
    $sqlFile = 'database.sql';
    if (!file_exists($sqlFile)) {
        die("Error: database.sql file not found.\n");
    }

    $sql = file_get_contents($sqlFile);

    // 5. Clean up SQL and split by semicolon
    // First remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    $statements = explode(';', $sql);

    $count = 0;
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if (!empty($stmt)) {
            try {
                $pdo->exec($stmt);
                $count++;
            } catch (PDOException $e) {
                echo "Warning on statement: " . substr($stmt, 0, 50) . "... Error: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "Successfully executed $count SQL statements from $sqlFile.\n";
    echo "Database setup complete!\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>