<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = isset($_POST['password']) ? MD5($_POST['password']) : '';

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    mysqli_query($conn, "INSERT INTO login_history (username) VALUES ('$username')");

    if ($data = mysqli_fetch_assoc($query)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $data['username'];

        header("Location: admin.php");
        exit;
    } else {
        header("Location: login.php?error=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Parking - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-grid">
            
            <div class="login-form-wrapper">
                <div class="login-header">
                    <h1>Welcome Back</h1>
                    <p>Sign in to access your parking dashboard *Admin ID: admin2, Password: 123</p>
                </div>

                <form method="POST" action="login.php" class="login-form">
                    <div class="form-group">
                        <label for="id">Admin ID</label>
                        <input type="text" id="username" name="username" placeholder="enter admin ID" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>
                </form>
            </div>

            <div class="illustration-wrapper">
                <div class="illustration-card">
                    <svg width="400" height="400" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">

                        <defs>
                            <linearGradient id="buildingGradient" x1="80" y1="100" x2="320" y2="320" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#6fb3b8" />
                                <stop offset="100%" stop-color="#247175" />
                            </linearGradient>
                        </defs>

                        <rect x="80" y="100" width="240" height="220" fill="url(#buildingGradient)" rx="8" />

                        <rect x="100" y="120" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="150" y="120" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="200" y="120" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="250" y="120" width="30" height="40" fill="#f8feff" rx="4" />

                        <rect x="100" y="180" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="150" y="180" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="200" y="180" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="250" y="180" width="30" height="40" fill="#f8feff" rx="4" />

                        <rect x="100" y="240" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="150" y="240" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="200" y="240" width="30" height="40" fill="#f8feff" rx="4" />
                        <rect x="250" y="240" width="30" height="40" fill="#f8feff" rx="4" />

                        <rect x="40" y="320" width="320" height="40" fill="#E5E7EB" rx="4" />

                        <rect x="160" y="290" width="80" height="30" fill="#EF4444" rx="4" />
                        <rect x="190" y="250" width="20" height="40" fill="#374151" />

                        <circle cx="160" cy="310" r="8" fill="#374151" />
                        <rect x="156" y="260" width="8" height="50" fill="#374151" />

                        <rect x="60" y="340" width="40" height="50" fill="#10b981" stroke="#059669" stroke-width="2" rx="4" />
                        <rect x="110" y="340" width="40" height="50" fill="#EF4444" stroke="#DC2626" stroke-width="2" rx="4" />
                        <rect x="250" y="340" width="40" height="50" fill="#10B981" stroke="#059669" stroke-width="2" rx="4" />
                        <rect x="300" y="340" width="40" height="50" fill="#10B981" stroke="#059669" stroke-width="2" rx="4" />

                        <rect x="113" y="345" width="34" height="20" fill="#6B7280" rx="3" />
                        <rect x="116" y="350" width="28" height="12" fill="#9CA3AF" rx="2" />

                        <text x="200" y="60" text-anchor="middle" fill="#000000" font-size="24" font-weight="bold">Smart Parking</text>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
