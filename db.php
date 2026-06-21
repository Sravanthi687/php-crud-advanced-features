<?php
// db.php
// Database connection file using PDO SQLite

$db_file = __DIR__ . '/advanced_tasks.db';

try {
    // Connect to SQLite
    $pdo = new PDO("sqlite:" . $db_file);
    
    // Configure PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create users table if it does not exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            phone TEXT,
            gender TEXT,
            bio TEXT,
            role TEXT DEFAULT 'user',
            profile_picture TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createTableQuery);

} catch (PDOException $e) {
    // Log error internally if logging is set up; display user-friendly database connection error
    error_log("Database connection failed: " . $e->getMessage());
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Database Error</title>
        <style>
            body { font-family: sans-serif; background: #f8d7da; color: #721c24; padding: 20px; text-align: center; }
            .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Database Error</h1>
            <p>Sorry, we are experiencing some technical difficulties connecting to our database. Please try again later.</p>
        </div>
    </body>
    </html>";
    exit;
}
?>
