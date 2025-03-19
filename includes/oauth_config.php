<?php
/**
 * OAuth Configuration for Social Login
 * 
 * This file contains configuration settings for various OAuth providers
 * You need to register your application with each provider to get the client ID and secret
 */

// Base URL for redirects (update this to your domain in production)
$base_url = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$base_url .= $_SERVER['HTTP_HOST'];

// Google OAuth settings
// Register at: https://console.developers.google.com/
$google_config = [
    'clientId'     => getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID',
    'clientSecret' => getenv('GOOGLE_CLIENT_SECRET') ?: 'YOUR_GOOGLE_CLIENT_SECRET',
    'redirectUri'  => $base_url . '/oauth_callback.php?provider=google',
    'scopes'       => ['email', 'profile']
];

// Facebook OAuth settings
// Register at: https://developers.facebook.com/
$facebook_config = [
    'clientId'     => getenv('FACEBOOK_CLIENT_ID') ?: 'YOUR_FACEBOOK_CLIENT_ID',
    'clientSecret' => getenv('FACEBOOK_CLIENT_SECRET') ?: 'YOUR_FACEBOOK_CLIENT_SECRET',
    'redirectUri'  => $base_url . '/oauth_callback.php?provider=facebook',
    'graphApiVersion' => 'v12.0',
    'scopes'       => ['email']
];

// GitHub OAuth settings
// Register at: https://github.com/settings/applications/new
$github_config = [
    'clientId'     => getenv('GITHUB_CLIENT_ID') ?: 'YOUR_GITHUB_CLIENT_ID',
    'clientSecret' => getenv('GITHUB_CLIENT_SECRET') ?: 'YOUR_GITHUB_CLIENT_SECRET',
    'redirectUri'  => $base_url . '/oauth_callback.php?provider=github',
    'scopes'       => ['user:email']
];

// Apple OAuth settings
// Register at: https://developer.apple.com/account/resources/identifiers/list/serviceId
$apple_config = [
    'clientId'     => getenv('APPLE_CLIENT_ID') ?: 'YOUR_APPLE_CLIENT_ID',
    'teamId'       => getenv('APPLE_TEAM_ID') ?: 'YOUR_APPLE_TEAM_ID',
    'keyFileId'    => getenv('APPLE_KEY_FILE_ID') ?: 'YOUR_APPLE_KEY_FILE_ID',
    'keyFilePath'  => getenv('APPLE_KEY_FILE_PATH') ?: __DIR__ . '/../keys/AuthKey_APPLE_KEY_FILE_ID.p8',
    'redirectUri'  => $base_url . '/oauth_callback.php?provider=apple',
    'scopes'       => ['name', 'email']
];
?> 