<?php
namespace NgramSearch;
use NgramSearch\StorageAdapter\StorageAdapterInterface;

class NgramIndex
{
    public function __construct(string $name, StorageAdapterInterface $storage_adapter)
    {
        if(!$storage_adapter->indexExists($name)) {
            throw new \InvalidArgumentException('Index \'' . $name . '\' not found.');
        }
        $this->name = $name;
        $this->storage_adapter = $storage_adapter;
    }

    public function query(array $ngrams, int $min_hits = NULL) : array
    {
        $raw_counts = array_count_values(array_reduce(
            $ngrams,
            function($carry, $ngram) {
                return array_merge(
                    $carry, 
                    $this->storage_adapter->getNgramData($this->name, $ngram)
                );
            },
            []   
        ));
        arsort($raw_counts);
        $return_arr = [];
        foreach($raw_counts as $key => $count) {
            if($min_hits !== NULL && $count < $min_hits) {
                continue;
            }
            $parts = explode('|', $key);
            $return_arr[] = [
                'value' => $parts[0],
                'ngrams_hit' => $this->hitStats($ngrams, $parts[0]),
                'indexed_at' => $parts[1]
            ];
        }
        return $return_arr;    
    }

    protected function hitStats(array $search_ngrams, string $raw_value)
    {
        $value_ngrams = Ngrams::extract(Preparer::get($raw_value, false), false);
        return array_map(
            function ($item) use ($value_ngrams) {
                return [
                    'value' => $item,
                    'pos' => join(',', array_keys($value_ngrams, $item))
                ];
            },    
            array_values(array_intersect($search_ngrams, $value_ngrams))
        );
    }
}