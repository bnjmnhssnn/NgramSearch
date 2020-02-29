<?php
namespace NgramSearch\StorageAdapter;

interface StorageAdapterInterface
{
    public static function list_indexes() : array;     
}