<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public function indexExists(string $name) : bool;

    public function listIndexes() : array;  
    
    public function createIndex(string $name) : bool;

    public function dropIndex(string $name) : bool;

    public function addToIndex(string $index_name, string $value_to_store) : bool;

    public function getNgramData(string $index_name, string $ngram); 

    public function getKeyValuePair(string $index_name, int $id); 

    public function lastError();
}