<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\QueryIndex\run;
 
class RequestHandlerQueryIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/QueryIndex.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testRunOnNotExistingIndex() : void
    {
        ob_start();
        run(['index_name' => 'DoesNotExist', 'query_string' => 'foo']);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }


    public function testRunBasics() : void
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
        ob_start();
        run(['index_name' => 'MyIndex', 'query_string' => 'abcd']);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('data', $output);
        $this->assertObjectHasAttribute('meta', $output);
        $this->assertObjectHasAttribute('links', $output);
        $this->assertSame(
            [
                [
                    'id' => 1,
                    'key' => 'abcd',
                    'value' => 'foo',
                    'ngrams_hit' => 5,
                    'ngram_details' => [
                        ['value' => ' a', 'pos_in_key' => '0', 'pos_in_search' => '0'],
                        ['value' => 'ab', 'pos_in_key' => '1', 'pos_in_search' => '1'],
                        ['value' => 'bc', 'pos_in_key' => '2', 'pos_in_search' => '2'],
                        ['value' => 'cd', 'pos_in_key' => '3', 'pos_in_search' => '3'],
                        ['value' => 'd ', 'pos_in_key' => '4', 'pos_in_search' => '4']
                    ]
                ],
                [
                    'id' => 2,
                    'key' => 'abc',
                    'value' => 'bar',
                    'ngrams_hit' => 3,
                    'ngram_details' => [
                        ['value' => ' a', 'pos_in_key' => '0', 'pos_in_search' => '0'],
                        ['value' => 'ab', 'pos_in_key' => '1', 'pos_in_search' => '1'],
                        ['value' => 'bc', 'pos_in_key' => '2', 'pos_in_search' => '2']
                    ]
                ],
                [
                    'id' => 3,
                    'key' => 'ab',
                    'value' => 'baz',
                    'ngrams_hit' => 2,
                    'ngram_details' => [
                        ['value' => ' a', 'pos_in_key' => '0', 'pos_in_search' => '0'],
                        ['value' => 'ab', 'pos_in_key' => '1', 'pos_in_search' => '1']
                    ]
                ],
            ],
            json_decode(json_encode($output->data), true)
        ); 
    }

    public function testRunMultipleNgramPositions() : void
    {
        $test_ngram_data = [
            ' b' => [1],
            'ba' => [1],
            'an' => [1],
            'na' => [1],
            'a ' => [1]
        ];
        $test_key_value_pairs = [
            'banana;foo',
        ];
        generateTestData('MyIndex', $test_ngram_data, $test_key_value_pairs);
        ob_start();
        run(['index_name' => 'MyIndex', 'query_string' => 'banana']);
        $output = json_decode(ob_get_clean());
        $this->assertSame(
            [
                [
                    'id' => 1,
                    'key' => 'banana',
                    'value' => 'foo',
                    'ngrams_hit' => 5,
                    'ngram_details' => [
                        ['value' => ' b', 'pos_in_key' => '0', 'pos_in_search' => '0'],
                        ['value' => 'ba', 'pos_in_key' => '1', 'pos_in_search' => '1'],
                        ['value' => 'an', 'pos_in_key' => '2,4', 'pos_in_search' => '2,4'],
                        ['value' => 'na', 'pos_in_key' => '3,5', 'pos_in_search' => '3,5'],
                        ['value' => 'a ', 'pos_in_key' => '6', 'pos_in_search' => '6']
                    ]
                ]
            ],
            json_decode(json_encode($output->data), true)
        );
    }

    public function testRunMixedNgramPositions() : void
    {
        $test_ngram_data = [
            ' f' => [1],
            'fo' => [1],
            'oo' => [1],
            'o ' => [1],
            ' b' => [1],
            'ba' => [1],
            'ar' => [1],
            'r ' => [1],

        ];
        $test_key_value_pairs = [
            'foo bar;baz',
        ];
        generateTestData('MyIndex', $test_ngram_data, $test_key_value_pairs);
        ob_start();
        run(['index_name' => 'MyIndex', 'query_string' => 'bar foo']);
        $output = json_decode(ob_get_clean());
        $this->assertSame(
            [
                [
                    'id' => 1,
                    'key' => 'foo bar',
                    'value' => 'baz',
                    'ngrams_hit' => 8,
                    'ngram_details' => [
                        ['value' => ' f', 'pos_in_key' => '0', 'pos_in_search' => '4'],
                        ['value' => 'fo', 'pos_in_key' => '1', 'pos_in_search' => '5'],
                        ['value' => 'oo', 'pos_in_key' => '2', 'pos_in_search' => '6'],
                        ['value' => 'o ', 'pos_in_key' => '3', 'pos_in_search' => '7'],
                        ['value' => ' b', 'pos_in_key' => '4', 'pos_in_search' => '0'],
                        ['value' => 'ba', 'pos_in_key' => '5', 'pos_in_search' => '1'],
                        ['value' => 'ar', 'pos_in_key' => '6', 'pos_in_search' => '2'],
                        ['value' => 'r ', 'pos_in_key' => '7', 'pos_in_search' => '3']
                    ]
                ]
            ],
            json_decode(json_encode($output->data), true)
        );

    }
}