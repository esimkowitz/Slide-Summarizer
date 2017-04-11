<?php 
require_once __DIR__.'/../../vendor/autoload.php';
require 'templates/base.php';
session_start();
ini_set('memory_limit', '500M');
// putenv('GOOGLE_APPLICATION_CREDENTIALS='.CLIENT_SECRET_PATH);
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
$presentationId = "";
if (!empty($_GET['presentationId'])) {
  $presentationId = $_GET['presentationId'];
}
$service = new Google_Service_Slides($client);
?>
<!DOCTYPE html>
<html>
<head>
	<title>Slide Summarizer</title>
	<meta charset="utf-8" author="Evan Simkowitz">
  <link rel="stylesheet" href="slide_reader.css">
  <script
  src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
  integrity="sha256-k2WSCIexGzOj3Euiig+TlR8gA0EmPjuc79OEeY5L45g="
  crossorigin="anonymous"></script>
</head>
<body>
  <div id="header">
  <a href="index.php"><div id="return_link">Return to list of presentations</div></a>
  <?php if ($presentationId !== ""): ?>
  <?php
  if (is_object($service->presentations)) {
    $presentation = $service->presentations->get($presentationId, (array(
            'presentationId' => $presentationId,
            'fields' => 'slides(pageElements(shape(text(textElements(textRun(content))))),objectId),title',
        )));
    $slides = $presentation->getSlides();
    $last_unique_title = "";
    $bookmarks = [];
    foreach ($slides as $slide) {
      $page_elements = $slide->getPageElements();
      foreach ($page_elements as $page_element) {
        if (is_object($page_element)) {
          if (is_object($page_element->shape)) {
            if (is_object($page_element->shape->text)) {
              $text_elements = $page_element->shape->text->textElements;
              if (count($text_elements) > 1) {
                if (is_object($text_elements[1]->textRun)) {
                  if ($text_elements[1]->textRun->content !== $last_unique_title) {
                    array_push($bookmarks, [$text_elements[1]->textRun->content, $slide->objectId]);
                    $last_unique_title = $text_elements[1]->textRun->content;
                  }
                }
              }
            }            
            break;
          }
        }
      }
    } 
  }
  ?>
  <h1><?php echo $presentation->title; ?></h1>
  </div>
  <ul id="bookmark_list">
    <?php foreach ($bookmarks as $bookmark): ?>
      <li>
        <a href="#" id="<?php echo $bookmark[1]; ?>"><?php echo $bookmark[0] ?></a>
      </li>
    <?php endforeach; ?>
  </ul>
  <div id="slide_frame_div">
    <iframe id="slide_frame" src="https://docs.google.com/presentation/d/<?php echo $presentationId?>/embed?start=false&loop=false&delayms=3000" frameborder="0" width="960" height="569" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>
  </div>
  <script type="text/javascript" charset="utf-8">
    document.getElementById("bookmark_list").addEventListener('click', doSomething, false);
    function doSomething(e) {
      e.preventDefault();
      if (e.target !== e.currentTarget && e.target.id) {
        var clickedItem = e.target.id;
        console.log(clickedItem);
        document.getElementById("slide_frame").src = "https://docs.google.com/presentation/d/<?php echo $presentationId?>/embed?start=false&loop=false&delayms=3000&slide=id." + clickedItem;
      }
      e.stopPropagation();
    }
    // Find all iframes
    var $iframes = $( "iframe" );
    
    // Find &#x26; save the aspect ratio for all iframes
    $iframes.each(function () {
      $( this ).data( "ratio", 9/16 )
        // Remove the hardcoded width &#x26; height attributes
        .removeAttr( "width" )
        .removeAttr( "height" );
    });
    function resizeIframe() {
      $iframes.each( function() {
        if ($(window).width() > 3*($("#bookmark_list").width())) {
          var width = $(window).width() - 1.4*$("#bookmark_list").width();
          if (width * $(this).data("ratio") > 0.94*($(window).innerHeight()-$("#header").height())) {
            width = 0.95*(($(window).innerHeight()-$("#header").height()) / $(this).data("ratio"));
          }
          $(this).parent().width(width).height( width * $( this ).data( "ratio" ) );
          $(this).parent().css("position", "fixed");
          $(this).parent().css("padding-top", "7em");
        } else {
          var width = 0.98*$(window).width();
          $(this).parent().width(width).height( width * $( this ).data( "ratio" ) );
          $(this).parent().css("position", "relative");
          $(this).parent().css("padding-top", "0");
        }
      });
    // Resize to fix all iframes on page load.
    }
    resizeIframe();
    // Resize the iframes when the window is resized
    $( window ).resize(resizeIframe).resize();
  </script>
  <?php else: ?>
    <h3>Invalid presentationId</h3>
    </div>
  <?php endif; ?>
</body>
</html>