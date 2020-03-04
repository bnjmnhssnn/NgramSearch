<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;
use NgramSearch\NgramIndex;

function query_index(array $vars = []) : void {

    try {
        $index = new NgramIndex($vars['index_name'], get_storage_adapter()); 
    } catch (\InvalidArgumentException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'title' => 'Index \'' . $vars['index_name'] . '\' does not exist.',
                        'source' => [
                            'parameter' => '/{index_name}/query'
                        ]
                    ]
                ]
            ]
        ); 
        return;
    }
    try {
        $query_ngrams = Ngrams::extract(Preparer::get($vars['query_string'], false));
    } catch (\InvalidArgumentException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'title' =>  'Could nor extract ngrams from query string.',
                        'source' => [
                            'parameter' => '/' . $vars['index_name'] . '/query/{query_string}'
                        ]
                    ]
                ]
            ]
        ); 
        return;
    }
    $time_start = microtime(true);
    $query_res = $index->query($query_ngrams);
    $time_end = microtime(true);
    $duration = $time_end - $time_start;
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'data' => array_slice($query_res, 0, 50), 
            'meta' => [
                'result_length' => min(50, count($query_res)),
                'duration' => $duration
            ],
            'links' => [
                'self' => '/' . $vars['index_name'] . '/query/' . $vars['query_string'],
            ]
        ]
    );
    return;   
}