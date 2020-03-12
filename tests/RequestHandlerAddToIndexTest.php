<?php
use PHPUnit\Framework\TestCase;
 
class RequestHandlerAddToIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/add_to_index.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testAddToIndex() : void
    {
        generateTestData('MyIndex', [], []);
        $payload = json_decode('{"key":"foo","value":"bar"}');
        ob_start();
        add_to_index(['index_name'=>'MyIndex'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);
    }
}