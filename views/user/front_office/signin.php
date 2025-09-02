<?php
require_once '../../../config.php';
require_once '../../controllers/UserController.php';

session_start();

// If user is already logged in, redirect to profile page
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit();
}

$error = '';
$success = '';

// Check for success message from registration
if (isset($_GET['success_global'])) {
    $success = $_GET['success_global'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $userController = new UserController();
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Attempt to authenticate user
        $result = $userController->loginUser($email, $password);

        if ($result['status']) {
            echo "<script>alert('Login successful!');</script>";
            header('Location: profile.php');
            exit();
        } else {
            $error = $result['message'];
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }
    } else {
        $error = 'Please provide both email and password.';
        echo "<script>alert('Please provide both email and password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .social-login {
        text-align: center;
        margin: 20px 0;
    }

    .social-login p {
        margin-bottom: 10px;
        color: #666;
    }

    .google-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #757575;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 10px 20px;
        margin: 0 auto;
        width: 80%;
        max-width: 240px;
        text-decoration: none;
        transition: background-color 0.3s;
    }

    .google-btn:hover {
        background-color: #f5f5f5;
    }

    .google-btn img {
        width: 18px;
        height: 18px;
        margin-right: 10px;
    }

        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #45a049;
        }
        .error-message {
            color: #ff0000;
            margin-bottom: 10px;
        }
        .success-message {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .signup-link {
            text-align: center;
            margin-top: 15px;
        }
        .password-input-container {
            position: relative;
        }
        .password-input-container i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Sign In</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" required>
                    <i id="eye-icon" class="fa-solid fa-eye-slash" onclick="togglePasswordVisibility()"></i>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="social-login">
            <p>Or sign in with:</p>
            <a href="google_signin.php" class="google-btn">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google Logo">
                Sign in with Google
            </a>
        </div>
        
        <div class="signup-link">
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
            <p><a href="forgot_password.php">Forgot Password?</a></p>
        </div>
    </div>

    <script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        }
    }
    </script>
</body>
</html>