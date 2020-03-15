<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\AddToIndex\run;

class RequestHandlerAddToIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/AddToIndex.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testRunWithMissingParam() : void
    {
        generateTestData('MyIndex', [], []);
        $payload = json_decode('{"key":"foo"}');
        ob_start();
        run(['index_name'=>'MyIndex'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);

        $payload = json_decode('{"value":"bar"}');
        ob_start();
        run(['index_name'=>'MyIndex'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }

    public function testRun() : void
    {
        generateTestData('MyIndex', [], []);
        $payload = json_decode('{"key":"foo","value":"bar"}');
        ob_start();
        run(['index_name'=>'MyIndex'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);
    }
}