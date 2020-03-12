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

    public function query(string $query_string, int $max_count = NULL, int $min_hits = NULL) : array
    {
        $ngrams = Ngrams::extract(Preparer::get($query_string, false));
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
        foreach($raw_counts as $id => $count) {
            if($min_hits !== NULL && $count < $min_hits) {
                continue;
            }
            $key_value_pair = $this->storage_adapter->getKeyValuePair($this->name, $id);
            $return_arr[] = [
                'id' => $id,
                'key' => $key_value_pair[0],
                'value' => $key_value_pair[1],
                'ngrams_hit' => $count
            ];
            if($max_count !== NULL && count($return_arr) === $max_count) {
                return $return_arr;
            }
        }
        return $return_arr;
    }
}