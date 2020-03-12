<?php
namespace NgramSearch\StorageAdapter;

use NgramSearch\Ngrams;

class Filesystem implements StorageAdapterInterface
{
    protected $last_error;

    const ERROR_INDEX_NAME_INUSE = 0;
    const ERROR_CREATE_INDEX = 1;
    const ERROR_INDEX_NOT_FOUND = 2;
    const ERROR_DROP_INDEX = 3;
    const ERROR_ADD_TO_INDEX = 4;
    const ERROR_INVALID_KEY_VALUE_PAIR = 5;
    const ERROR_VALUE_TO_REMOVE_NOT_FOUND_ON_INDEX = 6;

    public function __construct(string $storage_path)
    {
        if(!is_dir($storage_path)) {
            throw new \InvalidArgumentException($storage_path . ' is not a directory.');
        }
        $this->storage_path = rtrim($storage_path, '/');
    }

    public function indexExists(string $name) : bool
    {
        return (is_dir($this->storage_path . '/' . $name));
    } 

    public function listIndexes() : array
    {
        return array_values(array_filter(array_map(
            function($item) {
                if(is_dir($this->storage_path . '/' . $item) && !in_array($item, ['.', '..'])) {
                    $ngrams = scandir($this->storage_path . '/' . $item);   
                    return [
                        'index_name' => $item,
                        'ngrams' => array_values(array_filter(
                            $ngrams,
                            function($item) {
                                return !in_array($item, ['.', '..']);
                            }
                        ))  
                    ];
                }
            },
            scandir($this->storage_path)
        )));
    }  
    
    public function createIndex(string $name) : bool
    {
        if (file_exists($this->storage_path . '/' . $name)) {
            $this->last_error = self::ERROR_INDEX_NAME_INUSE; 
            return false;
        }
        if(!mkdir($this->storage_path . '/' . $name, 0777, true)) {
            $this->last_error = self::ERROR_CREATE_INDEX; 
            return false; 
        }
        if(!mkdir($this->storage_path . '/' . $name . '/ngrams')) {
            $this->last_error = self::ERROR_CREATE_INDEX; 
            return false; 
        }
        if(!touch($this->storage_path . '/' . $name . '/key_value_pairs.txt')) {
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
            $this->last_error = self::ERROR_DROP_INDEX; 
            return false;      
        }
        return true;
    }


    public function addToIndex(string $index_name, string $key_value_pair) : bool
    {
        $parts = explode(';', $key_value_pair);
        if(count($parts) !== 2) {
            $this->last_error = self::ERROR_INVALID_KEY_VALUE_PAIR; 
            return false;   
        }
        $ngrams = Ngrams::extract($parts[0]);
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            $this->last_error = self::ERROR_INDEX_NOT_FOUND; 
            return false;
        }
        if(!file_put_contents($index_path . '/key_value_pairs.txt', $key_value_pair . "\n", FILE_APPEND | LOCK_EX)) {
            $this->last_error = self::ERROR_ADD_TO_INDEX; 
            return false;
        }
        $values_file = new \SplFileObject($index_path . '/key_value_pairs.txt', 'r');
        $values_file->seek(PHP_INT_MAX);
        $new_value_id = $values_file->key();
        foreach ($ngrams as $ngram) {
            if(!file_put_contents($index_path . '/ngrams/' . $ngram, $new_value_id . "\n", FILE_APPEND | LOCK_EX)) {
                $this->last_error = self::ERROR_ADD_TO_INDEX; 
                return false;
            }
        }
        return true;
    }

    public function getNgramData(string $index_name, string $ngram)
    {
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            $this->last_error = self::ERROR_INDEX_NOT_FOUND; 
            return false;
        }
        $ngram_data_path = $index_path . '/ngrams/' . $ngram;
        if (!file_exists($ngram_data_path)) {
            return [];
        }
        return array_map(
            function($item) {
                return intval(rtrim($item, "\n"));
            },
            file($ngram_data_path)
        );
    } 

    public function getKeyValuePair(string $index_name, int $id)
    {
        $values_file = new \SplFileObject($this->storage_path . '/' . $index_name . '/key_value_pairs.txt', 'r');
        $values_file->seek($id - 1);
        return array_map(
            function ($item) {
                return rtrim($item, "\n");
            },
            preg_split('/;|\|/', $values_file->current())
        );
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

    /*
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
            if($file === '.' || $file === '..' || $file === '_values') {
                continue;
            }
            $filepath = $index_path . '/' . $file;+-
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
    */
}