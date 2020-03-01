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
        index_list();
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->expectOutputString('{"available_indexes":[]}');     
    }
}