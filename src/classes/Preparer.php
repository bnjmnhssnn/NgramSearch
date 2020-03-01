<?php
namespace NgramSearch;

class Preparer
{
    public static function get(string $string, bool $as_array = true) : array
    {
        $string = self::replaceAccentedChars($string);
        $word_separators = ['-', '.', ',', '&', '+', '_'];
        $string = self::removeSpecialChars($string, $word_separators);
        $delimiter = '/';
        $split_regex = $delimiter . '[\s' . join(
            array_map(
                function($item) use ($delimiter) {
                    return preg_quote($item, $delimiter);
                },
                $word_separators
            )
        ) . ']+' . $delimiter;
        $prepared_words_arr = array_values(array_filter(preg_split($split_regex, $string)));
        if($as_array) {
            return $prepared_words_arr; 
        } else {
            return join(' ', $prepared_words_arr); 
        }
    }

    protected static function removeSpecialChars(string $string, array $preserve = []) : string
    {
        $delimiter = '/';
        $regex = $delimiter . '[^\s\wÄäÖöÜüß' . join(
            array_map(
                function($item) use ($delimiter) {
                    return preg_quote($item, $delimiter);
                },
                $preserve
            )
        ) . ']' .$delimiter;
        return preg_replace($regex, '', $string);
    }

    protected static function replaceAccentedChars(string $string) : string
    {
        $accent_chars_and_replacements = [
            'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E','Ê'=>'E', 
            'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 
            'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'à'=>'a', 'á'=>'a', 'â'=>'a', 
            'ã'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 
            'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ø'=>'o', 'ù'=>'u', 
            'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
        ];
        $patterns = array_map(
            function($item) {
                return '/' . $item . '/';
            },
            array_keys($accent_chars_and_replacements)
        ); 
        $replacements = array_values($accent_chars_and_replacements);

        return preg_replace($patterns, $replacements, $string);
    }
}