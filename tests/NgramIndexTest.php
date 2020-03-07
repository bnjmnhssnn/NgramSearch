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
                    'ngrams_hit' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'bc', 'pos' => '2'],
                        ['value' => 'cd', 'pos' => '3'],
                        ['value' => 'd ', 'pos' => '4']
                    ],
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'abc',
                    'ngrams_hit' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'bc', 'pos' => '2']
                    ],
                    'indexed_at' => '1461094800'
                ],
                [
                    'value' => 'ab',
                    'ngrams_hit' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                    ],
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }

    /**
     * @depends testQuery
     */
    public function testInterruptedMatch()
    {
        $test_data = [
            ' a' => [
                'abcde|1461094800',
            ],
            'ab' => [
                'abcde|1461094800',
            ],
            'bc' => [
                'abcde|1461094800',
            ],
            'cd' => [
                'abcde|1461094800',
            ],
            'de' => [
                'abcde|1461094800',
            ],
            'e ' => [
                'abcde|1461094800',
            ],
        ];
        generateTestData('MyIndex', $test_data);   
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query([' a', 'ab', 'bx', 'xd', 'de', 'e ']);
        $this->assertSame(
            [
                [
                    'value' => 'abcde',
                    'ngrams_hit' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'de', 'pos' => '4'],
                        ['value' => 'e ', 'pos' => '5'],    
                    ],
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }

    /**
     * @depends testQuery
     */
    public function testInterruptedMatch2()
    {
        $test_data = [
            ' a' => [
                'abde|1461094800',
            ],
            'ab' => [
                'abde|1461094800',
            ],
            'bd' => [
                'abde|1461094800',
            ],
            'de' => [
                'abde|1461094800',
            ],
            'e ' => [
                'abde|1461094800',
            ],
        ];
        generateTestData('MyIndex', $test_data);   
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query([' a', 'ab', 'bc', 'cd', 'de', 'e ']);
        $this->assertSame(
            [
                [
                    'value' => 'abde',
                    'ngrams_hit' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'de', 'pos' => '3'],
                        ['value' => 'e ', 'pos' => '4'],    
                    ],
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }

    /**
     * @depends testQuery
     */
    public function testDoubleNgramsMatch()
    {
        $test_data = [
            ' b' => [
                'banana|1461094800',
            ],
            'ba' => [
                'banana|1461094800',
            ],
            'an' => [
                'banana|1461094800',
            ],
            'na' => [
                'banana|1461094800',
            ],
            'a ' => [
                'banana|1461094800',
            ]
        ];

        generateTestData('MyIndex', $test_data);   
        $index = new NgramIndex('MyIndex', get_storage_adapter()); 
        $res = $index->query([' b', 'ba', 'an', 'na', 'a ']);
        print_r($res);
        $this->assertSame(
            [
                [
                    'value' => 'banana',
                    'ngrams_hit' => [
                        ['value' => ' b', 'pos' => '0'],
                        ['value' => 'ba', 'pos' => '1'],
                        ['value' => 'an', 'pos' => '2,4'],
                        ['value' => 'na', 'pos' => '3,5'],
                        ['value' => 'a ', 'pos' => '6'],   
                    ],
                    'indexed_at' => '1461094800'
                ]
            ],
            $res
        );    
    }
}
