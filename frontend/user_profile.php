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

// Fetch current admin details
$admin_details = fetchAdminDetails($_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="profile-container">
        <h1>User Profile</h1>
        
        <?php if ($admin_details): ?>
            <div class="profile-details">
                <?php if (!empty($admin_details['profile_picture_path'])): ?>
                    <div class="profile-picture">
                        <img src="<?php echo htmlspecialchars($admin_details['profile_picture_path']); ?>" alt="Profile Picture">
                    </div>
                <?php endif; ?>
                
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($admin_details['name']); ?></h2>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_details['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($admin_details['address']); ?></p>
                    <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($admin_details['mobile_number']); ?></p>
                </div>
                
                <div class="profile-actions">
                    <a href="edit_user_details.php" class="btn">Edit Profile</a>
                    <a href="user_dashboard.php" class="btn">Back to Dashboard</a>
                </div>
            </div>
        <?php else: ?>
            <p>Unable to fetch user details. Please try again later.</p>
        <?php endif; ?>
    </div>
</body>
</html>