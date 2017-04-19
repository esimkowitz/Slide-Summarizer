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
if ($credentials_file = checkServiceAccountCredentialsFile()) {
	// 	set the location manually
    $client->setAuthConfig($credentials_file);
}
elseif (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
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
?>
<!DOCTYPE html>
<html>
<head>
	<title>Slide Summarizer</title>
	<meta charset="utf-8" author="Evan Simkowitz">
    <meta name="description" content="Summarizes long Google Slides presentations into a list of bookmarks."/>
    <script type="text/javascript">
        function createKeenWebAutoCollector(){window.keenWebAutoCollector=window.KeenWebAutoCollector.create({projectId:'58ee6df995cfc9addc24722c',writeKey:'1A9D2151FAA1AD8B57043FC1FEB63D159114B6C842FBF03D70DDD75AF73BFFA6687611B30B1148561B472583FA7576F359F2C81EF4B6CF67E73F892BD45B02C5D15C2CDC1AF734FDC75EB737B0E38080FB0FC21D4B9CDB27E3AC2CF3F04B4C79',onloadCallbacks:window.keenWebAutoCollector.onloadCallbacks}),window.keenWebAutoCollector.loaded()}function initKeenWebAutoCollector(){window.keenWebAutoCollector.domReady()?window.createKeenWebAutoCollector():document.addEventListener("readystatechange",function(){window.keenWebAutoCollector.domReady()&&window.createKeenWebAutoCollector()})}window.keenWebAutoCollector={onloadCallbacks:[],onload:function(a){this.onloadCallbacks.push(a)},domReady:function(){return["ready","complete"].indexOf(document.readyState)>-1}};
    </script>
    <script async type="text/javascript" src="https://d26b395fwzu5fz.cloudfront.net/keen-web-autocollector-1.0.8.min.js" onload="initKeenWebAutoCollector()"></script>
</head>
<body>
    <?php

// Print the names and IDs for up to 10 files.
    $files;

$folderId = '0BzBVKysC8glQaEt3QjVJQW1CdjA';

$folder = $service->files->get($folderId);
$pageToken = null;
do {
	$q = "'".$folderId."' in parents";
	$response = $service->files->listFiles(
	        array(
	            'q' => $q,
	            'spaces' => 'drive',
	            'orderBy' => 'createdTime',
	            'pageToken' => $pageToken,
	            'fields' => 'nextPageToken, files(id, name)'
	        )
		);
	$files = $response->files;
}
while ($pageToken != null);
?>
    <h1><?php echo $folder->name;
?></h1>
    <ul id="file_list">
        <?php foreach ($files as $file): ?>
        <li>
            <a href="presentation/<?php echo urlencode($file->id);
?>" id="<?php echo $file->id;
?>"><?php echo $file->name;
?></a>
        </li>
        <?php endforeach;
?>
    </ul>
</body>
</html>