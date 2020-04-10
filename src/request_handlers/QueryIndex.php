<?php
namespace NgramSearch\RequestHandler\QueryIndex;

use NgramSearch\Preparer;
use NgramSearch\Ngrams;
use NgramSearch\NgramIndex;

function run(array $vars = []) : void 
{
    $min_hits = (!empty($_GET['min_hits'])) ? $_GET['min_hits'] : 2; 
    $max_results = (!empty($_GET['max_results'])) ? $_GET['max_results'] : 20;

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
    $time_start = microtime(true);
    $query_res = $index->query($vars['query_string'], $max_results, $min_hits);
    $time_end = microtime(true);
    $duration = $time_end - $time_start;
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/vnd.api+json');
    $response_array = 
    [
        'data' => prepare_result($query_res, $vars['query_string']),
        'meta' => [
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