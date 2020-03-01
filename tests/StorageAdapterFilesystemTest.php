<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
 
class StorageAdapterFilesystemTest extends TestCase {

    protected static $storage_adapter;

    public static function setUpBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public static function tearDownAfterClass() : void 
    {
        cleandir(STORAGE_PATH);
    }

    public function testListIndexesEmpty()
    {
        $storage_adapter = get_storage_adapter();
        $this->assertSame([], $storage_adapter->listIndexes());    
    }

    /**
     * @depends testListIndexesEmpty
     */
    public function testCreateIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $res = $storage_adapter->createIndex('MyIndex');
        $this->assertTrue($res); 
        $this->assertEmpty($storage_adapter->lastError()); 
        $res2 = $storage_adapter->createIndex('MyIndex');
        $this->assertFalse($res2);
        $this->assertSame(Filesystem::ERROR_INDEX_NAME_INUSE, $storage_adapter->lastError()); 
    }

    /**
     * @depends testCreateIndex
     */
    public function testDropIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('DropMe');
        $res = $storage_adapter->dropIndex('DropMe');
        $this->assertTrue($res); 
        $this->assertEmpty($storage_adapter->lastError()); 
        $res2 = $storage_adapter->dropIndex('DoesNotExist');
        $this->assertFalse($res2);
        $this->assertSame(Filesystem::ERROR_INDEX_NOT_FOUND, $storage_adapter->lastError()); 
    }

    /**
     * @depends testCreateIndex
     */
    public function testAddToIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $res = $storage_adapter->addToIndex('MyIndex', ['ab', 'xy'], 'foo');
        $this->assertTrue($res);
        $this->assertEmpty($storage_adapter->lastError());
        $index_content = scandir(STORAGE_PATH . '/MyIndex');
        $this->assertEquals(['.', '..', 'ab', 'xy'], $index_content);
        foreach($index_content as $ngram_data_file) {
            if(!in_array($ngram_data_file, ['.', '..'])) {
                $this->assertRegexp('/^foo\|\d+$/', file(STORAGE_PATH . '/MyIndex/' . $ngram_data_file)[0]);
            }
        }
        $res2 = $storage_adapter->addToIndex('MyIndex', ['xy', 'ww'], 'bar');
        $this->assertTrue($res2);
        $this->assertEmpty($storage_adapter->lastError());
        $index_content = scandir(STORAGE_PATH . '/MyIndex');
        $this->assertEquals(['.', '..', 'ab', 'ww', 'xy'], $index_content);
        $this->assertRegexp('/^foo\|\d+$/', file(STORAGE_PATH . '/MyIndex/' . $index_content[4])[0]);
        $this->assertRegexp('/^bar\|\d+$/', file(STORAGE_PATH . '/MyIndex/' . $index_content[4])[1]);
    }

    
}