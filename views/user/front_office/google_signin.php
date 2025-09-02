<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../config/google_config.php';
require_once __DIR__ . '/../../../controllers/UserController.php';


// Initialize Google Client
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope(GOOGLE_SCOPES);

// If we have a code from Google OAuth2
if (isset($_GET['code'])) {
    try {
        // Get token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);

        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        // Create UserController instance
        $userController = new UserController();

        // Check if user exists
        $email = $google_account_info->email;
        $user = $userController->getUserByEmail($email);

        if (!$user) {
            // Create new user
            $user = new User();
            $user->setFirstName($google_account_info->givenName);
            $user->setLastName($google_account_info->familyName);
            $user->setEmail($email);
            $user->setPassword(bin2hex(random_bytes(16))); // Random password for Google users
            $user->setRole('user');
            $user->setVerified(1); // Google accounts are verified
            $user->setBanned(0);
            $user->setAccountType('google');
            
            $userController->addUser($user);
        }

        // Log the user in
        $result = $userController->loginUserWithGoogle($email);

        if ($result['status']) {
            header('Location: profile.php');
            exit();
        } else {
            header('Location: signin.php?error=' . urlencode($result['message']));
            exit();
        }

    } catch (Exception $e) {
        header('Location: signin.php?error=' . urlencode('Authentication failed. Please try again.'));
        exit();
    }
} else {
    // Generate login URL
    $auth_url = $client->createAuthUrl();
    header('Location: ' . $auth_url);
    exit();
}