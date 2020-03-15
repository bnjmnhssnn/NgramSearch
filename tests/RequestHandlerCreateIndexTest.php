<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\CreateIndex\run;
 
class RequestHandlerCreateIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/CreateIndex.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testMissingParam() : void
    {
        
        $payload = json_decode('{}');
        ob_start();
        run([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }

    public function testCreateIndexInvalidName() : void
    {
        $payload = json_decode(json_encode(['index_name' => 'Contains whitespace']));
        ob_start();
        run([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);

        $payload = json_decode(json_encode(['index_name' => 'A_toooooooooooooooooooooooooo_long_index_name']));
        ob_start();
        run([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }

    public function testCreateIndex() : void
    {
        $payload = json_decode(json_encode(['index_name' => 'FooIndex']));
        ob_start();
        run([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 201 Created', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);    
    }
}