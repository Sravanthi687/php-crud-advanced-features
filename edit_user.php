<?php
// edit_user.php
require_once __DIR__ . '/db.php';
session_start();

// Enforce role-based access control
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    echo "<h1>403 Forbidden</h1><p>Access Denied: Admin permissions required.</p>";
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: manage_users.php");
    exit;
}

$message = '';
$error = '';
$user = null;

// Fetch user data to edit
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header("Location: manage_users.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $bio = trim($_POST['bio'] ?? '');

    // Server-side validation
    if (empty($name) || empty($email)) {
        $error = 'Name and Email are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!empty($phone) && !preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
        $error = 'Phone number format is invalid.';
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = 'Please select a valid gender option.';
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = 'Invalid role.';
    } else {
        try {
            // Check if email already registered for another user
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email is already registered by another user.';
            } else {
                // Update user details
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, gender = ?, role = ?, bio = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $gender, $role, $bio, $id]);
                $message = 'User profile updated successfully!';
                
                // Refresh updated details
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            error_log("Admin edit user DB error: " . $e->getMessage());
            $error = 'An error occurred while updating the profile. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User (Admin Only) - Advanced CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Edit User Profile (Admin View)</h1>
        <p class="nav-links"><a href="manage_users.php">Back to User Management</a></p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                Error: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="editForm" action="edit_user.php?id=<?php echo htmlspecialchars($id); ?>" method="POST" onsubmit="return validateEditForm()">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <div id="name-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                <div id="email-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                <div id="phone-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <div class="gender-group">
                    <label for="gender-male"><input type="radio" id="gender-male" name="gender" value="male" <?php echo ($user['gender'] === 'male') ? 'checked' : ''; ?>> Male</label>
                    <label for="gender-female"><input type="radio" id="gender-female" name="gender" value="female" <?php echo ($user['gender'] === 'female') ? 'checked' : ''; ?>> Female</label>
                    <label for="gender-other"><input type="radio" id="gender-other" name="gender" value="other" <?php echo ($user['gender'] === 'other') ? 'checked' : ''; ?>> Other</label>
                </div>
                <div id="gender-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>Regular User</option>
                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                </select>
                <div id="role-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>

    <script>
    function showError(elementId, message) {
        var errElement = document.getElementById(elementId);
        if (errElement) {
            errElement.innerText = message;
            errElement.style.display = 'block';
        }
    }

    function clearErrors() {
        var errElements = document.getElementsByClassName('error-message');
        for (var i = 0; i < errElements.length; i++) {
            errElements[i].style.display = 'none';
            errElements[i].innerText = '';
        }
    }

    function validateEditForm() {
        clearErrors();
        var isValid = true;

        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var role = document.getElementById('role').value;

        if (name === "") {
            showError('name-err', "Name is required.");
            isValid = false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            showError('email-err', "Email is required.");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('email-err', "Please enter a valid email address.");
            isValid = false;
        }

        if (phone !== "") {
            var phoneRegex = /^[0-9\-\+\s\(\)]+$/;
            if (!phoneRegex.test(phone)) {
                showError('phone-err', "Phone number contains invalid characters.");
                isValid = false;
            }
        }

        if (role !== "user" && role !== "admin") {
            showError('role-err', "Invalid role selected.");
            isValid = false;
        }

        return isValid;
    }
    </script>
</body>
</html>
