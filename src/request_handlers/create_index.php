<?php
function create_index(array $vars = [], stdClass $payload) : void {

    $index_name_max_strlen = 12;
    $storage = get_storage_adapter();

    if(empty($payload->index_name)) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => '$_POST[\'index_name\'] is a required parameter and must not be missing or empty.'
            ]
        ); 
        return;     
    }
    if(preg_match( '/[^a-zA-Z0-9_-]+/', $payload->index_name)) {
        set_header("HTTP/1.1 400 Bad Request");
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'The index name contains invalid chars.'
            ]
        ); 
        return; 
    }
    if(strlen($payload->index_name) > $index_name_max_strlen) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(
            [
                'msg' => 'The index name exeeds the allowed maximum of ' . $index_name_max_strlen . ' chars.'
            ]
        ); 
        return;  
    }
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