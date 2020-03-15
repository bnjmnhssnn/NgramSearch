<?php
namespace NgramSearch\RequestHandler\CreateIndex;

use NgramSearch\RequestValidator;
use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNameInUseException;

function run(array $vars = [], \stdClass $payload) : void {

    $validator = get_request_validator($payload);
    if(!empty($errors = $validator->execute())) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(['errors' => $errors]); 
        return;
    }

    $storage = get_storage_adapter();

    try {
        $storage->createIndex($payload->index_name);
    } catch (IndexNameInUseException $e) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'status' => 400,
                        'title' => 'Invalid Request Body',
                        'detail' => $e->getMessage(),
                        'source' => [
                            'pointer' => '/index_name'
                        ]
                    ]
                ]
            ]
        ); 
        return;
    } catch (NgramSearchException $e)  {
        set_header("HTTP/1.1 500 Internal Server Error"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(
            [
                'errors' => [
                    [
                        'status' => 500,
                        'title' => 'Internal Server Error',
                        'detail' => $e->getMessage()
                    ]
                ]
            ]
        );
        return;
    } 
    set_header("HTTP/1.1 201 Created"); 
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'msg' => 'Index \'' . $payload->index_name . '\' successfully_created.'
        ]
    ); 
    return; 
    
}

function get_request_validator(\stdClass $payload)
{
    $index_name_max_strlen = 12;

    return (new RequestValidator)
        ->addPostCheck(
            @$payload->index_name,
            function($value) {
                return !empty($value);
            },
            'Required parameter must not be empty.',
            '/index_name'
        )
        ->addPostCheck(
            @$payload->index_name,
            function($value) {
                return !preg_match( '/[^a-zA-Z0-9_-]+/', $value);
            },
            'Parameter contains invalid chars.',
            '/index_name'
        )
        ->addPostCheck(
            @$payload->index_name,
            function($value) use ($index_name_max_strlen) {
                return !(strlen($value) > $index_name_max_strlen);
            },
            'Parameter exeeds the allowed maximum length.',
            '/index_name'
        );
}