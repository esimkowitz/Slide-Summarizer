<?php 
	session_start();
	unset($_SESSION['access_token']);
	$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/oauth2callback.php';
  	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
 ?>