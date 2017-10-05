<?php
require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);
$router = new AltoRouter();
// $basePath = '/~esimk/Slide-Summarizer';
// $router->setBasePath($basePath);
// $_SERVER['BASE_PATH'] = $basePath;

// map homepage
$router->map( 'GET', '/', function() {
    include __DIR__.'/public/views/list_slides.php';
}, 'home');

// map oauth3 callback
$router->map( 'GET', '/oauth2callback', function() {
    include __DIR__.'/public/views/oauth2callback.php';
}, 'oauth2callback');

// map to presentations
$router->map( 'GET', '/presentation/[:id]', function( $id ) {
    $presentationId = $id;
    include __DIR__.'/public/views/slide_reader.php';
}, 'presentation');

// map to json of presentations
$router->map( 'GET', '/presentation/[:id].json', function( $id ) {
    $presentationId = $id;
    include __DIR__.'/public/views/slide_reader_json.php';
}, 'presentation_json');

// map to css files
$router->map( 'GET', '/[:filename].css', function($filename) {
    header("Content-Type: text/css");
    $file_path = __DIR__."/public/css"."/".$filename.".css";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'css');

// map to js files
$router->map( 'GET', '/[:filename].js', function($filename) {
    header("Content-Type: text/javascript");
    $file_path = __DIR__."/public/js"."/".$filename.".js";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'javascript');

// map to icons
$router->map( 'GET', '/[:filename].ico', function($filename) {
    header("Content-Type: image/x-icon");
    $file_path = __DIR__."/public/icons"."/".$filename.".ico";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'ico');
$match = $router->match();
// echo var_dump($match);
// call closure or throw 404 status
if( $match && is_callable( $match['target'] ) ) {
    call_user_func_array( $match['target'], $match['params'] );
} else {
    // no route was matched
    header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
}
?>