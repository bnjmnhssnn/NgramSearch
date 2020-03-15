<?php
namespace NgramSearch;

class RequestValidator
{
    public $checks = [];

    public function addGetCheck($value, $validation_func, string $detail, string $source) : self
    {
        return $this->addCheck('GET', $value, $validation_func, $detail, $source);
    }

    public function addPostCheck($value, $validation_func, string $detail, string $source) : self
    {
        return $this->addCheck('POST', $value, $validation_func, $detail, $source);
    }

    protected function addCheck(string $type, $value, $validation_func, string $detail, string $source) : self
    {
        $this->checks[] = [
            'type' => $type,
            'value' => $value,
            'validation_func' => $validation_func,
            'detail' => $detail,
            'source' => $source
        ];
        return $this;
    }

    protected function previousErrorExists(array $stored_errors, string $type, string $source) : bool 
    {
        switch ($type) {
            case 'GET':
                foreach ($stored_errors as $error) {
                    if (isset($error['source']['parameter']) && $error['source']['parameter'] === $source) {
                        return true;
                    }
                }
                return false;
            case 'POST':
                foreach ($stored_errors as $error) {
                    if (isset($error['source']['pointer']) && $error['source']['pointer'] === $source) {
                        return true;
                    }
                }
                return false;
        }  
    }

    public function execute() : array
    {
        return array_reduce(
            $this->checks,
            function($errors, $check) {
                if ($this->previousErrorExists($errors, $check['type'], $check['source'])) {
                    return $errors;
                }
                if (!is_callable($check['validation_func'])) {
                    throw new \InvalidArgumentException('Validation callback is not callable.');
                }
                $res = $check['validation_func']($check['value']);
                if (!is_bool($res)) {
                    throw new \InvalidArgumentException('The validation callback must return a boolean.');
                }
                if (!$res) {
                    // Generate a json:api error object
                    // https://jsonapi.org/examples/#error-objects
                    $error = [
                        'status' => 400, // Bad Request
                        'detail' => $check['detail']
                    ];
                    switch ($check['type']) {
                        case 'GET':
                            $errors[] = [
                                'status' => 400, // Bad Request
                                'title' => 'Invalid Query Parameter',
                                'detail' => $check['detail'],
                                'source' => [
                                    'parameter' => $check['source']
                                ]   
                            ];
                        break;
                        case 'POST':
                            $errors[] = [
                                'status' => 400, // Bad Request
                                'title' => 'Invalid Request Body',
                                'detail' => $check['detail'],
                                'source' => [
                                    'pointer' => $check['source']
                                ]   
                            ];
                        break;      
                    }
                }
                return $errors;
            },
            []
        );

    }
}