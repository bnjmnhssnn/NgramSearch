<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
 
class StorageAdapterFilesystemTest extends TestCase {

    protected static $storage_adapter;

    public static function setUpBeforeClass() {
        self::$storage_adapter = new Filesystem(realpath(__DIR__ . '/generated/filesystem/'));
    }

    public static function tearDownAfterClass() {

    }

    public function testListIndexes() {
        $this->assertSame([], self::$storage_adapter->listIndexes());    
    }

    
}