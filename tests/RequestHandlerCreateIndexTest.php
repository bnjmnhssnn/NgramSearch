<?php
use PHPUnit\Framework\TestCase;
 
class RequestHandlerCreateIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testCreateIndexMissingParam() : void
    {
        require __DIR__ .'/../src/request_handlers/create_index.php';
        $payload = json_decode('{}');
        ob_start();
        create_index([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);
    }

    public function testCreateIndexInvalidName() : void
    {
        require __DIR__ .'/../src/request_handlers/create_index.php';

        $payload = json_decode(json_encode(['index_name' => 'Contains whitespace']));
        ob_start();
        create_index([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);

        $payload = json_decode(json_encode(['index_name' => 'A_veeeeeeeeeeeeeeeeeeeery_long_index_name']));
        ob_start();
        create_index([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);
    }

    public function testCreateIndex() : void
    {
        require __DIR__ .'/../src/request_handlers/create_index.php';
        $payload = json_decode(json_encode(['index_name' => 'FooIndex']));
        ob_start();
        create_index([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 201 Created', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('msg', $output);
        $this->assertIsString($output->msg);    
    }


}