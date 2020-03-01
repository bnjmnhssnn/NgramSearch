<?php
function add_to_index(array $vars, stdClass $payload) : void {

    if(!validate_request($payload)) {
        return;
    }
   
    
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