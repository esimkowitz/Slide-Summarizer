<?php
require_once __DIR__.'/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfigFile('client_secret.json');
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);   // incremental auth
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/oauth2callback.php');
$client->addScope(Google_Service_Slides::PRESENTATIONS_READONLY);
if (! isset($_GET['code'])) {
  	$auth_url = $client->createAuthUrl();
  	header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else if ($_GET['code']) {
  	$client->authenticate($_GET['code']);
  	$_SESSION['access_token'] = $client->getAccessToken();
  	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/slide_reader.php?presentationId=' . $_SESSION['presentationId'];
  	unset($_SESSION['presentationId']);
  	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
} else {
	printf("no code");
}