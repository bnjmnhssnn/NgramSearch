<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\DropIndex\run;
 
class RequestHandlerDropIndexTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/DropIndex.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testDummy() : void
    {
        $payload = json_decode('{}');
        ob_start();
        run([], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 500 Internal Server Error', $GLOBALS['phpunit_header_jar']);
    }
}