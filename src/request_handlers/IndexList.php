<?php
namespace NgramSearch\RequestHandler\IndexList;

function run(array $vars = []) : void {

    $storage = get_storage_adapter();

    $indexes = array_map(
        function($item) {
            $item['links'] = [
                'self' => API_BASE_URL . '/' . $item['name']
            ];
            return $item;
        },   
        $storage->listIndexes()
    );
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'data' => $indexes, 
            'links' => [
                'self' => API_BASE_URL,
            ]
        ]
    );
    
}