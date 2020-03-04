<?php
use PHPUnit\Framework\TestCase;
 
class RequestHandlerIndexListTest extends TestCase {

    public static function setupBeforeClass() : void
    {
        cleandir(STORAGE_PATH);
    } 

    public static function tearDownAfterClass() : void
    {
        cleandir(STORAGE_PATH);
    }
    
    public function testIndexListIsEmpty(){
        require __DIR__ .'/../src/request_handlers/index_list.php';
        ob_start();
        index_list();
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 200 OK', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('data', $output);
        $this->assertObjectHasAttribute('links', $output); 
    }
}