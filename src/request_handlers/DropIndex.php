<?php
namespace NgramSearch\RequestHandler\DropIndex;

use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNotFoundException;

function run(array $vars = [], \stdClass $payload) : void {

    $storage = get_storage_adapter();

    try {
        $storage->dropIndex($vars['index_name']);
    } catch (IndexNotFoundException $e)  {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'status' => 400,
                        'title' => 'Invalid Request Body',
                        'detail' => $e->getMessage(),
                        'source' => [
                            'pointer' => '/index_name'
                        ]
                    ]
                ]
            ]
        );
        return;
    } catch (NgramSearchException $e)  {
        set_header("HTTP/1.1 500 Internal Server Error"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'status' => 500,
                        'title' => 'Internal Server Error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ]
        );
        return;
    } 
    set_header("HTTP/1.1 204 No Content");
}