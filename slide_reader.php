<?php 
require_once __DIR__.'/vendor/autoload.php';
session_start(); 
$client = new Google_Client();
$client->setAuthConfig('client_secret.json');
$client->addScope(Google_Service_Slides::PRESENTATIONS_READONLY);
$service;
$presentationId = "";
if (!empty($_GET['presentationId'])) {
  $presentationId = $_GET['presentationId'];
  $_SESSION['presentationId'] = $presentationId;
}
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
  $service = new Google_Service_Slides($client);
  $client->setAccessType("offline");
  if (!empty($_SESSION['presentationId'])) {
    $presentationId = $_SESSION['presentationId'];
  }
  // session_destroy();
} else {
  $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/Slide-Summarizer/oauth2callback.php';
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
}
$presentation = $service->presentations->get($presentationId);
$slides = $presentation->getSlides();
$last_unique_title = "";
$bookmarks = [];
foreach ($slides as $slide) {
  $page_elements = $slide->getPageElements();
  foreach ($page_elements as $page_element) {
    if ($page_element->shape->shapeType === "TEXT_BOX") {
      $text_elements = $page_element->shape->text->textElements;
      if ($text_elements[1]->textRun->content !== $last_unique_title) {
        array_push($bookmarks, [$text_elements[1]->textRun->content, $slide->objectId]);
        $last_unique_title = $text_elements[1]->textRun->content;
      }
      break;
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Slide Summarizer</title>
	<meta charset="utf-8" author="Evan Simkowitz">
</head>
<body>
<h1><?php echo $presentation->title; ?></h1>
<ul id="bookmark_list">
	<?php foreach ($bookmarks as $bookmark): ?>
		<li>
			<a href="#" id="<?php echo $bookmark[1]; ?>"><?php echo $bookmark[0] ?></a>
		</li>
	<?php endforeach; ?>
</ul>
<iframe id="slide_frame" src="https://docs.google.com/presentation/d/<?php echo $presentationId?>/embed?start=false&loop=false&delayms=3000" frameborder="0" width="960" height="569" allowfullscreen="true" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>
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
</script>
</body>
</html>