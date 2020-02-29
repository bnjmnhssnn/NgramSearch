<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
 
class StorageAdapterFilesystemTest extends TestCase {

    protected static $storage_adapter;

    public static function setUpBeforeClass() {
        self::$storage_adapter = new Filesystem(realpath(__DIR__ . '/generated/filesystem/') . '/');
    }

    public static function tearDownAfterClass() {
        cleandir(realpath(__DIR__ . '/generated/filesystem/'));
    }

    public function testListIndexesEmpty() {
        $this->assertSame([], self::$storage_adapter->listIndexes());    
    }

    /**
     * @depends testListIndexesEmpty
     */
    public function testCreateIndex() {
        $res = self::$storage_adapter->createIndex('MyIndex');
        $this->assertTrue($res); 
        $this->assertEmpty(self::$storage_adapter->lastError()); 
        $res2 = self::$storage_adapter->createIndex('MyIndex');
        $this->assertFalse($res2);
        $this->assertIsString(self::$storage_adapter->lastError()); 
    }

    /**
     * @depends testCreateIndex
     */
    public function testDropIndex() {
        self::$storage_adapter->createIndex('DropMe');
        $res = self::$storage_adapter->dropIndex('DropMe');
        $this->assertTrue($res); 
        $this->assertEmpty(self::$storage_adapter->lastError()); 
        $res2 = self::$storage_adapter->dropIndex('DoesNotExist');
        $this->assertFalse($res2);
        $this->assertIsString(self::$storage_adapter->lastError()); 
    }

    
}