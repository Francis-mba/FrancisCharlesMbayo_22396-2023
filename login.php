<?php
$page_title = "Login";
require_once 'includes/header.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: /computer_shop/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            loginUser($user);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /computer_shop/admin/dashboard.php');
            } else {
                header('Location: /computer_shop/index.php');
            }
            exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
    }
}
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">Login to Your Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="username">Username or Email:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-submit">Login</button>
        </form>
        
        <div style="text-align: center; margin-top: 2rem;">
            <p>Don't have an account? <a href="/computer_shop/register.php" style="color: #e91e63;">Register here</a></p>
        </div>
        
        <div style="text-align: center; margin-top: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px;">
            <h4>Demo Accounts:</h4>
            <p><strong>Admin:</strong> username: admin, password: admin123</p>
            <p><strong>Customer:</strong> Register a new account or use admin account</p>
        </div>
    </div>
</div>

<script>
document.getElementById('login-form').addEventListener('submit', function(e) {
    if (!validateForm('login-form')) {
        e.preventDefault();
        showAlert('Please fill in all required fields correctly.', 'error');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>