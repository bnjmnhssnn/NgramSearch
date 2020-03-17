<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\IndexInfo\run;
 
class RequestHandlerIndexInfoTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/IndexInfo.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }

    public function testDummy() : void
    {
        ob_start();
        run([]);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 500 Internal Server Error', $GLOBALS['phpunit_header_jar']);
    }
}