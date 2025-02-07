<?php
session_start();

// Function to revoke tokens via backend API
function revokeTokens($admin_id, $access_token) {
    $ch = curl_init('http://localhost:8000/admin/logout');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'admin_id' => $admin_id
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code == 200;
}

// Revoke tokens if logged in
if (isset($_SESSION['admin_id']) && isset($_SESSION['access_token'])) {
    revokeTokens($_SESSION['admin_id'], $_SESSION['access_token']);
}

// Destroy session
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();