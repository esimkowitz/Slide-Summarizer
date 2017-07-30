<?php
require_once __DIR__.'/../../../vendor/autoload.php';
require 'templates/base.php';
session_start();

$client = new Google_Client();
$client->setApplicationName("Slide-Summarizer");
if ($credentials_file = getOAuthCredentialsFile()) {
    // 	set the location manually
    $client->setAuthConfig($credentials_file);
    $credentials_json = json_decode(file_get_contents($credentials_file));
}
else {
    echo missingServiceAccountDetailsWarning();
    return;
}
$client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/oauth2callback');
$client->addScope(Google_Service_Drive::DRIVE_READONLY);

if (! isset($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}