
<?php 
require_once __DIR__.'/vendor/autoload.php';
session_start();
$service;
$isLoggedIn = false;
$client = new Google_Client();
define('CLIENT_SECRET_PATH', __DIR__ . '/../client_secret.json');
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);   // incremental auth
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
$client->addScope(Google_Service_Slides::PRESENTATIONS_READONLY);
$cache = new Stash\Pool(new Stash\Driver\FileSystem(array()));
$client->setCache($cache);
$_SESSION['requester'] = "index";
unset($_SESSION['presentationId']);
if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
  $service = new Google_Service_Drive($client);
  $isLoggedIn = true;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Slide Summarizer</title>
	<meta charset="utf-8" author="Evan Simkowitz">
</head>
<body>
    <?php if ($isLoggedIn): ?>
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
    <?php else: ?>
        <h3>Please click below to login with Google</h3>
        <a href="login_redirect.php"><input type="button" name="Login" value="Login"></a>
    <?php endif; ?>
</body>
</html>