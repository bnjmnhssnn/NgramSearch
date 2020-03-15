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


    public function testRun() : void
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
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'bc', 'pos' => '2'],
                        ['value' => 'cd', 'pos' => '3'],
                        ['value' => 'd ', 'pos' => '4']
                    ]
                ],
                [
                    'id' => 2,
                    'key' => 'abc',
                    'value' => 'bar',
                    'ngrams_hit' => 3,
                    'ngram_details' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1'],
                        ['value' => 'bc', 'pos' => '2']
                    ]
                ],
                [
                    'id' => 3,
                    'key' => 'ab',
                    'value' => 'baz',
                    'ngrams_hit' => 2,
                    'ngram_details' => [
                        ['value' => ' a', 'pos' => '0'],
                        ['value' => 'ab', 'pos' => '1']
                    ]
                ],
            ],
            json_decode(json_encode($output->data), true)
        ); 
    }
}