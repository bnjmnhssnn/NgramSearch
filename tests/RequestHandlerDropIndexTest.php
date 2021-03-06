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

    public function testRunIndexDoesNotExist() : void
    {
        $payload = json_decode('{}');
        ob_start();
        run(['index_name' => 'WrongIndexName'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 400 Bad Request', $GLOBALS['phpunit_header_jar']);
        $this->assertContains('Content-type: application/vnd.api+json', $GLOBALS['phpunit_header_jar']);
        $this->assertObjectHasAttribute('errors', $output);
    }

    public function testRun() : void
    {
        generateTestData('MyIndex', [], []);
        $payload = json_decode('{}');
        ob_start();
        run(['index_name' => 'MyIndex'], $payload);
        $output = json_decode(ob_get_clean());
        $this->assertContains('HTTP/1.1 204 No Content', $GLOBALS['phpunit_header_jar']);
    }
}