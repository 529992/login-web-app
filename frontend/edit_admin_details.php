<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

function fetchAdminDetails($admin_id) {
    // In a real-world scenario, call your backend API to fetch admin details
    $ch = curl_init("http://localhost:8000/admin/profile/{$admin_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['access_token']
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code == 200 ? json_decode($response, true) : null;
}

function updateAdminProfile($admin_id, $update_data, $profile_picture = null, $e_signature = null) {
    $ch = curl_init("http://localhost:8000/admin/update/{$admin_id}");
    
    $postFields = [
        'name' => $update_data['name'],
        'address' => $update_data['address'],
        'mobile_number' => $update_data['mobile_number']
    ];

    // Add file uploads if they exist
    if ($profile_picture && is_uploaded_file($profile_picture['tmp_name'])) {
        $postFields['profile_picture'] = new CURLFile(
            $profile_picture['tmp_name'], 
            $profile_picture['type'], 
            $profile_picture['name']
        );
    }

    if ($e_signature && is_uploaded_file($e_signature['tmp_name'])) {
        $postFields['e_signature'] = new CURLFile(
            $e_signature['tmp_name'], 
            $e_signature['type'], 
            $e_signature['name']
        );
    }

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $_SESSION['access_token']
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'success' => $http_code == 200,
        'response' => json_decode($response, true)
    ];
}

// Fetch current admin details
$admin_details = fetchAdminDetails($_SESSION['admin_id']);

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and process form submission
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $mobile_number = preg_replace('/[^0-9]/', '', $_POST['mobile_number'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (strlen($mobile_number) < 10) {
        $errors[] = "Invalid mobile number";
    }
    
    // Profile picture validation
    $profile_picture = $_FILES['profile_picture'] ?? null;
    $e_signature = $_FILES['e_signature'] ?? null;
    
    if (empty($errors)) {
        $update_data = [
            'name' => $name,
            'address' => $address,
            'mobile_number' => $mobile_number
        ];
        
        $update_result = updateAdminProfile(
            $_SESSION['admin_id'], 
            $update_data, 
            $profile_picture, 
            $e_signature
        );
        
        if ($update_result['success']) {
            $success_message = "Profile updated successfully";
            // Refresh admin details
            $admin_details = $update_result['response'];
        } else {
            $errors[] = $update_result['response']['detail'] ?? "Update failed";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Admin Profile</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="js/script.js"></script>
</head>
<body>
    <div class="edit-profile-container">
        <form method="post" enctype="multipart/form-data">
            <h2>Edit Admin Profile</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required
                       value="<?php echo htmlspecialchars($admin_details['name'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email (Read Only):</label>
                <input type="email" id="email" name="email" readonly
                       value="<?php echo htmlspecialchars($admin_details['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($admin_details['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="mobile_number">Mobile Number:</label>
                <input type="tel" id="mobile_number" name="mobile_number" required
                       value="<?php echo htmlspecialchars($admin_details['mobile_number'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="profile_picture">Update Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture" 
                       accept="image/jpeg,image/png,image/gif">
                <?php if (!empty($admin_details['profile_picture_path'])): ?>
                    <p>Current Picture: 
                        <a href="<?php echo htmlspecialchars($admin_details['profile_picture_path']); ?>" target="_blank">View</a>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="e_signature">Update E-Signature:</label>
                <input type="file" id="e_signature" name="e_signature" accept="image/png">
                <?php if (!empty($admin_details['e_signature_path'])): ?>
                    <p>Current E-Signature: 
                        <a href="<?php echo htmlspecialchars($admin_details['e_signature_path']); ?>" target="_blank">View</a>
                    </p>
                <?php endif; ?>
            </div>
            
            <button type="submit">Update Profile</button>
            
            <p class="back-link">
                <a href="admin_