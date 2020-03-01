<?php
namespace NgramSearch\StorageAdapter;

class Filesystem implements StorageAdapterInterface
{
    protected $last_error;

    const ERROR_INDEX_NAME_INUSE = 0;
    const ERROR_CREATE_INDEX = 1;
    const ERROR_INDEX_NOT_FOUND = 2;
    const ERROR_DROP_INDEX = 3;
    const ERROR_ADD_TO_INDEX = 4;
    const ERROR_VALUE_TO_REMOVE_NOT_FOUND_ON_INDEX = 5;

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

    public function addToIndex(string $index_name, array $ngrams, string $value_to_store) : bool
    {
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            $this->last_error = self::ERROR_INDEX_NOT_FOUND; 
            return false;
        }
        $this->removeFromIndex($index_name, $value_to_store); // Remove existing entries with same value
        $this->last_error = NULL; // Remove the error in case the value wasn't stored before
        foreach ($ngrams as $ngram) {
            if(!file_put_contents($index_path . '/' . $ngram, $value_to_store . '|' . time() . "\n", FILE_APPEND | LOCK_EX)) {
                $this->removeFromIndex($index_name, $value_to_store); // Remove existing entries with same value
                $this->last_error = self::ERROR_ADD_TO_INDEX; 
                return false;
            }
        }
        return true;
    }

    public function removeFromIndex(string $index_name, string $value_to_remove) : bool
    {   
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            $this->last_error = self::ERROR_INDEX_NOT_FOUND; 
            return false;
        }
        $dh = opendir($index_path);
        $found = false;
        while (($file = readdir($dh)) !== false) {
            if($file === '.' || $file === '..') {
                continue;
            }
            $filepath = $index_path . '/' . $file;
            $read = fopen($filepath, 'r');
            $write = fopen($filepath . '.tmp', 'w');
            while (!feof($read)) {
                $line = fgets($read);
                if (explode('|', $line)[0] === $value_to_remove) {
                    $found = true;
                } else {
                    fputs($write, $line);
                }
            }
            fclose($read); 
            fclose($write);
            unlink($filepath);
            if(filesize($filepath . '.tmp') > 0) {
                rename($filepath . '.tmp', $filepath);
            } else {
                unlink($filepath . '.tmp');
            }
        }
        closedir($dh);
        if(!$found) {
            $this->last_error = self::ERROR_VALUE_TO_REMOVE_NOT_FOUND_ON_INDEX;
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