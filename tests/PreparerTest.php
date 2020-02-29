<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\Preparer;
 
class PreparerTest extends TestCase {

    public function testBasicUsage() : void
    {
        $this->assertSame(['Abcdefghijklm', 'nopqrstuvwxyz'], Preparer::get('Abcdefghijklm nopqrstuvwxyz'));
    } 

    public function testWhitespaceRemoval() : void
    {
        $this->assertSame(['Foo', 'Bar'], Preparer::get(' Foo   Bar   '));
    } 

    public function testSpecialCharsRemoval() : void
    {
        $this->assertSame(['Foobar'], Preparer::get('%F?o!o(b)a§r"'));
    }

    public function testAccentedCharsReplacement() : void
    {
        $this->assertSame(
            ['Citroen', 'Loreal', 'Slavoj', 'Zizek', 'Smorrebrod'], 
            Preparer::get('Citroên Loréal Slavoj Žižek Smørrebrød')
        );
    }

    public function testGermanUmlautPreservation() : void
    {
        $this->assertSame(['ÄÖÜ', 'äöü', 'ß'], Preparer::get('ÄÖÜ äöü ß'));
    }
    


    


    
}
