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
        
        $_GET['query_string'] = 'foo';
        ob_start();
        query_index(['index_name' => 'DoesNotExist']);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);
    }


    public function testQueryIndex() : void
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
        $_GET['query_string'] = 'abcd';
        ob_start();
        query_index(['index_name' => 'MyIndex']);
        $output = json_decode(ob_get_clean());

        print_r($output);
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('stats', $output);
        $this->assertObjectHasAttribute('query_result', $output);

    }
}