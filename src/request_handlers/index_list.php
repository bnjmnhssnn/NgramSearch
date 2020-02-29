<?php
function index_list($vars = []) {

    $storage = get_storage_adapter();

    set_header('Content-type: application/json');
    echo json_encode(
        [
            'available_indexes' => $storage::listIndexes()
        ]
    );
}