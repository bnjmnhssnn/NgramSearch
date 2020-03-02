<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;

function add_to_index(array $vars, stdClass $payload) : void {

    if(!validate_request($payload)) {
        return;
    }
    try {
        $key_ngrams = Ngrams::extract(Preparer::get($payload->key, false));
    } catch (\InvalidArgumentException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'Could nor extract ngrams from key.'
            ]
        ); 
        return;
    }
    $storage = get_storage_adapter();

    if(!$storage->addToIndex($vars['index_name'], $key_ngrams, $payload->value)) {
        switch($storage->lastError()) {
            case $storage::ERROR_INDEX_NOT_FOUND:
                $msg = 'Index \'' . $vars['index_name'] . '\' not found.';
                set_header("HTTP/1.1 400 Bad Request");  
                break;
            case $storage::ERROR_ADD_TO_INDEX:
            default:
                $msg = 'Unknown Error adding to Index \'' . $vars['index_name'] . '\'.';
                set_header("HTTP/1.1 500 Internal Server Error"); 
                break;
        }
        set_header('Content-type: application/json');
        echo json_encode(['msg' => $msg]);
    }
    set_header("HTTP/1.1 200 OK");
    set_header('Content-type: application/json');
    echo json_encode(
        [
            'msg' => 'The value \'' . $payload->value . '\' has been added to index \'' . $vars['index_name'] . '\'.',
            'ngrams_used_for_indexing' => $key_ngrams 
        ]
    );
}

function validate_request(stdClass $payload) : bool
{
    $validation_errors = [];
    if(empty($payload->key)) {
        $validation_errors[] = '$_POST[\'key\'] is a required parameter and must not be missing or empty.';  
    }
    if(empty($payload->value)) {
        $validation_errors[] = '$_POST[\'value\'] is a required parameter and must not be missing or empty.';
    }
    if (!empty($validation_errors)) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'The validation of the request has failed.',
                'validation_errors' => $validation_errors
            ]
        ); 
        return false; 
    }
    return true;
}