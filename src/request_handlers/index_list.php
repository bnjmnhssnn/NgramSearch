<?php
function index_list($vars = []) {
    set_header('Content-type: application/json');
    echo json_encode(
        [
            'basename' => basename(__FILE__)
        ]
    );
}