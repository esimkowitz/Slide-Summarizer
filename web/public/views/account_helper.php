<?php
require_once __DIR__.'/../../../vendor/autoload.php';
require 'templates/base.php';

session_start();

$client = new Google_Client();


/************************************************
ATTENTION: Fill in these values, or make sure you
have set the GOOGLE_APPLICATION_CREDENTIALS
environment variable. You can get these credentials
by creating a new Service Account in the
API console. Be sure to store the key file
somewhere you can get to it - though in real
operations you'd want to make sure it wasn't
accessible from the webserver!
Make sure the Books API is enabled on this
account as well, or the call will fail.
************************************************/

if (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    // 	use the application default credentials
    $client->useApplicationDefaultCredentials();
}
else {
    echo missingServiceAccountDetailsWarning();
    return;
}
$client->setApplicationName("Slide-Summarizer");
$client->addScope(Google_Service_Drive::DRIVE_READONLY);
$service = new Google_Service_Drive($client);

$account_token;
if (!empty($_POST['account_token'])) {
    $account_token = $_POST['account_token'];
}

$action;
if (!empty($_POST['action'])) {
    $action = $_POST['action'];
}

switch($action) {
    case "account_details":
        // TODO: add SQL interface so I can get info on a user's existing folders

        // TODO: return a JSON for the existing folders
        break;
    case "new_entry":
        break;
    case "delete_entry":
        break;
    case "logout":
        break;
    default:
        break;
}



?>