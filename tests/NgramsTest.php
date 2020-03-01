<?php
use PHPUnit\Framework\TestCase;
use NgramSearch\Ngrams;
 
class NgramsTest extends TestCase {

    public function testGetValidNgrams() : void
    {
        $ngrams = Ngrams::validNgrams();
        $this->assertIsArray($ngrams);
        $this->assertFalse(
            in_array(
                false,
                array_map(
                    function($item) {
                        return (is_string($item) && mb_strlen($item) == 2);   
                    },
                    $ngrams
                )
            )
        );
    }

    /**
     * @depends testGetValidNgrams
     */
    public function testNgramExtraction() : void
    {
        $this->assertSame([' a', 'a '], Ngrams::extract('a'));
        $this->assertSame([' a', 'ab', 'b '], Ngrams::extract('ab'));
        $this->assertSame([' a', 'ab', 'bc', 'c '], Ngrams::extract('abc'));
        $this->assertSame([' a', 'ab', 'bc', 'cd', 'd '], Ngrams::extract('abcd'));

        $this->assertSame(
            [' o', 'on', 'ne', 'e ', ' t', 'tw', 'wo', 'o '], 
            Ngrams::extract('one two')
        );
    }  

    /**
     * @depends testNgramExtraction
     */
    public function throwExceptionWhenCharIsDisallowed() : void
    {
        $this->expectException(InvalidArgumentException::class);
        Ngrams::extract('äüöê');
    }
}