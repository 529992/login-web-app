<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Function to fetch admin details
function fetchAdminDetails($admin_id) {
    $ch = curl_init("http://localhost:8000/admin/profile/{$admin_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code == 200 ? json_decode($response, true) : null;
}

// Function to update admin details
function updateAdminDetails($admin_id, $update_data) {
    $ch = curl_init("http://localhost:8000/admin/update/{$admin_id}");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($update_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code == 200;
}

// Fetch current admin details
$admin_details = fetchAdminDetails($_SESSION['admin_id']);

// Handle form submission
$error = '';
$success = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($address) || empty($mobile_number)) {
        $error = "All fields are required";
    } else {
        $update_data = [
            'name' => $name,
            'address' => $address,
            'mobile_number' => $mobile_number
        ];
        
        if (updateAdminDetails($_SESSION['admin_id'], $update_data)) {
            $success = "Profile updated successfully";
            // Refresh admin details
            $admin_details = fetchAdminDetails($_SESSION['admin_id']);
        } else {
            $error = "Failed to update profile";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User Details</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="edit-profile-container">
        <h1>Edit User Details</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($admin_details): ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo htmlspecialchars($admin_details['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email (Read Only):</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($admin_details['email']); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required><?php echo htmlspecialchars($admin_details['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="mobile_number">Mobile Number:</label>
                    <input type="tel" id="mobile_number" name="mobile_number" 
                           value="<?php echo htmlspecialchars($admin_details['mobile_number']); ?>" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn">Update Profile</button>
                    <a href="user_profile.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p>Unable to fetch user details. Please try again later.</p>
        <?php endif; ?>
    </div>
</body>
</html>