<?php
namespace NgramSearch\StorageAdapter;

class Filesystem implements StorageAdapterInterface
{
    public function __construct(string $storage_path)
    {
        if(!is_dir($storage_path)) {
            throw new \InvalidArgumentException($storage_path . ' is not a directory.');
        }
        $this->storage_path = $storage_path;
    }

    public function listIndexes() : array
    {
        return array_filter(
            scandir($this->storage_path),
            function($item) {
                return (is_dir($this->storage_path . $item));
            }
        );
    }   
}