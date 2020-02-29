<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public static function listIndexes() : array;     
}