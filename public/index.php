<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/env.php';

$logger = new Monolog\Logger('main_logger');
$logger->pushHandler(
    new Monolog\Handler\RotatingFileHandler(__DIR__ . '/../logs/main.log', LOGROTATE_MAX_FILES, LOG_LEVEL)
);

register_shutdown_function(function() use ($logger){
    return log_last_error($logger);
});

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/','index_list');
    $r->addRoute('POST', '/create_index', 'create_index');
    $r->addRoute('POST', '/{index_name}/drop', 'drop_index');
    $r->addRoute('POST', '/{index_name}/add', 'add_to_index');
    $r->addRoute('POST', '/{index_name}/remove', 'remove_from_index');
    $r->addRoute('GET', '/{index_name}/query/{query_string}', 'query_index');
    /*
    $r->addRoute('GET', '/{param_name:allowed_value_1|allowed_value_1}', 'foo');
    */

});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $logger->debug('Route not found');
        http_response_code(404);
        exit;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $logger->debug('Method not allowed');
        http_response_code(405);
        exit;
    case FastRoute\Dispatcher::FOUND:
        $request_handler = $routeInfo[1];
        require __DIR__ . '/../src/request_handlers/' . $request_handler . '.php'; 
        $vars = $routeInfo[2];
        if($httpMethod === 'POST') {
            $payload = get_post_payload();
            $request_handler($vars, $payload);     
        } else {
            $request_handler($vars); 
        }
        break;
}




