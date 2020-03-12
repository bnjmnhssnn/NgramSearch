<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
 
class StorageAdapterFilesystemTest extends TestCase {

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

        $res = $storage_adapter->addToIndex('MyIndex', 'abc;foo');
        $this->assertTrue($res);
        $this->assertEmpty($storage_adapter->lastError());
        $index_content = scandir(STORAGE_PATH . '/MyIndex/ngrams');
        $expected_content = ['.', '..', ' a', 'ab', 'bc', 'c '];
        sort($index_content);
        sort($expected_content);
        $this->assertSame($expected_content, $index_content);

        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/ a')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/ab')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/bc')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/c ')[0]);
        $this->assertSame("abc;foo\n", file(STORAGE_PATH . '/MyIndex/key_value_pairs.txt')[0]);

        $res2 = $storage_adapter->addToIndex('MyIndex', 'bcd;bar');
        $this->assertTrue($res2);
        $this->assertEmpty($storage_adapter->lastError());
        $index_content = scandir(STORAGE_PATH . '/MyIndex/ngrams');
        $expected_content = ['.', '..', ' a', ' b', 'ab', 'bc', 'cd', 'c ', 'd '];
        sort($index_content);
        sort($expected_content);
        $this->assertSame($expected_content, $index_content);

        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/ a')[0]);
        $this->assertSame("2\n", file(STORAGE_PATH . '/MyIndex/ngrams/ b')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/ab')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/bc')[0]);
        $this->assertSame("2\n", file(STORAGE_PATH . '/MyIndex/ngrams/bc')[1]);
        $this->assertSame("2\n", file(STORAGE_PATH . '/MyIndex/ngrams/cd')[0]);
        $this->assertSame("1\n", file(STORAGE_PATH . '/MyIndex/ngrams/c ')[0]); 
        $this->assertSame("2\n", file(STORAGE_PATH . '/MyIndex/ngrams/d ')[0]);
        $this->assertSame("bcd;bar\n", file(STORAGE_PATH . '/MyIndex/key_value_pairs.txt')[1]);
    }

    /**
     * @depends testAddToIndex
     */
    public function testGetNgramData() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $storage_adapter->addToIndex('MyIndex', 'ab;foo');
        $storage_adapter->addToIndex('MyIndex', 'ab;bar');
        $storage_adapter->addToIndex('MyIndex', 'ab;baz');
        $data = $storage_adapter->getNgramData('MyIndex', 'ab');
        $this->assertSame(3, count($data));
        $this->assertSame(1, $data[0]);
        $this->assertSame(2, $data[1]);
        $this->assertSame(3, $data[2]);
    }

    /**
     * @depends testAddToIndex
     */
    public function testGetKeyValuePair() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $storage_adapter->addToIndex('MyIndex', 'ab;foo');
        $this->assertSame(
            ['ab', 'foo'],
            $storage_adapter->getKeyValuePair('MyIndex', 1)
        );
    } 
}