<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;

function add_to_index(array $vars, stdClass $payload) : void {

    if(empty($payload->key) || empty($payload->value)) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'The validation of the request has failed.',
                'validation_errors' => ['$_POST[\'key\'] and $_POST[\'value\'] are required parameters and must not be empty.']
            ]
        );
        return;
    }
    $storage = get_storage_adapter();

    if(!$storage->addToIndex($vars['index_name'], $payload->key . ';' . $payload->value)) {
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
        return;
    }
    set_header("HTTP/1.1 200 OK");
    set_header('Content-type: application/json');
    echo json_encode(
        [
            'msg' => '\'' . $payload->value . '\' has been added to index \'' . $vars['index_name'] . '\'.'
        ]
    );
}