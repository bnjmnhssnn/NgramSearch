<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;
use NgramSearch\NgramIndex;

function query_index(array $vars = []) : void {

    try {
        $index = new NgramIndex($vars['index_name'], get_storage_adapter()); 
    } catch (\InvalidArgumentException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'Index \'' . $vars['index_name'] . '\' does not exist.'
            ]
        ); 
        return;
    }
    try {
        $query_ngrams = Ngrams::extract(Preparer::get($_GET['query_string'], false));
    } catch (\InvalidArgumentException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'Could nor extract ngrams from query string.'
            ]
        ); 
        return;
    }
    $time_start = microtime(true);
    $query_res = $index->query($query_ngrams);
    $time_end = microtime(true);
    $duration = $time_end - $time_start;
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/json');
    echo json_encode(
        [
            'stats' => [
                'result_length' => count($query_res),
                'duration' => $duration
            ],
            'query_result' => $query_res
        ]
    ); 
    return;   
}