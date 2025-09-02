<?php
session_start();

require_once '../../../config.php';
require_once '../../../config/GoogleLogin.php';
require_once '../../../controllers/UserController.php';
require_once '../../../models/User.php';
require_once '../../../vendor/autoload.php';

// Initialize GoogleLogin with the correct redirect URI
$googleLogin = new GoogleLogin(GOOGLE_REDIRECT_URI);
$client = $googleLogin->getClient();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);

// If we have a code from Google OAuth2
if (isset($_GET['code'])) {
    try {
        // Get token
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            throw new Exception($token['error_description'] ?? 'Failed to get access token');
        }
        
        $client->setAccessToken($token);

        // Get user info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        if (!$google_account_info->email) {
            throw new Exception('Failed to get user email from Google');
        }

        // Create UserController instance
        $userController = new UserController();

        // Check if user exists
        $email = $google_account_info->email;
        $user = $userController->getUserByEmail($email);

        if (!$user) {
            // Create new user
            $user = new User();
            $user->setFirstName($google_account_info->givenName ?? '');
            $user->setLastName($google_account_info->familyName ?? '');
            $user->setEmail($email);
            $user->setPassword(password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)); // Secure random password
            $user->setRole('user');
            $user->setVerified(1); // Google accounts are verified
            $user->setBanned(0);
            $user->setAccountType('google');
            
            try {
                $userController->addUser($user);
            } catch (Exception $e) {
                throw new Exception('Failed to create new user: ' . $e->getMessage());
            }
        }

        // Log the user in
        $result = $userController->loginUserWithGoogle($email);

        if ($result['status']) {
            header('Location: profile.php');
            exit();
        } else {
            throw new Exception($result['message']);
        }

    } catch (Exception $e) {
        $error_message = $e->getMessage();
        error_log('Google Auth Error: ' . $error_message);
        header('Location: signin.php?error=' . urlencode($error_message));
        exit();
    }
} else {
    // Generate login URL
    $auth_url = $client->createAuthUrl();
    header('Location: ' . $auth_url);
    exit();
}