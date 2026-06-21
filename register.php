<?php
// register.php
require_once __DIR__ . '/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and trim inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $role = trim($_POST['role'] ?? 'user');

    // Server-side validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Name, Email, and Password are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!empty($phone) && !preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
        $error = 'Phone number format is invalid.';
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = 'Please select a valid gender option.';
    } elseif (!in_array($role, ['user', 'admin'])) {
        $error = 'Invalid user role selected.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Email is already registered.';
            } else {
                // Hash the password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, gender, bio, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password, $phone, $gender, $bio, $role]);
                $message = 'User registered successfully! You can now <a href="login.php" style="color: inherit; font-weight: bold; text-decoration: underline;">Login</a>.';
            }
        } catch (PDOException $e) {
            error_log("Registration DB error: " . $e->getMessage());
            $error = 'An error occurred while creating your account. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Advanced CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container auth-container">
        <h1>Register New Account</h1>
        <p><a href="login.php">Back to Login</a></p>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                Error: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="regForm" action="register.php" method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" required>
                <div id="name-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
                <div id="email-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="password">Password (min 6 chars) *</label>
                <input type="password" id="password" name="password" required>
                <div id="password-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" placeholder="e.g. +1234567890">
                <div id="phone-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label>Gender</label>
                <div class="gender-group">
                    <label for="gender-male"><input type="radio" id="gender-male" name="gender" value="male" checked> Male</label>
                    <label for="gender-female"><input type="radio" id="gender-female" name="gender" value="female"> Female</label>
                    <label for="gender-other"><input type="radio" id="gender-other" name="gender" value="other"> Other</label>
                </div>
                <div id="gender-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <option value="user">Regular User</option>
                    <option value="admin">Administrator</option>
                </select>
                <div id="role-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="bio">Short Bio</label>
                <textarea id="bio" name="bio" rows="4"></textarea>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Register</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
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

    function validateForm() {
        clearErrors();
        var isValid = true;

        var name = document.getElementById('name').value.trim();
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;
        var phone = document.getElementById('phone').value.trim();
        var role = document.getElementById('role').value;

        if (name === "") {
            showError('name-err', "Full Name is required.");
            isValid = false;
        }

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            showError('email-err', "Email address is required.");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('email-err', "Please enter a valid email address.");
            isValid = false;
        }

        if (password === "") {
            showError('password-err', "Password is required.");
            isValid = false;
        } else if (password.length < 6) {
            showError('password-err', "Password must be at least 6 characters long.");
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
