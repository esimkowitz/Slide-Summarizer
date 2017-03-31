<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();

$client = new Google_Client();
define('CLIENT_SECRET_PATH', __DIR__ . '/../client_secret.json');
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);   // incremental auth
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/oauth2callback.php');
$client->addScope(Google_Service_Slides::PRESENTATIONS_READONLY);
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
if (! isset($_GET['code'])) {
  	$auth_url = $client->createAuthUrl();
  	header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else if ($_GET['code']) {
  	$client->authenticate($_GET['code']);
  	$_SESSION['access_token'] = $client->getAccessToken();
	if ($_SESSION['requester'] === "slide_reader") {
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/slide_reader.php?presentationId=' . $_SESSION['presentationId'];
		unset($_SESSION['presentationId']);
	} else {
		$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/index.php';
	}
	unset($_SESSION['requester']);
  	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} else {
	printf("no code");
}