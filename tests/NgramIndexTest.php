<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\NgramIndex;

class NgramIndexTest extends TestCase {

    public static function setUpBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public static function tearDownAfterClass() : void 
    {
        cleandir(STORAGE_PATH);
    }

    public function testMissingIndexThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $storage_adapter = get_storage_adapter();
        new NgramIndex('DoesNotExist', get_storage_adapter());   
    }

    public function testInstanciation()
    {
        generateTestData('MyIndex', [], []);
        $storage_adapter = get_storage_adapter();
        try {
            new NgramIndex('MyIndex', get_storage_adapter()); 
        } catch (\Exception $e) {
            $this->fail();
        }
        $this->assertTrue(true);  
    }

    /**
     * @depends testInstanciation
     */
    public function testQuery()
    {
        $test_ngram_data = [
            ' a' => [1, 2, 3],
            'ab' => [1, 2, 3],
            'bc' => [1, 2],
            'cd' => [1],
            'b ' => [3],
            'c ' => [2],
            'd ' => [1]
        ];
        $test_key_value_pairs = [
            'abcd;foo',
            'abc;bar',
            'ab;baz',
        ];
        generateTestData('MyIndex', $test_ngram_data, $test_key_value_pairs);
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query('abcd');
        $this->assertSame(
            [
                [
                    'id' => 1,
                    'key' => 'abcd',
                    'value' => 'foo',
                    'ngrams_hit' => 5
                ],
                [
                    'id' => 2,
                    'key' => 'abc',
                    'value' => 'bar',
                    'ngrams_hit' => 3
                ],
                [
                    'id' => 3,
                    'key' => 'ab',
                    'value' => 'baz',
                    'ngrams_hit' => 2
                ],
            ],
            $res
        ); 
   
    }
}
