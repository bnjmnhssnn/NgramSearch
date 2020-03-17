<?php
namespace NgramSearch\RequestHandler\FlushIndex;

use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNotFoundException;

function run(array $vars = [], \stdClass $payload) : void {

    $storage = get_storage_adapter();

    try {
        $storage->flushIndex($vars['index_name']);
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
    set_header("HTTP/1.1 200 OK");
    set_header('Location: ' . API_BASE_URL . '/indexes/' . $vars['index_name']);
    echo json_encode(
        [
            'data' => [
                'type' => 'index',
                'name' => $vars['index_name']
            ],
            'links' => [
                'self' => API_BASE_URL . '/indexes/' . $vars['index_name']
            ],
            'meta' => [
                'msg' => 'Index \'' . $vars['index_name'] . '\' successfully flushed, is now empty.'   
            ]
        ]
    ); 
    return; 
}