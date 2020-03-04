<?php
namespace NgramSearch;

use Ngrams\Preparer as Preparer;

class Ngrams
{
    public static function validNgrams() : array
    {
        $alphabet = range('a', 'z');
        $numbers = range('0', '9');
        $special_chars = ['ä', 'ö', 'ü', 'ß', ' '];
        $base = array_merge($alphabet, $numbers, $special_chars);
      
        return array_reduce(
            $base, 
            function($carry, $item) use ($base) {
                return array_merge(
                    $carry, 
                    array_map(
                        function($a_item) use ($item) {
                            return $item . $a_item;
                        }, 
                        $base
                    )
                );
            }, 
            []
        );  
    }

    public static function extract(string $prepared_string) : array
    {
        $chars = preg_split(
            '//u', 
            mb_strtolower($prepared_string), 
            -1, 
            PREG_SPLIT_NO_EMPTY
        );
        array_push($chars, ' ');
        array_unshift($chars, ' ');
        $valid_ngrams = self::validNgrams();
        $res = [];
        for($i = 0; $i < count($chars) - 1; $i++) {
            $ngram = $chars[$i] . $chars[$i + 1]; 
            if(in_array($ngram, $valid_ngrams)) {    
                $res[] = $ngram; 
            } else {
                throw new \InvalidArgumentException('Ngram \'' . $ngram . '\' contains at least one disallowed char.');
            }
        }
        return array_unique($res);
    }
}