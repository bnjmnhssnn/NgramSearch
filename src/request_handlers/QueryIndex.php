<?php
namespace NgramSearch\RequestHandler\QueryIndex;

use NgramSearch\Preparer;
use NgramSearch\Ngrams;

function run(array $vars = []) : void 
{
    $time_start = microtime(true);
    $min_hits = (!empty($_GET['min_hits'])) ? $_GET['min_hits'] : 2; 
    $max_results = (!empty($_GET['max_results'])) ? $_GET['max_results'] : 20;

    $storage_adapter = get_storage_adapter();
    if(!$storage_adapter->indexExists($vars['index_name'])) {
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
    $ngrams = Ngrams::extract(Preparer::get($vars['query_string'], false));
    $query_res = $storage_adapter->queryIndex(
        $vars['index_name'], 
        $ngrams, 
        $max_results, 
        $min_hits
    );
    $time_end = microtime(true);
    $duration = $time_end - $time_start;
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/vnd.api+json');
    $response_array = 
    [
        'data' => prepare_result($query_res, $vars['query_string']),
        'meta' => [
            'search_ngrams' => $ngrams,
            'result_length' => min(50, count($query_res)),
            'duration' => $duration,
            'peak_memory' => (memory_get_peak_usage(false)/1000/1000) . 'MB'
        ],
        'links' => [
            'self' => '/' . $vars['index_name'] . '/query/' . $vars['query_string'],
        ]
    ];
    echo json_encode($response_array);
    return;   
}

function prepare_result(array $query_res, string $query_string) : array
{
    $search_ngrams = Ngrams::extract(Preparer::get($query_string, false), false);  
    return array_map(
        function($item) use ($search_ngrams) { 
            $key_ngrams = Ngrams::extract(Preparer::get($item['key'], false), false); 
            $item['ngram_details'] = array_map(
                function ($item) use ($search_ngrams, $key_ngrams) {
                    return [
                        'value' => $item,
                        'pos_in_key' => join(',', array_keys($key_ngrams, $item)),
                        'pos_in_search' => join(',', array_keys($search_ngrams, $item))
                    ];
                },    
                array_values(array_unique(array_intersect($key_ngrams, $search_ngrams)))
            );
            return $item;
        },
        $query_res
    ); 
}