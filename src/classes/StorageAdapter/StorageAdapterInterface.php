<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public function listIndexes() : array;  
    
    public function createIndex(string $name) : bool;

    public function dropIndex(string $name) : bool;

    public function lastError();
}