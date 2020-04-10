<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public function indexExists(string $name) : bool;

    public function listIndexes() : array;  
    
    public function createIndex(string $name) : void;

    public function dropIndex(string $name) : void;

    public function addToIndex(string $index_name, string $value_to_store) : void;

    public function queryIndex(string $index_name, array $ngrams, int $max_count, int $min_hits) : array;
}