<?php
// login.php
require_once __DIR__ . '/db.php';

session_start();

// Redirect logged-in users directly to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and Password are required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email format.';
    } else {
        try {
            // Retrieve user by email securely
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            error_log("Login database error: " . $e->getMessage());
            $error = 'An error occurred during authentication. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Advanced CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container auth-container">
        <h1>Login</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                Error: <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" action="login.php" method="POST" onsubmit="return validateLogin()">
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
                <div id="email-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
                <div id="password-err" class="error-message"></div>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="footer-link">Don't have an account? <a href="register.php">Register Here</a></p>
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

    function validateLogin() {
        clearErrors();
        var isValid = true;
        var email = document.getElementById('email').value.trim();
        var password = document.getElementById('password').value;

        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email === "") {
            showError('email-err', "Email Address is required.");
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('email-err', "Please enter a valid email address.");
            isValid = false;
        }

        if (password === "") {
            showError('password-err', "Password is required.");
            isValid = false;
        }

        return isValid;
    }
    </script>
</body>
</html>
