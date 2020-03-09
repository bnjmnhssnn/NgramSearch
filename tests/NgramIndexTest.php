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
            ' a' => [
                'abcd|1461094800',
                'abc|1461094800',
                'ab|1461094800',
            ],
            'ab' => [
                'abcd|1461094800',
                'abc|1461094800',
                'ab|1461094800',
            ],
            'bc' => [
                'abcd|1461094800',
                'abc|1461094800',
            ],
            'cd' => [
                'abcd|1461094800',
            ],
            'b ' => [
                'ab|1461094800',
            ],
            'c ' => [
                'abc|1461094800',
            ],
            'd ' => [
                'abcd|1461094800',
            ],
        ];
        generateTestData('MyIndex', $test_data);   
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query([' a', 'ab', 'bc', 'cd', 'd ' ]);
        $this->assertSame(
            [
                [
                    'value' => 'abcd',
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'abc',
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'ab',
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }
}
