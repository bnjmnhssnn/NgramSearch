<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public function listIndexes() : array;     
}