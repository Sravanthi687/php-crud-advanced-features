<?php
// profile.php
require_once __DIR__ . '/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetch current user details
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Session user doesn't exist anymore
        header("Location: logout.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Server-side validation
    if (empty($name)) {
        $error = 'Full Name is a required field.';
    } elseif (!empty($phone) && !preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
        $error = 'Phone number contains invalid characters.';
    } elseif (!in_array($gender, ['male', 'female', 'other'])) {
        $error = 'Please select a valid gender option.';
    } else {
        try {
            // Profile image upload handling
            $profile_pic_path = $user['profile_picture']; // Default to old path

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['profile_pic'];
                
                // 1. Check for upload errors
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("File upload failed with error code: " . $file['error']);
                }

                // 2. Validate file size (Limit to 2MB)
                $max_size = 2 * 1024 * 1024; // 2MB
                if ($file['size'] > $max_size) {
                    throw new Exception("File size exceeds 2MB limit.");
                }

                // 3. Validate file MIME type/extension
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime_type, $allowed_types)) {
                    throw new Exception("Invalid file type. Only JPG, PNG, and GIF are allowed.");
                }

                // 4. Ensure upload folder exists
                $upload_dir = __DIR__ . '/uploads';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                // 5. Generate a unique safe file name
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $safe_filename = uniqid('profile_', true) . '.' . $extension;
                $destination = $upload_dir . '/' . $safe_filename;

                // 6. Move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Delete old profile picture file if it exists
                    if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])) {
                        @unlink(__DIR__ . '/' . $user['profile_picture']);
                    }
                    $profile_pic_path = 'uploads/' . $safe_filename;
                } else {
                    throw new Exception("Error moving uploaded file to destination folder.");
                }
            }

            // Update user details in database
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, gender = ?, bio = ?, profile_picture = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $gender, $bio, $profile_pic_path, $user_id]);
            
            // Refresh session name if it changed
            $_SESSION['name'] = $name;

            // Refresh user details variable
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            $message = 'Profile updated successfully!';
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Advanced CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>My Profile</h1>
        <p class="nav-links"><a href="dashboard.php">Back to Dashboard</a> | <a href="logout.php" style="color: var(--error-color)">Logout</a></p>

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

        <h2>Profile Information</h2>
        
        <div class="profile-pic-container">
            <?php if (!empty($user['profile_picture']) && file_exists(__DIR__ . '/' . $user['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-img-lg">
            <?php else: ?>
                <div style="width: 100px; height: 100px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); font-weight: bold; font-size: 2rem;">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <div>
                <h3 style="margin: 0;"><?php echo htmlspecialchars($user['name']); ?></h3>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.9rem;"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <form action="profile.php" method="POST" enctype="multipart/form-data" onsubmit="return validateProfileForm()">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                <div id="name-err" class="error-message"></div>
            </div>

            <div class="form-group">
                <label>Email (Cannot be changed)</label>
                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
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
                <label for="bio">Short Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="profile_pic">Upload Profile Picture (JPG, PNG, GIF - Max 2MB)</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                <div id="file-err" class="error-message"></div>
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

    function validateProfileForm() {
        clearErrors();
        var isValid = true;

        var name = document.getElementById('name').value.trim();
        var phone = document.getElementById('phone').value.trim();
        var profilePic = document.getElementById('profile_pic');

        if (name === "") {
            showError('name-err', "Full Name is required.");
            isValid = false;
        }

        if (phone !== "") {
            var phoneRegex = /^[0-9\-\+\s\(\)]+$/;
            if (!phoneRegex.test(phone)) {
                showError('phone-err', "Phone number contains invalid characters.");
                isValid = false;
            }
        }

        if (profilePic.files.length > 0) {
            var file = profilePic.files[0];
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (file.size > 2 * 1024 * 1024) {
                showError('file-err', "File size exceeds 2MB limit.");
                isValid = false;
            }

            if (!allowedTypes.includes(file.type)) {
                showError('file-err', "Only JPG, PNG, and GIF files are allowed.");
                isValid = false;
            }
        }

        return isValid;
    }
    </script>
</body>
</html>
