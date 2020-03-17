<?php
namespace NgramSearch\RequestHandler\RebuildIndex;

function run(array $vars = [], \stdClass $payload) : void {

    set_header("HTTP/1.1 500 Internal Server Error"); 
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'errors' => [
                [
                    'status' => 500,
                    'title' => 'Internal Server Error',
                    'detail' => 'Request handler not implemented.'
                ]
            ]
        ]
    );
    return;
}