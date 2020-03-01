<?php
function create_index(array $vars = [], stdClass $payload) : void {

    if(!validate_request($payload)) {
        return;
    }
    $storage = get_storage_adapter();

    if(!$storage->createIndex($payload->index_name)) {
        switch($storage->lastError()) {
            case $storage::ERROR_INDEX_NAME_INUSE:
                $msg = 'Index Name \'' . $payload->index_name . '\' already in use.';
                set_header("HTTP/1.1 400 Bad Request");  
                break;
            case $storage::ERROR_CREATE_INDEX:
            default:
                $msg = 'Unknown Error creating Index \'' . $payload->index_name . '\'.';
                set_header("HTTP/1.1 500 Internal Server Error"); 
                break;
        }
        set_header('Content-type: application/json');
        echo json_encode(['msg' => $msg]);
    }
    set_header("HTTP/1.1 201 Created"); 
    set_header('Content-type: application/json');
    echo json_encode(
        [
            'msg' => 'Index \'' . $payload->index_name . '\' successfully_created.'
        ]
    ); 
    return; 
    
}

function validate_request(stdClass $payload) : bool
{
    $index_name_max_strlen = 12;
    $validation_errors = [];

    if(empty($payload->index_name)) {
        $validation_errors[] = '$_POST[\'index_name\'] is a required parameter and must not be missing or empty.';  
    } else {
        if(preg_match( '/[^a-zA-Z0-9_-]+/', $payload->index_name)) {
            $validation_errors[] = 'The index name contains invalid chars.';
        }
        if(strlen($payload->index_name) > $index_name_max_strlen) {
            $validation_errors[] = 'The index name exeeds the allowed maximum of ' . $index_name_max_strlen . ' chars.';
        }
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