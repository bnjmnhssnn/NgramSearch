<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
 
class StorageAdapterFilesystemTest extends TestCase {

    protected static $storage_adapter;

    public static function setUpBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
        self::$storage_adapter = get_storage_adapter();
    }

    public static function tearDownAfterClass() : void 
    {
        cleandir(STORAGE_PATH);
    }

    public function testListIndexesEmpty()
    {
        $this->assertSame([], self::$storage_adapter->listIndexes());    
    }

    /**
     * @depends testListIndexesEmpty
     */
    public function testCreateIndex() : void
    {
        $res = self::$storage_adapter->createIndex('MyIndex');
        $this->assertTrue($res); 
        $this->assertEmpty(self::$storage_adapter->lastError()); 
        $res2 = self::$storage_adapter->createIndex('MyIndex');
        $this->assertFalse($res2);
        $this->assertSame(Filesystem::ERROR_INDEX_NAME_INUSE, self::$storage_adapter->lastError()); 
    }

    /**
     * @depends testCreateIndex
     */
    public function testDropIndex() : void
    {
        self::$storage_adapter->createIndex('DropMe');
        $res = self::$storage_adapter->dropIndex('DropMe');
        $this->assertTrue($res); 
        $this->assertEmpty(self::$storage_adapter->lastError()); 
        $res2 = self::$storage_adapter->dropIndex('DoesNotExist');
        $this->assertFalse($res2);
        $this->assertSame(Filesystem::ERROR_INDEX_NOT_FOUND, self::$storage_adapter->lastError()); 
    }

    
}