<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

function callPythonRegistrationAPI($adminData, $profilePicture = null, $eSignature = null) {
    $ch = curl_init('http://localhost:8000/admin/register');
    
    // Prepare multipart form data
    $postFields = [
        'name' => $adminData['name'],
        'email' => $adminData['email'],
        'address' => $adminData['address'],
        'mobile_number' => $adminData['mobile_number'],
        'password' => $adminData['password']
    ];

    // Add file uploads if they exist
    if ($profilePicture && is_uploaded_file($profilePicture['tmp_name'])) {
        $postFields['profile_picture'] = new CURLFile(
            $profilePicture['tmp_name'], 
            $profilePicture['type'], 
            $profilePicture['name']
        );
    }

    if ($eSignature && is_uploaded_file($eSignature['tmp_name'])) {
        $postFields['e_signature'] = new CURLFile(
            $eSignature['tmp_name'], 
            $eSignature['type'], 
            $eSignature['name']
        );
    }

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $httpCode == 200,
        'response' => json_decode($response, true),
        'http_code' => $httpCode
    ];
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $name = trim($_POST['name'] ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $address = trim($_POST['address'] ?? '');
    $mobile_number = preg_replace('/[^0-9]/', '', $_POST['mobile_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation checks
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (!$email) {
        $errors[] = "Invalid email address";
    }

    if (empty($address)) {
        $errors[] = "Address is required";
    }

    if (strlen($mobile_number) < 10) {
        $errors[] = "Invalid mobile number";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Profile picture validation
    $profile_picture = $_FILES['profile_picture'] ?? null;
    if ($profile_picture && $profile_picture['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($profile_picture['type'], $allowed_types)) {
            $errors[] = "Invalid profile picture type";
        }
        if ($profile_picture['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors[] = "Profile picture too large";
        }
    }

    // E-signature validation
    $e_signature = $_FILES['e_signature'] ?? null;
    if ($e_signature && $e_signature['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/png'];
        if (!in_array($e_signature['type'], $allowed_types)) {
            $errors[] = "E-signature must be a PNG file";
        }
        if ($e_signature['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = "E-signature too large";
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        $adminData = [
            'name' => $name,
            'email' => $email,
            'address' => $address,
            'mobile_number' => $mobile_number,
            'password' => $password
        ];

        $registration_result = callPythonRegistrationAPI(
            $adminData, 
            $profile_picture, 
            $e_signature
        );

        if ($registration_result['success']) {
            // Redirect to login with success message
            session_start();
            $_SESSION['registration_success'] = "Registration successful. Please log in.";
            header("Location: login.php");
            exit();
        } else {
            // Handle registration errors
            $errors[] = $registration_result['response']['detail'] ?? "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Registration</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="registration-container">
        <form method="post" enctype="multipart/form-data">
            <h2>Admin Registration</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($name ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required
                       value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="mobile_number">Mobile Number:</label>
                <input type="tel" id="mobile_number" name="mobile_number" required
                       value="<?php echo htmlspecialchars($mobile_number ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <label for="profile_picture">Profile Picture (Optional):</label>
                <input type="file" id="profile_picture" name="profile_picture" 
                       accept="image/jpeg,image/png,image/gif">
            </div>
            
            <div class="form-group">
                <label for="e_signature">E-Signature (PNG, Optional):</label>
                <input type="file" id="e_signature" name="e_signature" accept="image/png">
            </div>
            
            <button type="submit">Register</button>
            
            <p class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>