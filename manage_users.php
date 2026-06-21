<?php
// manage_users.php
require_once __DIR__ . '/db.php';
session_start();

// Enforce role-based access control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Access Denied: Admin permissions required to view this page.</p>";
    echo '<p><a href="dashboard.php">Back to Dashboard</a></p>';
    exit;
}

try {
    // Retrieve all users
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users (Admin Only) - Advanced CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <h1>User Management Console</h1>
        <p class="nav-links" style="margin-bottom: 20px;"><a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a> <a href="logout.php" class="btn btn-danger" style="float: right;">Logout</a></p>
        <div style="clear: both; height: 10px;"></div>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">
                Success: User deleted successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'delete_failed'): ?>
            <div class="alert alert-danger">
                Error: Failed to delete the user. Please try again later.
            </div>
        <?php endif; ?>

        <h2>Users Console</h2>
        <div class="table-responsive mobile-table-card-view">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile Pic</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Role</th>
                        <th>Bio</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td data-label="ID"><?php echo htmlspecialchars($user['id']); ?></td>
                            <td data-label="Profile Pic">
                                <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Pic" class="profile-img-sm">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; color: var(--text-secondary); font-weight: bold;">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td data-label="Name"><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                            <td data-label="Email"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td data-label="Phone"><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                            <td data-label="Gender"><?php echo htmlspecialchars($user['gender'] ?: '-'); ?></td>
                            <td data-label="Role"><span class="badge" style="background: #f1f5f9; padding: 2px 8px; border-radius: 4px; font-weight: bold;"><?php echo htmlspecialchars($user['role']); ?></span></td>
                            <td data-label="Bio"><?php echo htmlspecialchars(mb_strimwidth($user['bio'] ?? '', 0, 40, '...')); ?></td>
                            <td data-label="Created At"><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td data-label="Actions">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.85rem;">Edit</a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger" style="padding: 4px 8px; font-size: 0.85rem;" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
