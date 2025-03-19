<?php
/**
 * OAuth Login Initiator
 * 
 * This script initiates the OAuth authentication flow for the selected provider
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once 'config.php';
require_once 'includes/oauth_config.php';

// Check if Composer is installed and autoload file exists
if (!file_exists('vendor/autoload.php')) {
    die('Please run "composer install" to install the required dependencies.');
}

// Require Composer autoloader
require_once 'vendor/autoload.php';

// Get the requested provider from URL parameter
$provider = isset($_GET['provider']) ? strtolower($_GET['provider']) : null;

// Check if the provider is valid
$validProviders = ['google', 'facebook', 'github', 'apple'];
if (!in_array($provider, $validProviders)) {
    die('Invalid authentication provider specified.');
}

// Create OAuth provider instance based on requested provider
try {
    switch ($provider) {
        case 'google':
            $providerObj = new League\OAuth2\Client\Provider\Google([
                'clientId'     => $google_config['clientId'],
                'clientSecret' => $google_config['clientSecret'],
                'redirectUri'  => $google_config['redirectUri'],
            ]);
            $scopes = $google_config['scopes'];
            break;
            
        case 'facebook':
            $providerObj = new League\OAuth2\Client\Provider\Facebook([
                'clientId'     => $facebook_config['clientId'],
                'clientSecret' => $facebook_config['clientSecret'],
                'redirectUri'  => $facebook_config['redirectUri'],
                'graphApiVersion' => $facebook_config['graphApiVersion'],
            ]);
            $scopes = $facebook_config['scopes'];
            break;
            
        case 'github':
            $providerObj = new League\OAuth2\Client\Provider\Github([
                'clientId'     => $github_config['clientId'],
                'clientSecret' => $github_config['clientSecret'],
                'redirectUri'  => $github_config['redirectUri'],
            ]);
            $scopes = $github_config['scopes'];
            break;
            
        case 'apple':
            $providerObj = new PatrickBussmann\OAuth2\Client\Provider\Apple([
                'clientId'     => $apple_config['clientId'],
                'teamId'       => $apple_config['teamId'],
                'keyFileId'    => $apple_config['keyFileId'],
                'keyFilePath'  => $apple_config['keyFilePath'],
                'redirectUri'  => $apple_config['redirectUri'],
            ]);
            $scopes = $apple_config['scopes'];
            break;
    }
    
    // Store state in session to prevent CSRF attacks
    $_SESSION['oauth2state'] = $providerObj->getState();
    
    // Create authorization URL with scopes
    $authUrl = $providerObj->getAuthorizationUrl(['scope' => $scopes]);
    
    // Store the provider for use in the callback
    $_SESSION['oauth_provider'] = $provider;
    
    // Redirect user to the authorization URL
    header('Location: ' . $authUrl);
    exit;
    
} catch (Exception $e) {
    // Handle any exceptions that may occur
    die('Error: ' . $e->getMessage());
}
?> 