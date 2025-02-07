<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch admin details from backend API
$admin_details = fetch_admin_details($_SESSION['admin_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        
        <div class="admin-profile">
            <h2>Admin Profile</h2>
            <img src="<?php echo $admin_details['profile_picture_path']; ?>" alt="Profile Picture">
            <p>Name: <?php echo $admin_details['name']; ?></p>
            <p>Email: <?php echo $admin_details['email']; ?></p>
            <p>Address: <?php echo $admin_details['address']; ?></p>
            <p>Mobile: <?php echo $admin_details['mobile_number']; ?></p>
            
            <div class="e-signature">
                <h3>E-Signature</h3>
                <img src="<?php echo $admin_details['e_signature_path']; ?>" alt="E-Signature">
            </div>
        </div>
        
        <div class="dashboard-actions">
            <a href="edit_admin_details.php">Edit Profile</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>