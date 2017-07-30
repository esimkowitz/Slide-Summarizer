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
if ($presentationExists) {
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
                    array_push($bookmarks, [preg_replace('/[\x00-\x1F\x7F]/u', '',$next_title), $slide->objectId]);
                    $last_unique_title = $next_title;
                }
            }
            break;
        }
    }
    $response_json = [
        "title" => $presentationTitle,
        "bookmarks" => $bookmarks
    ];
    echo json_encode($response_json);
}
?>