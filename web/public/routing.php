<?php
require_once __DIR__.'/../../vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

$router = new AltoRouter();

// map homepage
$router->map( 'GET', '/', function() {
    include __DIR__.'/views/account.php';
}, 'home');

// map cse247 filler page
$router->map( 'GET', '/cse247', function() {
    include __DIR__.'/views/list_slides.php';
}, 'cse247');

// map account page helper
$router->map( 'GET', '/account_helper', function() {
    include __DIR__.'/views/account_helper.php';
}, 'account_helper');

// map oauth3 callback
$router->map( 'GET', '/oauth2callback', function() {
    include __DIR__.'/views/oauth2callback.php';
}, 'oauth2callback');

// map to presentations
$router->map( 'GET', '/presentation/[:id]', function( $id ) {
    $presentationId = $id;
    include __DIR__.'/views/slide_reader.php';
}, 'presentation');

// map to json of presentations
$router->map( 'GET', '/presentation/[:id].json', function( $id ) {
    $presentationId = $id;
    include __DIR__.'/views/slide_reader_json.php';
}, 'presentation_json');

// map to css files
$router->map( 'GET', '/[:filename].css', function($filename) {
    header("Content-Type: text/css");
    $file_path = __DIR__."/css"."/".$filename.".css";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'css');

// map to js files
$router->map( 'GET', '/[:filename].js', function($filename) {
    header("Content-Type: text/javascript");
    $file_path = __DIR__."/js"."/".$filename.".js";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'javascript');

// map to icons
$router->map( 'GET', '/[:filename].ico', function($filename) {
    header("Content-Type: image/x-icon");
    $file_path = __DIR__."/icons"."/".$filename.".ico";
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