<?php
function index_list(array $vars = []) : void {

    $storage = get_storage_adapter();

    $indexes = array_map(
        function($item) {
            $item['ngram_count'] = count($item['ngrams']);
            unset($item['ngrams']);
            $item['links'] = [
                'self' => '/' . $item['index_name']
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
                'self' => '/',
            ]
        ]
    );
    
}