<?php
function index_list(array $vars = []) : void {

    $storage = get_storage_adapter();

    set_header('Content-type: application/json');
    echo json_encode(
        [
            'available_indexes' => $storage->listIndexes()
        ]
    );
}