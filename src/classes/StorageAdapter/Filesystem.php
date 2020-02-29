<?php
namespace NgramSearch\StorageAdapter;

class Filesystem implements StorageAdapterInterface
{
    public static function list_indexes() : array
    {
        return array_filter(
            scandir(STORAGE_PATH),
            function($item) {
                return (is_dir(STORAGE_PATH . $item));
            }
        );
    }   
}