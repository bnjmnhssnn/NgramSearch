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
    public function testIndexExists() : void
    {
        $storage_adapter = get_storage_adapter();
        $res = $storage_adapter->createIndex('MyIndex');
        $this->assertTrue($storage_adapter->indexExists('MyIndex')); 
        $this->assertFalse($storage_adapter->indexExists('DoesNotExist')); 
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

    /**
     * @depends testAddToIndex
     */
    public function testRemoveFromIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $storage_adapter->addToIndex('MyIndex', ['ab', 'xy'], 'foo');
        $storage_adapter->addToIndex('MyIndex', ['ab', 'xy'], 'remove_me');
        $storage_adapter->addToIndex('MyIndex', ['ab', 'xy'], 'baz');
        $res = $storage_adapter->removeFromIndex('MyIndex', 'remove_me');
        $this->assertTrue($res);
        $index_content = scandir(STORAGE_PATH . '/MyIndex');
        $ab_content = file(STORAGE_PATH . '/MyIndex/' . $index_content[2]);
        $xy_content = file(STORAGE_PATH . '/MyIndex/' . $index_content[2]);
        $this->assertSame(2, count($ab_content));
        $this->assertRegexp('/^foo\|\d+$/', $ab_content[0]);
        $this->assertRegexp('/^baz\|\d+$/', $ab_content[1]);
        $this->assertSame(2, count($xy_content));
        $this->assertRegexp('/^foo\|\d+$/', $xy_content[0]);
        $this->assertRegexp('/^baz\|\d+$/', $xy_content[1]);

        $storage_adapter->removeFromIndex('MyIndex', 'foo');
        $storage_adapter->removeFromIndex('MyIndex', 'baz');
        $index_content = scandir(STORAGE_PATH . '/MyIndex');
        $this->assertEquals(['.', '..'], $index_content);
    }

    /**
     * @depends testAddToIndex
     */
    public function testGetNgramData() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $storage_adapter->addToIndex('MyIndex', ['ab'], 'foo');
        $storage_adapter->addToIndex('MyIndex', ['ab'], 'bar');
        $storage_adapter->addToIndex('MyIndex', ['ab'], 'baz');
        $data = $storage_adapter->getNgramData('MyIndex', 'ab');
        $this->assertSame(3, count($data));
        $this->assertRegexp('/^foo\|\d+$/', $data[0]);
        $this->assertRegexp('/^bar\|\d+$/', $data[1]);
        $this->assertRegexp('/^baz\|\d+$/', $data[2]);
    }



    
}