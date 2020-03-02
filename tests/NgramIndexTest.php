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
        generateTestData('MyIndex', []);
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
        $test_data = [
            'ab' => [
                'baz|1461094800'
            ],
            'bc' => [
                'bar|1461094800',
                'baz|1461094800'
            ],
            'cd' => [
                'foo|1461094800',
                'bar|1461094800',
                'baz|1461094800'
            ]
        ];
        generateTestData('MyIndex', $test_data);   
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query(['ab', 'bc', 'cd']);
        $this->assertSame(
            [
                [
                    'value' => 'baz',
                    'ngrams_hit' => 3,
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'bar',
                    'ngrams_hit' => 2,
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'foo',
                    'ngrams_hit' => 1,
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }
}
