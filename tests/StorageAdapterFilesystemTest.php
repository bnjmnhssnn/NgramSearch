<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\StorageAdapter\Filesystem;
use NgramSearch\Exception\IndexNameInUseException;
use NgramSearch\Exception\IndexNotFoundException;
 
class StorageAdapterFilesystemTest extends TestCase {

    public static function setUpBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public static function tearDownAfterClass() : void 
    {
        cleandir(STORAGE_PATH);
    }

    public function testCreateIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $this->assertTrue(file_exists(STORAGE_PATH . '/MyIndex'));
        $this->assertTrue(file_exists(STORAGE_PATH . '/MyIndex/ngrams'));
        $this->assertTrue(file_exists(STORAGE_PATH . '/MyIndex/key_value_pairs.txt'));
    }

    /**
     * @depends testCreateIndex
     */
    public function testCreateDuplicateIndexThrowsException() : void
    {
        $this->expectException(IndexNameInUseException::class);
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
        $storage_adapter->createIndex('MyIndex');    
    }

    /**
     * @depends testCreateIndex
     */
    public function testIndexExists() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');
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
        $storage_adapter->dropIndex('DropMe');
        $this->assertFalse(file_exists(STORAGE_PATH . '/DropMe')); 
    }

    /**
     * @depends testDropIndex
     */
    public function testDropInexistingIndexThrowsException() : void
    {
        $this->expectException(IndexNotFoundException::class);
        $storage_adapter = get_storage_adapter();
        $storage_adapter->dropIndex('DoesNotExist');  
    }

    /**
     * @depends testCreateIndex
     */
    public function testAddToIndex() : void
    {
        $storage_adapter = get_storage_adapter();
        $storage_adapter->createIndex('MyIndex');

        $storage_adapter->addToIndex('MyIndex', 'abc;foo');
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

        $storage_adapter->addToIndex('MyIndex', 'bcd;bar');
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