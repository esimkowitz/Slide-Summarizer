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
$credentials_json;

$client = new Google_Client();
if ($credentials_file = getOAuthCredentialsFile()) {
    // 	set the location manually
    $client->setAuthConfig($credentials_file);
    $credentials_json = json_decode(file_get_contents($credentials_file));
}
else {
    echo missingServiceAccountDetailsWarning();
    return;
}
$client->setApplicationName("Slide-Summarizer");
$service;
$isLoggedIn = false;
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
    $client->addScope(Google_Service_Drive::DRIVE_READONLY);
    $service = new Google_Service_Drive($client);
    $isLoggedIn = true;
} else {
    $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . '/oauth2callback';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
?>

  <!DOCTYPE html>
  <html xmlns="http://www.w3.org/1999/xhtml">

  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Slide Summarizer</title>
    <?php if ($isLoggedIn): ?>
      <script type="text/javascript">
        // The Browser API key obtained from the Google API Console.
        // Replace with your own Browser API key, or your own key.
        var developerKey = '<?php echo htmlspecialchars(base64_decode(getenv('
        GOOGLE_PICKER_KEY '))); ?>';

        // The Client ID obtained from the Google API Console. Replace with your own Client ID.
        var clientId = '<?php echo htmlspecialchars($credentials_json->web->client_id); ?>';

        // Replace with your own project number from console.developers.google.com.
        // See "Project number" under "IAM & Admin" > "Settings"
        var appId = "<?php echo htmlspecialchars(substr($credentials_json->web->client_id, 0, strpos($credentials_json->web->client_id, '-'))); ?>";

        // Scope to use to access user's Drive items.
        var scope = ['https://www.googleapis.com/auth/drive.readonly'];

        var pickerApiLoaded = false;
        var oauthToken = "<?php echo htmlspecialchars($_SESSION['access_token']['access_token']); ?>";

        // Use the Google API Loader script to load the google.picker script.
        function loadPicker() {
          // gapi.load('auth', {
          //   'callback': onAuthApiLoad
          // });
          gapi.load('picker', {
            'callback': onPickerApiLoad
          });
        }

        function onPickerApiLoad() {
          pickerApiLoaded = true;
          createPicker();
        }
        // Create and render a Picker object for searching images.
        function createPicker() {
          if (pickerApiLoaded && oauthToken) {
            var view = new google.picker.DocsView(google.picker.ViewId.FOLDERS)
              .setIncludeFolders(true)
              .setSelectFolderEnabled(true)
              .setOwnedByMe(true);
            var picker = new google.picker.PickerBuilder()
              .setAppId(appId)
              .setOAuthToken(oauthToken)
              .addView(view)
              .setDeveloperKey(developerKey)
              .setCallback(pickerCallback)
              .build();
            picker.setVisible(true);
          }
        }

        // A simple callback implementation.
        function pickerCallback(data) {
          if (data.action == google.picker.Action.PICKED) {
            var fileId = data.docs[0].id;
            alert('The user selected: ' + fileId);
          }
        }
      </script>
      <?php endif; ?>
  </head>

  <body>
    <div id="result"></div>
    <?php if (!$isLoggedIn): ?>
      <input type="button" name="login_button" value="Login" class="button" id="login_button"></input>

      <script>
        document.getElementById("login_button").addEventListener("click", loadPicker);
      </script>
      <?php else: ?>
        <!-- The Google API Loader script. -->
        <script type="text/javascript" src="https://apis.google.com/js/api.js?onload=loadPicker"></script>
        <?php endif; ?>
  </body>

  </html>