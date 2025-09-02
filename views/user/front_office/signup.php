<?php

require_once '../../../config.php';
require_once '../../../controllers/UserController.php';

// Create a new instance of UserController
$userC = new UserController();

if (
    isset($_POST["first_name"]) &&
    isset($_POST["last_name"]) &&
    isset($_POST["email"]) &&
    isset($_POST["password"])
) {
    if (
        !empty($_POST['first_name']) &&
        !empty($_POST['last_name']) &&
        !empty($_POST["email"]) &&
        !empty($_POST["password"])
    ) {
        // Initialize and set user properties
        $user = new User();
        $user->setFirstName($_POST['first_name']);
        $user->setLastName($_POST['last_name']);
        $user->setEmail($_POST['email']);
        $user->setPassword($_POST['password']);
        $user->setRole('client');
        $user->setVerified(0);
        $user->setBanned(0);
        $user->setAccountType('normal');
        
        try {
            // Attempt to create the user using UserController
            $userC->addUser($user);
            // Fetch the user (assuming getUserByEmail exists)
            $newUser = $userC->getUserByEmail($user->getEmail());
            if (session_status() === PHP_SESSION_NONE) {
                session_set_cookie_params(0, '/', '', true, true);
                session_start();
            }
            $_SESSION['user_id'] = $newUser['id'];
            $_SESSION['first_name'] = $newUser['first_name'];
            $_SESSION['last_name'] = $newUser['last_name'];
            $_SESSION['email'] = $newUser['email'];
            $_SESSION['role'] = $newUser['role'];
            $_SESSION['verified'] = $newUser['verified'];
            $_SESSION['banned'] = $newUser['banned'];
            $_SESSION['account_type'] = $newUser['account_type'];
            header('Location: home.php');
            exit();
        } catch (Exception $e) {
            $error_message = "Failed to create account. Please try again later.";
            header('Location: register.php?error_global=' . urlencode($error_message));
            exit();
        }
    } else {
        $error_message = "All fields are required.";
        header('Location: register.php?error_global=' . urlencode($error_message));
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recaptchaSecret = '6LfoESorAAAAAA95d8vIESOrFaW4e4dVsNBohfiX';
    $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha = file_get_contents($recaptchaUrl . '?secret=' . $recaptchaSecret . '&response=' . $recaptchaResponse);
    $recaptcha = json_decode($recaptcha);
    if (!$recaptcha || !$recaptcha->success) {
        $error_message = 'Please complete the reCAPTCHA.';
        header('Location: register.php?error_global=' . urlencode($error_message));
        exit();
    }
}

?>