<?php
/**
 * Admin Login Page
 * Separate admin authentication system
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Start session
session_init();

// Redirect if already logged in as admin
if (is_admin_logged_in()) {
    header('Location: ' . APP_URL . '/admin/index');
    exit;
}

$page_title = 'Admin Login';
$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        // Verify credentials from admins table
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Login successful - create admin session
            $_SESSION['admin'] = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'email' => $admin['email']
            ];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: ' . APP_URL . '/admin/index');
            exit;
        } else {
            $error = 'Invalid credentials or account is inactive';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | Tour de Roar</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #FF6B1A, #E53E3E, #805AD5);
            padding: 2.5rem 2rem;
            text-align: center;
        }
        
        .login-header h1 {
            color: white;
            font-size: 2rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.875rem;
            margin: 0;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #FF6B1A;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #FF6B1A, #E53E3E);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 107, 26, 0.3);
        }
        
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 0.875rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #E53E3E;
            font-size: 0.875rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: #805AD5;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .back-link a:hover {
            color: #6b46c1;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>Admin Portal</h1>
        <p>Tour de Roar Management System</p>
    </div>
    
    <div class="login-body">
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope mr-1"></i>
                    Email Address
                </label>
                <input type="email" name="email" class="form-input" required autofocus
                       placeholder="admin@tourderoar.org"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock mr-1"></i>
                    Password
                </label>
                <input type="password" name="password" class="form-input" required
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login to Admin Panel
            </button>
        </form>
        
        <div class="back-link">
            <a href="<?= APP_URL ?>">
                <i class="fas fa-arrow-left mr-1"></i>
                Back to Website
            </a>
        </div>
    </div>
</div>

</body>
</html>
