<?php
include '../vendor/autoload.php';
// Simple HTTP static file server using Swoole
$host = $argv[1] ?? '127.0.0.1';
$port = $argv[2] ?? 9501;
$http = new swoole_http_server($host, $port);
// The usage of enable_static_handler seems to produce errors
// $http->set([
//    'document_root' => __DIR__,
//    'enable_static_handler' => true
// ]);
$http->on("start", function ($server) {
    printf("HTTP server started at http://%s:%s\n", $server->host, $server->port);
});
$static = [
    'png' => 'image/png',
    'gif' => 'image/gif',
    'jpg' => 'image/jpg',
    'jpeg' => 'image/jpg',
    'svg' => 'image/svg+xml'
];
$http->on("request", function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($static) {
    $request_uri = $request->server['request_uri'];
    $uris = explode('@', $request_uri);
    $path = filter_var($uris[0], FILTER_SANITIZE_STRING);
    $staticFile = __DIR__ . '/../storage' . $path;

    if (!file_exists($staticFile)) {
        return response_error($response);
    }
    $type = pathinfo($staticFile, PATHINFO_EXTENSION);
    if (!isset($static[$type])) {
        return response_error($response);
    }

    if (!empty($uris[1])) {
        $option = filter_var($uris[1], FILTER_SANITIZE_SPECIAL_CHARS);

        $image = new \Gumlet\ImageResize($staticFile);
        if (strstr( $option, 'w')) {
            $option = preg_replace("/[^0-9]/", '', $option);
            $image->resizeToWidth($option);
        }
        if (strstr( $option, 'h')) {
            $option = preg_replace("/[^0-9]/", '', $option);
            $image->resizeToHeight($option);
        }
        var_dump($option);
        $resizeDirectory = __DIR__ . '/../storage/resized';
        if (!file_exists($resizeDirectory)) {
            mkdir($resizeDirectory, 0777, true);
        }
        $staticFile = $resizeDirectory . $path;
        var_dump($staticFile);
        if(!file_exists($staticFile)){
            touch($staticFile);
            chmod($staticFile, 0755);
        }
        $image->save($staticFile);
    }

    $response->header('Content-Type', $static[$type]);
    $response->sendfile($staticFile);
    return true;

});
$http->start();
/**
 * @param Swoole\Http\Response $response
 * @param int $code
 */
function response_error(Swoole\Http\Response $response, $code = 404): bool
{
    $response->status($code);
    $response->end();

    return false;
}
