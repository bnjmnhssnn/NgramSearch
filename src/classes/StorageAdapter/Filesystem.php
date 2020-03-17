<?php
namespace NgramSearch\StorageAdapter;

use NgramSearch\Ngrams;
use NgramSearch\Preparer;
use NgramSearch\Exception\DropIndexException;
use NgramSearch\Exception\AddToIndexException;
use NgramSearch\Exception\CreateIndexException;
use NgramSearch\Exception\IndexNotFoundException;
use NgramSearch\Exception\IndexNameInUseException;

class Filesystem implements StorageAdapterInterface
{
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
                        'name' => $item,
                        'size' => $this->getIndexSize($item) 
                    ];
                }
            },
            scandir($this->storage_path)
        )));
    }  
    
    public function createIndex(string $name) : void
    {
        if (file_exists($this->storage_path . '/' . $name)) {
            throw new IndexNameInUseException('Index name \'' . $name . '\' already in use.'); 
        }
        if(!mkdir($this->storage_path . '/' . $name, 0777, true)) {
            throw new CreateIndexException('Could not create index dir.');  
        }
        if(!mkdir($this->storage_path . '/' . $name . '/ngrams')) {
            throw new CreateIndexException('Could not create index subdir \'ngrams\'.'); 
        }
        if(!touch($this->storage_path . '/' . $name . '/key_value_pairs.txt')) {
            throw new CreateIndexException('Could not create file \'key_value_pairs.txt\'.');  
        }
    }
    
    public function dropIndex(string $name) : void
    {
        if (!file_exists($this->storage_path . '/' . $name)) {
            throw new IndexNotFoundException('Index \'' . $name . '\' not found.'); 
        }
        if(!$this->rrmdir($this->storage_path . '/' . $name)) {
            throw new DropIndexException('Index \'' . $name . '\' could not be removed.');     
        }
    }

    public function addToIndex(string $index_name, string $key_value_pair) : void
    {
        $parts = explode(';', $key_value_pair);
        if(count($parts) !== 2) {
            throw new \InvalidArgumentException('Argument 2 \'$key_value_pair\' could not be splitted.');  
        }
        $ngrams = Ngrams::extract(Preparer::get($parts[0], false));
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            throw new IndexNotFoundException('Index \'' . $name . '\' not found.'); 
        }
        if(!file_put_contents($index_path . '/key_value_pairs.txt', $key_value_pair . "\n", FILE_APPEND | LOCK_EX)) {
            throw new AddToIndexException('Could not write to file \'key_value_pairs.txt\'.'); 
        }
        $values_file = new \SplFileObject($index_path . '/key_value_pairs.txt', 'r');
        $values_file->seek(PHP_INT_MAX);
        $new_value_id = $values_file->key();
        foreach ($ngrams as $ngram) {
            if(!file_put_contents($index_path . '/ngrams/' . $ngram, $new_value_id . "\n", FILE_APPEND | LOCK_EX)) {
                throw new AddToIndexException('Could not write to ngram data file \'' . $ngram . '\'.'); 
            }
        }
    }

    public function getNgramData(string $index_name, string $ngram) : array
    {
        $index_path = $this->storage_path . '/' . $index_name;
        if (!file_exists($index_path) || !is_dir($index_path)) {
            throw new IndexNotFoundException('Index \'' . $name . '\' not found.');  
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

    public function getKeyValuePair(string $index_name, int $id) : array
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

    public function getIndexSize(string $index_name) : int
    {
        $values_file = new \SplFileObject($this->storage_path . '/' . $index_name . '/key_value_pairs.txt', 'r');
        $values_file->seek(PHP_INT_MAX);
        return $values_file->key();
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