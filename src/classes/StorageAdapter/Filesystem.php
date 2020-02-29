<?php
namespace NgramSearch\StorageAdapter;

class Filesystem implements StorageAdapterInterface
{
    protected $last_error;

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
                return (is_dir($this->storage_path . $item) && !in_array($item, ['.', '..']));
            }
        );
    }  
    
    public function createIndex(string $name) : bool
    {
        if (file_exists($this->storage_path . $name)) {
            $this->last_error = 'Index Name \'' . $name . '\' already in use.'; 
            return false;
        }
        if(!mkdir($this->storage_path . $name, 0777, true)) {
            $this->last_error = 'Error creating Index \'' . $name . '\'.'; 
            return false; 
        }
        return true;
    }
    
    public function dropIndex(string $name) : bool
    {
        if (!file_exists($this->storage_path . $name) || !is_dir($this->storage_path . $name)) {
            $this->last_error = 'Index \'' . $name . '\' not found.'; 
            return false;
        }
        if(!$this->rrmdir($this->storage_path . $name)) {
            $this->last_error = 'Error while removing Index \'' . $name . '\'.'; 
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