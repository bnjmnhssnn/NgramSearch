<?php
use NgramSearch\Preparer;
use NgramSearch\Ngrams;
use NgramSearch\Exception\NgramSearchException;
use NgramSearch\Exception\IndexNotFoundException;

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
    try {
        $storage->addToIndex($vars['index_name'], $payload->key . ';' . $payload->value);
    } catch (IndexNotFoundException $e)  {
        set_header("HTTP/1.1 400 Bad Request"); 
        set_header('Content-type: application/json');
        echo json_encode(['msg' => $e->getMessage()]);
        return;
    } catch (NgramSearchException $e)  {
        set_header("HTTP/1.1 500 Internal Server Error"); 
        set_header('Content-type: application/json');
        echo json_encode(['msg' => $e->getMessage()]);
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