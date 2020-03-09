<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;
use NgramSearch\NgramIndex;

function query_index(array $vars = []) : void 
{
    $min_hits = 2;

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
    $query_res = $index->query($query_ngrams, 20, 2);
    $time_end = microtime(true);
    $duration = $time_end - $time_start;
    set_header("HTTP/1.1 200 OK"); 
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'data' => prepare_result($query_res, $query_ngrams, $min_hits), 
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

function prepare_result(array $query_res, array $query_ngrams, int $min_hits = NULL) : array
{
    $return_arr = [];
    foreach($query_res as $res_item) {

        $ngrams_hit = ngram_details($query_ngrams, $res_item['value']);

        if($min_hits !== NULL && count($ngrams_hit) < $min_hits) {
            continue;
        }
        $return_arr[] = [
            'value' => $res_item['value'],
            'ngrams_hit' => $ngrams_hit,
            'indexed_at' => $res_item['indexed_at']
        ];
    }
    return $return_arr;   
}

function ngram_details(array $search_ngrams, string $raw_value) : array
{
    $value_ngrams = Ngrams::extract(Preparer::get($raw_value, false), false);
    return array_map(
        function ($item) use ($value_ngrams) {
            return [
                'value' => $item,
                'pos' => join(',', array_keys($value_ngrams, $item))
            ];
        },    
        array_values(array_intersect($search_ngrams, $value_ngrams))
    );
}