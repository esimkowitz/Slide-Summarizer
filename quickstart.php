
<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Slides API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/slides.googleapis.com-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/slides.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Slides::PRESENTATIONS_READONLY)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Slides($client);

// Prints the number of slides and elements in a sample presentation:
// https://docs.google.com/presentation/d/1iDrIm6uNWIrThzIxglIO2-rbnB11qWTt41LBgic9glQ/edit
// $presentationId = '1iDrIm6uNWIrThzIxglIO2-rbnB11qWTt41LBgic9glQ';
$presentationId = $argv[1];
$presentation = $service->presentations->get($presentationId);
$slides = $presentation->getSlides();
$last_unique_title = "";
printf("The presentation contains %s slides:\n", count($slides));
foreach ($slides as $i => $slide) {
  $page_elements = $slide->getPageElements();
  foreach ($page_elements as $j => $page_element) {
    if ($page_element->shape->shapeType === "TEXT_BOX") {
      $text_elements = $page_element->shape->text->textElements;
      if ($text_elements[1]->textRun->content !== $last_unique_title) {
        printf("- Slide %s with title: %s\n",$slide->objectId, $text_elements[1]->textRun->content);
        $last_unique_title = $text_elements[1]->textRun->content;
      }
      // printf("-- %s\n",$text_elements[1]->textRun->content);
      // foreach ($text_elements as $k => $text_element) {
      //   if ($text_element->textRun->content) {
      //     printf("--- %s\n",$text_element->textRun->content);
      //   }
      // }
      break;
    }
  }
}
