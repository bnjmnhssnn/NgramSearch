<?php
namespace NgramSearch\StorageAdapter;

class Filesystem implements StorageAdapterInterface
{
    protected $last_error;

    const ERROR_INDEX_NAME_INUSE = 0;
    const ERROR_CREATE_INDEX = 1;
    const ERROR_INDEX_NOT_FOUND = 2;
    const ERROR_DROP_INDEX = 3;

    public function __construct(string $storage_path)
    {
        if(!is_dir($storage_path)) {
            throw new \InvalidArgumentException($storage_path . ' is not a directory.');
        }
        $this->storage_path = rtrim($storage_path, '/');
    }

    public function listIndexes() : array
    {
        return array_filter(
            scandir($this->storage_path),
            function($item) {
                return (is_dir($this->storage_path . '/' . $item) && !in_array($item, ['.', '..']));
            }
        );
    }  
    
    public function createIndex(string $name) : bool
    {
        if (file_exists($this->storage_path . '/' . $name)) {
            #$this->last_error = 'Index Name \'' . $name . '\' already in use.'; 
            $this->last_error = self::ERROR_INDEX_NAME_INUSE; 
            return false;
        }
        if(!mkdir($this->storage_path . '/' . $name, 0777, true)) {
            #$this->last_error = 'Error creating Index \'' . $name . '\'.';
            $this->last_error = self::ERROR_CREATE_INDEX; 
            return false; 
        }
        return true;
    }
    
    public function dropIndex(string $name) : bool
    {
        if (!file_exists($this->storage_path . '/' . $name) || !is_dir($this->storage_path . '/' . $name)) {
            #$this->last_error = 'Index \'' . $name . '\' not found.'; 
            $this->last_error = self::ERROR_INDEX_NOT_FOUND; 
            return false;
        }
        if(!$this->rrmdir($this->storage_path . '/' . $name)) {
            #$this->last_error = 'Error while removing Index \'' . $name . '\'.'; 
            $this->last_error = self::ERROR_DROP_INDEX; 
            return false;      
        }
        return true;
    }

    public function lastError()
    {
        return $this->last_error;
    }

    protected function rrmdir($dir) { 
        if (!is_dir($dir)) {
            return false;
        }
        array_map(
            function($item) use ($dir) {
                if (in_array($item, ['.', '..'])) {
                    return;
                }
                if(!$this->rrmdir($dir . '/' . $item)) {
                    unlink($dir . '/' . $item);
                }
            },
            scandir($dir)
        );
        return rmdir($dir); 
    }
}