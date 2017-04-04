<?php 
require_once __DIR__.'/vendor/autoload.php';
session_start();
ini_set('memory_limit', '500M');
$service;
$isLoggedIn = false;
$client = new Google_Client();
define('CLIENT_SECRET_PATH', __DIR__ . '/../client_secret.json');
$client->setAuthConfigFile(CLIENT_SECRET_PATH);
$client->setAccessType("offline");
$client->setIncludeGrantedScopes(true);   // incremental auth
$client->addScope(Google_Service_Slides::PRESENTATIONS_READONLY);
$cache = new Stash\Pool(new Stash\Driver\FileSystem(array()));
$client->setCache($cache);
$presentationId = "";
if (!empty($_GET['presentationId'])) {
  $presentationId = $_GET['presentationId'];
  $_SESSION['presentationId'] = $presentationId;
  $_SESSION['requester'] = "slide_reader";
}
if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
  $service = new Google_Service_Slides($client);
  $isLoggedIn = true;
}
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
  <?php if ($isLoggedIn && $presentationId !== ""): ?>
  <?php
  if (is_object($service->presentations)) {
    $presentation = $service->presentations->get($presentationId);
    $slides = $presentation->getSlides();
    $last_unique_title = "";
    $bookmarks = [];
    foreach ($slides as $slide) {
      $page_elements = $slide->getPageElements();
      foreach ($page_elements as $page_element) {
        if (is_object($page_element)) {
          if (is_object($page_element->shape)) {
            if ($page_element->shape->shapeType === "TEXT_BOX") {
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
  <?php elseif (!$isLoggedIn): ?>
    <h3>Please click below to login with Google</h3>
    <a href="login_redirect.php"><input type="button" name="Login" value="Login"></a>
    </div>
  <?php else: ?>
    <h3>Invalid presentationId</h3>
    </div>
  <?php endif; ?>
</body>
</html>