<?php
require_once __DIR__.'/../../../vendor/autoload.php';

require 'templates/base.php';

session_start();
ini_set('memory_limit', '500M');

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

// if ($credentials_file = checkServiceAccountCredentialsFile()) {
//     // 	set the location manually
//     $client->setAuthConfig($credentials_file);
// }
// else
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

$service = new Google_Service_Slides($client);
$presentationExists = false;
$presentationTitle = "Invalid presentationId";
if ($presentationId !== "") {
    if (is_object($service->presentations)) {
        $presentation = $service->presentations->get($presentationId, (array(
        'presentationId' => $presentationId,
        'fields' => 'slides(pageElements(shape(placeholder(type),text(textElements(textRun(content))))),objectId),title',
        )));
        
        $presentationExists = true;
        $presentationTitle = $presentation->title;
    }
}
?>
  <!DOCTYPE html>
  <html>

  <head>
    <title>
      <?php echo htmlspecialchars($presentationTitle);?>
    </title>
    <meta charset="utf-8">
    <meta name="name" content="Slide Summarizer">
    <meta name="author" content="Evan Simkowitz">
    <meta name="keywords" content="Google,Slides,Summarizer,PHP,Slide">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Summarizes long Google Slides presentations into a list of bookmarks." />
    <link rel="stylesheet" href="https://<?php echo(urlencode($_SERVER['SERVER_NAME']));?>/slide_reader.css">
    <link rel="shortcut icon" type="image/x-icon" href="https://<?php echo(urlencode($_SERVER['SERVER_NAME']));?>/favicon.ico" />
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g=" crossorigin="anonymous"></script>
  </head>

  <body>
    <div id="header">
      <a href="https://<?php echo (urlencode($_SERVER['SERVER_NAME']));?>">
        <div id="return_link">Return to list of presentations</div>
      </a>
      <?php if ($presentationExists): ?>
        <?php
$slides = $presentation->getSlides();
$last_unique_title = "";
$bookmarks = [];
foreach ($slides as $slide) {
    $page_elements = $slide->getPageElements();
    foreach ($page_elements as $page_element) {
        if (is_object($page_element)) {
            if (is_object($page_element->shape)) {
                if (is_object($page_element->shape->text) && is_object($page_element->shape->placeholder)) {
                    $object_type = $page_element->shape->placeholder->type;
                    if ($object_type === "TITLE" || $object_type === "CENTERED_TITLE") {
                        $isTitle = true;
                        $text_elements = $page_element->shape->text->textElements;
                        $next_title = "";
                        for ($i = 1; $i < count($text_elements); ++$i) {
                            if (is_object($text_elements[$i]->textRun)) {
                                if($text_elements[$i]->textRun->content === "\n")
                                    break;
                                if ($text_elements[$i]->textRun->content !== "" && $text_elements[$i]->textRun->content !== " ") {
                                    $next_title = $next_title.$text_elements[$i]->textRun->content;
                                }
                            }
                        }
                    }
                }
            }
            
            if ($next_title !== $last_unique_title && $next_title !== "") {
                array_push($bookmarks, [$next_title, $slide->objectId]);
                $last_unique_title = $next_title;
            }
        }
        break;
    }
}
?>
        <h1><?php echo htmlspecialchars($presentation->title);?></h1>
    </div>
    <ul id="bookmark_list">
      <?php foreach ($bookmarks as $bookmark): ?>
        <li>
          <a href="#" id="<?php echo urlencode($bookmark[1]);?>">
            <?php echo htmlspecialchars(preg_replace('/[\x00-\x1F\x7F]/u', '',$bookmark[0]));?>
          </a>
        </li>
        <?php endforeach;?>
    </ul>
    <div id="slide_frame_div">
      <iframe id="slide_frame" src="https://docs.google.com/presentation/d/<?php echo urlencode($presentationId);?>/embed?start=false&loop=false&delayms=3000"></iframe>
    </div>
    <script type="text/javascript">
      document.getElementById("bookmark_list").addEventListener('click', doSomething, false);

      function doSomething(e) {
        e.preventDefault();
        if (e.target !== e.currentTarget && e.target.id) {
          var clickedItem = e.target.id;
          console.log(clickedItem);
          document.getElementById("slide_frame").contentWindow.location.replace("https://docs.google.com/presentation/d/<?php echo urlencode($presentationId);?>/embed?start=false&loop=false&delayms=3000&slide=id." + clickedItem);
        }
        e.stopPropagation();
      }
      // Find all iframes
      var $iframe = $("#slide_frame");
      // Find &#x26; save the aspect ratio for all iframes
      $iframe.data("ratio", 9 / 16)
        // Remove the hardcoded width &#x26; height attributes
        .removeAttr("width")
        .removeAttr("height");

      function resizeIframe() {
        if ($(window).width() > 3 * ($("#bookmark_list").width())) {
          var width = $(window).width() - 1.4 * $("#bookmark_list").width();
          if (width * $iframe.data("ratio") > 0.94 * ($(window).innerHeight() - $("#header").height())) {
            width = 0.95 * (($(window).innerHeight() - $("#header").height()) / $iframe.data("ratio"));
          }
          $iframe.parent().width(width).height(width * $iframe.data("ratio"));
          $iframe.parent().css("position", "fixed");
          $iframe.parent().css("padding-top", "7em");
        } else {
          var width = 0.98 * $(window).width();
          $iframe.parent().width(width).height(width * $iframe.data("ratio"));
          $iframe.parent().css("position", "relative");
          $iframe.parent().css("padding-top", "0");
        }
      }
      resizeIframe();
      var $last_header_height = 0;
      function makeMobileFriendly() {
        if ($("#header").height() !== $last_header_height) {
          $("#bookmark_list").css("padding-top", $("#header").height());
          $last_header_height = $("#header").height();
        }
      }
      makeMobileFriendly();
      // Resize the iframes when the window is resized
      $(window).resize(resizeIframe).resize(makeMobileFriendly).resize();
    </script>
    <?php else: ?>
      <h3>Invalid presentationId</h3>
      </div>
      <?php endif;?>
  </body>

  </html>