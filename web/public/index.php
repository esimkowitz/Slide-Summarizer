<?php
require_once __DIR__.'/../../vendor/autoload.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

$router = new AltoRouter();

// map homepage
$router->map( 'GET', '/', function() {
    include __DIR__.'/views/list_slides.php';
}, 'home');

// map to presentations
$router->map( 'GET', '/presentation/[:id]', function( $id ) {
    $presentationId = $id;
    include __DIR__.'/views/slide_reader.php';
}, 'presentation');

// map to css files
$router->map( 'GET', '/[:filename].css', function($filename) {
    header("Content-Type: text/css");
    $file_path = __DIR__."/css"."/".$filename.".css";
    if (file_exists($file_path)) {
        include $file_path;
    }
}, 'css');
$router->map( 'GET', '/[:filename].ico', function($filename) {
    header("Content-Type: image/x-icon");
    $file_path = __DIR__."/pictures"."/".$filename.".ico";
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