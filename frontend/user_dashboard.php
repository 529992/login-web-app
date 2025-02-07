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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <h1>User Dashboard</h1>
        
        <?php if ($admin_details): ?>
            <div class="user-profile">
                <h2>Welcome, <?php echo htmlspecialchars($admin_details['name']); ?></h2>
                
                <div class="profile-details">
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($admin_details['email']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($admin_details['address']); ?></p>
                    <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($admin_details['mobile_number']); ?></p>
                    
                    <?php if (!empty($admin_details['profile_picture_path'])): ?>
                        <div class="profile-picture">
                            <h3>Profile Picture</h3>
                            <img src="<?php echo htmlspecialchars($admin_details['profile_picture_path']); ?>" alt="Profile Picture">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="dashboard-actions">
                    <a href="edit_admin_details.php" class="btn">Edit Profile</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <p>Unable to fetch user details. Please try again later.</p>
        <?php endif; ?>
    </div>
</body>
</html>