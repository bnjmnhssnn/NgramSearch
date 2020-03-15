<?php
use PHPUnit\Framework\TestCase;
use function NgramSearch\RequestHandler\IndexList\run;
 
class RequestHandlerIndexListTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        require_once __DIR__ .'/../src/request_handlers/IndexList.php';
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }
    
    public function testRunNoIndexes() : void
    {
        ob_start();
        run();
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('data', $output);
        $this->assertObjectHasAttribute('links', $output); 
    }
}