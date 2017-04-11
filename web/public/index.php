
<?php 
require_once __DIR__.'/../../vendor/autoload.php';
require 'templates/base.php';

session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

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
if ($credentials_file = checkServiceAccountCredentialsFile()) {
  // set the location manually
  $client->setAuthConfig($credentials_file);
} elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
  // use the application default credentials
  $client->useApplicationDefaultCredentials();
} else {
  echo missingServiceAccountDetailsWarning();
  return;
}
$client->setApplicationName("Slide-Summarizer");
$client->addScope(Google_Service_Drive::DRIVE_READONLY);
$service = new Google_Service_Drive($client);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Slide Summarizer</title>
	<meta charset="utf-8" author="Evan Simkowitz">
</head>
<body>
    <?php

    // Print the names and IDs for up to 10 files.
    $files;

    $folderId = '0B7FNQBi7QMOpb3hPdTFmVmYxYm8';
    
    $folder = $service->files->get($folderId);
    $pageToken = null;
    do {
        $q = "'".$folderId."' in parents";
        $response = $service->files->listFiles(array(
            'q' => $q,
            'spaces' => 'drive',
            'orderBy' => 'createdTime',
            'pageToken' => $pageToken,
            'fields' => 'nextPageToken, files(id, name)',
        ));
        $files = $response->files;
    } while ($pageToken != null);
    ?>
    <h1><?php echo $folder->name; ?></h1>
    <ul id="file_list">
        <?php foreach ($files as $file): ?>
        <li>
            <a href="slide_reader.php?presentationId=<?php echo urlencode($file->id); ?>" id="<?php echo $file->id; ?>"><?php echo $file->name; ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>