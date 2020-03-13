<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public function indexExists(string $name) : bool;

    public function listIndexes() : array;  
    
    public function createIndex(string $name) : void;

    public function dropIndex(string $name) : void;

    public function addToIndex(string $index_name, string $value_to_store) : void;

    public function getNgramData(string $index_name, string $ngram) : array; 

    public function getKeyValuePair(string $index_name, int $id) : array; 
}