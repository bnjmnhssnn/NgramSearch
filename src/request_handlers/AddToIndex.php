<?php
namespace NgramSearch\RequestHandler\AddToIndex;

use NgramSearch\Ngrams;
use NgramSearch\Preparer;
use NgramSearch\RequestValidator;
use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNotFoundException;

function run(array $vars, \stdClass $payload) : void {

    $validator = get_request_validator($payload);
    if(!empty($errors = $validator->execute())) {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(['errors' => $errors]); 
        return;
    }

    $storage = get_storage_adapter();

    try {
        $storage->addToIndex($vars['index_name'], $payload->key . ';' . $payload->value);
    } catch (IndexNotFoundException $e)  {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(['msg' => $e->getMessage()]);
        return;
    } catch (NgramSearchException $e)  {
        set_header("HTTP/1.1 500 Internal Server Error"); 
        set_header('Content-type: application/vnd.api+json');
        echo json_encode(['msg' => $e->getMessage()]);
        return;
    } 
    set_header("HTTP/1.1 200 OK");
    set_header('Content-type: application/vnd.api+json');
    echo json_encode(
        [
            'msg' => '\'' . $payload->value . '\' has been added to index \'' . $vars['index_name'] . '\'.'
        ]
    );
}

function get_request_validator(\stdClass $payload)
{
    return (new RequestValidator)
        ->addPostCheck(
            @$payload->key,
            function($value) {
                return !empty($value);
            },
            'Required parameter must not be empty.',
            '/key'
        )
        ->addPostCheck(
            @$payload->value,
            function($value) {
                return !empty($value);
            },
            'Required parameter must not be empty.',
            '/value'
        );
}