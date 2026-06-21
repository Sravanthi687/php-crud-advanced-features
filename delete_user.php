<?php
// delete_user.php
require_once __DIR__ . '/db.php';
session_start();

// Enforce role-based access control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Access Denied: Admin permissions required.</p>";
    exit;
}

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        // Delete user database entry
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        
        // Redirect back with success flag
        header("Location: manage_users.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        error_log("Delete user DB error: " . $e->getMessage());
        header("Location: manage_users.php?error=delete_failed");
        exit;
    }
} else {
    header("Location: manage_users.php");
    exit;
}
?>
