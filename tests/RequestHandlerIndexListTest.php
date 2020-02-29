<?php
use PHPUnit\Framework\TestCase;
 
class RequestHandlerIndexListTest extends TestCase {

    public function testIndexListIsEmpty(){
        require __DIR__ .'/../src/request_handlers/index_list.php';
        index_list();
        $this->assertContains('Content-type: application/json', $GLOBALS['phpunit_header_jar']);
        $this->expectOutputString('{"available_indexes":[]}');     
    }
}