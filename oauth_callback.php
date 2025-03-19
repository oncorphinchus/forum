<?php
/**
 * OAuth Callback Handler
 * 
 * This script handles the callback from OAuth providers and processes user authentication
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'config.php';
require_once 'includes/oauth_config.php';
require_once 'includes/functions.php';

// Check if Composer is installed and autoload file exists
if (!file_exists('vendor/autoload.php')) {
    die('Please run "composer install" to install the required dependencies.');
}

// Require Composer autoloader
require_once 'vendor/autoload.php';

// Get the provider from URL parameter or session
$provider = isset($_GET['provider']) ? strtolower($_GET['provider']) : 
           (isset($_SESSION['oauth_provider']) ? $_SESSION['oauth_provider'] : null);

// Check if the provider is valid
$validProviders = ['google', 'facebook', 'github', 'apple'];
if (!in_array($provider, $validProviders)) {
    redirect('login.php', 'Invalid authentication provider.');
}

// Check for errors from OAuth provider
if (isset($_GET['error'])) {
    redirect('login.php', 'Authentication failed: ' . htmlspecialchars($_GET['error']));
}

// Verify state parameter to prevent CSRF attacks
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    // Invalid state, clear the session and redirect to login
    unset($_SESSION['oauth2state']);
    redirect('login.php', 'Invalid state parameter. Please try again.');
}

// Create OAuth provider instance based on the provider
try {
    switch ($provider) {
        case 'google':
            $providerObj = new League\OAuth2\Client\Provider\Google([
                'clientId'     => $google_config['clientId'],
                'clientSecret' => $google_config['clientSecret'],
                'redirectUri'  => $google_config['redirectUri'],
            ]);
            break;
            
        case 'facebook':
            $providerObj = new League\OAuth2\Client\Provider\Facebook([
                'clientId'     => $facebook_config['clientId'],
                'clientSecret' => $facebook_config['clientSecret'],
                'redirectUri'  => $facebook_config['redirectUri'],
                'graphApiVersion' => $facebook_config['graphApiVersion'],
            ]);
            break;
            
        case 'github':
            $providerObj = new League\OAuth2\Client\Provider\Github([
                'clientId'     => $github_config['clientId'],
                'clientSecret' => $github_config['clientSecret'],
                'redirectUri'  => $github_config['redirectUri'],
            ]);
            break;
            
        case 'apple':
            $providerObj = new PatrickBussmann\OAuth2\Client\Provider\Apple([
                'clientId'     => $apple_config['clientId'],
                'teamId'       => $apple_config['teamId'],
                'keyFileId'    => $apple_config['keyFileId'],
                'keyFilePath'  => $apple_config['keyFilePath'],
                'redirectUri'  => $apple_config['redirectUri'],
            ]);
            break;
    }
    
    // Exchange authorization code for access token
    $token = $providerObj->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    
    // Get user details from provider
    $user = $providerObj->getResourceOwner($token);
    
    // Extract user data (each provider has a different structure)
    $userData = $user->toArray();
    
    // Get the OAuth user ID and email
    switch ($provider) {
        case 'google':
            $oauthId = $userData['id'];
            $email = $userData['email'];
            $username = isset($userData['given_name']) ? $userData['given_name'] . rand(100, 999) : '';
            $avatar = isset($userData['picture']) ? $userData['picture'] : '';
            break;
            
        case 'facebook':
            $oauthId = $userData['id'];
            $email = $userData['email'];
            $username = isset($userData['name']) ? explode(' ', $userData['name'])[0] . rand(100, 999) : '';
            $avatar = "https://graph.facebook.com/{$oauthId}/picture?type=large";
            break;
            
        case 'github':
            $oauthId = $userData['id'];
            $email = $userData['email'] ?? '';
            // If email not provided, try to fetch it explicitly (GitHub doesn't always provide email)
            if (empty($email)) {
                $request = $providerObj->getAuthenticatedRequest(
                    'GET',
                    'https://api.github.com/user/emails',
                    $token
                );
                $emails = $providerObj->getParsedResponse($request);
                foreach ($emails as $emailData) {
                    if ($emailData['primary'] && $emailData['verified']) {
                        $email = $emailData['email'];
                        break;
                    }
                }
            }
            $username = $userData['login'] ?? '';
            $avatar = $userData['avatar_url'] ?? '';
            break;
            
        case 'apple':
            $oauthId = $userData['sub'];
            $email = $userData['email'] ?? '';
            // With Apple, name is only provided on first login
            $username = empty($_POST['user']) ? '' : json_decode($_POST['user'], true)['name']['firstName'] . rand(100, 999);
            $avatar = '';
            break;
            
        default:
            redirect('login.php', 'Unknown provider.');
    }
    
    // Check if user exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE oauth_provider = ? AND oauth_id = ?");
    $stmt->bind_param("ss", $provider, $oauthId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, log them in
        $user = $result->fetch_assoc();
        
        // Update user data if necessary
        if (!empty($avatar) && empty($user['avatar_url'])) {
            $stmt = $conn->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $stmt->bind_param("si", $avatar, $user['id']);
            $stmt->execute();
        }
        
        // Log the user in
        login_user($user['id']);
        
        // Redirect to home page
        redirect('index.php', 'You have been logged in successfully!');
    } else {
        // Check if email already exists
        if (!empty($email)) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Email exists but with different auth method
                $user = $result->fetch_assoc();
                
                // Link this OAuth account to the existing user
                $stmt = $conn->prepare("UPDATE users SET oauth_provider = ?, oauth_id = ?, oauth_data = ? WHERE id = ?");
                $oauthData = json_encode($userData);
                $stmt->bind_param("sssi", $provider, $oauthId, $oauthData, $user['id']);
                $stmt->execute();
                
                // Log the user in
                login_user($user['id']);
                
                // Redirect to home page
                redirect('index.php', 'Your account has been linked to ' . ucfirst($provider) . '!');
            }
        }
        
        // New user, register them
        // Generate a unique username if not provided
        if (empty($username)) {
            $username = strtolower(substr($provider, 0, 1) . substr(md5($oauthId), 0, 7));
        }
        
        // Make sure username is unique
        $baseUsername = $username;
        $counter = 1;
        while (true) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                break;
            }
            
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        // Insert the new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, oauth_provider, oauth_id, oauth_data, avatar_url) VALUES (?, ?, ?, ?, ?, ?)");
        $oauthData = json_encode($userData);
        $stmt->bind_param("ssssss", $username, $email, $provider, $oauthId, $oauthData, $avatar);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Get the new user's ID
            $userId = $conn->insert_id;
            
            // Log the user in
            login_user($userId);
            
            // Redirect to username confirmation page
            redirect('confirm_username.php', 'Account created successfully! Please confirm your username.');
        } else {
            // Registration failed
            redirect('login.php', 'Failed to create account. Please try again.');
        }
    }
    
} catch (Exception $e) {
    // Handle any exceptions that may occur
    redirect('login.php', 'Error: ' . $e->getMessage());
}

// Helper function to redirect with message
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header('Location: ' . $url);
    exit;
}
?> 