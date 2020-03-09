<?php
use PHPUnit\Framework\TestCase;
 
class RequestHandlerQueryIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/query_index.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testQueryNotExistingIndex() : void
    {
        ob_start();
        query_index(['index_name' => 'DoesNotExist', 'query_string' => 'foo']);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }


    public function testQueryIndex() : void
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
        ob_start();
        query_index(['index_name' => 'MyIndex', 'query_string' => 'abcd']);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('data', $output);
        $this->assertObjectHasAttribute('meta', $output);
        $this->assertObjectHasAttribute('links', $output);
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
            json_decode(json_encode($output->data), true)
        ); 
    }

    /**
     * @depends testQueryIndex
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
        ob_start();
        query_index(['index_name' => 'MyIndex', 'query_string' => 'abXde']);
        $output = json_decode(ob_get_clean());
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
            json_decode(json_encode($output->data), true)
        );    
    }

    /**
     * @depends testQueryIndex
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
        ob_start();
        query_index(['index_name' => 'MyIndex', 'query_string' => 'abcde']);
        $output = json_decode(ob_get_clean());
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
            json_decode(json_encode($output->data), true)
        );    
    }
}